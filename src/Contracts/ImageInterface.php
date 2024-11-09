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

    /**
     * Get image resource
     * This method should return the native image resource (GD or Imagick)
     */
    public function getResource(): mixed;

    /**
     * Clean up resources
     */
    public function destroy(): void;
}
