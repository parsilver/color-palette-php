# Color Manipulation

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

This section covers all color manipulation methods available in the library.

## Color Creation

### Creating a Color

```php
use Farzai\ColorPalette\Color;

// Create from hex
$color = Color::fromHex('#ff0000');

// Create from RGB
$color = Color::fromRgb(255, 0, 0);

// Create from HSL
$color = Color::fromHsl(0, 100, 50);
```

## Color Modification

### Lighten and Darken

```php
$color = Color::fromHex('#ff0000');

// Lighten by 20%
$lighter = $color->lighten(20);

// Darken by 20%
$darker = $color->darken(20);
```

### Saturate and Desaturate

```php
// Increase saturation by 20%
$saturated = $color->saturate(20);

// Decrease saturation by 20%
$desaturated = $color->desaturate(20);
```

### Adjusting Opacity

```php
// Set opacity to 50%
$transparent = $color->opacity(0.5);
```

## Color Information

### Getting Color Values

```php
$color = Color::fromHex('#ff0000');

// Get hex value
$hex = $color->toHex(); // Returns '#ff0000'

// Get RGB values
$rgb = $color->toRgb(); // Returns [255, 0, 0]

// Get HSL values
$hsl = $color->toHsl(); // Returns [0, 100, 50]
```

### Color Properties

```php
// Check if color is light
$isLight = $color->isLight();

// Check if color is dark
$isDark = $color->isDark();

// Get brightness value
$brightness = $color->getBrightness();
```

## Color Comparison

### Contrast Ratio

```php
$color1 = Color::fromHex('#ffffff');
$color2 = Color::fromHex('#000000');

$contrastRatio = $color1->getContrastRatio($color2);
```

### Color Distance

```php
// Get color distance (Delta E)
$distance = $color1->getDistance($color2);
```

## Error Handling

All color manipulation methods will throw `InvalidArgumentException` when provided with invalid input:

```php
try {
    $color = Color::fromHex('invalid');
} catch (\InvalidArgumentException $e) {
    // Handle invalid color format
}
``` 