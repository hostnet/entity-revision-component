<?php
namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
class RevisionResolver implements RevisionResolverInterface
{
    /**
     * @var string
     */
    private $annotation = 'Hostnet\Component\EntityRevision\Revision';

    /**
     * @var EntityAnnotationMetadataProvider
     */
    private $provider;

    /**
     * @param EntityMetadataProvider $provider
     */
    public function __construct(EntityAnnotationMetadataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @see \Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface::getRevisionAnnotation()
     */
    public function getRevisionAnnotation(EntityManagerInterface $em, $entity)
    {
        return $this->provider->getAnnotationFromEntity($em, $entity, $this->annotation);
    }

    /**
     * @see \Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface::getRevisionableFields()
     */
    public function getRevisionableFields(EntityManagerInterface $em, $entity)
    {
        $metadata = $em->getClassMetadata(get_class($entity));
        return array_merge($metadata->getFieldNames(), $metadata->getAssociationNames());
    }
}
