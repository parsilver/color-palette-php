<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;
use InvalidArgumentException;
use RuntimeException;

class ImageFactory
{
    public static function createFromPath(string $path, string $driver = 'gd'): ImageInterface
    {
        return match ($driver) {
            'gd' => self::createGdImage($path),
            'imagick' => self::createImagickImage($path),
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}"),
        };
    }

    private static function createGdImage(string $path): GdImage
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('GD extension is not available. Please install or enable the GD extension.');
        }

        if (! file_exists($path)) {
            throw new InvalidArgumentException("Image file not found: {$path}");
        }

        $imageData = @file_get_contents($path);
        if ($imageData === false) {
            throw new InvalidArgumentException("Failed to read image file: {$path}");
        }

        $image = @imagecreatefromstring($imageData);
        if ($image === false) {
            throw new InvalidArgumentException("Failed to create GD image from file: {$path}. The file may not be a valid image.");
        }

        return new GdImage($image);
    }

    /**
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    private static function createImagickImage(string $path): ImagickImage
    {
        if (! extension_loaded('imagick')) {
            throw new RuntimeException('Imagick extension is not available. Please install or enable the Imagick extension.');
        }

        try {
            /** @var \Imagick $image */
            $image = new \Imagick($path);

            return new ImagickImage($image);
        } catch (\ImagickException $e) {
            throw new InvalidArgumentException("Failed to create Imagick image from file: {$path}. ".$e->getMessage(), 0, $e);
        }
    }
}
