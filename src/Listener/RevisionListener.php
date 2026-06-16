<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Listener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Hostnet\Component\EntityRevision\Factory\RevisionFactoryInterface;
use Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface;
use Hostnet\Component\EntityRevision\RevisionableInterface;
use Hostnet\Component\EntityRevision\RevisionInterface;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class RevisionListener
{
    private ?RevisionInterface $revision = null;

    public function __construct(
        private RevisionResolverInterface $resolver,
        private RevisionFactoryInterface $factory,
        private ?LoggerInterface $logger = null,
        private ?CacheItemPoolInterface $is_revision_cache = new ArrayAdapter()
    ) {
        $this->logger = $logger ?: new NullLogger();
    }

    /**
     * Event is used to create a new revision
     *
     * Used to group all entities to the same revision
     * in the same flush if they use @Revision
     *
     * @deprecated functionality was moved to entityChanged and postFlush, will be removed when removing
     *             doctrine/annotations.
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event): void
    {
        trigger_error(__METHOD__ . ' is deprecated, please remove it from your event listener.', E_USER_DEPRECATED);
        $this->revision = $this->factory->createRevision(new \DateTime());
    }

    /**
     * Event is used to remove the previous revision
     *
     * Used to group all entities to the same revision
     * in the same flush if they use @Revision. This method
     * can safely be overwritten if you prefer a Revision
     * per Request.
     */
    public function postFlush(PostFlushEventArgs $event): void
    {
        $this->revision = null;
    }

    /**
     * @param EntityChangedEvent $event
     */
    public function entityChanged(EntityChangedEvent $event): void
    {
        if (!$this->shouldBePersisted($event)) {
            return;
        }

        if (null === $this->revision) {
            $this->revision = $this->factory->createRevision(new \DateTime());
        }

        if (null === $this->revision) {
            throw new \RuntimeException('No Revision set for current flush.');
        }

        $event->getEntityManager()->persist($this->revision);

        $entity = $event->getCurrentEntity();
        $entity->setRevision($this->revision);

        $this->logger->info(sprintf('Added revision for entity', ['entity' => get_class($entity)]));
    }

    /**
     * Checks if the current entity is eligable for a revision.
     *
     * @param EntityChangedEvent $event
     */
    private function shouldBePersisted(EntityChangedEvent $event): bool
    {
        $entity = $event->getCurrentEntity();
        $em     = $event->getEntityManager();

        if (!$this->isRevision($em, $entity)) {
            return false;
        }

        $fields = $this->resolver->getRevisionableFields($em, $entity);

        // only create a revision if the mutated fields are tracked
        if (count(array_intersect($fields, $event->getMutatedFields())) == 0) {
            return false;
        }

        return true;
    }

    private function isRevision($em, $entity): bool
    {
        $cache_key   = base64_encode('REVISION-' . get_class($entity));
        $cached_item = $this->is_revision_cache->getItem($cache_key);

        if ($cached_item->isHit()) {
            return $cached_item->get();
        }

        if (!($entity instanceof RevisionableInterface)) {
            return $this->save($cached_item, false);
        }

        if (null !== $this->resolver->getRevisionAttribute($em, $entity)) {
            return $this->save($cached_item, true);
        }

        if (null !== $this->resolver->getRevisionAnnotation($em, $entity)) {
            return $this->save($cached_item, true);
        }

        return $this->save($cached_item, false);
    }

    private function save(CacheItemInterface $item, bool $value): bool
    {
        $item->set($value);
        $this->is_revision_cache->save($item);

        return $value;
    }
}
