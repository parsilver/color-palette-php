<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Pastel color palette generation strategy
 *
 * Generates a palette of soft, muted pastel colors based on the base color's hue.
 * Perfect for gentle, calming designs.
 */
class PastelStrategy extends AbstractPaletteStrategy
{
    /**
     * Default saturation for pastel colors (low saturation for soft colors)
     */
    private const PASTEL_SATURATION = 25;

    /**
     * Default lightness for pastel colors (high lightness for soft colors)
     */
    private const PASTEL_LIGHTNESS = 90;

    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $colors = [];
        $hsl = $baseColor->toHsl();
        $baseHue = $hsl['h'];

        for ($i = 0; $i < 5; $i++) {
            $hue = ($baseHue + ($i * ColorSchemeConstants::PENTADIC_ANGLE)) % 360;
            $colors[] = Color::fromHsl($hue, self::PASTEL_SATURATION, self::PASTEL_LIGHTNESS);
        }

        return new ColorPalette($colors);
    }
}
