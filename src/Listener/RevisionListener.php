<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostFlushEventArgs;
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
        private LoggerInterface $logger = new NullLogger(),
        private CacheItemPoolInterface $is_revision_cache = new ArrayAdapter()
    ) {
    }

    /**
     * Event is used to remove the previous revision
     *
     * Used to group all entities to the same revision
     * in the same flush if they use #[Revision]. This method
     * can safely be overwritten if you prefer a Revision
     * per Request.
     */
    public function postFlush(PostFlushEventArgs $event): void
    {
        $this->revision = null;
    }

    public function entityChanged(EntityChangedEvent $event): void
    {
        if (!$this->shouldBePersisted($event)) {
            return;
        }

        if (null === $this->revision) {
            $this->revision = $this->factory->createRevision(new \DateTime());
        }

        $event->getEntityManager()->persist($this->revision);

        $entity = $event->getCurrentEntity();
        $entity->setRevision($this->revision);

        $this->logger->info('Added revision for entity {entity}', ['entity' => $entity::class]);
    }

    /**
     * Checks if the current entity is eligable for a revision.
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
        if (count(array_intersect($fields, $event->getMutatedFields())) === 0) {
            return false;
        }

        return true;
    }

    private function isRevision(EntityManagerInterface $em, object $entity): bool
    {
        $cache_key   = base64_encode('REVISION-' . $entity::class);
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

        return $this->save($cached_item, false);
    }

    private function save(CacheItemInterface $item, bool $value): bool
    {
        $item->set($value);
        $this->is_revision_cache->save($item);

        return $value;
    }
}
