<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Factory;

interface RevisionFactoryInterface
{
    /**
     * Create a revision
     *
     * @param \DateTime $created_at
     * @return \Hostnet\Component\EntityRevision\RevisionInterface
     */
    public function createRevision(\DateTime $created_at);
}
