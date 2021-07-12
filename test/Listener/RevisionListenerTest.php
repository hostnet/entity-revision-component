<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Listener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Hostnet\Component\EntityRevision\Factory\RevisionFactoryInterface;
use Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface;
use Hostnet\Component\EntityRevision\Revision;
use Hostnet\Component\EntityRevision\RevisionableInterface;
use Hostnet\Component\EntityRevision\RevisionInterface;
use Hostnet\Component\EntityTracker\Event\EntityChangedEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Hostnet\Component\EntityRevision\Listener\RevisionListener
 */
class RevisionListenerTest extends TestCase
{
    private $em;
    private $factory;
    private $entity;
    private $revision;
    private $resolver;

    public function setUp(): void
    {
        $this->em       = $this->createMock(EntityManagerInterface::class);
        $this->factory  = $this->createMock(RevisionFactoryInterface::class);
        $this->resolver = $this->createMock(RevisionResolverInterface::class);
        $this->entity   = $this->createMock(RevisionableInterface::class);
        $this->revision = $this->createMock(RevisionInterface::class);
        $this->logger   = $this->createMock(LoggerInterface::class);
    }

    public function testPreFlush()
    {
        $this->factory
            ->expects($this->once())
            ->method('createRevision');

        $doctrine_event = $this
            ->getMockBuilder(PreFlushEventArgs::class)
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

        $this->expectNotToPerformAssertions();

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

        $this->expectException(\RuntimeException::class);

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
