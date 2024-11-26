<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageLoaderInterface;
use InvalidArgumentException;

class ImageLoaderFactory
{
    private const SUPPORTED_DRIVERS = ['gd', 'imagick'];

    /**
     * Create a new image loader instance
     *
     * @param  array<string, mixed>|string  $options  Options array or driver name
     * @throws InvalidArgumentException
     */
    public static function create(array|string $options = []): ImageLoaderInterface
    {
        if (is_string($options)) {
            $options = ['driver' => $options];
        }

        $driver = $options['driver'] ?? 'gd';
        $tempDir = $options['temp_dir'] ?? null;

        if (!in_array($driver, self::SUPPORTED_DRIVERS)) {
            throw new InvalidArgumentException(
                sprintf('Unsupported driver: %s. Supported drivers are: %s', $driver, implode(', ', self::SUPPORTED_DRIVERS))
            );
        }

        if ($driver === 'imagick' && !extension_loaded('imagick')) {
            $driver = 'gd';
        }

        return new ImageLoader(
            driver: $driver,
            tempDir: $tempDir,
            maxWidth: $options['max_width'] ?? null,
            maxHeight: $options['max_height'] ?? null
        );
    }
}
