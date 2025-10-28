<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Tetradic (Rectangle) color palette generation strategy
 *
 * Generates a palette with four colors arranged as two complementary pairs.
 * Tetradic schemes are rich and offer plenty of color variety.
 */
class TetradicStrategy extends AbstractPaletteStrategy
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor;
        $color2 = $baseColor->rotate(ColorSchemeConstants::TETRADIC_ANGLE);
        $color3 = $baseColor->rotate(ColorSchemeConstants::COMPLEMENTARY_ANGLE);
        $color4 = $baseColor->rotate(ColorSchemeConstants::TETRADIC_ANGLE * 3);

        return new ColorPalette([$color1, $color2, $color3, $color4]);
    }
}
