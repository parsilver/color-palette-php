# Working with Color Palettes

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

## Creating Palettes

### Manual Palette Creation

```php
use Farzai\ColorPalette\Palette;
use Farzai\ColorPalette\Color;

// Create an empty palette
$palette = new Palette();

// Add individual colors
$palette->add(Color::fromHex('#2196f3')); // Blue
$palette->add(Color::fromHex('#f44336')); // Red
$palette->add(Color::fromHex('#4caf50')); // Green

// Create from array of hex colors
$palette = Palette::fromHexColors([
    '#2196f3',
    '#f44336',
    '#4caf50'
]);
```

### Generating Palettes

```php
// Create from a base color
$baseColor = Color::fromHex('#2196f3');

// Generate complementary palette
$complementary = Palette::fromColor($baseColor)
    ->complementary()
    ->generate();

// Generate analogous palette
$analogous = Palette::fromColor($baseColor)
    ->analogous()
    ->generate();

// Generate triadic palette
$triadic = Palette::fromColor($baseColor)
    ->triadic()
    ->generate();
```

## Working with Palettes

### Accessing Colors

```php
// Get all colors
$colors = $palette->getColors();

// Get color by index
$firstColor = $palette->getColor(0);

// Get color count
$count = $palette->count();

// Check if palette contains a color
$contains = $palette->contains(Color::fromHex('#2196f3'));
```

### Modifying Palettes

```php
// Add a new color
$palette->add(Color::fromHex('#9c27b0'));

// Remove a color
$palette->remove(Color::fromHex('#2196f3'));

// Clear all colors
$palette->clear();

// Replace all colors
$palette->setColors([
    Color::fromHex('#2196f3'),
    Color::fromHex('#f44336')
]);
```

## Palette Operations

### Sorting Colors

```php
// Sort by hue
$sortedByHue = $palette->sortByHue();

// Sort by brightness
$sortedByBrightness = $palette->sortByBrightness();

// Sort by saturation
$sortedBySaturation = $palette->sortBySaturation();

// Custom sorting
$sortedCustom = $palette->sort(function($a, $b) {
    return $a->getRed() - $b->getRed();
});
```

### Filtering Colors

```php
// Get light colors only
$lightColors = $palette->filter(function($color) {
    return $color->isLight();
});

// Get dark colors only
$darkColors = $palette->filter(function($color) {
    return $color->isDark();
});

// Get saturated colors
$saturatedColors = $palette->filter(function($color) {
    return $color->getSaturation() > 50;
});
```

## Palette Analysis

### Basic Analysis

```php
// Get average color
$average = $palette->getAverageColor();

// Get dominant color
$dominant = $palette->getDominantColor();

// Get palette contrast
$contrast = $palette->getContrast();

// Check if monochromatic
$isMonochromatic = $palette->isMonochromatic();
```

### Color Distribution

```php
// Get color distribution
$distribution = $palette->getDistribution();

// Get color weights
$weights = $palette->getColorWeights();

// Get color variance
$variance = $palette->getColorVariance();
```

## Best Practices

1. **Palette Size**
   - Keep palettes between 3-7 colors for most use cases
   - Use monochromatic palettes for subtle variations
   - Include both light and dark colors for contrast

2. **Color Selection**
   - Start with a base color that represents your brand
   - Include neutral colors for balance
   - Consider accessibility when selecting colors

3. **Performance**
   ```php
   // Cache generated palettes
   $palette = Cache::remember('brand-palette', function() {
       return Palette::fromColor($brandColor)
           ->complementary()
           ->generate();
   });
   ```

4. **Error Handling**
   ```php
   try {
       $palette = Palette::fromHexColors($userInputColors);
   } catch (\InvalidArgumentException $e) {
       // Handle invalid color inputs
   }
   ```

5. **Palette Export**
   ```php
   // Export as CSS variables
   $css = $palette->toCssVariables([
       'prefix' => '--brand-color-'
   ]);

   // Export as JSON
   $json = $palette->toJson();
   ``` 