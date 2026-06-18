<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision;

/**
 * @TODO: add returntypehints on next BC break, when removing doctrine/annotation support
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
