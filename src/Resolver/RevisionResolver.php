<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityRevision\Attributes\Revision;
use Hostnet\Component\EntityTracker\Provider\EntityMetadataProvider;

class RevisionResolver implements RevisionResolverInterface
{
    public function __construct(private EntityMetadataProvider $provider)
    {
    }

    #[\Override]
    public function getRevisionAttribute(EntityManagerInterface $em, object $entity): ?Revision
    {
        return $this->provider->getAttributeFromEntity(Revision::class, $em, $entity);
    }

    #[\Override]
    public function getRevisionableFields(EntityManagerInterface $em, object $entity): array
    {
        $metadata = $em->getClassMetadata($entity::class);
        return array_merge($metadata->getFieldNames(), $metadata->getAssociationNames());
    }
}
