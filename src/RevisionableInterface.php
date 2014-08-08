<?php
namespace Hostnet\Component\EntityRevision;

/**
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
interface RevisionableInterface
{
    /**
     * Set the current revision
     *
     * @param RevisionInterface $revision
     */
    public function setRevision(RevisionInterface $revision);

    /**
     * Return the current revision
     *
     * @return RevisionInterface
     */
    public function getRevision();
}
