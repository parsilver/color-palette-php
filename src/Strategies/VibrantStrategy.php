<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Vibrant color palette generation strategy
 *
 * Generates a palette of highly saturated, vibrant colors based on the base color's hue.
 * Creates bold, energetic color schemes.
 */
class VibrantStrategy extends AbstractPaletteStrategy
{
    /**
     * Default saturation for vibrant colors (full saturation for bold colors)
     */
    private const VIBRANT_SATURATION = 100;

    /**
     * Default lightness for vibrant colors (medium lightness for balanced vibrant colors)
     */
    private const VIBRANT_LIGHTNESS = 50;

    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $colors = [];
        $hsl = $baseColor->toHsl();
        $baseHue = $hsl['h'];

        for ($i = 0; $i < 5; $i++) {
            $hue = ($baseHue + ($i * ColorSchemeConstants::PENTADIC_ANGLE)) % 360;
            $colors[] = Color::fromHsl($hue, self::VIBRANT_SATURATION, self::VIBRANT_LIGHTNESS);
        }

        return new ColorPalette($colors);
    }
}
