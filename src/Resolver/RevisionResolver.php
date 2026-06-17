<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityRevision\Attributes\Revision;
use Hostnet\Component\EntityRevision\Revision as RevisionAnnotation;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;

class RevisionResolver implements RevisionResolverInterface
{
    public function __construct(private EntityMetadataProvider $provider)
    {
    }

    /**
     * @see \Hostnet\Component\EntityRevision\Resolver\RevisionResolverInterface::getRevisionAnnotation()
     *
     * @deprecated Please use the Revision attribute instead
     */
    public function getRevisionAnnotation(EntityManagerInterface $em, $entity): ?RevisionAnnotation
    {
        return $this->provider->getAnnotationFromEntity($em, $entity, RevisionAnnotation::class);
    }

    public function getRevisionAttribute(EntityManagerInterface $em, $entity): ?Revision
    {
        return $this->provider->getAttributeFromEntity(Revision::class, $em, $entity);
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
