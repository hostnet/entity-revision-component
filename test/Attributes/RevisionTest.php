<?php
/**
 * @copyright 2026-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Attributes;

use Hostnet\Component\EntityTracker\Attributes\Tracked;
use PHPUnit\Framework\TestCase;

class RevisionTest extends TestCase
{
    public function testObject(): void
    {
        $revision = new Revision();
        self::assertInstanceOf(Tracked::class, $revision);
    }
}
