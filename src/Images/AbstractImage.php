<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Exceptions\ImageException;

/**
 * Abstract base class for image implementations
 */
abstract class AbstractImage implements ImageInterface
{
    /**
     * Image width
     */
    protected int $width;

    /**
     * Image height
     */
    protected int $height;

    /**
     * Native image resource (GD or Imagick)
     */
    protected mixed $resource;

    /**
     * {@inheritdoc}
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function getResource(): mixed
    {
        return $this->resource;
    }

    /**
     * Ensure resource is valid before operations
     *
     * @throws ImageException
     */
    protected function ensureResourceIsValid(): void
    {
        if (! $this->resource) {
            throw new ImageException('Invalid or destroyed image resource');
        }
    }
}
