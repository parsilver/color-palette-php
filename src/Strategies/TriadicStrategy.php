<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Triadic color palette generation strategy
 *
 * Generates a palette with three colors evenly spaced on the color wheel.
 * Triadic colors offer vibrant contrast while retaining harmony.
 */
class TriadicStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor;
        $color2 = $baseColor->rotate(120);
        $color3 = $baseColor->rotate(240);

        return new ColorPalette([$color1, $color2, $color3]);
    }
}
