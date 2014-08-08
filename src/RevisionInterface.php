<?php
namespace Hostnet\Component\EntityRevision;

/**
 * @author Yannick de Lange <ydelange@hostnet.nl>
 */
interface RevisionInterface
{
    /**
     * Return the user that created the revision.
     *
     * @return string
     */
    public function getUser();

    /**
     * Return the date on which the revision was created.
     *
     * @return \DateTime
     */
    public function getCreatedAt();
}
