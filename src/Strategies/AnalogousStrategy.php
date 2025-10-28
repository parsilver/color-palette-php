<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Analogous color palette generation strategy
 *
 * Generates a palette with colors adjacent to each other on the color wheel.
 * Analogous colors create serene and comfortable designs.
 */
class AnalogousStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor->rotate(-30);
        $color2 = $baseColor;
        $color3 = $baseColor->rotate(30);

        return new ColorPalette([$color1, $color2, $color3]);
    }
}
