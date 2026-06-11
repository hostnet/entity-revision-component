<?php
/**
 * @copyright 2026-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Listener;

use Hostnet\Component\EntityRevision\Attributes\Revision;
use Hostnet\Component\EntityRevision\RevisionableInterface;
use Hostnet\Component\EntityRevision\RevisionInterface;

#[Revision]
class EntityWithAttribute implements RevisionableInterface
{
    private $call_count = 0;

    public function setRevision(RevisionInterface $revision): void
    {
        $this->call_count++;
    }

    public function getRevision(): void
    {
    }

    public function getCallCount(): int
    {
        return $this->call_count;
    }
}
