---
layout: default
title: Theme Generation Guide - Color Palette PHP
description: Learn how to generate harmonious color themes and schemes using color theory principles
keywords: theme generation, color schemes, palette generator, color harmony, complementary, analogous, triadic
---

# Theme Generation Guide

Create beautiful, harmonious color themes using Color Palette PHP's theme generation capabilities. This guide covers color theory principles and practical theme creation techniques.

<div class="quick-links">
  <a href="#color-schemes">Color Schemes</a> •
  <a href="#theme-types">Theme Types</a> •
  <a href="#from-images">From Images</a> •
  <a href="#website-themes">Website Themes</a> •
  <a href="#custom-themes">Custom Themes</a>
</div>

## Color Schemes

### Complementary Colors

Colors opposite each other on the color wheel create high contrast and vibrant looks:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate complementary palette
$complementary = $generator->complementary();

echo "Complementary Color Scheme:\n";
foreach ($complementary->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

**Expected output:**
```
Complementary Color Scheme:
1. #3498db (Blue)
2. #db7834 (Orange)
```

> **Use Case:** Create bold, energetic designs. Perfect for call-to-action buttons against primary backgrounds.

### Analogous Colors

Adjacent colors on the color wheel create harmonious, serene palettes:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate analogous palette (30° apart)
$analogous = $generator->analogous();

echo "Analogous Color Scheme:\n";
foreach ($analogous->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

**Expected output:**
```
Analogous Color Scheme:
1. #3475db (Blue-Purple)
2. #3498db (Blue)
3. #34bbd8 (Blue-Cyan)
```

> **Use Case:** Create calming, cohesive designs. Ideal for backgrounds and gradient compositions.

### Triadic Colors

Three colors equally spaced on the color wheel (120° apart):

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate triadic palette
$triadic = $generator->triadic();

echo "Triadic Color Scheme:\n";
foreach ($triadic->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

**Expected output:**
```
Triadic Color Scheme:
1. #3498db (Blue)
2. #db3499 (Pink)
3. #98db34 (Yellow-Green)
```

> **Use Case:** Create vibrant, balanced designs with strong visual contrast.

### Tetradic Colors

Four colors forming a rectangle on the color wheel (90° apart):

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate tetradic palette
$tetradic = $generator->tetradic();

echo "Tetradic Color Scheme:\n";
foreach ($tetradic->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

**Expected output:**
```
Tetradic Color Scheme:
1. #3498db (Blue)
2. #9834db (Purple)
3. #db7834 (Orange)
4. #78db34 (Green)
```

> **Use Case:** Rich, complex designs with plenty of color variety. Works well for infographics and data visualization.

### Split Complementary

Base color plus two colors adjacent to its complement:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate split complementary palette
$splitComp = $generator->splitComplementary();

echo "Split Complementary Scheme:\n";
foreach ($splitComp->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

**Expected output:**
```
Split Complementary Scheme:
1. #3498db (Blue)
2. #db5134 (Red-Orange)
3. #dbb534 (Yellow-Orange)
```

> **Use Case:** Less tension than complementary, but still vibrant. Great for layouts needing visual interest without being overwhelming.

## Theme Types

### Monochromatic Themes

Create variations of a single hue with different lightness:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate monochromatic palette
$monochrome = $generator->monochromatic(7);

echo "Monochromatic Theme (7 shades):\n";
foreach ($monochrome->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . " (L: " . $color->toHsl()['l'] . "%)\n";
}
```

**Expected output:**
```
Monochromatic Theme (7 shades):
1. #3498db (L: 53%)
2. #4daae1 (L: 67%)
3. #66bbe6 (L: 80%)
4. #7fcdec (L: 93%)
5. #98def1 (L: 107%)
6. #b1eff7 (L: 120%)
7. #cbfffc (L: 133%)
```

> **Use Case:** Elegant, professional designs. Perfect for corporate branding and minimalist interfaces.

### Shades and Tints

Create darker shades or lighter tints:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate shades (darker versions)
$shades = $generator->shades(5);

echo "Shades (Darker):\n";
foreach ($shades->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}

echo "\n";

// Generate tints (lighter versions)
$tints = $generator->tints(5);

echo "Tints (Lighter):\n";
foreach ($tints->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

**Expected output:**
```
Shades (Darker):
1. #3498db
2. #2b7db0
3. #226385
4. #19495a
5. #10302f

Tints (Lighter):
1. #3498db
2. #5daee2
3. #86c3e9
4. #afd9f0
5. #d8eff7
```

### Pastel Themes

Create soft, muted pastel colors:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(231, 76, 60); // Red
$generator = new PaletteGenerator($baseColor);

// Generate pastel palette
$pastel = $generator->pastel();

echo "Pastel Theme:\n";
foreach ($pastel->getColors() as $i => $color) {
    $hsl = $color->toHsl();
    echo ($i + 1) . ". " . $color->toHex();
    echo " (S: {$hsl['s']}%, L: {$hsl['l']}%)\n";
}
```

**Expected output:**
```
Pastel Theme:
1. #f5b7b1 (S: 35%, L: 84%)
2. #f8d7da (S: 20%, L: 90%)
3. #fdecea (S: 10%, L: 95%)
```

> **Use Case:** Soft, friendly designs. Perfect for children's products, wellness apps, and spring/summer themes.

### Vivid Themes

Create highly saturated, vibrant colors:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate vivid palette
$vivid = $generator->vivid();

echo "Vivid Theme:\n";
foreach ($vivid->getColors() as $i => $color) {
    $hsl = $color->toHsl();
    echo ($i + 1) . ". " . $color->toHex();
    echo " (S: {$hsl['s']}%, L: {$hsl['l']}%)\n";
}
```

**Expected output:**
```
Vivid Theme:
1. #0088ff (S: 100%, L: 50%)
2. #00aaff (S: 100%, L: 50%)
3. #00ccff (S: 100%, L: 50%)
```

> **Use Case:** Eye-catching, energetic designs. Great for sports brands, entertainment, and youth-oriented products.

## From Images

### Extract and Generate Theme

Create themes from image colors:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\PaletteGenerator;

// Extract colors from image
$image = ImageFactory::createFromPath('photo.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5);

// Get dominant color
$dominantColor = $palette[0];

// Generate theme from dominant color
$generator = new PaletteGenerator($dominantColor);
$theme = $generator->analogous();

echo "Image Dominant Color: " . $dominantColor->toHex() . "\n\n";
echo "Generated Analogous Theme:\n";
foreach ($theme->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

### Surface Colors from Image

Get UI-ready surface colors:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('hero-image.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 10);

// Get suggested surface colors
$surfaces = $palette->getSuggestedSurfaceColors();

echo "Surface Colors from Image:\n";
echo "Surface:        " . $surfaces['surface']->toHex() . " (Lightest)\n";
echo "Background:     " . $surfaces['background']->toHex() . "\n";
echo "Accent:         " . $surfaces['accent']->toHex() . "\n";
echo "Surface Variant: " . $surfaces['surface_variant']->toHex() . "\n";
```

## Website Themes

### Complete Website Theme

Generate a full website color system:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

class WebsiteThemeGenerator {
    public function generate(Color $primaryColor): array {
        $generator = new PaletteGenerator($primaryColor);

        // Generate color schemes
        $triadic = $generator->triadic();
        $shades = $generator->shades(5);
        $tints = $generator->tints(5);

        // Build theme
        $theme = [
            // Primary colors
            'primary' => $primaryColor->toHex(),
            'primary-dark' => $primaryColor->darken(0.2)->toHex(),
            'primary-light' => $primaryColor->lighten(0.2)->toHex(),

            // Secondary (from triadic)
            'secondary' => $triadic[1]->toHex(),
            'secondary-dark' => $triadic[1]->darken(0.2)->toHex(),
            'secondary-light' => $triadic[1]->lighten(0.2)->toHex(),

            // Accent (from triadic)
            'accent' => $triadic[2]->toHex(),

            // Neutrals
            'background' => '#ffffff',
            'surface' => '#f5f5f5',
            'text' => '#212121',
            'text-secondary' => '#757575',

            // States
            'success' => '#4caf50',
            'warning' => '#ff9800',
            'error' => '#f44336',
            'info' => $primaryColor->toHex(),
        ];

        return $theme;
    }

    public function generateCss(array $theme): string {
        $css = ":root {\n";
        foreach ($theme as $name => $color) {
            $css .= "  --color-{$name}: {$color};\n";
        }
        $css .= "}\n";
        return $css;
    }
}

// Usage
$generator = new WebsiteThemeGenerator();
$theme = $generator->generate(new Color(52, 152, 219));

echo $generator->generateCss($theme);
```

**Expected output:**
```css
:root {
  --color-primary: #3498db;
  --color-primary-dark: #2874af;
  --color-primary-light: #5dade2;
  --color-secondary: #db3499;
  --color-secondary-dark: #af2977;
  --color-secondary-light: #e25db6;
  --color-accent: #98db34;
  --color-background: #ffffff;
  --color-surface: #f5f5f5;
  --color-text: #212121;
  --color-text-secondary: #757575;
  --color-success: #4caf50;
  --color-warning: #ff9800;
  --color-error: #f44336;
  --color-info: #3498db;
}
```

### Material Design Theme

Generate Material Design-style themes:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

class MaterialThemeGenerator {
    public function generate(Color $primary, Color $secondary): array {
        $palette = new ColorPalette([$primary, $secondary]);

        $theme = [
            // Primary palette
            'primary-main' => $primary->toHex(),
            'primary-light' => $primary->lighten(0.2)->toHex(),
            'primary-dark' => $primary->darken(0.2)->toHex(),
            'primary-contrast' => $palette->getSuggestedTextColor($primary)->toHex(),

            // Secondary palette
            'secondary-main' => $secondary->toHex(),
            'secondary-light' => $secondary->lighten(0.2)->toHex(),
            'secondary-dark' => $secondary->darken(0.2)->toHex(),
            'secondary-contrast' => $palette->getSuggestedTextColor($secondary)->toHex(),

            // Background
            'background-default' => '#fafafa',
            'background-paper' => '#ffffff',

            // Text
            'text-primary' => 'rgba(0, 0, 0, 0.87)',
            'text-secondary' => 'rgba(0, 0, 0, 0.60)',
            'text-disabled' => 'rgba(0, 0, 0, 0.38)',

            // Divider
            'divider' => 'rgba(0, 0, 0, 0.12)',
        ];

        return $theme;
    }
}

// Usage
$generator = new MaterialThemeGenerator();
$primary = new Color(63, 81, 181);   // Indigo
$secondary = new Color(255, 64, 129); // Pink

$theme = $generator->generate($primary, $secondary);

echo "Material Design Theme:\n";
foreach ($theme as $name => $value) {
    echo "{$name}: {$value}\n";
}
```

### Dark Mode Theme

Generate dark mode color themes:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

class DarkModeThemeGenerator {
    public function generate(Color $primaryColor): array {
        $generator = new PaletteGenerator($primaryColor);

        // Adjust primary color for dark mode (slightly desaturated)
        $darkPrimary = $primaryColor->desaturate(0.1)->lighten(0.1);

        return [
            // Colors
            'primary' => $darkPrimary->toHex(),
            'secondary' => $darkPrimary->rotate(180)->toHex(),

            // Backgrounds (dark to light)
            'background' => '#121212',
            'surface' => '#1e1e1e',
            'surface-variant' => '#2d2d2d',

            // Text (light on dark)
            'text-primary' => 'rgba(255, 255, 255, 0.87)',
            'text-secondary' => 'rgba(255, 255, 255, 0.60)',
            'text-disabled' => 'rgba(255, 255, 255, 0.38)',

            // Divider
            'divider' => 'rgba(255, 255, 255, 0.12)',

            // Elevation overlays
            'elevation-1' => 'rgba(255, 255, 255, 0.05)',
            'elevation-2' => 'rgba(255, 255, 255, 0.07)',
            'elevation-3' => 'rgba(255, 255, 255, 0.08)',
        ];
    }
}

// Usage
$generator = new DarkModeThemeGenerator();
$darkTheme = $generator->generate(new Color(52, 152, 219));

echo "Dark Mode Theme:\n";
foreach ($darkTheme as $name => $value) {
    echo str_pad($name . ':', 20) . $value . "\n";
}
```

## Custom Themes

### Brand Color System

Create a complete brand color system:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

class BrandColorSystem {
    public function generate(Color $brandColor): array {
        $generator = new PaletteGenerator($brandColor);

        // Generate 9-step scale for each color
        $scale = $this->generateScale($brandColor, 9);

        // Generate complementary color scale
        $complementary = $brandColor->rotate(180);
        $compScale = $this->generateScale($complementary, 9);

        return [
            'primary' => $scale,
            'secondary' => $compScale,
        ];
    }

    private function generateScale(Color $baseColor, int $steps): array {
        $scale = [];
        $hsl = $baseColor->toHsl();

        for ($i = 0; $i < $steps; $i++) {
            $lightness = 95 - ($i * 10); // 95, 85, 75, ..., 15, 5
            $color = Color::fromHsl($hsl['h'], $hsl['s'], $lightness);
            $scale[($i + 1) * 100] = $color->toHex();
        }

        return $scale;
    }
}

// Usage
$system = new BrandColorSystem();
$colors = $system->generate(new Color(52, 152, 219));

echo "Primary Scale:\n";
foreach ($colors['primary'] as $step => $hex) {
    echo "  {$step}: {$hex}\n";
}

echo "\nSecondary Scale:\n";
foreach ($colors['secondary'] as $step => $hex) {
    echo "  {$step}: {$hex}\n";
}
```

### Semantic Color Theme

Create theme with semantic color names:

```php
<?php

use Farzai\ColorPalette\Color;

class SemanticThemeGenerator {
    public function generate(): array {
        return [
            // Success (green)
            'success' => new Color(46, 204, 113),
            'success-light' => Color::fromHex('#48c774'),
            'success-dark' => Color::fromHex('#2ecc71'),

            // Warning (yellow/orange)
            'warning' => new Color(241, 196, 15),
            'warning-light' => Color::fromHex('#ffdd57'),
            'warning-dark' => Color::fromHex('#f1c40f'),

            // Error (red)
            'error' => new Color(231, 76, 60),
            'error-light' => Color::fromHex('#ff6b6b'),
            'error-dark' => Color::fromHex('#e74c3c'),

            // Info (blue)
            'info' => new Color(52, 152, 219),
            'info-light' => Color::fromHex('#3498db'),
            'info-dark' => Color::fromHex('#2980b9'),

            // Neutral (gray)
            'neutral-100' => new Color(245, 245, 245),
            'neutral-200' => new Color(238, 238, 238),
            'neutral-300' => new Color(224, 224, 224),
            'neutral-500' => new Color(158, 158, 158),
            'neutral-700' => new Color(97, 97, 97),
            'neutral-900' => new Color(33, 33, 33),
        ];
    }

    public function toArray(array $theme): array {
        $result = [];
        foreach ($theme as $name => $color) {
            $result[$name] = $color->toHex();
        }
        return $result;
    }
}

// Usage
$generator = new SemanticThemeGenerator();
$theme = $generator->generate();
$themeArray = $generator->toArray($theme);

echo "Semantic Color Theme:\n";
foreach ($themeArray as $name => $hex) {
    echo str_pad($name . ':', 20) . $hex . "\n";
}
```

## Real-World Examples

### Example 1: E-commerce Theme

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

class EcommerceThemeGenerator {
    public function generate(Color $brandColor): array {
        $generator = new PaletteGenerator($brandColor);
        $triadic = $generator->triadic();

        return [
            // Brand
            'brand-primary' => $brandColor->toHex(),
            'brand-secondary' => $triadic[1]->toHex(),

            // Actions
            'add-to-cart' => '#00a854',     // Green
            'buy-now' => $brandColor->saturate(0.2)->toHex(),
            'sale' => '#ff4d4f',            // Red

            // Product ratings
            'rating-star' => '#fadb14',     // Gold

            // Trust indicators
            'verified' => '#52c41a',        // Green
            'bestseller' => '#1890ff',      // Blue

            // Backgrounds
            'product-bg' => '#ffffff',
            'category-bg' => '#fafafa',
            'footer-bg' => '#001529',
        ];
    }
}

$generator = new EcommerceThemeGenerator();
$theme = $generator->generate(new Color(230, 0, 122));

echo "E-commerce Theme:\n";
foreach ($theme as $name => $color) {
    echo "{$name}: {$color}\n";
}
```

## Next Steps

- **[Color Extraction Guide](color-extraction)** - Extract colors from images
- **[Color Manipulation Guide](color-manipulation)** - Transform colors
- **[Advanced Techniques](advanced-techniques)** - Optimize and extend
- **[API Reference](../api/)** - Complete API documentation

## Quick Reference

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(52, 152, 219);
$generator = new PaletteGenerator($baseColor);

// Color schemes
$complementary = $generator->complementary();
$analogous = $generator->analogous();
$triadic = $generator->triadic();
$tetradic = $generator->tetradic();
$splitComp = $generator->splitComplementary();

// Theme types
$monochrome = $generator->monochromatic(5);
$shades = $generator->shades(5);
$tints = $generator->tints(5);
$pastel = $generator->pastel();
$vivid = $generator->vivid();
```
