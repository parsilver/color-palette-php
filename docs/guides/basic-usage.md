---
layout: default
title: Basic Usage Guide - Color Palette PHP
description: Learn the fundamental operations and common workflows for using Color Palette PHP
keywords: basic usage, color creation, color conversion, getting started, examples
---

# Basic Usage Guide

Learn the fundamental operations and common workflows for working with Color Palette PHP. This guide covers everything you need to know to start working with colors effectively.

<div class="quick-links">
  <a href="#creating-colors">Creating Colors</a> •
  <a href="#color-formats">Color Formats</a> •
  <a href="#color-properties">Color Properties</a> •
  <a href="#working-with-palettes">Working with Palettes</a> •
  <a href="#common-workflows">Common Workflows</a>
</div>

## Creating Colors

### Direct Instantiation

The most straightforward way to create a color is by specifying RGB values:

```php
<?php

use Farzai\ColorPalette\Color;

// Create a color with RGB values (0-255)
$red = new Color(255, 0, 0);
$blue = new Color(0, 0, 255);
$customColor = new Color(52, 152, 219); // A nice blue

echo $customColor->toHex(); // Output: #3498db
```

> **Note:** RGB values must be integers between 0 and 255. Values outside this range will throw an `InvalidArgumentException`.

### From Hex Strings

Create colors from hexadecimal color codes:

```php
<?php

use Farzai\ColorPalette\Color;

// With hash symbol
$color1 = Color::fromHex('#3498db');

// Without hash symbol (also works)
$color2 = Color::fromHex('3498db');

// Short notation is NOT supported
// Color::fromHex('#fff'); // This will throw an exception

// Always use 6-character hex codes
$white = Color::fromHex('#ffffff');
$black = Color::fromHex('#000000');
```

**Expected output:**
```
$color1->toHex() → "#3498db"
$color2->toHex() → "#3498db"
```

### From RGB Arrays

Create colors from RGB arrays in different formats:

```php
<?php

use Farzai\ColorPalette\Color;

// Associative array with 'r', 'g', 'b' keys
$color1 = Color::fromRgb(['r' => 52, 'g' => 152, 'b' => 219]);

// Indexed array
$color2 = Color::fromRgb([52, 152, 219]);

// Both produce the same color
echo $color1->toHex(); // #3498db
echo $color2->toHex(); // #3498db
```

### From HSL Values

Create colors from Hue, Saturation, and Lightness:

```php
<?php

use Farzai\ColorPalette\Color;

// HSL values: Hue (0-360), Saturation (0-100), Lightness (0-100)
$color = Color::fromHsl(204, 70, 53);

echo $color->toHex(); // #3498db

// Common color wheels positions:
$red = Color::fromHsl(0, 100, 50);      // Pure red
$green = Color::fromHsl(120, 100, 50);  // Pure green
$blue = Color::fromHsl(240, 100, 50);   // Pure blue
```

**HSL Value Ranges:**
- **Hue**: 0-360 degrees on the color wheel
- **Saturation**: 0-100 percent (0 = gray, 100 = full color)
- **Lightness**: 0-100 percent (0 = black, 50 = normal, 100 = white)

### From HSV Values

Create colors from Hue, Saturation, and Value:

```php
<?php

use Farzai\ColorPalette\Color;

// HSV values: Hue (0-360), Saturation (0-100), Value (0-100)
$color = Color::fromHsv(204, 76, 86);

echo $color->toHex(); // #3498db

// HSV is useful for brightness-based operations
$bright = Color::fromHsv(120, 100, 100); // Bright green
$dim = Color::fromHsv(120, 100, 50);     // Dim green
```

### From CMYK Values

Create colors from Cyan, Magenta, Yellow, and Key (black):

```php
<?php

use Farzai\ColorPalette\Color;

// CMYK values: all in range 0-100
$color = Color::fromCmyk(76, 31, 0, 14);

echo $color->toHex(); // #3498db

// CMYK is commonly used for print design
$printRed = Color::fromCmyk(0, 100, 100, 0);
$printBlue = Color::fromCmyk(100, 100, 0, 0);
```

### From LAB Values

Create colors from LAB color space (perceptually uniform):

```php
<?php

use Farzai\ColorPalette\Color;

// LAB values: L (0-100), A (-128 to 127), B (-128 to 127)
$color = Color::fromLab(56, -10, -37);

echo $color->toHex(); // #3498db

// LAB is useful for accurate color matching
$neutralGray = Color::fromLab(50, 0, 0);
```

## Color Formats

### Converting Between Formats

Convert colors seamlessly between different color spaces:

```php
<?php

use Farzai\ColorPalette\Color;

$color = new Color(52, 152, 219);

// Convert to different formats
$hex = $color->toHex();      // "#3498db"
$rgb = $color->toRgb();      // ['r' => 52, 'g' => 152, 'b' => 219]
$hsl = $color->toHsl();      // ['h' => 204, 's' => 70, 'l' => 53]
$hsv = $color->toHsv();      // ['h' => 204, 's' => 76, 'v' => 86]
$cmyk = $color->toCmyk();    // ['c' => 76, 'm' => 31, 'y' => 0, 'k' => 14]
$lab = $color->toLab();      // ['l' => 56, 'a' => -10, 'b' => -37]

// Access individual components
echo "Red: " . $color->getRed() . "\n";     // 52
echo "Green: " . $color->getGreen() . "\n"; // 152
echo "Blue: " . $color->getBlue() . "\n";   // 219
```

**Expected output:**
```
Hex: #3498db
RGB: {"r":52,"g":152,"b":219}
HSL: {"h":204,"s":70,"l":53}
```

### Practical Conversion Examples

```php
<?php

use Farzai\ColorPalette\Color;

// Example 1: Convert hex to RGB for CSS
$color = Color::fromHex('#3498db');
$rgb = $color->toRgb();
echo "rgb({$rgb['r']}, {$rgb['g']}, {$rgb['b']})";
// Output: rgb(52, 152, 219)

// Example 2: Get HSL for CSS
$hsl = $color->toHsl();
echo "hsl({$hsl['h']}, {$hsl['s']}%, {$hsl['l']}%)";
// Output: hsl(204, 70%, 53%)

// Example 3: Print-ready CMYK values
$cmyk = $color->toCmyk();
echo "CMYK: C{$cmyk['c']}% M{$cmyk['m']}% Y{$cmyk['y']}% K{$cmyk['k']}%";
// Output: CMYK: C76% M31% Y0% K14%
```

## Color Properties

### Brightness Analysis

Determine how light or dark a color is:

```php
<?php

use Farzai\ColorPalette\Color;

$color = new Color(52, 152, 219);

// Get brightness value (0-255)
$brightness = $color->getBrightness();
echo "Brightness: " . $brightness . "\n"; // ~135

// Check if color is light or dark (threshold is 128)
if ($color->isLight()) {
    echo "This is a light color\n";
} else {
    echo "This is a dark color\n";
}

// Alternative check
if ($color->isDark()) {
    echo "Use light text on this background\n";
}
```

**Expected output:**
```
Brightness: 135
This is a light color
```

### Luminance Calculation

Get the relative luminance (0.0 to 1.0) for contrast calculations:

```php
<?php

use Farzai\ColorPalette\Color;

$color = new Color(52, 152, 219);

// Get luminance (0.0 = darkest, 1.0 = lightest)
$luminance = $color->getLuminance();
echo "Luminance: " . round($luminance, 3) . "\n"; // ~0.275

// Compare luminance of different colors
$white = new Color(255, 255, 255);
$black = new Color(0, 0, 0);

echo "White luminance: " . $white->getLuminance() . "\n"; // 1.0
echo "Black luminance: " . $black->getLuminance() . "\n"; // 0.0
```

### Contrast Ratio

Calculate contrast ratio between two colors (important for accessibility):

```php
<?php

use Farzai\ColorPalette\Color;

$backgroundColor = new Color(52, 152, 219); // Blue
$textColor = new Color(255, 255, 255);      // White

// Get contrast ratio (1:1 to 21:1)
$contrastRatio = $backgroundColor->getContrastRatio($textColor);
echo "Contrast ratio: " . round($contrastRatio, 2) . ":1\n"; // ~3.58:1

// Check WCAG compliance
if ($contrastRatio >= 7.0) {
    echo "✓ Passes WCAG AAA (normal text)\n";
} elseif ($contrastRatio >= 4.5) {
    echo "✓ Passes WCAG AA (normal text)\n";
} elseif ($contrastRatio >= 3.0) {
    echo "✓ Passes WCAG AA (large text only)\n";
} else {
    echo "✗ Does not meet WCAG standards\n";
}
```

**WCAG Contrast Standards:**
- **AAA Normal Text**: 7:1 minimum
- **AA Normal Text**: 4.5:1 minimum
- **AA Large Text**: 3:1 minimum
- **Large Text**: 18pt+ or 14pt+ bold

## Working with Palettes

### Creating Palettes

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

// Create a palette with multiple colors
$colors = [
    new Color(52, 152, 219),   // Blue
    new Color(46, 204, 113),   // Green
    new Color(231, 76, 60),    // Red
    new Color(241, 196, 15),   // Yellow
    new Color(155, 89, 182),   // Purple
];

$palette = new ColorPalette($colors);

// Get palette information
echo "Palette has " . count($palette) . " colors\n";
```

### Accessing Palette Colors

```php
<?php

use Farzai\ColorPalette\ColorPalette;

// Access colors like an array
$firstColor = $palette[0];
echo "First color: " . $firstColor->toHex() . "\n";

// Iterate through colors
foreach ($palette->getColors() as $index => $color) {
    echo "Color {$index}: {$color->toHex()}\n";
}

// Convert entire palette to hex array
$hexColors = $palette->toArray();
print_r($hexColors);
```

**Expected output:**
```
First color: #3498db
Color 0: #3498db
Color 1: #2ecc71
Color 2: #e74c3c
Color 3: #f1c40f
Color 4: #9b59b6
```

### Palette Text Colors

Get optimal text colors for backgrounds:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

$colors = [
    new Color(52, 152, 219),   // Light blue
    new Color(44, 62, 80),     // Dark blue
    new Color(241, 196, 15),   // Yellow
];

$palette = new ColorPalette($colors);

// Get suggested text color for each background
foreach ($palette->getColors() as $bgColor) {
    $textColor = $palette->getSuggestedTextColor($bgColor);
    echo "Background: " . $bgColor->toHex() . " → ";
    echo "Text: " . $textColor->toHex() . "\n";
}
```

**Expected output:**
```
Background: #3498db → Text: #ffffff
Background: #2c3e50 → Text: #ffffff
Background: #f1c40f → Text: #000000
```

### Surface Colors

Get suggested surface colors sorted by brightness:

```php
<?php

use Farzai\ColorPalette\ColorPalette;

$surfaceColors = $palette->getSuggestedSurfaceColors();

// Available keys: 'surface', 'background', 'accent', 'surface_variant'
echo "Surface: " . $surfaceColors['surface']->toHex() . "\n";
echo "Background: " . $surfaceColors['background']->toHex() . "\n";
echo "Accent: " . $surfaceColors['accent']->toHex() . "\n";
echo "Surface Variant: " . $surfaceColors['surface_variant']->toHex() . "\n";
```

## Common Workflows

### Workflow 1: Create Color from User Input

```php
<?php

use Farzai\ColorPalette\Color;

function createColorFromInput(string $input): ?Color {
    try {
        // Try hex format first
        if (str_starts_with($input, '#') || ctype_xdigit($input)) {
            return Color::fromHex($input);
        }

        // Try RGB format: "rgb(52, 152, 219)"
        if (preg_match('/rgb\((\d+),\s*(\d+),\s*(\d+)\)/', $input, $matches)) {
            return new Color((int)$matches[1], (int)$matches[2], (int)$matches[3]);
        }

        // Try HSL format: "hsl(204, 70, 53)"
        if (preg_match('/hsl\((\d+),\s*(\d+),\s*(\d+)\)/', $input, $matches)) {
            return Color::fromHsl((float)$matches[1], (float)$matches[2], (float)$matches[3]);
        }

        return null;
    } catch (\Exception $e) {
        return null;
    }
}

// Usage examples
$color1 = createColorFromInput('#3498db');
$color2 = createColorFromInput('rgb(52, 152, 219)');
$color3 = createColorFromInput('hsl(204, 70, 53)');

if ($color1) {
    echo "Color created: " . $color1->toHex() . "\n";
}
```

### Workflow 2: Find Readable Text Color

```php
<?php

use Farzai\ColorPalette\Color;

function getReadableTextColor(Color $background): Color {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);

    // Calculate contrast ratios
    $whiteContrast = $background->getContrastRatio($white);
    $blackContrast = $background->getContrastRatio($black);

    // Return color with better contrast
    return $whiteContrast > $blackContrast ? $white : $black;
}

// Usage
$bgColor = new Color(52, 152, 219);
$textColor = getReadableTextColor($bgColor);

echo "Background: " . $bgColor->toHex() . "\n";
echo "Text: " . $textColor->toHex() . "\n";
echo "Contrast: " . $bgColor->getContrastRatio($textColor) . ":1\n";
```

**Expected output:**
```
Background: #3498db
Text: #ffffff
Contrast: 3.58:1
```

### Workflow 3: Generate Color Variants

```php
<?php

use Farzai\ColorPalette\Color;

function generateColorVariants(Color $base): array {
    return [
        'original' => $base->toHex(),
        'light' => $base->lighten(0.2)->toHex(),
        'lighter' => $base->lighten(0.4)->toHex(),
        'dark' => $base->darken(0.2)->toHex(),
        'darker' => $base->darken(0.4)->toHex(),
    ];
}

// Usage
$baseColor = new Color(52, 152, 219);
$variants = generateColorVariants($baseColor);

foreach ($variants as $name => $hex) {
    echo ucfirst($name) . ": " . $hex . "\n";
}
```

**Expected output:**
```
Original: #3498db
Light: #5dade2
Lighter: #85c1e9
Dark: #2e86c1
Darker: #21618c
```

### Workflow 4: Color Palette from Array

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

function createPaletteFromHexArray(array $hexColors): ColorPalette {
    $colors = array_map(function($hex) {
        return Color::fromHex($hex);
    }, $hexColors);

    return new ColorPalette($colors);
}

// Usage
$hexArray = ['#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6'];
$palette = createPaletteFromHexArray($hexArray);

echo "Created palette with " . count($palette) . " colors\n";

// Display palette
foreach ($palette->getColors() as $i => $color) {
    echo ($i + 1) . ". " . $color->toHex() . "\n";
}
```

### Workflow 5: Build CSS Color Variables

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

function generateCssVariables(ColorPalette $palette): string {
    $css = ":root {\n";

    foreach ($palette->getColors() as $i => $color) {
        $css .= "  --color-" . ($i + 1) . ": " . $color->toHex() . ";\n";

        $rgb = $color->toRgb();
        $css .= "  --color-" . ($i + 1) . "-rgb: {$rgb['r']}, {$rgb['g']}, {$rgb['b']};\n";

        $hsl = $color->toHsl();
        $css .= "  --color-" . ($i + 1) . "-hsl: {$hsl['h']}, {$hsl['s']}%, {$hsl['l']}%;\n";
    }

    $css .= "}\n";
    return $css;
}

// Usage
$colors = [
    new Color(52, 152, 219),
    new Color(46, 204, 113),
    new Color(231, 76, 60),
];

$palette = new ColorPalette($colors);
echo generateCssVariables($palette);
```

**Expected output:**
```css
:root {
  --color-1: #3498db;
  --color-1-rgb: 52, 152, 219;
  --color-1-hsl: 204, 70%, 53%;
  --color-2: #2ecc71;
  --color-2-rgb: 46, 204, 113;
  --color-2-hsl: 145, 63%, 49%;
  --color-3: #e74c3c;
  --color-3-rgb: 231, 76, 60;
  --color-3-hsl: 6, 78%, 57%;
}
```

## Best Practices

### Input Validation

Always validate color input from users:

```php
<?php

use Farzai\ColorPalette\Color;

function validateAndCreateColor(string $input): ?Color {
    try {
        if (preg_match('/^#[0-9A-Fa-f]{6}$/', $input)) {
            return Color::fromHex($input);
        }
        return null;
    } catch (\InvalidArgumentException $e) {
        // Log error or notify user
        error_log("Invalid color input: " . $e->getMessage());
        return null;
    }
}
```

### Error Handling

Handle exceptions gracefully:

```php
<?php

use Farzai\ColorPalette\Color;

try {
    $color = Color::fromHex($userInput);
    echo $color->toHex();
} catch (\InvalidArgumentException $e) {
    // Handle invalid input
    echo "Please provide a valid hex color (e.g., #3498db)";
}
```

### Performance Tips

```php
<?php

// Cache color objects when reusing
$cachedColors = [];

function getColor(string $hex) {
    global $cachedColors;

    if (!isset($cachedColors[$hex])) {
        $cachedColors[$hex] = Color::fromHex($hex);
    }

    return $cachedColors[$hex];
}

// Reuse color instances
$blue = getColor('#3498db');
```

## Next Steps

Now that you understand the basics:

- **[Color Extraction Guide](color-extraction)** - Extract colors from images
- **[Color Manipulation Guide](color-manipulation)** - Transform and adjust colors
- **[Theme Generation Guide](theme-generation)** - Create harmonious color schemes
- **[Advanced Techniques](advanced-techniques)** - Optimize and extend functionality

## Quick Reference

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

// Creation
$color = new Color(255, 0, 0);
$color = Color::fromHex('#3498db');
$color = Color::fromRgb(['r' => 52, 'g' => 152, 'b' => 219]);
$color = Color::fromHsl(204, 70, 53);

// Conversion
$hex = $color->toHex();
$rgb = $color->toRgb();
$hsl = $color->toHsl();

// Properties
$brightness = $color->getBrightness();
$isLight = $color->isLight();
$contrast = $color->getContrastRatio($otherColor);

// Palettes
$palette = new ColorPalette($colors);
$textColor = $palette->getSuggestedTextColor($bgColor);
$surfaces = $palette->getSuggestedSurfaceColors();
```
