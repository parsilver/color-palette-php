<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Images;

use Farzai\ColorPalette\Exceptions\ImageException;

/**
 * GD implementation of ImageInterface
 */
class GdImage extends AbstractImage
{
    /**
     * Create a new GD image instance
     *
     * @param resource|\GdImage $resource
     * @throws ImageException
     */
    public function __construct($resource)
    {
        if (!($resource instanceof \GdImage) && !is_resource($resource)) {
            throw new ImageException('Invalid GD image resource provided');
        }

        $this->resource = $resource;
        $this->width = imagesx($resource);
        $this->height = imagesy($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function destroy(): void
    {
        if ($this->resource) {
            imagedestroy($this->resource);
            $this->resource = null;
        }
    }

    /**
     * Create from file path
     *
     * @param string $path
     * @return self
     * @throws ImageException
     */
    public static function createFromPath(string $path): self
    {
        if (!file_exists($path)) {
            throw new ImageException("Image file not found: {$path}");
        }

        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            throw new ImageException("Invalid image file: {$path}");
        }

        $resource = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => throw new ImageException("Unsupported image type: {$imageInfo[2]}")
        };

        if ($resource === false) {
            throw new ImageException("Failed to create image resource from: {$path}");
        }

        return new self($resource);
    }

    public function __destruct()
    {
        $this->destroy();
    }
}
