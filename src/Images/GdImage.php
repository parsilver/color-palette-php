<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Contracts\ImageInterface;

class GdImage implements ImageInterface
{
    public function __construct(private readonly \GdImage $resource) {}

    public function getWidth(): int
    {
        return imagesx($this->resource);
    }

    public function getHeight(): int
    {
        return imagesy($this->resource);
    }

    public function getResource(): \GdImage
    {
        return $this->resource;
    }

    public function __destruct()
    {
        imagedestroy($this->resource);
    }
}
