<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\Services\ExtensionChecker;
use InvalidArgumentException;

class ImageFactory
{
    public function __construct(private readonly ?ExtensionChecker $extensionChecker = null) {}

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

        try {
            /** @var \Imagick $image */
            $image = new \Imagick($path);

            return new ImagickImage($image);
        } catch (\ImagickException $e) {
            throw new InvalidArgumentException("Failed to create Imagick image from file: {$path}. ".$e->getMessage(), 0, $e);
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
