# Color Spaces

## Navigation

- [Home](../README.md)
- [Getting Started](../getting-started.md)
- [Core Concepts](../core-concepts.md)
- [Examples](../examples/README.md)

### API Documentation
- [API Home](./README.md)
- [Color Manipulation](./color-manipulation.md)
- [Palette Generation](./palette-generation.md)
- [Color Schemes](./color-schemes.md)
- [Color Spaces](./color-spaces.md)
- [Utilities](./utilities.md)

---

This section covers the color space conversions and manipulations available in the library.

## Supported Color Spaces

The library supports the following color spaces:
- RGB (Red, Green, Blue)
- HSL (Hue, Saturation, Lightness)
- HSV (Hue, Saturation, Value)
- CMYK (Cyan, Magenta, Yellow, Key/Black)
- LAB (CIELAB Color Space)

## Color Space Conversion

### RGB Conversions

```php
use Farzai\ColorPalette\Color;

$color = Color::fromRgb(255, 0, 0);

// Convert to different spaces
$hsl = $color->toHsl();  // [0, 100, 50]
$hsv = $color->toHsv();  // [0, 100, 100]
$cmyk = $color->toCmyk(); // [0, 100, 100, 0]
$lab = $color->toLab();  // [53.24, 80.09, 67.20]
```

### HSL Conversions

```php
// Create from HSL
$color = Color::fromHsl(0, 100, 50);

// Convert to RGB
$rgb = $color->toRgb();  // [255, 0, 0]
```

### HSV Conversions

```php
// Create from HSV
$color = Color::fromHsv(0, 100, 100);

// Convert to other spaces
$rgb = $color->toRgb();  // [255, 0, 0]
$hsl = $color->toHsl();  // [0, 100, 50]
```

## Working with Color Components

### Accessing Components

```php
$color = Color::fromRgb(255, 0, 0);

// RGB components
$red = $color->getRed();    // 255
$green = $color->getGreen(); // 0
$blue = $color->getBlue();   // 0

// HSL components
$hue = $color->getHue();        // 0
$saturation = $color->getSaturation(); // 100
$lightness = $color->getLightness();   // 50
```

### Modifying Components

```php
$color = Color::fromRgb(255, 0, 0);

// Modify individual components
$color = $color->withRed(128);
$color = $color->withHue(180);
$color = $color->withSaturation(50);
```

## Color Space Utilities

### Gamma Correction

```php
$color = Color::fromRgb(255, 0, 0);

// Apply gamma correction
$corrected = $color->applyGamma(2.2);
```

### Color Space Interpolation

```php
$color1 = Color::fromRgb(255, 0, 0);
$color2 = Color::fromRgb(0, 0, 255);

// Interpolate in RGB space
$mixed = $color1->interpolateRgb($color2, 0.5);

// Interpolate in HSL space
$mixed = $color1->interpolateHsl($color2, 0.5);
```

## Error Handling

```php
try {
    // Invalid RGB values
    $color = Color::fromRgb(300, 0, 0);
} catch (\InvalidArgumentException $e) {
    // Handle invalid color space values
}

try {
    // Invalid HSL values
    $color = Color::fromHsl(400, 150, 200);
} catch (\InvalidArgumentException $e) {
    // Handle invalid color space values
}
``` 