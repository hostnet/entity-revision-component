<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision;

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
