<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

class PaletteGenerator
{
    private Color $baseColor;

    public function __construct(Color $baseColor)
    {
        $this->baseColor = $baseColor;
    }

    /**
     * Generate monochromatic colors
     */
    public function monochromatic(int $count = 5): ColorPalette
    {
        $colors = [$this->baseColor];
        $hsl = $this->baseColor->toHsl();
        $lightnessStep = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $lightness = $lightnessStep * $i;
            $colors[] = Color::fromHsl($hsl['h'], $hsl['s'], $lightness * 100);
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate complementary colors
     */
    public function complementary(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor,
            $this->baseColor->rotate(180),
        ]);
    }

    /**
     * Generate analogous colors
     */
    public function analogous(int $count = 3, float $angle = 30): ColorPalette
    {
        $colors = [];
        $middleIndex = floor($count / 2);

        for ($i = 0; $i < $count; $i++) {
            if ($i === $middleIndex) {
                $colors[] = $this->baseColor;
            } else {
                $rotation = ($i - $middleIndex) * $angle;
                $colors[] = $this->baseColor->rotate($rotation)->saturate(0.1);
            }
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate triadic colors
     */
    public function triadic(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor,
            $this->baseColor->rotate(120),
            $this->baseColor->rotate(240),
        ]);
    }

    /**
     * Generate tetradic (double complementary) colors
     */
    public function tetradic(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor,
            $this->baseColor->rotate(90),
            $this->baseColor->rotate(180),
            $this->baseColor->rotate(270),
        ]);
    }

    /**
     * Generate split complementary colors
     */
    public function splitComplementary(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor,
            $this->baseColor->rotate(150),
            $this->baseColor->rotate(210),
        ]);
    }

    /**
     * Generate shades (darker variations)
     */
    public function shades(int $count = 5): ColorPalette
    {
        $colors = [$this->baseColor];
        $step = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $amount = $step * $i;
            $colors[] = $this->baseColor->darken($amount);
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate tints (lighter variations)
     */
    public function tints(int $count = 5): ColorPalette
    {
        $colors = [$this->baseColor];
        $step = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $amount = $step * $i;
            $colors[] = $this->baseColor->lighten($amount);
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate a pastel palette
     */
    public function pastel(int $count = 4): ColorPalette
    {
        $colors = [];
        $hueStep = 360 / $count;

        for ($i = 0; $i < $count; $i++) {
            $hue = ($this->baseColor->toHsl()['h'] + ($hueStep * $i)) % 360;
            $colors[] = Color::fromHsl($hue, 35, 85); // Pastel colors have high lightness and low saturation
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate a vibrant palette
     */
    public function vibrant(int $count = 4): ColorPalette
    {
        $colors = [];
        $hueStep = 360 / $count;

        for ($i = 0; $i < $count; $i++) {
            $hue = ($this->baseColor->toHsl()['h'] + ($hueStep * $i)) % 360;
            $colors[] = Color::fromHsl($hue, 85, 60); // Vibrant colors have high saturation
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate a modern website theme
     */
    public function websiteTheme(): ColorPalette
    {
        $baseHsl = $this->baseColor->toHsl();

        return new ColorPalette([
            'primary' => $this->baseColor,
            'secondary' => $this->baseColor->rotate(30)->saturate(0.1),
            'accent' => $this->baseColor->rotate(180)->saturate(0.2),
            'background' => Color::fromHsl($baseHsl['h'], 10, 98),
            'surface' => Color::fromHsl($baseHsl['h'], 5, 100),
            'text' => Color::fromHsl($baseHsl['h'], 15, 15),
            'text_light' => Color::fromHsl($baseHsl['h'], 10, 30),
        ]);
    }
}
