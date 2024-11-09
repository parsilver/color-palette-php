<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Contracts;

/**
 * Interface for theme representation
 */
interface ThemeInterface
{
    /**
     * Get primary color
     */
    public function getPrimaryColor(): ColorInterface;

    /**
     * Get secondary color
     */
    public function getSecondaryColor(): ColorInterface;

    /**
     * Get accent color
     */
    public function getAccentColor(): ColorInterface;

    /**
     * Get background color
     */
    public function getBackgroundColor(): ColorInterface;

    /**
     * Get surface color
     */
    public function getSurfaceColor(): ColorInterface;

    /**
     * Export theme as array
     *
     * @return array<string, string>
     */
    public function toArray(): array;
}
