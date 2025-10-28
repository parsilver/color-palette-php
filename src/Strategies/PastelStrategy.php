<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Pastel color palette generation strategy
 *
 * Generates a palette of soft, muted pastel colors based on the base color's hue.
 * Perfect for gentle, calming designs.
 */
class PastelStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $colors = [];
        $hsl = $baseColor->toHsl();
        $baseHue = $hsl['h'];

        for ($i = 0; $i < 5; $i++) {
            $hue = ($baseHue + ($i * 72)) % 360;
            $colors[] = Color::fromHsl($hue, 25, 90);
        }

        return new ColorPalette($colors);
    }
}
