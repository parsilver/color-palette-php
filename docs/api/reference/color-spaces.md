---
layout: default
title: Color Spaces API
description: Complete API reference for RGB, HSL, and Hex color space conversions and component access
parent: API Reference
grand_parent: Home
nav_order: 3
keywords: color spaces, rgb hsl conversion, hex colors, color components
---

# Color Spaces

This section covers the color space conversions available in the library.

## Supported Color Spaces

The library supports the following color spaces:
- RGB (Red, Green, Blue) - Integer values from 0 to 255
- HSL (Hue: 0-360°, Saturation: 0-100%, Lightness: 0-100%)
- Hex (6-digit hexadecimal color notation, e.g., #FF0000)

## Color Space Conversion

### RGB Conversions

```php
use Farzai\ColorPalette\Color;

// Create from direct RGB values (0-255)
$color = new Color(255, 0, 0);

// Create from RGB array (supports both named and numeric keys)
$color = Color::fromRgb(['r' => 255, 'g' => 0, 'b' => 0]);
// or
$color = Color::fromRgb([255, 0, 0]);
// Missing values default to 0
$color = Color::fromRgb(['r' => 255]); // g and b will be 0

// Convert to HSL
$hsl = $color->toHsl();  // ['h' => 0, 's' => 100, 'l' => 50]

// Convert to Hex
$hex = $color->toHex();  // '#ff0000'
```

### HSL Conversions

```php
// Create from HSL values
// hue: 0-360 (degrees)
// saturation: 0-100 (percentage)
// lightness: 0-100 (percentage)
$color = Color::fromHsl(0, 100, 50);

// Convert to RGB (HSL values are normalized internally)
$rgb = $color->toRgb();  // ['r' => 255, 'g' => 0, 'b' => 0]

// Convert to Hex
$hex = $color->toHex();  // '#ff0000'
```

### Hex Conversions

```php
// Create from Hex (must be exactly 6 hex digits)
$color = Color::fromHex('#ff0000');  // Leading # is optional
// or
$color = Color::fromHex('ff0000');

// Convert to RGB
$rgb = $color->toRgb();  // ['r' => 255, 'g' => 0, 'b' => 0]

// Convert to HSL
$hsl = $color->toHsl();  // ['h' => 0, 's' => 100, 'l' => 50]
```

## Working with Color Components

### Accessing Components

```php
$color = new Color(255, 0, 0);

// RGB components (integers 0-255)
$red = $color->getRed();     // 255
$green = $color->getGreen(); // 0
$blue = $color->getBlue();   // 0

// Get HSL values through conversion
$hsl = $color->toHsl();
$hue = $hsl['h'];        // 0 (0-360 degrees)
$saturation = $hsl['s']; // 100 (0-100 percentage)
$lightness = $hsl['l'];  // 50 (0-100 percentage)
```

## Error Handling

```php
try {
    // Each RGB component must be between 0 and 255
    $color = new Color(300, 0, 0);
} catch (\InvalidArgumentException $e) {
    // "Invalid red color component. Must be between 0 and 255"
}

try {
    // Hex format must be exactly 6 hex digits
    $color = Color::fromHex('invalid');
} catch (\InvalidArgumentException $e) {
    // "Invalid hex color format"
}

try {
    // HSL values are normalized internally
    // hue is wrapped to 0-360
    // saturation and lightness are clamped to 0-100
    $color = Color::fromHsl(400, 150, 200);
} catch (\InvalidArgumentException $e) {
    // Handle invalid HSL values
}
``` 