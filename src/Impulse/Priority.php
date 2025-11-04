<?php

/**
 * Impulse
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Impulse;

enum Priority: int
{
    case Low = 1;
    case Medium = 2;
    case High = 3;
}
