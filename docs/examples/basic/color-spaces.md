---
layout: default
title: Color Space Conversions
description: Convert between RGB, HSL, HSV, CMYK, and LAB color spaces with practical examples
parent: Examples
grand_parent: Home
nav_order: 2
keywords: color space conversion, rgb to hsl, color format conversion
---

# Color Spaces

> **⚠️ DOCUMENTATION UPDATE IN PROGRESS**
>
> This page is currently being updated to reflect the actual API. Many methods documented here do not exist in the current implementation.
>
> For accurate information, please refer to:
> - [Quick Start Guide](../../quick-start.md)
> - [Color Spaces Concepts](../../concepts/color-spaces.md)
> - [Color Manipulation Examples](./color-manipulation.md)

## Available Color Space Conversions

The Color class supports the following color space conversions:

### Supported Color Spaces

```php
use Farzai\ColorPalette\Color;

$color = new Color(37, 99, 235); // Create from RGB

// Convert to different color spaces
$hex = $color->toHex();   // Hexadecimal: '#2563eb'
$rgb = $color->toRgb();   // RGB: ['r' => 37, 'g' => 99, 'b' => 235]
$hsl = $color->toHsl();   // HSL: ['h' => 220, 's' => 84, 'l' => 53]
$hsv = $color->toHsv();   // HSV: ['h' => 220, 's' => 84, 'v' => 92]
$cmyk = $color->toCmyk(); // CMYK: ['c' => 84, 'm' => 58, 'y' => 0, 'k' => 8]
$lab = $color->toLab();   // LAB: ['l' => 45, 'a' => 8, 'b' => -65]
```

### Creating Colors from Different Spaces

```php
// From Hex
$color = Color::fromHex('#2563eb');

// From RGB (array format)
$color = Color::fromRgb([37, 99, 235]);
$color = Color::fromRgb(['r' => 37, 'g' => 99, 'b' => 235]);

// From HSL (hue 0-360, saturation 0-100, lightness 0-100)
$color = Color::fromHsl(220, 84, 53);

// From HSV (hue 0-360, saturation 0-100, value 0-100)
$color = Color::fromHsv(220, 84, 92);

// From CMYK (all values 0-100)
$color = Color::fromCmyk(84, 58, 0, 8);

// From LAB (lightness 0-100, a/b -128 to 127)
$color = Color::fromLab(45, 8, -65);
```

## Color Manipulation

For information on color manipulation methods, see:
- [Color Manipulation Examples](./color-manipulation.md)
- [Color Theory Concepts](../../concepts/color-theory.md)
