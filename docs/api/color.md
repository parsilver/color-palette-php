---
layout: default
title: Color Class - Color Palette PHP API
description: Documentation for the Color class in Color Palette PHP, including color creation, manipulation, and conversion methods
keywords: php color class, color manipulation, color conversion, color spaces
---

# Color Class

The `Color` class is the core component for representing and manipulating colors in Color Palette PHP.

## Overview

```php
namespace Farzai\ColorPalette;

class Color implements ColorInterface
{
    // ...
}
```

The `Color` class provides comprehensive functionality for:
- Color creation and representation in RGB format
- Color space conversions (RGB, HSL, CMYK, LAB)
- Color manipulation and adjustments
- Color analysis and comparison

## Creating Colors

### Constructor

```php
public function __construct(int $red, int $green, int $blue)
```

Creates a new Color instance from RGB values.

**Parameters:**
- `$red` (int): Red component (0-255)
- `$green` (int): Green component (0-255)
- `$blue` (int): Blue component (0-255)

### Static Factory Methods

```php
public static function fromHex(string $hex): self
public static function fromRgb(array $rgb): self
public static function fromHsl(float $hue, float $saturation, float $lightness): self
public static function fromCmyk(float $cyan, float $magenta, float $yellow, float $key): self
public static function fromLab(float $lightness, float $a, float $b): self
```

## Color Space Conversions

```php
public function toHex(): string
public function toRgb(): array
public function toHsl(): array
public function toHsv(): array
public function toCmyk(): array
public function toLab(): array
```

## Color Analysis

```php
public function getBrightness(): float
public function isLight(): bool
public function isDark(): bool
public function getContrastRatio(ColorInterface $color): float
public function getLuminance(): float
```

## Color Manipulation

```php
public function lighten(float $amount): self
public function darken(float $amount): self
public function saturate(float $amount): self
public function desaturate(float $amount): self
public function rotate(float $degrees): self
public function withLightness(float $lightness): self
```

## Detailed Method Documentation

### Color Space Conversion Methods

#### toHex()
Converts the color to hexadecimal format.
```php
$color = new Color(255, 0, 0);
echo $color->toHex(); // "#ff0000"
```

#### toRgb()
Returns an array with RGB values.
```php
$color = new Color(255, 0, 0);
$rgb = $color->toRgb(); // ['r' => 255, 'g' => 0, 'b' => 0]
```

#### toHsl()
Returns an array with HSL values.
```php
$color = new Color(255, 0, 0);
$hsl = $color->toHsl(); // ['h' => 0, 's' => 100, 'l' => 50]
```

#### toHsv()
Returns an array with HSV values.
```php
$color = new Color(255, 0, 0);
$hsv = $color->toHsv(); // ['h' => 0, 's' => 100, 'v' => 100]
```

#### toCmyk()
Returns an array with CMYK values (0-100 for each component).
```php
$color = new Color(255, 0, 0);
$cmyk = $color->toCmyk(); // ['c' => 0, 'm' => 100, 'y' => 100, 'k' => 0]
```

#### toLab()
Returns an array with LAB color space values.
```php
$color = new Color(255, 0, 0);
$lab = $color->toLab(); // ['l' => 53, 'a' => 80, 'b' => 67]
```

### Static Creation Methods

#### fromCmyk()
Creates a new color from CMYK values.
```php
$color = Color::fromCmyk(0, 100, 100, 0); // Red color
```

#### fromLab()
Creates a new color from LAB color space values.
```php
$color = Color::fromLab(53, 80, 67); // Approximately red color
```

### Color Analysis Methods

#### getBrightness()
Returns the perceived brightness of the color (0-255).

#### isLight()
Returns true if the color is considered light (brightness > 128).

#### isDark()
Returns true if the color is considered dark (brightness <= 128).

#### getContrastRatio()
Calculates the contrast ratio between this color and another color according to WCAG standards.
```php
$color1 = new Color(255, 255, 255);
$color2 = new Color(0, 0, 0);
echo $color1->getContrastRatio($color2); // 21
```

#### getLuminance()
Returns the relative luminance of the color according to WCAG standards.

### Color Manipulation Methods

#### lighten(float $amount)
Creates a lighter version of the color.
```php
$color = new Color(255, 0, 0);
$lighter = $color->lighten(0.2); // 20% lighter
```

#### darken(float $amount)
Creates a darker version of the color.
```php
$color = new Color(255, 0, 0);
$darker = $color->darken(0.2); // 20% darker
```

#### saturate(float $amount)
Increases the saturation of the color.
```php
$color = new Color(255, 0, 0);
$saturated = $color->saturate(0.2); // 20% more saturated
```

#### desaturate(float $amount)
Decreases the saturation of the color.
```php
$color = new Color(255, 0, 0);
$desaturated = $color->desaturate(0.2); // 20% less saturated
```

#### rotate(float $degrees)
Rotates the hue of the color by the specified degrees.
```php
$color = new Color(255, 0, 0);
$rotated = $color->rotate(180); // Complementary color
```

#### withLightness(float $lightness)
Creates a new color with the specified lightness value (0-1).
```php
$color = new Color(255, 0, 0);
$newColor = $color->withLightness(0.8); // 80% lightness
```

## See Also

- [ColorPalette Class](color-palette)
- [Theme Class](theme)
- [Color Manipulation](color-manipulation)
- [Color Spaces](color-spaces)