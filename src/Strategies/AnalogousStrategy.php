<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Analogous color palette generation strategy
 *
 * Generates a palette with colors adjacent to each other on the color wheel.
 * Analogous colors create serene and comfortable designs.
 */
class AnalogousStrategy extends AbstractPaletteStrategy
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $color1 = $baseColor->rotate(-ColorSchemeConstants::ANALOGOUS_ANGLE);
        $color2 = $baseColor;
        $color3 = $baseColor->rotate(ColorSchemeConstants::ANALOGOUS_ANGLE);

        return new ColorPalette([$color1, $color2, $color3]);
    }
}
