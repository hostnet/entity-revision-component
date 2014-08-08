<?php
namespace Hostnet\Component\EntityRevision\Listener;

use Doctrine\ORM\Event\PreFlushEventArgs;
use Hostnet\Component\EntityRevision\Factory\RevisionFactoryInterface;
use Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface;
use Hostnet\Component\EntityRevision\RevisionableInterface;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;

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
     * @var Revision
     */
    private $revision = null;

    /**
     * @param RevisionResolverInterface $resolver
     * @param RevisionFactoryInterface  $factory
     */
    public function __construct(RevisionResolverInterface $resolver, RevisionFactoryInterface $factory)
    {
        $this->resolver = $resolver;
        $this->factory  = $factory;
    }

    /**
     * Event is used to create a new revision
     *
     * Used to group all entities to the same revision
     * in the same flush if they use @Revision
     *
     * @param PreFlushEventArgs $event
     */
    public function preFlush(PreFlushEventArgs $event)
    {
        $this->revision = $this->factory->createRevision(new \DateTime());
    }

    /**
     * @param EntityChangedEvent $event
     */
    public function onEntityChanged(EntityChangedEvent $event)
    {
        if (!($entity = $event->getCurrentEntity()) instanceof RevisionableInterface) {
            return;
        }

        $em = $event->getEntityManager();

        if (null === $this->resolver->getRevisionAnnotation($em, $entity)) {
            return;
        }

        $fields = $this->resolver->getRevisionableFields($em, $entity);

        // no revisionable fields found
        if (empty($fields)) {
            return;
        }

        // only create a revision if the mutated fields are tracked
        if (!count(array_intersect($fields, $event->getMutatedFields()))) {
            return;
        }

        if ($this->revision === null) {
            throw new \RuntimeException('No Revision set for current flush.');
        }

        $em->persist($this->revision);

        // set the revision
        $entity->setRevision($this->revision);
    }
}
