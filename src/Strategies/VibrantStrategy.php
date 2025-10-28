<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Vibrant color palette generation strategy
 *
 * Generates a palette of highly saturated, vibrant colors based on the base color's hue.
 * Creates bold, energetic color schemes.
 */
class VibrantStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $colors = [];
        $hsl = $baseColor->toHsl();
        $baseHue = $hsl['h'];

        for ($i = 0; $i < 5; $i++) {
            $hue = ($baseHue + ($i * 72)) % 360;
            $colors[] = Color::fromHsl($hue, 100, 50);
        }

        return new ColorPalette($colors);
    }
}
