<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Hostnet\Component\EntityRevision\Revision;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\EntityRevision\Resolver\RevisionResolver
 */
class RevisionResolverTest extends TestCase
{
    private $provider;
    private $resolver;
    private $em;

    public function setUp(): void
    {
        $this->provider = $this
            ->getMockBuilder(EntityAnnotationMetadataProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this
            ->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new RevisionResolver($this->provider);
    }

    public function testGetRevisionAnnotation()
    {
        $entity = new \stdClass();

        $this->provider
            ->expects($this->once())
            ->method('getAnnotationFromEntity')
            ->with($this->em, $entity, Revision::class);

        $this->resolver->getRevisionAnnotation($this->em, $entity);
    }

    public function testGetRevisionableFields()
    {
        $entity   = new \stdClass();
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getFieldNames')->willReturn(['id']);
        $metadata->expects($this->once())->method('getAssociationNames')->willReturn(['test']);

        $this->em
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($entity))
            ->willReturn($metadata);

        $this->assertEquals(['id', 'test'], $this->resolver->getRevisionableFields($this->em, $entity));
    }
}
