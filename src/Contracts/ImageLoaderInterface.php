<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

use Farzai\ColorPalette\Exceptions\InvalidImageException;

/**
 * Interface for handling image loading operations
 */
interface ImageLoaderInterface
{
    /**
     * Load image from different sources
     *
     * @param  string  $source  Image source (URL or file path)
     *
     * @throws InvalidImageException If the image cannot be loaded (also covers its
     *                               HttpException and SsrfException subclasses)
     * @throws \RuntimeException If a URL is requested but no PSR-18 client / PSR-17
     *                           request factory is available
     */
    public function load(string $source): ImageInterface;

    /**
     * Check if the source is supported by this loader
     */
    public function supports(string $source): bool;
}
