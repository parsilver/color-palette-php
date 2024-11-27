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

    public function monochromatic(int $count = 5): ColorPalette
    {
        $colors = [$this->baseColor];
        $hsl = $this->baseColor->toHsl();
        $step = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $lightness = max(0, min(100, $hsl['l'] + ($step * $i * 100)));
            $colors[] = Color::fromHsl($hsl['h'], $hsl['s'], $lightness);
        }

        return new ColorPalette($colors);
    }

    public function complementary(): ColorPalette
    {
        $complement = $this->baseColor->rotate(180);

        return new ColorPalette([$this->baseColor, $complement]);
    }

    public function analogous(): ColorPalette
    {
        $color1 = $this->baseColor->rotate(-30);
        $color2 = $this->baseColor;
        $color3 = $this->baseColor->rotate(30);

        return new ColorPalette([$color1, $color2, $color3]);
    }

    public function triadic(): ColorPalette
    {
        $color1 = $this->baseColor;
        $color2 = $this->baseColor->rotate(120);
        $color3 = $this->baseColor->rotate(240);

        return new ColorPalette([$color1, $color2, $color3]);
    }

    public function tetradic(): ColorPalette
    {
        $color1 = $this->baseColor;
        $color2 = $this->baseColor->rotate(90);
        $color3 = $this->baseColor->rotate(180);
        $color4 = $this->baseColor->rotate(270);

        return new ColorPalette([$color1, $color2, $color3, $color4]);
    }

    public function splitComplementary(): ColorPalette
    {
        $color1 = $this->baseColor;
        $color2 = $this->baseColor->rotate(150);
        $color3 = $this->baseColor->rotate(210);

        return new ColorPalette([$color1, $color2, $color3]);
    }

    public function shades(int $count = 5): ColorPalette
    {
        $colors = [$this->baseColor];
        $step = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $colors[] = $this->baseColor->darken($step * $i);
        }

        return new ColorPalette($colors);
    }

    public function tints(int $count = 5): ColorPalette
    {
        $colors = [$this->baseColor];
        $step = 0.8 / ($count - 1);

        for ($i = 1; $i < $count; $i++) {
            $colors[] = $this->baseColor->lighten($step * $i);
        }

        return new ColorPalette($colors);
    }

    public function pastel(): ColorPalette
    {
        $colors = [];
        $hsl = $this->baseColor->toHsl();
        $baseHue = $hsl['h'];

        for ($i = 0; $i < 5; $i++) {
            $hue = ($baseHue + ($i * 72)) % 360;
            $colors[] = Color::fromHsl($hue, 25, 90);
        }

        return new ColorPalette($colors);
    }

    public function vibrant(): ColorPalette
    {
        $colors = [];
        $hsl = $this->baseColor->toHsl();
        $baseHue = $hsl['h'];

        for ($i = 0; $i < 5; $i++) {
            $hue = ($baseHue + ($i * 72)) % 360;
            $colors[] = Color::fromHsl($hue, 100, 50);
        }

        return new ColorPalette($colors);
    }

    public function websiteTheme(): ColorPalette
    {
        return new ColorPalette([
            'primary' => $this->baseColor,
            'secondary' => $this->baseColor->rotate(30)->desaturate(0.2),
            'accent' => $this->baseColor->rotate(180)->saturate(0.2),
            'background' => Color::fromHsl(0, 0, 98),
            'surface' => Color::fromHsl(0, 0, 100),
        ]);
    }
}
