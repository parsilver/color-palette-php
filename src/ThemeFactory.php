<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use InvalidArgumentException;

class ThemeFactory
{
    /**
     * Create a new theme from an array of colors
     *
     * @param  array<string, string|ColorInterface>  $colors
     *
     * @throws InvalidArgumentException
     */
    public function create(array $colors): Theme
    {
        $themeColors = [];

        foreach ($colors as $key => $color) {
            if ($color instanceof ColorInterface) {
                $themeColors[$key] = $color;
            } elseif (is_string($color)) {
                try {
                    $themeColors[$key] = new Color($color);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid color format for key "%s": %s', $key, $e->getMessage())
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    sprintf('Invalid color type for key "%s". Expected string or ColorInterface', $key)
                );
            }
        }

        return new Theme($themeColors);
    }
}
