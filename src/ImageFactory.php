<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\ImageConstants;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\Services\ExtensionChecker;
use InvalidArgumentException;

class ImageFactory
{
    public function __construct(private readonly ?ExtensionChecker $extensionChecker = null) {}

    /**
     * Static convenience method to create an image from a file path
     *
     * This is a shorthand for: (new ImageFactory())->createFromPath($path, $driver)
     *
     * @param  string  $path  Path to the image file
     * @param  string  $driver  Image processing driver: 'gd' or 'imagick' (default: 'gd')
     *
     * @throws InvalidArgumentException If the file doesn't exist or driver is invalid
     *
     * @example
     * ```php
     * $image = ImageFactory::fromPath('photo.jpg');
     * $image = ImageFactory::fromPath('photo.jpg', 'imagick');
     * ```
     */
    public static function fromPath(string $path, string $driver = 'gd'): ImageInterface
    {
        return (new self)->createFromPath($path, $driver);
    }

    public function createFromPath(string $path, string $driver = 'gd'): ImageInterface
    {
        return match ($driver) {
            'gd' => $this->createGdImage($path),
            'imagick' => $this->createImagickImage($path),
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}"),
        };
    }

    private function createGdImage(string $path): GdImage
    {
        $this->getExtensionChecker()->ensureGdLoaded();

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Image file not found: {$path}");
        }

        // Validate file before processing
        $this->validateImageFile($path);

        $imageData = @file_get_contents($path);
        if ($imageData === false) {
            throw new InvalidArgumentException("Failed to read image file: {$path}");
        }

        // Attempt to create image from string, suppressing PHP warnings
        // imagecreatefromstring() returns false on failure and may emit warnings
        $image = @imagecreatefromstring($imageData);

        if ($image === false) {
            throw new InvalidArgumentException(
                "Failed to create GD image from file: {$path}. ".
                'The file may not be a valid image format supported by GD. '.
                'Supported formats: JPEG, PNG, GIF, BMP, WBMP, WebP, XBM, XPM.'
            );
        }

        return new GdImage($image);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createImagickImage(string $path): ImagickImage
    {
        $this->getExtensionChecker()->ensureImagickLoaded();

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Image file not found: {$path}");
        }

        // Apply the same size/MIME/dimension guards as the GD path before decoding.
        $this->validateImageFile($path);

        try {
            /** @var \Imagick $image */
            $image = new \Imagick($path);

            return new ImagickImage($image);
        } catch (\ImagickException $e) {
            throw new InvalidArgumentException("Failed to create Imagick image from file: {$path}. ".$e->getMessage(), 0, $e);
        }
    }

    /**
     * Validate image file for security
     *
     * Checks file size and MIME type to prevent malicious uploads
     *
     * @param  string  $path  Path to the image file
     *
     * @throws InvalidArgumentException If file fails validation
     */
    private function validateImageFile(string $path): void
    {
        // Check file size
        $fileSize = filesize($path);
        if ($fileSize === false) {
            throw new InvalidArgumentException("Unable to determine file size: {$path}");
        }

        if ($fileSize > ImageConstants::MAX_IMAGE_FILE_SIZE) {
            $maxSizeMB = ImageConstants::MAX_IMAGE_FILE_SIZE / (1024 * 1024);
            $actualSizeMB = round($fileSize / (1024 * 1024), 2);
            throw new InvalidArgumentException(
                sprintf(
                    'Image file is too large: %s MB (maximum: %s MB)',
                    $actualSizeMB,
                    $maxSizeMB
                )
            );
        }

        // Guard against decompression bombs before any decoder loads the bitmap.
        // Runs regardless of finfo availability since getimagesize reads only the header.
        $this->validateImageDimensions($path);

        // Check MIME type
        if (! function_exists('finfo_open')) {
            // finfo not available, skip MIME check
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            // finfo failed to open, skip MIME check
            return;
        }

        $mimeType = finfo_file($finfo, $path);

        if ($mimeType === false) {
            throw new InvalidArgumentException("Unable to determine file MIME type: {$path}");
        }

        if (! in_array($mimeType, ImageConstants::ALLOWED_IMAGE_MIME_TYPES, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Unsupported image MIME type: %s. Allowed types: %s',
                    $mimeType,
                    implode(', ', ImageConstants::ALLOWED_IMAGE_MIME_TYPES)
                )
            );
        }
    }

    /**
     * Validate decoded image dimensions to prevent decompression-bomb / pixel-flood DoS
     *
     * Reads dimensions from the image header (getimagesize) without decoding the
     * pixel data, then rejects images that exceed the per-side or total-pixel caps.
     *
     * @param  string  $path  Path to the image file
     *
     * @throws InvalidArgumentException If dimensions are unreadable or too large
     */
    private function validateImageDimensions(string $path): void
    {
        $dimensions = @getimagesize($path);

        if ($dimensions === false) {
            throw new InvalidArgumentException(
                "Unable to read image dimensions (file may not be a supported image): {$path}"
            );
        }

        [$width, $height] = $dimensions;

        if ($width <= 0 || $height <= 0) {
            throw new InvalidArgumentException(
                sprintf('Invalid image dimensions: %dx%d', $width, $height)
            );
        }

        if ($width > ImageConstants::MAX_IMAGE_WIDTH
            || $height > ImageConstants::MAX_IMAGE_HEIGHT
            || ($width * $height) > ImageConstants::MAX_IMAGE_PIXELS) {
            throw new InvalidArgumentException(
                sprintf(
                    'Image dimensions too large: %dx%d (%d pixels). Maximum allowed: %dx%d or %d pixels.',
                    $width,
                    $height,
                    $width * $height,
                    ImageConstants::MAX_IMAGE_WIDTH,
                    ImageConstants::MAX_IMAGE_HEIGHT,
                    ImageConstants::MAX_IMAGE_PIXELS
                )
            );
        }
    }

    /**
     * Get the extension checker instance
     *
     * Creates a default instance if not injected (for backwards compatibility)
     */
    private function getExtensionChecker(): ExtensionChecker
    {
        return $this->extensionChecker ?? new ExtensionChecker;
    }
}
