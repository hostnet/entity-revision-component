<?php
namespace Hostnet\Component\EntityRevision\Listener;

use Hostnet\Component\EntityRevision\Revision;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;

/**
 * @covers Hostnet\Component\EntityRevision\Listener\RevisionListener
 */
class RevisionListenerTest extends \PHPUnit_Framework_TestCase
{
    private $em;
    private $factory;
    private $entity;
    private $revision;
    private $resolver;

    public function setUp()
    {
        $revision_loc   = 'Hostnet\Component\EntityRevision';
        $this->em       = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $this->factory  = $this->getMock($revision_loc . '\Factory\RevisionFactoryInterface');
        $this->resolver = $this->getMock($revision_loc . '\Resolver\RevisionResolverInterface');
        $this->entity   = $this->getMock($revision_loc . '\RevisionableInterface');
        $this->revision = $this->getMock($revision_loc . '\RevisionInterface');
    }

    public function testPreFlush()
    {
        $this->factory
            ->expects($this->once())
            ->method('createRevision');

        $doctrine_event = $this
            ->getMockBuilder('Doctrine\ORM\Event\PreFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->preFlush($doctrine_event);
    }

    public function testOnEntityChangedNoInterface()
    {
        $this->resolver
            ->expects($this->never())
            ->method('getRevisionableFields');

        $event    = new EntityChangedEvent($this->em, new \stdClass(), $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);
    }

    public function testOnEntityChangedNoAnnotation()
    {
        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);
    }

    public function testOnEntityChangedNoRevisionFields()
    {
        $this->resolver
            ->expects($this->once())
            ->method('getRevisionAnnotation')
            ->willReturn(new Revision());

        $this->resolver
            ->expects($this->once())
            ->method('getRevisionableFields')
            ->willReturn([]);

        $this->factory
            ->expects($this->never())
            ->method('never');

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);
    }

    public function testOnEntityChangedNoTrackedMutations()
    {
        $this->resolver
            ->expects($this->exactly(2))
            ->method('getRevisionAnnotation')
            ->willReturn(new Revision());

        $this->resolver
            ->expects($this->exactly(2))
            ->method('getRevisionableFields')
            ->willReturn(['something']);

        $this->factory
            ->expects($this->never())
            ->method('createRevision');

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, ['created_at']);
        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOnEntityChangedNoRevision()
    {
        $history = new Revision();

        $this->resolver
            ->expects($this->once())
            ->method('getRevisionAnnotation')
            ->willReturn($history);

        $this->resolver
            ->expects($this->once())
            ->method('getRevisionableFields')
            ->willReturn(['something']);

        $this->factory
            ->expects($this->never())
            ->method('createRevision');

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, ['something']);
        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);
    }

    public function testOnEntityChangedFullPath()
    {
        $history = new Revision();
        $this->resolver
            ->expects($this->once())
            ->method('getRevisionAnnotation')
            ->willReturn($history);

        $this->resolver
            ->expects($this->once())
            ->method('getRevisionableFields')
            ->willReturn(['something']);

        $this->factory
            ->expects($this->once())
            ->method('createRevision')
            ->willReturn($this->revision);

        $this->em
            ->expects($this->once())
            ->method('persist')
            ->with($this->revision);

        $this->entity
            ->expects($this->once())
            ->method('setRevision')
            ->with($this->revision);

        $event          = new EntityChangedEvent($this->em, $this->entity, $this->entity, ['something']);
        $doctrine_event = $this
            ->getMockBuilder('Doctrine\ORM\Event\PreFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->preFlush($doctrine_event);
        $listener->entityChanged($event);
    }
}
