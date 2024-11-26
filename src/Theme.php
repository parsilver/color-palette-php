<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use JsonSerializable;

class Theme implements JsonSerializable
{
    private array $colors;

    /**
     * Create a new Theme instance
     *
     * @param  array<string, ColorInterface>  $colors
     */
    public function __construct(array $colors)
    {
        $this->colors = $colors;
    }

    /**
     * Get the primary color
     */
    public function getPrimary(): ColorInterface
    {
        return $this->colors['primary'];
    }

    /**
     * Get the primary color (alias for getPrimary)
     */
    public function getPrimaryColor(): ColorInterface
    {
        return $this->getPrimary();
    }

    /**
     * Get the secondary color
     */
    public function getSecondary(): ColorInterface
    {
        return $this->colors['secondary'];
    }

    /**
     * Get the secondary color (alias for getSecondary)
     */
    public function getSecondaryColor(): ColorInterface
    {
        return $this->getSecondary();
    }

    /**
     * Get the accent color
     */
    public function getAccent(): ColorInterface
    {
        return $this->colors['accent'];
    }

    /**
     * Get the accent color (alias for getAccent)
     */
    public function getAccentColor(): ColorInterface
    {
        return $this->getAccent();
    }

    /**
     * Get the background color
     */
    public function getBackground(): ColorInterface
    {
        return $this->colors['background'];
    }

    /**
     * Get the background color (alias for getBackground)
     */
    public function getBackgroundColor(): ColorInterface
    {
        return $this->getBackground();
    }

    /**
     * Get the surface color
     */
    public function getSurface(): ColorInterface
    {
        return $this->colors['surface'];
    }

    /**
     * Get the surface color (alias for getSurface)
     */
    public function getSurfaceColor(): ColorInterface
    {
        return $this->getSurface();
    }

    /**
     * Get a color by key
     */
    public function get(string $key): ?ColorInterface
    {
        return $this->colors[$key] ?? null;
    }

    /**
     * Check if a color exists
     */
    public function has(string $key): bool
    {
        return isset($this->colors[$key]);
    }

    /**
     * Get all colors as an array of hex values
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->colors as $key => $color) {
            $result[$key] = $color->toHex();
        }

        return $result;
    }

    /**
     * Create a theme from an array of hex colors
     *
     * @param  array<string, string>  $colors
     */
    public static function fromArray(array $colors): self
    {
        $themeColors = [];
        foreach ($colors as $key => $hex) {
            $themeColors[$key] = new Color($hex);
        }

        return new self($themeColors);
    }

    /**
     * Convert to JSON
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
