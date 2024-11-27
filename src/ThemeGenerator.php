<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use InvalidArgumentException;

class ThemeGenerator
{
    /**
     * Generate a theme from a color palette
     *
     * @param  array<string>  $colorNames
     *
     * @throws InvalidArgumentException
     */
    public function generate(ColorPalette $palette, array $colorNames = []): Theme
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
