<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ThemeInterface;
use InvalidArgumentException;

class Theme implements ThemeInterface
{
    /**
     * @var array<string, ColorInterface>
     */
    private array $colors = [];

    /**
     * Create a new Theme instance
     *
     * @param  array<string, ColorInterface>  $colors
     */
    public function __construct(array $colors = [])
    {
        $this->colors = $colors;
    }

    /**
     * Create a theme from colors
     *
     * @param  array<string, ColorInterface>  $colors
     */
    public static function fromColors(array $colors): self
    {
        return new self($colors);
    }

    /**
     * Get a color by name
     *
     * @throws InvalidArgumentException
     */
    public function getColor(string $name): ColorInterface
    {
        if (! $this->hasColor($name)) {
            throw new InvalidArgumentException("Color '{$name}' not found in theme");
        }

        return $this->colors[$name];
    }

    /**
     * Check if a color exists in the theme
     */
    public function hasColor(string $name): bool
    {
        return isset($this->colors[$name]);
    }

    /**
     * Get all colors in the theme
     *
     * @return array<string, ColorInterface>
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * Convert theme to array
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $result = [];
        foreach ($this->colors as $name => $color) {
            $result[$name] = $color->toHex();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrimaryColor(): ColorInterface
    {
        return $this->getColor('primary');
    }

    /**
     * {@inheritdoc}
     */
    public function getSecondaryColor(): ColorInterface
    {
        return $this->getColor('secondary');
    }

    /**
     * {@inheritdoc}
     */
    public function getAccentColor(): ColorInterface
    {
        return $this->getColor('accent');
    }

    /**
     * {@inheritdoc}
     */
    public function getBackgroundColor(): ColorInterface
    {
        return $this->getColor('background');
    }

    /**
     * {@inheritdoc}
     */
    public function getSurfaceColor(): ColorInterface
    {
        return $this->getColor('surface');
    }
}
