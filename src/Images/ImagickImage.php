<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Exceptions\ImageException;

/**
 * Imagick implementation of ImageInterface
 */
class ImagickImage extends AbstractImage
{
    /**
     * Create a new Imagick image instance
     *
     * @throws ImageException
     */
    public function __construct(\Imagick $resource)
    {
        if (! extension_loaded('imagick')) {
            throw new ImageException('Imagick extension is not loaded');
        }

        $this->resource = $resource;
        $geometry = $resource->getImageGeometry();
        $this->width = $geometry['width'];
        $this->height = $geometry['height'];
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(): void
    {
        if ($this->resource instanceof \Imagick) {
            $this->resource->clear();
            $this->resource = null;
        }
    }

    /**
     * Create from file path
     *
     * @throws ImageException
     */
    public static function createFromPath(string $path): self
    {
        if (! file_exists($path)) {
            throw new ImageException("Image file not found: {$path}");
        }

        try {
            $imagick = new \Imagick($path);
            // Convert to RGB colorspace if needed
            if ($imagick->getImageColorspace() !== \Imagick::COLORSPACE_RGB) {
                $imagick->transformImageColorspace(\Imagick::COLORSPACE_RGB);
            }

            return new self($imagick);
        } catch (\ImagickException $e) {
            throw new ImageException("Failed to create Imagick image: {$e->getMessage()}");
        }
    }

    public function __destruct()
    {
        $this->destroy();
    }
}
