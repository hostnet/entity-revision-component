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
        $this->em       = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $this->factory  = $this->createMock($revision_loc . '\Factory\RevisionFactoryInterface');
        $this->resolver = $this->createMock($revision_loc . '\Resolver\RevisionResolverInterface');
        $this->entity   = $this->createMock($revision_loc . '\RevisionableInterface');
        $this->revision = $this->createMock($revision_loc . '\RevisionInterface');
        $this->logger   = $this->createMock('Psr\Log\LoggerInterface');
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

        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
        $listener->preFlush($doctrine_event);
    }

    public function testOnEntityChangedNoInterface()
    {
        $this->resolver
            ->expects($this->never())
            ->method('getRevisionableFields');

        $event    = new EntityChangedEvent($this->em, new \stdClass(), $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
        $listener->entityChanged($event);
    }

    public function testOnEntityChangedNoAnnotation()
    {
        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
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
            ->method('createRevision');

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, []);
        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
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
        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
        $listener->entityChanged($event);

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, ['created_at']);
        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
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
            ->expects($this->once())
            ->method('createRevision')
            ->willReturn(null);

        $event    = new EntityChangedEvent($this->em, $this->entity, $this->entity, ['something']);
        $listener = new RevisionListener($this->resolver, $this->factory, $this->logger);
        $listener->entityChanged($event);
    }

    public function testOnEntityChanged()
    {
        $r1 = $this->createMock('Hostnet\Component\EntityRevision\RevisionInterface');
        $r2 = $this->createMock('Hostnet\Component\EntityRevision\RevisionInterface');

        $history = new Revision();
        $this->resolver
            ->expects($this->any())
            ->method('getRevisionAnnotation')
            ->willReturn($history);

        $this->resolver
            ->expects($this->any())
            ->method('getRevisionableFields')
            ->willReturn(['something']);

        $this->factory
            ->expects($this->exactly(2))
            ->method('createRevision')
            ->willReturnOnConsecutiveCalls($r1, $r2);

        $this->em
            ->expects($this->any())
            ->method('persist')
            ->withConsecutive([$this->identicalTo($r1)], [$this->identicalTo($r2)]);

        $this->entity
            ->expects($this->exactly(3))
            ->method('setRevision')
            ->withConsecutive([$this->identicalTo($r1)], [$this->identicalTo($r2)], [$this->identicalTo($r2)]);

        $event          = new EntityChangedEvent($this->em, $this->entity, $this->entity, ['something']);
        $doctrine_event = $this
            ->getMockBuilder('Doctrine\ORM\Event\PostFlushEventArgs')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new RevisionListener($this->resolver, $this->factory);
        $listener->entityChanged($event);
        $listener->postFlush($doctrine_event);
        $listener->entityChanged($event);
        $listener->entityChanged($event);
    }
}
