<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface;

/**
 * Complementary color palette generation strategy
 *
 * Generates a palette with the base color and its complement (opposite on color wheel).
 * Complementary colors create high contrast and vibrant looks.
 */
class ComplementaryStrategy implements PaletteGenerationStrategyInterface
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $complement = $baseColor->rotate(180);

        return new ColorPalette([$baseColor, $complement]);
    }
}
