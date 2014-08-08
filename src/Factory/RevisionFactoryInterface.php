<?php
namespace Hostnet\Component\EntityRevision\Factory;

/**
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
interface RevisionFactoryInterface
{
    /**
     * Create a revision
     *
     * @param \DateTime $created_at
     * @return Hostnet\Component\EntityRevision\RevisionInterface
     */
    public function createRevision(\DateTime $created_at);
}
