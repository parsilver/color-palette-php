<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;


/**
 * Interface for handling image loading operations
 */
interface ImageLoaderInterface
{
    /**
     * Load image from different sources
     *
     * @param string $source Image source (URL or file path)
     * @throws \Farzai\ColorPalette\Exceptions\ImageLoadException
     * @return ImageInterface
     */
    public function load(string $source): ImageInterface;

    /**
     * Check if the source is supported by this loader
     *
     * @param string $source
     * @return bool
     */
    public function supports(string $source): bool;
}