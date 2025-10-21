---
layout: default
title: Color Manipulation - Color Palette PHP
description: Documentation for color manipulation features in Color Palette PHP
keywords: color manipulation, color transformation, color spaces, php color library
---

# Color Manipulation

Color Palette PHP provides color manipulation capabilities through the `Color` class.

## Basic Color Operations

### Creating Colors

```php
use Farzai\ColorPalette\Color;

// Create from RGB values (0-255)
$color = new Color(37, 99, 235);

// Create from hex string (6 hex digits with optional #)
$color = Color::fromHex('#2563eb');

// Create from HSL values (hue: 0-360, saturation/lightness: 0-100)
$color = Color::fromHsl(220, 84, 53);

// Create from RGB array (supports both named and numeric keys)
$color = Color::fromRgb(['r' => 37, 'g' => 99, 'b' => 235]);
// or
$color = Color::fromRgb([37, 99, 235]);
// Missing values default to 0
$color = Color::fromRgb(['r' => 37]); // g and b will be 0
```

### Color Space Conversions

```php
$color = new Color(37, 99, 235);

// Convert to different formats
$hex = $color->toHex();           // '#2563eb'
$rgb = $color->toRgb();           // ['r' => 37, 'g' => 99, 'b' => 235]
$hsl = $color->toHsl();           // ['h' => 220, 's' => 84, 'l' => 53]
```

## Color Transformations

### Lightness Adjustments

```php
$color = new Color(37, 99, 235);

// Lighten or darken (amount is a float between 0 and 1)
$lighter = $color->lighten(0.2);    // Increase lightness by 20% (capped at 100%)
$darker = $color->darken(0.2);      // Decrease lightness by 20% (capped at 0%)
```

### Saturation Adjustments

```php
// Adjust saturation (amount is a float between 0 and 1)
$saturated = $color->saturate(0.1);     // Increase saturation by 10% (capped at 100%)
```

## Color Analysis

### Brightness and Contrast

```php
// Get brightness value using formula: (299R + 587G + 114B) / 1000
$brightness = $color->getBrightness();   // Returns float between 0 and 255

// Check if color is light or dark (threshold is 128)
$isLight = $color->isLight();    // true if brightness > 128
$isDark = $color->isDark();      // true if brightness <= 128

// Get contrast ratio with another color (WCAG standard)
$otherColor = new Color(255, 255, 255);
$contrastRatio = $color->getContrastRatio($otherColor);  // Returns ratio between 1 and 21
```

### Color Properties

```php
// Get individual RGB components (0-255)
$red = $color->getRed();      // 0-255
$green = $color->getGreen();  // 0-255
$blue = $color->getBlue();    // 0-255

// Get relative luminance (WCAG standard)
$luminance = $color->getLuminance();  // Returns float between 0 and 1
```

## Error Handling

```php
use InvalidArgumentException;

try {
    // Each RGB component must be between 0 and 255
    $color = new Color(300, 0, 0);
} catch (InvalidArgumentException $e) {
    // "Invalid red color component. Must be between 0 and 255"
}

try {
    // Hex format must be exactly 6 hex digits
    $color = Color::fromHex('invalid');
} catch (InvalidArgumentException $e) {
    // "Invalid hex color format"
}
```

## See Also

- [Color Spaces](color-spaces.html)
- [Palette Generation](palette-generation.html)
- [Theme Generation](theme.html) 