<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision;

interface RevisionInterface
{
    /**
     * Return the user that created the revision, or null if there is none.
     */
    public function getUser(): ?string;

    /**
     * Return the date on which the revision was created.
     */
    public function getCreatedAt(): \DateTimeInterface;
}
