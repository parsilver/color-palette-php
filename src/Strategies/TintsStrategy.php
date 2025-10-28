<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Tints color palette generation strategy
 *
 * Generates a palette by creating progressively lighter versions of the base color.
 * Useful for creating soft, gentle color schemes.
 *
 * Options:
 * - count: Number of tints to generate (default: 5)
 */
class TintsStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $count = $options['count'] ?? 5;
        $colors = [$baseColor];
        $step = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $colors[] = $baseColor->lighten($step * $i);
        }

        return new ColorPalette($colors);
    }
}
