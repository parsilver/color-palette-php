<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorInterface;

class PaletteGenerator
{
    private ColorInterface $baseColor;

    public function __construct(ColorInterface $baseColor)
    {
        $this->baseColor = $baseColor;
    }

    /**
     * Generate tints of the base color
     *
     * @param  int  $count  Number of tints to generate
     */
    public function tints(int $count): ColorPalette
    {
        $colors = [];
        $step = 100 / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $amount = $step * $i;
            $colors[] = $this->baseColor->lighten($amount);
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate shades of the base color
     *
     * @param  int  $count  Number of shades to generate
     */
    public function shades(int $count): ColorPalette
    {
        $colors = [];
        $step = 100 / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $amount = $step * $i;
            $colors[] = $this->baseColor->darken($amount);
        }

        return new ColorPalette($colors);
    }

    /**
     * Generate monochromatic colors
     *
     * @param  int  $count  Number of colors to generate
     */
    public function monochromatic(int $count): ColorPalette
    {
        $colors = [];
        $step = 100 / $count;

        for ($i = 0; $i < $count; $i++) {
            $lightness = $step * $i;
            $colors[] = $this->baseColor->withLightness($lightness);
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
     *
     * @param  int  $count  Number of colors to generate (default: 3)
     * @param  int  $angle  Angle between colors (default: 30)
     */
    public function analogous(int $count = 3, int $angle = 30): ColorPalette
    {
        $colors = [];
        $middleIndex = floor(($count - 1) / 2);

        for ($i = 0; $i < $count; $i++) {
            if ($i === $middleIndex) {
                $colors[] = new Color($this->baseColor->toHex());
            } else {
                $rotation = ($i - $middleIndex) * $angle;
                $colors[] = $this->baseColor->adjustHue($rotation);
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
     * Generate a modern vibrant color palette
     */
    public function vibrant(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor,
            $this->baseColor->rotate(60)->saturate(0.1),
            $this->baseColor->rotate(180)->saturate(0.2),
            $this->baseColor->rotate(240)->saturate(0.1),
        ]);
    }

    /**
     * Generate a pastel color palette
     */
    public function pastel(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor->lighten(0.2)->desaturate(0.1),
            $this->baseColor->rotate(90)->lighten(0.2)->desaturate(0.1),
            $this->baseColor->rotate(180)->lighten(0.2)->desaturate(0.1),
            $this->baseColor->rotate(270)->lighten(0.2)->desaturate(0.1),
        ]);
    }

    /**
     * Generate a modern color palette with golden ratio harmony
     */
    public function goldenRatio(): ColorPalette
    {
        $goldenAngle = 137.5;
        
        return new ColorPalette([
            $this->baseColor,
            $this->baseColor->rotate($goldenAngle),
            $this->baseColor->rotate($goldenAngle * 2),
            $this->baseColor->rotate($goldenAngle * 3),
        ]);
    }

    /**
     * Generate a trendy gradient-like palette
     */
    public function gradient(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor->darken(0.2),
            $this->baseColor,
            $this->baseColor->lighten(0.1)->saturate(0.1),
            $this->baseColor->lighten(0.2)->saturate(0.2),
        ]);
    }

    /**
     * Generate a modern neutral palette
     */
    public function neutral(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor->desaturate(0.6),
            $this->baseColor->desaturate(0.4),
            $this->baseColor->desaturate(0.2),
            $this->baseColor,
        ]);
    }

    /**
     * Generate an autumn-inspired palette
     */
    public function autumn(): ColorPalette
    {
        return new ColorPalette([
            $this->baseColor->rotate(-30)->saturate(0.1),
            $this->baseColor,
            $this->baseColor->rotate(30)->desaturate(0.1),
            $this->baseColor->rotate(60)->warmify(),
        ]);
    }
} 