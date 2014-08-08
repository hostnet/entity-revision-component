<?php
namespace Hostnet\Component\EntityRevision\Resolver;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Iltar van der Berg <ivanderberg@hostnet.nl>
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
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
