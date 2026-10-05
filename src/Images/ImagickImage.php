<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Contracts\ImageInterface;

/**
 * Wraps an \Imagick without taking ownership of it: the wrapper never clears
 * the object, so the caller can keep using it and wrap it again. PHP frees it
 * once its last reference is gone.
 *
 * @requires extension imagick
 */
class ImagickImage implements ImageInterface
{
    public function __construct(private readonly \Imagick $resource) {}

    public function getWidth(): int
    {
        return $this->resource->getImageWidth();
    }

    public function getHeight(): int
    {
        return $this->resource->getImageHeight();
    }

    public function getResource(): \Imagick
    {
        return $this->resource;
    }
}
