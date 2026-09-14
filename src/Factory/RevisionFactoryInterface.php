<?php
/**
 * @copyright 2014-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Factory;

use Hostnet\Component\EntityRevision\RevisionInterface;

interface RevisionFactoryInterface
{
    /**
     * Create a revision
     */
    public function createRevision(\DateTime $created_at): RevisionInterface;
}
