<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;

class ThemeGenerator
{
    /**
     * Generate a theme from a color palette
     */
    public function generate(ColorPaletteInterface $palette, array $options = []): Theme
    {
        $colors = $palette->getColors();
        if (empty($colors)) {
            throw new \InvalidArgumentException('Color palette must not be empty');
        }

        $primary = $colors[0];
        $secondary = $colors[1] ?? $primary->rotate(180);
        $accent = $colors[2] ?? $primary->rotate(120);
        $background = new Color('#ffffff');
        $surface = new Color('#f5f5f5');

        $colors = [
            'primary' => $primary,
            'secondary' => $secondary,
            'accent' => $accent,
            'background' => $background,
            'surface' => $surface,
            'on_primary' => $this->getContrastColor($primary),
            'on_secondary' => $this->getContrastColor($secondary),
            'on_accent' => $this->getContrastColor($accent),
            'on_background' => $this->getContrastColor($background),
            'on_surface' => $this->getContrastColor($surface),
        ];

        return new Theme($colors);
    }

    /**
     * Generate a theme with custom options
     *
     * @param  array<string, mixed>  $options
     */
    public function generateWithOptions(ColorInterface $baseColor, array $options = []): Theme
    {
        $colors = [
            'primary' => $baseColor,
            'secondary' => $baseColor->rotate($options['secondary_rotation'] ?? 180),
            'accent' => $baseColor->rotate($options['accent_rotation'] ?? 120),
            'background' => new Color('#ffffff'),
            'surface' => new Color('#f5f5f5'),
        ];

        $colors['on_primary'] = $this->getContrastColor($colors['primary']);
        $colors['on_secondary'] = $this->getContrastColor($colors['secondary']);
        $colors['on_accent'] = $this->getContrastColor($colors['accent']);
        $colors['on_background'] = $this->getContrastColor($colors['background']);
        $colors['on_surface'] = $this->getContrastColor($colors['surface']);

        return new Theme($colors);
    }

    /**
     * Get a contrasting color for text
     */
    private function getContrastColor(ColorInterface $background): ColorInterface
    {
        return $background->isLight() ? new Color('#000000') : new Color('#ffffff');
    }
}
