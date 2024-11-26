<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;

class ColorPalette implements ColorPaletteInterface
{
    /**
     * @var array<int, ColorInterface>
     */
    private array $colors;

    /**
     * Create a new ColorPalette instance
     *
     * @param  array<int, ColorInterface>  $colors
     */
    public function __construct(array $colors)
    {
        $this->colors = array_values(array_filter($colors, fn ($color) => $color instanceof ColorInterface));
    }

    /**
     * {@inheritdoc}
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface
    {
        // Use WCAG contrast ratio guidelines
        // For best readability, we'll aim for a contrast ratio of at least 4.5:1
        $lightColor = new Color(255, 255, 255); // White
        $darkColor = new Color(0, 0, 0);        // Black

        $lightContrast = $backgroundColor->getContrastRatio($lightColor);
        $darkContrast = $backgroundColor->getContrastRatio($darkColor);

        return $lightContrast > $darkContrast ? $lightColor : $darkColor;
    }

    /**
     * {@inheritdoc}
     */
    public function getSuggestedSurfaceColors(): array
    {
        if (empty($this->colors)) {
            return [];
        }

        $suggestedColors = [];
        $baseColors = $this->colors;

        // Sort colors by brightness for better surface color selection
        usort($baseColors, fn (ColorInterface $a, ColorInterface $b) => $b->getBrightness() <=> $a->getBrightness()
        );

        // Primary surface color (usually the lightest color)
        $suggestedColors['surface'] = $baseColors[0];

        // Background color (second lightest, if available)
        $suggestedColors['background'] = isset($baseColors[1]) ? $baseColors[1] : $baseColors[0];

        // Find a good accent color (preferably a mid-tone color with good contrast)
        $accentColor = $this->findAccentColor($baseColors);
        $suggestedColors['accent'] = $accentColor;

        // For surfaces that need emphasis (like cards or modals)
        $suggestedColors['surface_variant'] = $this->createVariant(
            $suggestedColors['surface'],
            $suggestedColors['surface']->isLight() ? -10 : 10
        );

        // On-surface colors (text and icons)
        $suggestedColors['on_surface'] = $this->getSuggestedTextColor($suggestedColors['surface']);
        $suggestedColors['on_background'] = $this->getSuggestedTextColor($suggestedColors['background']);

        return $suggestedColors;
    }

    /**
     * Find a suitable accent color from the palette
     *
     * @param  array<ColorInterface>  $colors
     */
    private function findAccentColor(array $colors): ColorInterface
    {
        // Try to find a color with good contrast against both light and dark surfaces
        foreach ($colors as $color) {
            $lightContrast = $color->getContrastRatio(new Color(255, 255, 255));
            $darkContrast = $color->getContrastRatio(new Color(0, 0, 0));

            // Look for colors that have decent contrast with both light and dark
            if ($lightContrast >= 3.0 && $darkContrast >= 3.0) {
                return $color;
            }
        }

        // If no ideal accent color is found, use the middle color from the palette
        $middleIndex = (int) floor(count($colors) / 2);

        return $colors[$middleIndex];
    }

    /**
     * Create a variant of a color by adjusting its brightness
     *
     * @param  int  $adjustment  Percentage to adjust brightness (-100 to 100)
     */
    private function createVariant(ColorInterface $color, int $adjustment): ColorInterface
    {
        $rgb = $color->toRgb();
        $factor = 1 + ($adjustment / 100);

        return new Color(
            (int) min(255, max(0, round($rgb['r'] * $factor))),
            (int) min(255, max(0, round($rgb['g'] * $factor))),
            (int) min(255, max(0, round($rgb['b'] * $factor)))
        );
    }

    /**
     * Create a color palette from predefined color values
     *
     * @param  array<string>  $hexColors  Array of hex color codes
     */
    public static function fromHexColors(array $hexColors): self
    {
        $colors = array_map(
            fn (string $hex) => Color::fromHex($hex),
            array_filter($hexColors, fn ($hex) => is_string($hex))
        );

        return new self($colors);
    }

    /**
     * Create a color palette from RGB values
     *
     * @param  array<array{r: int, g: int, b: int}>  $rgbColors  Array of RGB color arrays
     */
    public static function fromRgbColors(array $rgbColors): self
    {
        $colors = array_map(
            fn (array $rgb) => Color::fromRgb($rgb),
            array_filter($rgbColors, fn ($rgb) => is_array($rgb) &&
                isset($rgb['r'], $rgb['g'], $rgb['b'])
            )
        );

        return new self($colors);
    }

    /**
     * Get the number of colors in the palette
     */
    public function count(): int
    {
        return count($this->colors);
    }

    /**
     * Convert the palette to an array of hex colors
     *
     * @return array<string>
     */
    public function toArray(): array
    {
        return array_map(fn (ColorInterface $color) => $color->toHex(), $this->colors);
    }
}
