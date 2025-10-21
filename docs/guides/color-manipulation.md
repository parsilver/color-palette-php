---
layout: default
title: Color Manipulation
parent: Guides
nav_order: 4
description: Master color manipulation techniques including lightening, darkening, saturation, hue rotation, and more
keywords: color manipulation, lighten, darken, saturate, desaturate, hue rotation, color adjustment
---

# Color Manipulation Guide

Master the art of color manipulation with Color Palette PHP. This guide covers all techniques for transforming and adjusting colors programmatically.

<div class="quick-links">
  <a href="#brightness-control">Brightness Control</a> •
  <a href="#saturation-control">Saturation Control</a> •
  <a href="#hue-rotation">Hue Rotation</a> •
  <a href="#color-analysis">Color Analysis</a> •
  <a href="#accessibility">Accessibility</a>
</div>

## Brightness Control

### Lightening Colors

Make colors lighter by increasing their lightness value:

```php
<?php

use Farzai\ColorPalette\Color;

$baseColor = new Color(52, 152, 219); // #3498db - Medium blue

// Lighten by different amounts (0.0 to 1.0)
$light10 = $baseColor->lighten(0.1);  // 10% lighter
$light20 = $baseColor->lighten(0.2);  // 20% lighter
$light50 = $baseColor->lighten(0.5);  // 50% lighter

echo "Base:     " . $baseColor->toHex() . "\n";
echo "Light 10%: " . $light10->toHex() . "\n";
echo "Light 20%: " . $light20->toHex() . "\n";
echo "Light 50%: " . $light50->toHex() . "\n";
```

**Expected output:**
```
Base:      #3498db
Light 10%: #4aa3df
Light 20%: #5dade2
Light 50%: #a9d4f2
```

> **Tip:** Lightening is useful for creating hover states, disabled states, and subtle background variations.

### Darkening Colors

Make colors darker by decreasing their lightness value:

```php
<?php

use Farzai\ColorPalette\Color;

$baseColor = new Color(52, 152, 219); // #3498db - Medium blue

// Darken by different amounts (0.0 to 1.0)
$dark10 = $baseColor->darken(0.1);  // 10% darker
$dark20 = $baseColor->darken(0.2);  // 20% darker
$dark50 = $baseColor->darken(0.5);  // 50% darker

echo "Base:     " . $baseColor->toHex() . "\n";
echo "Dark 10%: " . $dark10->toHex() . "\n";
echo "Dark 20%: " . $dark20->toHex() . "\n";
echo "Dark 50%: " . $dark50->toHex() . "\n";
```

**Expected output:**
```
Base:      #3498db
Dark 10%:  #2b84c4
Dark 20%:  #2874af
Dark 50%:  #144565
```

> **Tip:** Darkening is perfect for active states, borders, and text on light backgrounds.

### Setting Specific Lightness

Set an exact lightness value instead of adjusting relatively:

```php
<?php

use Farzai\ColorPalette\Color;

$baseColor = new Color(52, 152, 219); // #3498db

// Set specific lightness levels (0.0 to 1.0)
$veryLight = $baseColor->withLightness(0.9);  // 90% lightness
$light = $baseColor->withLightness(0.7);      // 70% lightness
$medium = $baseColor->withLightness(0.5);     // 50% lightness
$dark = $baseColor->withLightness(0.3);       // 30% lightness
$veryDark = $baseColor->withLightness(0.1);   // 10% lightness

echo "Very Light: " . $veryLight->toHex() . "\n";
echo "Light:      " . $light->toHex() . "\n";
echo "Medium:     " . $medium->toHex() . "\n";
echo "Dark:       " . $dark->toHex() . "\n";
echo "Very Dark:  " . $veryDark->toHex() . "\n";
```

**Expected output:**
```
Very Light: #e1f0fa
Light:      #7ac2f0
Medium:     #2983cc
Dark:       #184f7a
Very Dark:  #081a29
```

### Practical Brightness Examples

Create complete color variations:

```php
<?php

use Farzai\ColorPalette\Color;

function createBrightnessScale(Color $baseColor, int $steps = 9): array {
    $scale = [];
    $stepSize = 1.0 / ($steps - 1);

    for ($i = 0; $i < $steps; $i++) {
        $lightness = $i * $stepSize;
        $scale[] = $baseColor->withLightness($lightness);
    }

    return $scale;
}

// Create a 9-step brightness scale
$baseColor = new Color(52, 152, 219);
$scale = createBrightnessScale($baseColor, 9);

foreach ($scale as $i => $color) {
    $step = ($i + 1) * 100;
    echo "{$step}: " . $color->toHex() . "\n";
}
```

**Expected output:**
```
100: #0d1114
200: #1b3a52
300: #285e85
400: #2983cc
500: #3aa4f5
600: #68b9f8
700: #97cffb
800: #c5e4fd
900: #f4f9ff
```

## Saturation Control

### Increasing Saturation

Make colors more vibrant by increasing saturation:

```php
<?php

use Farzai\ColorPalette\Color;

$mutedColor = new Color(120, 140, 160); // A muted blue-gray

// Increase saturation (0.0 to 1.0)
$saturate20 = $mutedColor->saturate(0.2);  // 20% more saturated
$saturate40 = $mutedColor->saturate(0.4);  // 40% more saturated
$saturate60 = $mutedColor->saturate(0.6);  // 60% more saturated

echo "Original:   " . $mutedColor->toHex() . "\n";
echo "Saturate 20%: " . $saturate20->toHex() . "\n";
echo "Saturate 40%: " . $saturate40->toHex() . "\n";
echo "Saturate 60%: " . $saturate60->toHex() . "\n";

// Check saturation values
$hsl = $mutedColor->toHsl();
echo "\nOriginal saturation: {$hsl['s']}%\n";
```

**Expected output:**
```
Original:     #788c9f
Saturate 20%: #6989a8
Saturate 40%: #5a86b1
Saturate 60%: #4b83b9
```

> **Note:** Increasing saturation makes colors more vivid and eye-catching. Use for emphasis and call-to-action elements.

### Decreasing Saturation

Create more subtle, muted colors by decreasing saturation:

```php
<?php

use Farzai\ColorPalette\Color;

$vibrantColor = new Color(231, 76, 60); // #e74c3c - Vibrant red

// Decrease saturation (0.0 to 1.0)
$desaturate20 = $vibrantColor->desaturate(0.2);  // 20% less saturated
$desaturate40 = $vibrantColor->desaturate(0.4);  // 40% less saturated
$desaturate60 = $vibrantColor->desaturate(0.6);  // 60% less saturated

echo "Vibrant:      " . $vibrantColor->toHex() . "\n";
echo "Desaturate 20%: " . $desaturate20->toHex() . "\n";
echo "Desaturate 40%: " . $desaturate40->toHex() . "\n";
echo "Desaturate 60%: " . $desaturate60->toHex() . "\n";
```

**Expected output:**
```
Vibrant:        #e74c3c
Desaturate 20%: #db6355
Desaturate 40%: #ce7a6f
Desaturate 60%: #c29188
```

> **Tip:** Desaturated colors work well for secondary elements, backgrounds, and creating professional, subdued color schemes.

### Creating Grayscale

Remove all color saturation to create grayscale:

```php
<?php

use Farzai\ColorPalette\Color;

$coloredImage = new Color(52, 152, 219); // Blue

// Complete desaturation creates grayscale
$grayscale = $coloredImage->desaturate(1.0);

echo "Colored:   " . $coloredImage->toHex() . "\n";
echo "Grayscale: " . $grayscale->toHex() . "\n";

// The grayscale version maintains the original brightness
echo "Original brightness: " . $coloredImage->getBrightness() . "\n";
echo "Grayscale brightness: " . $grayscale->getBrightness() . "\n";
```

## Hue Rotation

### Basic Hue Rotation

Rotate the color's hue around the color wheel:

```php
<?php

use Farzai\ColorPalette\Color;

$baseColor = new Color(52, 152, 219); // Blue (~204° hue)

// Rotate hue by degrees (-360 to 360)
$rotate30 = $baseColor->rotate(30);    // Slight rotation
$rotate90 = $baseColor->rotate(90);    // Quarter turn
$rotate180 = $baseColor->rotate(180);  // Opposite color (complementary)
$rotate270 = $baseColor->rotate(270);  // Three-quarter turn

echo "Base (204°):  " . $baseColor->toHex() . "\n";
echo "Rotate 30°:   " . $rotate30->toHex() . "\n";
echo "Rotate 90°:   " . $rotate90->toHex() . "\n";
echo "Rotate 180°:  " . $rotate180->toHex() . "\n";
echo "Rotate 270°:  " . $rotate270->toHex() . "\n";
```

**Expected output:**
```
Base (204°):  #3498db
Rotate 30°:   #3475db
Rotate 90°:   #41db34
Rotate 180°:  #db7834
Rotate 270°:  #db34a0
```

### Color Wheel Navigation

Navigate the color wheel systematically:

```php
<?php

use Farzai\ColorPalette\Color;

function createColorWheel(Color $baseColor, int $colors = 12): array {
    $wheel = [];
    $step = 360 / $colors;

    for ($i = 0; $i < $colors; $i++) {
        $rotation = $i * $step;
        $wheel[] = [
            'degrees' => $rotation,
            'color' => $baseColor->rotate($rotation)
        ];
    }

    return $wheel;
}

// Create a 12-color wheel
$baseColor = new Color(255, 0, 0); // Red
$colorWheel = createColorWheel($baseColor, 12);

foreach ($colorWheel as $entry) {
    echo str_pad($entry['degrees'] . "°", 6) . ": " . $entry['color']->toHex() . "\n";
}
```

**Expected output:**
```
0°:    #ff0000  (Red)
30°:   #ff8000  (Orange)
60°:   #ffff00  (Yellow)
90°:   #80ff00  (Yellow-Green)
120°:  #00ff00  (Green)
150°:  #00ff80  (Green-Cyan)
180°:  #00ffff  (Cyan)
210°:  #0080ff  (Blue-Cyan)
240°:  #0000ff  (Blue)
270°:  #8000ff  (Purple)
300°:  #ff00ff  (Magenta)
330°:  #ff0080  (Pink)
```

### Finding Complementary Colors

Get the opposite color on the color wheel:

```php
<?php

use Farzai\ColorPalette\Color;

function getComplementary(Color $color): Color {
    return $color->rotate(180);
}

// Find complementary colors
$blue = new Color(52, 152, 219);
$complementary = getComplementary($blue);

echo "Base color: " . $blue->toHex() . "\n";
echo "Complementary: " . $complementary->toHex() . "\n";

// Another example
$green = new Color(46, 204, 113);
$complementary2 = getComplementary($green);

echo "\nBase color: " . $green->toHex() . "\n";
echo "Complementary: " . $complementary2->toHex() . "\n";
```

## Color Analysis

### Brightness Detection

Analyze color brightness for UI decisions:

```php
<?php

use Farzai\ColorPalette\Color;

function analyzeColor(Color $color): array {
    $brightness = $color->getBrightness();
    $luminance = $color->getLuminance();
    $isLight = $color->isLight();
    $isDark = $color->isDark();

    return [
        'hex' => $color->toHex(),
        'brightness' => $brightness,
        'luminance' => round($luminance, 3),
        'is_light' => $isLight,
        'is_dark' => $isDark,
        'recommended_text' => $isLight ? '#000000' : '#ffffff'
    ];
}

// Analyze different colors
$colors = [
    new Color(255, 255, 255),  // White
    new Color(52, 152, 219),   // Blue
    new Color(0, 0, 0),        // Black
];

foreach ($colors as $color) {
    $analysis = analyzeColor($color);
    echo "Color: {$analysis['hex']}\n";
    echo "  Brightness: {$analysis['brightness']} (0-255)\n";
    echo "  Luminance: {$analysis['luminance']} (0-1)\n";
    echo "  Light: " . ($analysis['is_light'] ? 'Yes' : 'No') . "\n";
    echo "  Text Color: {$analysis['recommended_text']}\n\n";
}
```

**Expected output:**
```
Color: #ffffff
  Brightness: 255 (0-255)
  Luminance: 1.000 (0-1)
  Light: Yes
  Text Color: #000000

Color: #3498db
  Brightness: 135 (0-255)
  Luminance: 0.275 (0-1)
  Light: Yes
  Text Color: #000000

Color: #000000
  Brightness: 0 (0-255)
  Luminance: 0.000 (0-1)
  Light: No
  Text Color: #ffffff
```

### Luminance Calculation

Understand relative luminance for contrast calculations:

```php
<?php

use Farzai\ColorPalette\Color;

$colors = [
    'White' => new Color(255, 255, 255),
    'Light Gray' => new Color(200, 200, 200),
    'Medium Gray' => new Color(128, 128, 128),
    'Dark Gray' => new Color(50, 50, 50),
    'Black' => new Color(0, 0, 0),
];

echo "Luminance Scale (0.0 = darkest, 1.0 = lightest):\n\n";

foreach ($colors as $name => $color) {
    $luminance = $color->getLuminance();
    echo str_pad($name, 15) . ": " . round($luminance, 3) . "\n";
}
```

**Expected output:**
```
Luminance Scale (0.0 = darkest, 1.0 = lightest):

White          : 1.000
Light Gray     : 0.528
Medium Gray    : 0.216
Dark Gray      : 0.029
Black          : 0.000
```

## Accessibility

### Contrast Ratio Calculations

Ensure colors meet WCAG accessibility standards:

```php
<?php

use Farzai\ColorPalette\Color;

function checkContrast(Color $background, Color $text): array {
    $contrast = $background->getContrastRatio($text);

    return [
        'ratio' => round($contrast, 2),
        'aa_normal' => $contrast >= 4.5,    // 4.5:1 for normal text
        'aa_large' => $contrast >= 3.0,     // 3:1 for large text
        'aaa_normal' => $contrast >= 7.0,   // 7:1 for normal text
        'aaa_large' => $contrast >= 4.5,    // 4.5:1 for large text
    ];
}

// Test different color combinations
$background = new Color(52, 152, 219); // Blue
$white = new Color(255, 255, 255);
$black = new Color(0, 0, 0);
$gray = new Color(128, 128, 128);

echo "Blue Background (#3498db) Contrast Tests:\n\n";

foreach (['white' => $white, 'black' => $black, 'gray' => $gray] as $name => $textColor) {
    $result = checkContrast($background, $textColor);

    echo ucfirst($name) . " Text:\n";
    echo "  Contrast Ratio: {$result['ratio']}:1\n";
    echo "  WCAG AA (Normal): " . ($result['aa_normal'] ? '✓ Pass' : '✗ Fail') . "\n";
    echo "  WCAG AA (Large):  " . ($result['aa_large'] ? '✓ Pass' : '✗ Fail') . "\n";
    echo "  WCAG AAA (Normal): " . ($result['aaa_normal'] ? '✓ Pass' : '✗ Fail') . "\n";
    echo "  WCAG AAA (Large):  " . ($result['aaa_large'] ? '✓ Pass' : '✗ Fail') . "\n\n";
}
```

**Expected output:**
```
Blue Background (#3498db) Contrast Tests:

White Text:
  Contrast Ratio: 3.58:1
  WCAG AA (Normal): ✗ Fail
  WCAG AA (Large):  ✓ Pass
  WCAG AAA (Normal): ✗ Fail
  WCAG AAA (Large):  ✗ Fail

Black Text:
  Contrast Ratio: 5.88:1
  WCAG AA (Normal): ✓ Pass
  WCAG AA (Large):  ✓ Pass
  WCAG AAA (Normal): ✗ Fail
  WCAG AAA (Large):  ✓ Pass

Gray Text:
  Contrast Ratio: 1.65:1
  WCAG AA (Normal): ✗ Fail
  WCAG AA (Large):  ✗ Fail
  WCAG AAA (Normal): ✗ Fail
  WCAG AAA (Large):  ✗ Fail
```

### Finding Accessible Text Colors

Automatically find accessible text colors:

```php
<?php

use Farzai\ColorPalette\Color;

function findAccessibleTextColor(Color $background, float $minContrast = 4.5): Color {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);

    $whiteContrast = $background->getContrastRatio($white);
    $blackContrast = $background->getContrastRatio($black);

    // If white meets the requirement and has better contrast, use white
    if ($whiteContrast >= $minContrast && $whiteContrast > $blackContrast) {
        return $white;
    }

    // If black meets the requirement, use black
    if ($blackContrast >= $minContrast) {
        return $black;
    }

    // If neither meets requirement, use the one with better contrast
    return $whiteContrast > $blackContrast ? $white : $black;
}

// Test with various backgrounds
$backgrounds = [
    'Light Blue' => new Color(174, 214, 241),
    'Medium Blue' => new Color(52, 152, 219),
    'Dark Blue' => new Color(21, 67, 96),
    'Yellow' => new Color(241, 196, 15),
];

echo "Accessible Text Colors (WCAG AA - 4.5:1):\n\n";

foreach ($backgrounds as $name => $bgColor) {
    $textColor = findAccessibleTextColor($bgColor, 4.5);
    $contrast = $bgColor->getContrastRatio($textColor);

    echo str_pad($name, 15) . ": " . $bgColor->toHex();
    echo " → Text: " . $textColor->toHex();
    echo " (Contrast: " . round($contrast, 2) . ":1)\n";
}
```

### Contrast-Aware Color Adjustment

Adjust colors to meet accessibility requirements:

```php
<?php

use Farzai\ColorPalette\Color;

function makeAccessible(Color $background, Color $text, float $targetContrast = 4.5): Color {
    $currentContrast = $background->getContrastRatio($text);

    if ($currentContrast >= $targetContrast) {
        return $text; // Already accessible
    }

    // Try darkening or lightening the text color
    $maxAttempts = 20;
    $adjustment = 0.05;

    for ($i = 0; $i < $maxAttempts; $i++) {
        // Try darkening
        $darker = $text->darken($adjustment * ($i + 1));
        if ($background->getContrastRatio($darker) >= $targetContrast) {
            return $darker;
        }

        // Try lightening
        $lighter = $text->lighten($adjustment * ($i + 1));
        if ($background->getContrastRatio($lighter) >= $targetContrast) {
            return $lighter;
        }
    }

    // If adjustment didn't work, return white or black based on background
    return $background->isLight() ? new Color(0, 0, 0) : new Color(255, 255, 255);
}

// Example usage
$background = new Color(52, 152, 219);  // Blue
$poorText = new Color(100, 180, 230);   // Light blue (poor contrast)

echo "Original text: " . $poorText->toHex() . "\n";
echo "Original contrast: " . round($background->getContrastRatio($poorText), 2) . ":1\n\n";

$accessibleText = makeAccessible($background, $poorText, 4.5);

echo "Adjusted text: " . $accessibleText->toHex() . "\n";
echo "New contrast: " . round($background->getContrastRatio($accessibleText), 2) . ":1\n";
```

## Real-World Examples

### Example 1: Button State Generator

Generate hover and active states for buttons:

```php
<?php

use Farzai\ColorPalette\Color;

class ButtonStateGenerator {
    public function generateStates(Color $baseColor): array {
        return [
            'default' => $baseColor->toHex(),
            'hover' => $baseColor->lighten(0.1)->toHex(),
            'active' => $baseColor->darken(0.1)->toHex(),
            'disabled' => $baseColor->desaturate(0.5)->lighten(0.2)->toHex(),
            'focus_ring' => $baseColor->saturate(0.2)->lighten(0.3)->toHex(),
        ];
    }

    public function generateCss(string $className, Color $baseColor): string {
        $states = $this->generateStates($baseColor);

        return ".{$className} {
  background-color: {$states['default']};
}

.{$className}:hover {
  background-color: {$states['hover']};
}

.{$className}:active {
  background-color: {$states['active']};
}

.{$className}:disabled {
  background-color: {$states['disabled']};
}

.{$className}:focus {
  box-shadow: 0 0 0 3px {$states['focus_ring']};
}";
    }
}

// Usage
$generator = new ButtonStateGenerator();
$primaryButton = new Color(52, 152, 219);

echo $generator->generateCss('btn-primary', $primaryButton);
```

### Example 2: Dark Mode Converter

Convert light mode colors to dark mode:

```php
<?php

use Farzai\ColorPalette\Color;

class DarkModeConverter {
    public function convert(array $lightModeColors): array {
        $darkMode = [];

        foreach ($lightModeColors as $name => $color) {
            if ($color->isLight()) {
                // Light colors become dark
                $darkMode[$name] = $color->darken(0.7)->desaturate(0.1);
            } else {
                // Dark colors become light
                $darkMode[$name] = $color->lighten(0.6)->desaturate(0.1);
            }
        }

        return $darkMode;
    }
}

// Usage
$lightMode = [
    'background' => new Color(255, 255, 255),
    'text' => new Color(0, 0, 0),
    'primary' => new Color(52, 152, 219),
    'surface' => new Color(245, 245, 245),
];

$converter = new DarkModeConverter();
$darkMode = $converter->convert($lightMode);

echo "Light Mode → Dark Mode Conversion:\n\n";

foreach ($lightMode as $name => $lightColor) {
    echo ucfirst($name) . ":\n";
    echo "  Light: " . $lightColor->toHex() . "\n";
    echo "  Dark:  " . $darkMode[$name]->toHex() . "\n\n";
}
```

## Next Steps

Continue exploring color capabilities:

- **[Theme Generation Guide](theme-generation)** - Create harmonious color schemes
- **[Color Extraction Guide](color-extraction)** - Extract colors from images
- **[Advanced Techniques](advanced-techniques)** - Optimize and extend functionality
- **[API Reference](../api/)** - Detailed API documentation

## Quick Reference

```php
<?php

use Farzai\ColorPalette\Color;

$color = new Color(52, 152, 219);

// Brightness
$lighter = $color->lighten(0.2);
$darker = $color->darken(0.2);
$specific = $color->withLightness(0.5);

// Saturation
$saturated = $color->saturate(0.3);
$desaturated = $color->desaturate(0.3);

// Hue
$rotated = $color->rotate(180);

// Analysis
$brightness = $color->getBrightness();
$luminance = $color->getLuminance();
$isLight = $color->isLight();
$isDark = $color->isDark();
$contrast = $color->getContrastRatio($otherColor);
```
