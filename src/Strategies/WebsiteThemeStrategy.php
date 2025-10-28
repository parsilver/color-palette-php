<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Website theme color palette generation strategy
 *
 * Generates a complete website theme palette with semantic color names.
 * Includes primary, secondary, accent, background, and surface colors.
 */
class WebsiteThemeStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        return new ColorPalette([
            'primary' => $baseColor,
            'secondary' => $baseColor->rotate(30)->desaturate(0.2),
            'accent' => $baseColor->rotate(180)->saturate(0.2),
            'background' => Color::fromHsl(0, 0, 98),
            'surface' => Color::fromHsl(0, 0, 100),
        ]);
    }
}
