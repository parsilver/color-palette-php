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
     *
     * @return int
     */
    public function getWidth(): int;

    /**
     * Get image height
     *
     * @return int
     */
    public function getHeight(): int;

    /**
     * Get image resource
     * This method should return the native image resource (GD or Imagick)
     *
     * @return mixed
     */
    public function getResource(): mixed;

    /**
     * Clean up resources
     *
     * @return void
     */
    public function destroy(): void;
}
