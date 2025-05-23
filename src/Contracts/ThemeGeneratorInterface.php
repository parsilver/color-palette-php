<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

/**
 * Interface for color palette theme generation
 */
interface ThemeGeneratorInterface
{
    /**
     * Generate a theme from a color palette
     */
    public function generate(ColorPaletteInterface $palette): ThemeInterface;
}
