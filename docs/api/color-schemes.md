# Color Schemes

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

This section covers the color scheme generation and manipulation capabilities of the library.

## Basic Color Schemes

### Complementary Colors

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Scheme;

$color = Color::fromHex('#ff0000');
$scheme = Scheme::complementary($color);

// Get complementary color
$complement = $scheme->getColors()[1]; // #00ffff
```

### Analogous Colors

```php
// Generate analogous scheme (3 colors by default)
$scheme = Scheme::analogous($color);

// Get colors
$colors = $scheme->getColors(); // [#ff0000, #ff8000, #ff0080]

// Custom angle and count
$scheme = Scheme::analogous($color)
    ->withAngle(15)
    ->withCount(5);
```

### Triadic Colors

```php
// Generate triadic scheme
$scheme = Scheme::triadic($color);

// Get colors
$colors = $scheme->getColors(); // [#ff0000, #00ff00, #0000ff]
```

## Advanced Color Schemes

### Split Complementary

```php
// Generate split complementary scheme
$scheme = Scheme::splitComplementary($color);

// Get colors
$colors = $scheme->getColors();
```

### Square

```php
// Generate square color scheme
$scheme = Scheme::square($color);

// Get colors (4 colors at 90° intervals)
$colors = $scheme->getColors();
```

### Rectangle (Double Split Complementary)

```php
// Generate rectangular color scheme
$scheme = Scheme::rectangle($color);

// Get colors
$colors = $scheme->getColors();
```

## Custom Schemes

### Custom Angles

```php
// Generate custom scheme with specific angles
$scheme = Scheme::custom($color)
    ->withAngles([30, 60, 90, 180])
    ->generate();
```

### Monochromatic Schemes

```php
// Generate monochromatic scheme
$scheme = Scheme::monochromatic($color)
    ->withCount(5)
    ->generate();

// With specific lightness steps
$scheme = Scheme::monochromatic($color)
    ->withLightnessSteps([20, 40, 60, 80])
    ->generate();
```

## Scheme Manipulation

### Adjusting Schemes

```php
// Adjust entire scheme's saturation
$scheme = $scheme->adjustSaturation(20);

// Adjust entire scheme's lightness
$scheme = $scheme->adjustLightness(-10);
```

### Filtering and Sorting

```php
// Filter scheme colors
$lightColors = $scheme->filter(function($color) {
    return $color->isLight();
});

// Sort by brightness
$sortedScheme = $scheme->sortByBrightness();
```

## Scheme Analysis

### Color Harmony

```php
// Check scheme harmony
$harmonyScore = $scheme->getHarmonyScore();

// Get color relationships
$relationships = $scheme->getColorRelationships();
```

### Accessibility

```php
// Check if scheme meets WCAG contrast requirements
$isAccessible = $scheme->meetsWcagRequirements();

// Get best text colors for each scheme color
$textColors = $scheme->getSuggestedTextColors();
```

## Error Handling

```php
try {
    $scheme = Scheme::analogous($color)
        ->withCount(0); // Invalid count
} catch (\InvalidArgumentException $e) {
    // Handle invalid scheme parameters
}
``` 