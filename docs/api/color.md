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

### Component Getters

```php
public function getRed(): int
public function getGreen(): int
public function getBlue(): int
```

Get individual RGB color components.

**Returns:** int - Component value (0-255)

**Example:**
```php
$color = new Color(255, 128, 64);
echo $color->getRed();   // 255
echo $color->getGreen(); // 128
echo $color->getBlue();  // 64
```

### Static Factory Methods

```php
public static function fromHex(string $hex): self
public static function fromRgb(array $rgb): self
public static function fromHsl(float $hue, float $saturation, float $lightness): self
public static function fromHsv(float $hue, float $saturation, float $value): self
public static function fromCmyk(float $cyan, float $magenta, float $yellow, float $key): self
public static function fromLab(float $lightness, float $a, float $b): self
```

**Parameter Ranges:**
- `fromHex()`: Hex string in format `#RRGGBB` or `RRGGBB`
- `fromRgb()`: r, g, b values: 0-255
- `fromHsl()`: hue: 0-360, saturation: 0-100, lightness: 0-100
- `fromHsv()`: hue: 0-360, saturation: 0-100, value: 0-100
- `fromCmyk()`: cyan, magenta, yellow, key: 0-100
- `fromLab()`: lightness: 0-100, a: -128 to 127, b: -128 to 127

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

#### fromHsv()
Creates a new color from HSV (Hue, Saturation, Value) color space values.

**Parameters:**
- `$hue` (float): Hue component (0-360 degrees)
- `$saturation` (float): Saturation component (0-100)
- `$value` (float): Value/Brightness component (0-100)

```php
$color = Color::fromHsv(220, 84, 92); // Blue color
```

#### fromCmyk()
Creates a new color from CMYK values.

**Parameters:**
- `$cyan` (float): Cyan component (0-100)
- `$magenta` (float): Magenta component (0-100)
- `$yellow` (float): Yellow component (0-100)
- `$key` (float): Key/Black component (0-100)

```php
$color = Color::fromCmyk(0, 100, 100, 0); // Red color
```

#### fromLab()
Creates a new color from LAB color space values.

**Parameters:**
- `$lightness` (float): Lightness component (0-100)
- `$a` (float): A component, green-red axis (-128 to 127)
- `$b` (float): B component, blue-yellow axis (-128 to 127)

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

## Color Space Parameter Ranges Reference

This quick reference table shows the expected parameter ranges for all color space conversions:

| Color Space | Parameter | Range | Description |
|-------------|-----------|-------|-------------|
| **RGB** | r, g, b | 0-255 | Red, Green, Blue components |
| **Hex** | hex | #000000-#FFFFFF | Hexadecimal color code |
| **HSL** | hue | 0-360 | Hue in degrees |
|  | saturation | 0-100 | Saturation percentage |
|  | lightness | 0-100 | Lightness percentage |
| **HSV** | hue | 0-360 | Hue in degrees |
|  | saturation | 0-100 | Saturation percentage |
|  | value | 0-100 | Value/Brightness percentage |
| **CMYK** | cyan | 0-100 | Cyan percentage |
|  | magenta | 0-100 | Magenta percentage |
|  | yellow | 0-100 | Yellow percentage |
|  | key | 0-100 | Black/Key percentage |
| **LAB** | lightness | 0-100 | Lightness value |
|  | a | -128 to 127 | Green-Red axis |
|  | b | -128 to 127 | Blue-Yellow axis |

### Important Notes:

- **HSL and HSV**: Both use 0-100 for saturation (NOT 0-1 decimals)
- **LAB**: The a and b components can be negative values
- **Hex**: Both `#RRGGBB` and `RRGGBB` formats are accepted
- All `from*()` methods validate input ranges and throw `InvalidArgumentException` if values are out of range
- All `to*()` methods return values in the ranges specified above

## See Also

- [ColorPalette Class](color-palette)
- [Theme Class](theme)
- [Color Manipulation](color-manipulation)
- [Color Spaces](color-spaces)