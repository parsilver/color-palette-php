---
layout: default
title: Color Manipulation
description: Master color manipulation techniques including lightening, darkening, saturation, and hue rotation
parent: Examples
grand_parent: Home
nav_order: 4
keywords: color manipulation, lighten darken, color saturation, hue rotation
---

# Basic Color Manipulation Examples

## Creating Colors

### From Hex Values

```php
use Farzai\ColorPalette\Color;

// Create a red color
$red = Color::fromHex('#ff0000');

// Without hash symbol is also supported
$red = Color::fromHex('ff0000');
```

### From RGB Values

```php
// Create using RGB values (array format)
$blue = Color::fromRgb([0, 0, 255]);

// Or with associative array
$blue = Color::fromRgb(['r' => 0, 'g' => 0, 'b' => 255]);
```

## Basic Manipulations

### Lightening and Darkening

```php
$color = Color::fromHex('#2196f3');

// Create lighter variations (0.0-1.0, where 0.2 = 20%)
$lighter = $color->lighten(0.2);  // 20% lighter
$evenLighter = $color->lighten(0.4);  // 40% lighter

// Create darker variations
$darker = $color->darken(0.2);  // 20% darker
$evenDarker = $color->darken(0.4);  // 40% darker

// Set absolute lightness (0.0-1.0)
$specificLight = $color->withLightness(0.7);  // Set to 70% lightness
```

### Saturation Adjustments

```php
// Increase saturation (0.0-1.0, where 0.2 = 20%)
$moreSaturated = $color->saturate(0.2);

// Decrease saturation
$lessSaturated = $color->desaturate(0.2);

// Complete desaturation (grayscale)
$grayscale = $color->desaturate(1.0);
```

## Color Information

### Getting Color Values

```php
$color = Color::fromHex('#2196f3');

// Get different formats
$hex = $color->toHex();  // '#2196f3'
$rgb = $color->toRgb();  // ['r' => 33, 'g' => 150, 'b' => 243]
$hsl = $color->toHsl();  // ['h' => 207, 's' => 90, 'l' => 54]
$hsv = $color->toHsv();  // ['h' => 207, 's' => 86, 'v' => 95]
$cmyk = $color->toCmyk(); // ['c' => 86, 'm' => 38, 'y' => 0, 'k' => 5]
$lab = $color->toLab();   // ['l' => 55, 'a' => -5, 'b' => -45]

// Get individual components
$red = $color->getRed();      // 33
$green = $color->getGreen();  // 150
$blue = $color->getBlue();    // 243
```

### Color Properties

```php
// Check color properties
$isLight = $color->isLight();  // true/false
$isDark = $color->isDark();    // true/false

// Get brightness value (0-255)
$brightness = $color->getBrightness();  // 150
```

## Working with Multiple Colors

### Color Comparison

```php
$color1 = Color::fromHex('#ffffff');
$color2 = Color::fromHex('#000000');

// Get WCAG contrast ratio (1-21)
$contrast = $color1->getContrastRatio($color2);  // ~21 for black/white

// Check if contrast meets accessibility standards
$isReadable = $contrast >= 4.5;  // WCAG AA standard for normal text
$isHighlyReadable = $contrast >= 7.0;  // WCAG AAA standard
```

### Hue Rotation

```php
$color = Color::fromHex('#2196f3');

// Rotate hue by degrees
$complementary = $color->rotate(180);  // Complementary color
$analogous1 = $color->rotate(30);      // Analogous color
$analogous2 = $color->rotate(-30);     // Analogous color (opposite direction)
```

## Error Handling

```php
try {
    // Invalid hex color
    $color = Color::fromHex('not-a-color');
} catch (\InvalidArgumentException $e) {
    echo "Invalid color format: " . $e->getMessage();
}

try {
    // Invalid RGB values (must be 0-255)
    $color = Color::fromRgb([300, 0, 0]);
} catch (\InvalidArgumentException $e) {
    echo "Invalid RGB values: " . $e->getMessage();
}
```

## Best Practices

1. Always validate user input before creating colors
2. Use try-catch blocks when working with potentially invalid color values
3. Consider color accessibility when choosing colors
4. Use semantic color names in your code for better readability
5. Cache color objects if you're doing multiple operations on the same color 