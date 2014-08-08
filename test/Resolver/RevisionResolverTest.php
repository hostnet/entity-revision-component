<?php
namespace Hostnet\Component\EntityRevision\Resolver;

use Hostnet\Component\EntityRevision\Revision;

/**
 * @coversDefaultClass Hostnet\Component\EntityRevision\Resolver\RevisionResolver
 * @covers ::__construct
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
            ->getMockBuilder("Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider")
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this
            ->getMockBuilder("Doctrine\ORM\EntityManagerInterface")
            ->disableOriginalConstructor()
            ->getMock();

        $this->resolver = new RevisionResolver($this->provider);
    }

    /**
     * @covers ::getRevisionAnnotation
     */
    public function testGetRevisionAnnotation()
    {
        $entity = new \stdClass();

        $this->provider
            ->expects($this->once())
            ->method("getAnnotationFromEntity")
            ->with($this->em, $entity, "Hostnet\Component\EntityRevision\Revision");

        $this->resolver->getRevisionAnnotation($this->em, $entity);
    }

    /**
     * @covers ::getRevisionableFields
     */
    public function testGetRevisionableFieldsNoAnnotation()
    {
        $entity = new \stdClass();

        $this->provider
            ->expects($this->once())
            ->method("getAnnotationFromEntity")
            ->willReturn(null);

        $this->assertEmpty($this->resolver->getRevisionableFields($this->em, $entity));
    }

    /**
     * @covers ::getRevisionableFields
     */
    public function testGetRevisionableFields()
    {
        $entity        = new \stdClass();
        $metadata      = $this->getMock("\Doctrine\Common\Persistence\Mapping\ClassMetadata");
        $metadata_meta = $this->getMock("\Doctrine\Common\Persistence\Mapping\ClassMetadata");

        $this->provider
            ->expects($this->once())
            ->method("getAnnotationFromEntity")
            ->willReturnOnConsecutiveCalls(new Revision());

        $metadata->expects($this->once())->method("getFieldNames")->willReturn(["id"]);
        $metadata->expects($this->once())->method("getAssociationNames")->willReturn(["test"]);
        $metadata_meta->expects($this->once())->method("getFieldNames")->willReturn(["id"]);
        $metadata_meta->expects($this->once())->method("getAssociationNames")->willReturn(["test"]);

        $this->em
            ->expects($this->exactly(2))
            ->method("getClassMetadata")
            ->withConsecutive([get_class($entity)], [get_class($entity) . "Mutation"])
            ->willReturnOnConsecutiveCalls($metadata, $metadata_meta);

        $this->assertEquals(["id", "test"], $this->resolver->getRevisionableFields($this->em, $entity));
    }
}
