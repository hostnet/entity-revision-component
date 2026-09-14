<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Hostnet\Component\EntityRevision\Attributes\Revision;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Hostnet\Component\EntityRevision\Resolver\RevisionResolver
 */
class RevisionResolverTest extends TestCase
{
    private EntityMetadataProvider&MockObject $provider;
    private EntityManagerInterface&MockObject $em;
    private RevisionResolver $resolver;

    public function setUp(): void
    {
        $this->provider = $this->createMock(EntityMetadataProvider::class);
        $this->em       = $this->createMock(EntityManagerInterface::class);
        $this->resolver = new RevisionResolver($this->provider);
    }

    public function testGetRevisionableFields(): void
    {
        $entity   = new \stdClass();
        $metadata = $this->createMock(ClassMetadata::class);
        $metadata->expects($this->once())->method('getFieldNames')->willReturn(['id']);
        $metadata->expects($this->once())->method('getAssociationNames')->willReturn(['test']);

        $this->em
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($entity::class)
            ->willReturn($metadata);

        $this->assertEquals(['id', 'test'], $this->resolver->getRevisionableFields($this->em, $entity));
    }

    public function testGetRevisionAttribute(): void
    {
        $entity = new \stdClass();

        $attribute = new Revision();

        $this->provider
            ->expects($this->once())
            ->method('getAttributeFromEntity')
            ->with(Revision::class, $this->em, $entity)
            ->willReturn($attribute);

        self::assertSame($attribute, $this->resolver->getRevisionAttribute($this->em, $entity));
    }
}
