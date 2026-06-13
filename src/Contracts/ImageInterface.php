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
     * Get the underlying driver resource.
     *
     * Returns the backend-specific handle (e.g. a \GdImage or \Imagick instance);
     * implementations narrow this to their concrete resource type.
     */
    public function getResource(): mixed;
}
