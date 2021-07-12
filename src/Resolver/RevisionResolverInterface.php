<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;

interface RevisionResolverInterface
{
    /**
     * Return list of revisionable fields
     *
     * @return string[]
     */
    public function getRevisionableFields(EntityManagerInterface $em, $entity);

    /**
     * Return the revision annotation
     *
     * @return Mutation
     */
    public function getRevisionAnnotation(EntityManagerInterface $em, $entity);
}
