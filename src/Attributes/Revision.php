<?php
/**
 * @copyright 2026-present Hostnet B.V.
 */
declare(strict_types=1);

namespace Hostnet\Component\EntityRevision\Attributes;

use Hostnet\Component\EntityTracker\Attributes\Tracked;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Revision extends Tracked
{
}
