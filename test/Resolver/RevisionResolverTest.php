<?php
namespace Hostnet\Component\EntityRevision\Resolver;

use Hostnet\Component\EntityRevision\Revision;

/**
 * @covers Hostnet\Component\EntityRevision\Resolver\RevisionResolver
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
class RevisionResolverTest extends \PHPUnit_Framework_TestCase
{
    private $provider;
    private $resolver;
    private $em;

    public function setUp()
    {
        $this->provider = $this
            ->getMockBuilder('Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this
            ->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
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
            ->with($this->em, $entity, 'Hostnet\Component\EntityRevision\Revision');

        $this->resolver->getRevisionAnnotation($this->em, $entity);
    }

    public function testGetRevisionableFields()
    {
        $entity   = new \stdClass();
        $metadata = $this->createMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
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
