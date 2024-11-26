<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use InvalidArgumentException;

/**
 * Factory for creating different types of color palettes
 */
class PaletteFactory
{
    /**
     * Create a new color palette from an array of colors
     *
     * @param  array<string|ColorInterface>  $colors
     * @throws InvalidArgumentException
     */
    public function create(array $colors): ColorPalette
    {
        $paletteColors = [];

        foreach ($colors as $color) {
            if ($color instanceof ColorInterface) {
                $paletteColors[] = $color;
            } elseif (is_string($color)) {
                try {
                    $paletteColors[] = new Color($color);
                } catch (InvalidArgumentException $e) {
                    throw new InvalidArgumentException(
                        sprintf('Invalid color format: %s', $e->getMessage())
                    );
                }
            } else {
                throw new InvalidArgumentException(
                    'Invalid color type. Expected string or ColorInterface'
                );
            }
        }

        return new ColorPalette($paletteColors);
    }
} 