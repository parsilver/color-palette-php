<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Contracts\ImageInterface;

/**
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

    public function __destruct()
    {
        $this->resource->clear();
    }
}
