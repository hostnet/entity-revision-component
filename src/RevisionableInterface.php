<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision;

/**
 * @TODO: add returntypehints on next BC break, when removing doctrine/annotation support
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
