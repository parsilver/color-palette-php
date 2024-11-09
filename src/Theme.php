<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ThemeInterface;

class Theme implements ThemeInterface
{
    /**
     * Create a new Theme instance
     */
    public function __construct(
        private readonly ColorInterface $primaryColor,
        private readonly ColorInterface $secondaryColor,
        private readonly ColorInterface $accentColor,
        private readonly ColorInterface $backgroundColor,
        private readonly ColorInterface $surfaceColor
    ) {}

    /**
     * {@inheritdoc}
     */
    public function getPrimaryColor(): ColorInterface
    {
        return $this->primaryColor;
    }

    /**
     * {@inheritdoc}
     */
    public function getSecondaryColor(): ColorInterface
    {
        return $this->secondaryColor;
    }

    /**
     * {@inheritdoc}
     */
    public function getAccentColor(): ColorInterface
    {
        return $this->accentColor;
    }

    /**
     * {@inheritdoc}
     */
    public function getBackgroundColor(): ColorInterface
    {
        return $this->backgroundColor;
    }

    /**
     * {@inheritdoc}
     */
    public function getSurfaceColor(): ColorInterface
    {
        return $this->surfaceColor;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'primary' => $this->primaryColor->toHex(),
            'secondary' => $this->secondaryColor->toHex(),
            'accent' => $this->accentColor->toHex(),
            'background' => $this->backgroundColor->toHex(),
            'surface' => $this->surfaceColor->toHex(),
            'on_primary' => $this->getTextColorFor($this->primaryColor)->toHex(),
            'on_secondary' => $this->getTextColorFor($this->secondaryColor)->toHex(),
            'on_accent' => $this->getTextColorFor($this->accentColor)->toHex(),
            'on_background' => $this->getTextColorFor($this->backgroundColor)->toHex(),
            'on_surface' => $this->getTextColorFor($this->surfaceColor)->toHex(),
        ];
    }

    /**
     * Get appropriate text color for a background color
     */
    private function getTextColorFor(ColorInterface $backgroundColor): ColorInterface
    {
        return $backgroundColor->isLight()
            ? new Color(0, 0, 0)     // Black text for light backgrounds
            : new Color(255, 255, 255); // White text for dark backgrounds
    }

    /**
     * Create a theme from a color palette JSON file
     *
     * @param  string  $jsonPath  Path to the JSON color palette file
     * @param  string  $baseColor  Base color name from the palette (e.g., 'blue', 'red')
     *
     * @throws \InvalidArgumentException
     */
    public static function fromPalette(string $jsonPath, string $baseColor): self
    {
        $palette = json_decode(file_get_contents($jsonPath), true);

        if (! isset($palette[$baseColor])) {
            throw new \InvalidArgumentException("Color '{$baseColor}' not found in palette");
        }

        $colors = $palette[$baseColor];

        return new self(
            Color::fromHex($colors['500']), // Primary
            Color::fromHex($colors['300']), // Secondary
            Color::fromHex($colors['a400']), // Accent
            Color::fromHex($colors['50']),  // Background
            Color::fromHex($colors['100'])  // Surface
        );
    }

    /**
     * Create a theme from hex color values
     *
     * @param  array<string, string>  $colors  Array of hex color values
     *
     * @throws \InvalidArgumentException
     */
    public static function fromHexColors(array $colors): self
    {
        $requiredColors = ['primary', 'secondary', 'accent', 'background', 'surface'];

        foreach ($requiredColors as $color) {
            if (! isset($colors[$color])) {
                throw new \InvalidArgumentException("Required color '{$color}' not provided");
            }
        }

        return new self(
            Color::fromHex($colors['primary']),
            Color::fromHex($colors['secondary']),
            Color::fromHex($colors['accent']),
            Color::fromHex($colors['background']),
            Color::fromHex($colors['surface'])
        );
    }

    /**
     * Create a monochromatic theme from a single color
     */
    public static function createMonochromatic(ColorInterface $baseColor): self
    {
        $rgb = $baseColor->toRgb();

        // Create variations of the base color
        return new self(
            $baseColor, // Primary color (original)
            new Color(  // Secondary color (lighter)
                min(255, (int) ($rgb['r'] * 1.2)),
                min(255, (int) ($rgb['g'] * 1.2)),
                min(255, (int) ($rgb['b'] * 1.2))
            ),
            new Color(  // Accent color (more saturated)
                min(255, (int) ($rgb['r'] * 1.4)),
                min(255, (int) ($rgb['g'] * 0.8)),
                min(255, (int) ($rgb['b'] * 0.8))
            ),
            new Color(  // Background color (very light)
                min(255, (int) ($rgb['r'] * 0.95 + 242)),
                min(255, (int) ($rgb['g'] * 0.95 + 242)),
                min(255, (int) ($rgb['b'] * 0.95 + 242))
            ),
            new Color(  // Surface color (light)
                min(255, (int) ($rgb['r'] * 0.9 + 230)),
                min(255, (int) ($rgb['g'] * 0.9 + 230)),
                min(255, (int) ($rgb['b'] * 0.9 + 230))
            )
        );
    }
}
