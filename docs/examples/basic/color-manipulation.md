# Basic Color Manipulation Examples

## Navigation

- [Home](../../README.md)
- [Getting Started](../../getting-started.md)
- [Core Concepts](../../core-concepts.md)
- [API Documentation](../../api/README.md)

### Examples
- [Examples Home](../README.md)
- [Basic Examples](../basic/README.md)
- [Advanced Examples](../advanced/README.md)
- [Applications](../applications/README.md)
- [Integration](../integration/README.md)

---

## Creating Colors

### From Hex Values

```php
use Farzai\ColorPalette\Color;

// Create a red color
$red = Color::fromHex('#ff0000');

// Create with alpha channel
$transparentRed = Color::fromHex('#ff0000aa');
```

### From RGB Values

```php
// Create using RGB values
$blue = Color::fromRgb(0, 0, 255);

// Create with alpha
$transparentBlue = Color::fromRgba(0, 0, 255, 0.5);
```

## Basic Manipulations

### Lightening and Darkening

```php
$color = Color::fromHex('#2196f3');

// Create lighter variations
$lighter = $color->lighten(20);  // 20% lighter
$evenLighter = $color->lighten(40);  // 40% lighter

// Create darker variations
$darker = $color->darken(20);  // 20% darker
$evenDarker = $color->darken(40);  // 40% darker
```

### Saturation Adjustments

```php
// Increase saturation
$moreSaturated = $color->saturate(20);

// Decrease saturation
$lessSaturated = $color->desaturate(20);

// Complete desaturation (grayscale)
$grayscale = $color->desaturate(100);
```

## Color Information

### Getting Color Values

```php
$color = Color::fromHex('#2196f3');

// Get different formats
$hex = $color->toHex();  // '#2196f3'
$rgb = $color->toRgb();  // [33, 150, 243]
$hsl = $color->toHsl();  // [207, 90, 54]

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

### Color Mixing

```php
$red = Color::fromHex('#ff0000');
$blue = Color::fromHex('#0000ff');

// Mix colors with equal weight
$purple = $red->mix($blue);

// Mix with custom weight
$redPurple = $red->mix($blue, 0.25);  // 25% blue, 75% red
```

### Color Comparison

```php
$color1 = Color::fromHex('#ffffff');
$color2 = Color::fromHex('#000000');

// Get contrast ratio
$contrast = $color1->getContrastRatio($color2);  // 21

// Check if colors are similar
$distance = $color1->getDistance($color2);  // Color difference
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
    // Invalid RGB values
    $color = Color::fromRgb(300, 0, 0);  // RGB values must be 0-255
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