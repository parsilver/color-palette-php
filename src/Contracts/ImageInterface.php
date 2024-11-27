<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

/**
 * Interface for image representation
 */
interface ImageInterface
{
    /**
     * Get image width
     */
    public function getWidth(): int;

    /**
     * Get image height
     */
    public function getHeight(): int;
}
