<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Tetradic (Rectangle) color palette generation strategy
 *
 * Generates a palette with four colors arranged as two complementary pairs.
 * Tetradic schemes are rich and offer plenty of color variety.
 */
class TetradicStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor;
        $color2 = $baseColor->rotate(90);
        $color3 = $baseColor->rotate(180);
        $color4 = $baseColor->rotate(270);

        return new ColorPalette([$color1, $color2, $color3, $color4]);
    }
}
