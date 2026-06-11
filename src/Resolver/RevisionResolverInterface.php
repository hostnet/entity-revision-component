<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;
use Hostnet\Component\EntityRevision\Revision;

interface RevisionResolverInterface
{
    /**
     * Return list of revisionable fields
     *
     * @return string[]
     */
    public function getRevisionableFields(EntityManagerInterface $em, $entity): array;

    /**
     * Return the revision annotation or null
     *
     * @deprecated
     */
    public function getRevisionAnnotation(EntityManagerInterface $em, $entity): ?Revision;
}
