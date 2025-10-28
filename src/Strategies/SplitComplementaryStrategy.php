<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Split-Complementary color palette generation strategy
 *
 * Generates a palette with the base color and two colors adjacent to its complement.
 * Provides high contrast like complementary but with more nuance.
 */
class SplitComplementaryStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor;
        $color2 = $baseColor->rotate(150);
        $color3 = $baseColor->rotate(210);

        return new ColorPalette([$color1, $color2, $color3]);
    }
}
