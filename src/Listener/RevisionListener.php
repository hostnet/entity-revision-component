<?php
namespace Hostnet\Component\EntityRevision\Listener;

use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Hostnet\Component\EntityRevision\Factory\RevisionFactoryInterface;
use Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface;
use Hostnet\Component\EntityRevision\RevisionableInterface;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
class RevisionListener
{
    /**
     * @var RevisionResolverInterface
     */
    private $resolver;

    /**
     * @var RevisionFactoryInterface
     */
    private $factory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Revision
     */
    private $revision;

    /**
     * @param RevisionResolverInterface $resolver
     * @param RevisionFactoryInterface  $factory
     * @param LoggerInterface           $logger
     */
    public function __construct(
        RevisionResolverInterface $resolver,
        RevisionFactoryInterface  $factory,
        LoggerInterface           $logger = null
    ) {
        $this->resolver = $resolver;
        $this->factory  = $factory;
        $this->logger   = $logger ?: new NullLogger();
    }

    /**
     * Event is used to create a new revision
     *
     * Used to group all entities to the same revision
     * in the same flush if they use @Revision
     *
     * @deprecated functionality was moved to entityChanged and postFlush
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event)
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
     *
     * @param PreFlushEventArgs $event
     */
    public function postFlush(PostFlushEventArgs $event)
    {
        $this->revision = null;
    }

    /**
     * @param EntityChangedEvent $event
     */
    public function entityChanged(EntityChangedEvent $event)
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
    private function shouldBePersisted(EntityChangedEvent $event)
    {
        if (!($entity = $event->getCurrentEntity()) instanceof RevisionableInterface) {
            return false;
        }

        $em = $event->getEntityManager();

        if (null === $this->resolver->getRevisionAnnotation($em, $entity)) {
            return false;
        }

        $fields = $this->resolver->getRevisionableFields($em, $entity);

        // only create a revision if the mutated fields are tracked
        if (count(array_intersect($fields, $event->getMutatedFields())) == 0) {
            return false;
        }

        return true;
    }
}
