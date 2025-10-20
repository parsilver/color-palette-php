<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ThemeGeneratorInterface;
use Farzai\ColorPalette\Contracts\ThemeInterface;
use InvalidArgumentException;

class ThemeGenerator implements ThemeGeneratorInterface
{
    /**
     * Generate a theme from a color palette
     *
     * @param  ColorPaletteInterface  $palette
     * @param  array<string>  $colorNames  Optional color names for theme colors
     *
     * @throws InvalidArgumentException
     */
    public function generate(ColorPaletteInterface $palette, array $colorNames = []): ThemeInterface
    {
        $colors = $palette->getColors();

        if (empty($colorNames)) {
            $colorNames = ['primary', 'secondary', 'accent'];
        }

        if (count($colors) !== count($colorNames)) {
            throw new InvalidArgumentException('Number of colors in palette does not match number of color names');
        }

        $themeColors = [];
        foreach ($colors as $index => $color) {
            $themeColors[$colorNames[$index]] = $color;
        }

        return Theme::fromColors($themeColors);
    }
}
