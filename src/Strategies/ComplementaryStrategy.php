<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Strategies;

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Constants\ColorSchemeConstants;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Complementary color palette generation strategy
 *
 * Generates a palette with the base color and its complement (opposite on color wheel).
 * Complementary colors create high contrast and vibrant looks.
 */
class ComplementaryStrategy extends AbstractPaletteStrategy
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        $complement = $baseColor->rotate(ColorSchemeConstants::COMPLEMENTARY_ANGLE);

        return new ColorPalette([$baseColor, $complement]);
    }
}
