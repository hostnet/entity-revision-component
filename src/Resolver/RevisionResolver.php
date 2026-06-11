<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityRevision\Revision;
use Hostnet\Component\EntityTracker\Provider\EntityAnnotationMetadataProvider;

class RevisionResolver implements RevisionResolverInterface
{
    /**
     * @var EntityAnnotationMetadataProvider
     */
    private $provider;

    public function __construct(EntityAnnotationMetadataProvider $provider)
    {
        $this->provider = $provider;
    }

    /**
     * @see \Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface::getRevisionAnnotation()
     *
     * @deprecated Please use the Revision attribute instead
     */
    public function getRevisionAnnotation(EntityManagerInterface $em, $entity): ?Revision
    {
        return $this->provider->getAnnotationFromEntity($em, $entity, Revision::class);
    }

    /**
     * @see \Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface::getRevisionableFields()
     */
    public function getRevisionableFields(EntityManagerInterface $em, $entity): array
    {
        $metadata = $em->getClassMetadata(get_class($entity));
        return array_merge($metadata->getFieldNames(), $metadata->getAssociationNames());
    }
}
