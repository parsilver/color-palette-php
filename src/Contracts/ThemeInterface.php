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
     *
     * @return ColorInterface
     */
    public function getPrimaryColor(): ColorInterface;

    /**
     * Get secondary color
     *
     * @return ColorInterface
     */
    public function getSecondaryColor(): ColorInterface;

    /**
     * Get accent color
     *
     * @return ColorInterface
     */
    public function getAccentColor(): ColorInterface;

    /**
     * Get background color
     *
     * @return ColorInterface
     */
    public function getBackgroundColor(): ColorInterface;

    /**
     * Get surface color
     *
     * @return ColorInterface
     */
    public function getSurfaceColor(): ColorInterface;

    /**
     * Export theme as array
     *
     * @return array<string, string>
     */
    public function toArray(): array;
}