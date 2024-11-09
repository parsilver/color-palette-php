<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;


/**
 * Interface for color extraction operations
 */
interface ColorExtractorInterface
{
    /**
     * Extract dominant colors from an image
     *
     * @param ImageInterface $image
     * @param int $count Number of colors to extract (default: 5)
     * @return ColorPaletteInterface
     */
    public function extract(ImageInterface $image, int $count = 5): ColorPaletteInterface;
}