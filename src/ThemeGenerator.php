<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ThemeGeneratorInterface;
use Farzai\ColorPalette\Contracts\ThemeInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ColorInterface;

/**
 * Default implementation of ThemeGeneratorInterface
 */
class ThemeGenerator implements ThemeGeneratorInterface
{
    /**
     * Minimum contrast ratio for text readability
     */
    private const MIN_CONTRAST_RATIO = 4.5;

    /**
     * {@inheritdoc}
     */
    public function generate(ColorPaletteInterface $palette): ThemeInterface
    {
        $colors = $palette->getColors();
        
        if (empty($colors)) {
            throw new \InvalidArgumentException('Color palette must contain at least one color');
        }

        // Sort colors by various metrics to make better choices
        $sortedByBrightness = $this->sortColorsByBrightness($colors);
        $sortedByContrast = $this->sortColorsByContrastRatio($colors);

        // Generate theme colors
        $primaryColor = $this->selectPrimaryColor($sortedByContrast);
        $secondaryColor = $this->selectSecondaryColor($sortedByContrast, $primaryColor);
        $accentColor = $this->selectAccentColor($colors, $primaryColor, $secondaryColor);
        $backgroundColor = $this->selectBackgroundColor($sortedByBrightness);
        $surfaceColor = $this->selectSurfaceColor($sortedByBrightness, $backgroundColor);

        return new Theme(
            $primaryColor,
            $secondaryColor,
            $accentColor,
            $backgroundColor,
            $surfaceColor
        );
    }

    /**
     * Sort colors by brightness
     *
     * @param array<ColorInterface> $colors
     * @return array<ColorInterface>
     */
    private function sortColorsByBrightness(array $colors): array
    {
        $sorted = $colors;
        usort($sorted, fn(ColorInterface $a, ColorInterface $b) =>
            $b->getBrightness() <=> $a->getBrightness()
        );
        return $sorted;
    }

    /**
     * Sort colors by their average contrast ratio with other colors
     *
     * @param array<ColorInterface> $colors
     * @return array<ColorInterface>
     */
    private function sortColorsByContrastRatio(array $colors): array
    {
        $sorted = $colors;
        usort($sorted, function (ColorInterface $a, ColorInterface $b) use ($colors) {
            $avgContrastA = $this->calculateAverageContrast($a, $colors);
            $avgContrastB = $this->calculateAverageContrast($b, $colors);
            return $avgContrastB <=> $avgContrastA;
        });
        return $sorted;
    }

    /**
     * Calculate average contrast ratio between a color and a set of colors
     *
     * @param ColorInterface $color
     * @param array<ColorInterface> $colors
     * @return float
     */
    private function calculateAverageContrast(ColorInterface $color, array $colors): float
    {
        $totalContrast = 0;
        $count = 0;

        foreach ($colors as $otherColor) {
            if ($color !== $otherColor) {
                $totalContrast += $color->getContrastRatio($otherColor);
                $count++;
            }
        }

        return $count > 0 ? $totalContrast / $count : 0;
    }

    /**
     * Select primary color from sorted colors
     *
     * @param array<ColorInterface> $sortedColors
     * @return ColorInterface
     */
    private function selectPrimaryColor(array $sortedColors): ColorInterface
    {
        // Choose a color with good contrast against both light and dark backgrounds
        foreach ($sortedColors as $color) {
            $lightContrast = $color->getContrastRatio(new Color(255, 255, 255));
            $darkContrast = $color->getContrastRatio(new Color(0, 0, 0));
            
            if ($lightContrast >= self::MIN_CONTRAST_RATIO && $darkContrast >= self::MIN_CONTRAST_RATIO) {
                return $color;
            }
        }

        // If no ideal color is found, use the first color with the best average contrast
        return $sortedColors[0];
    }

    /**
     * Select secondary color that complements the primary color
     *
     * @param array<ColorInterface> $sortedColors
     * @param ColorInterface $primaryColor
     * @return ColorInterface
     */
    private function selectSecondaryColor(array $sortedColors, ColorInterface $primaryColor): ColorInterface
    {
        // Find a color that has good contrast with primary but isn't too similar
        foreach ($sortedColors as $color) {
            if ($color === $primaryColor) {
                continue;
            }

            $contrast = $color->getContrastRatio($primaryColor);
            if ($contrast >= self::MIN_CONTRAST_RATIO) {
                return $color;
            }
        }

        // If no ideal color is found, create a variant of the primary color
        return $this->createColorVariant($primaryColor, 30);
    }

    /**
     * Select accent color that stands out from primary and secondary colors
     *
     * @param array<ColorInterface> $colors
     * @param ColorInterface $primaryColor
     * @param ColorInterface $secondaryColor
     * @return ColorInterface
     */
    private function selectAccentColor(
        array $colors,
        ColorInterface $primaryColor,
        ColorInterface $secondaryColor
    ): ColorInterface {
        foreach ($colors as $color) {
            if ($color === $primaryColor || $color === $secondaryColor) {
                continue;
            }

            $primaryContrast = $color->getContrastRatio($primaryColor);
            $secondaryContrast = $color->getContrastRatio($secondaryColor);

            if ($primaryContrast >= 2.0 && $secondaryContrast >= 2.0) {
                return $color;
            }
        }

        // If no suitable color is found, create a vibrant variant
        return $this->createColorVariant($primaryColor, -20);
    }

    /**
     * Select background color (usually the lightest color)
     *
     * @param array<ColorInterface> $sortedByBrightness
     * @return ColorInterface
     */
    private function selectBackgroundColor(array $sortedByBrightness): ColorInterface
    {
        // Choose the lightest color, or create a light variant if none is suitable
        foreach ($sortedByBrightness as $color) {
            if ($color->isLight()) {
                return $color;
            }
        }

        // Create a light variant if no suitable color is found
        return new Color(250, 250, 250);
    }

    /**
     * Select surface color that works well with the background
     *
     * @param array<ColorInterface> $sortedByBrightness
     * @param ColorInterface $backgroundColor
     * @return ColorInterface
     */
    private function selectSurfaceColor(
        array $sortedByBrightness,
        ColorInterface $backgroundColor
    ): ColorInterface {
        // Find a color slightly different from the background
        foreach ($sortedByBrightness as $color) {
            if ($color === $backgroundColor) {
                continue;
            }

            $contrast = $color->getContrastRatio($backgroundColor);
            if ($contrast >= 1.1 && $contrast <= 2.0) {
                return $color;
            }
        }

        // Create a slight variant of the background color
        return $this->createColorVariant($backgroundColor, $backgroundColor->isLight() ? -5 : 5);
    }

    /**
     * Create a variant of a color by adjusting its brightness
     *
     * @param ColorInterface $color
     * @param int $adjustment Percentage to adjust (-100 to 100)
     * @return ColorInterface
     */
    private function createColorVariant(ColorInterface $color, int $adjustment): ColorInterface
    {
        $rgb = $color->toRgb();
        $factor = 1 + ($adjustment / 100);

        return new Color(
            (int) min(255, max(0, round($rgb['r'] * $factor))),
            (int) min(255, max(0, round($rgb['g'] * $factor))),
            (int) min(255, max(0, round($rgb['b'] * $factor)))
        );
    }
}