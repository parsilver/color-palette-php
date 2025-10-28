<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Split-Complementary color palette generation strategy
 *
 * Generates a palette with the base color and two colors adjacent to its complement.
 * Provides high contrast like complementary but with more nuance.
 */
class SplitComplementaryStrategy extends AbstractPaletteStrategy
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor;
        $color2 = $baseColor->rotate(ColorSchemeConstants::SPLIT_COMPLEMENTARY_ANGLE_1);
        $color3 = $baseColor->rotate(ColorSchemeConstants::SPLIT_COMPLEMENTARY_ANGLE_2);

        return new ColorPalette([$color1, $color2, $color3]);
    }
}
