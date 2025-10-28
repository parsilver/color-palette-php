<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Monochromatic color palette generation strategy
 *
 * Generates a palette of colors with the same hue but varying lightness.
 * This creates a harmonious, unified look with subtle variations.
 *
 * Options:
 * - count: Number of colors to generate (default: 5)
 */
class MonochromaticStrategy extends AbstractPaletteStrategy
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $count = $this->getCountOption($options, 5);
        $colors = [$baseColor];
        $hsl = $baseColor->toHsl();
        $step = ColorSchemeConstants::DEFAULT_MANIPULATION_STEP / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $lightness = max(0, min(100, $hsl['l'] + ($step * $i * 100)));
            $colors[] = Color::fromHsl($hsl['h'], $hsl['s'], $lightness);
        }

        return new ColorPalette($colors);
    }
}
