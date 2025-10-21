---
layout: default
title: Working with Palettes
description: Extract color palettes from images and work with surface colors and color schemes
parent: Examples
grand_parent: Home
nav_order: 5
keywords: color palettes, palette generation, surface colors, color schemes
---

# Color Palettes

> **⚠️ DOCUMENTATION UPDATE IN PROGRESS**
>
> This page is currently being updated to reflect the actual API. Many methods and classes documented here do not exist in the current implementation.
>
> For accurate information, please refer to:
> - [Quick Start Guide](../../quick-start.md)
> - [Color Spaces](../../concepts/color-spaces.md)
> - [Color Extraction Examples](./color-extraction.md)
> - [Theme Generation Examples](./theme-generation.md)

## Working with Color Palettes

### Extracting Palettes from Images

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image
$image = ImageFactory::createFromPath('image.jpg');

// Extract colors
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Extract palette (returns ColorPalette instance)
$palette = $extractor->extract($image, 5);

// Get all colors
$colors = $palette->getColors();

foreach ($colors as $color) {
    echo $color->toHex() . "\n";
}
```

### Getting Surface Colors

```php
// Get suggested surface colors for UI
$surfaceColors = $palette->getSuggestedSurfaceColors();

// Available keys: 'surface', 'background', 'accent', 'surface_variant'
foreach ($surfaceColors as $type => $color) {
    echo "$type: " . $color->toHex() . "\n";
}
```

### Generating Color Schemes

For generating color schemes and harmonies, use the `PaletteGenerator` class:

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(37, 99, 235);
$generator = new PaletteGenerator($baseColor);

// Generate different color schemes
$monochromatic = $generator->monochromatic(5);
$complementary = $generator->complementary();
$analogous = $generator->analogous();
$triadic = $generator->triadic();
$tetradic = $generator->tetradic();
$splitComplementary = $generator->splitComplementary();

// Generate shades and tints
$shades = $generator->shades(5);
$tints = $generator->tints(5);

// Generate style-based palettes
$pastel = $generator->pastel();
$vibrant = $generator->vibrant();

// Generate complete website theme
$websiteTheme = $generator->websiteTheme();
```

## Complete Example

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Extract colors from image
$image = ImageFactory::createFromPath('photo.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5);

// Display extracted colors
echo "Extracted Colors:\n";
foreach ($palette->getColors() as $index => $color) {
    echo ($index + 1) . ". " . $color->toHex() . "\n";
}

// Get suggested surface colors
echo "\nSuggested Surface Colors:\n";
$surfaceColors = $palette->getSuggestedSurfaceColors();
foreach ($surfaceColors as $type => $color) {
    echo "$type: " . $color->toHex() . "\n";

    // Get suggested text color for this surface
    $textColor = $palette->getSuggestedTextColor($color);
    echo "  Text: " . $textColor->toHex() . "\n";
}
```

For more examples, see:
- [Color Extraction Examples](./color-extraction.md)
- [Theme Generation Examples](./theme-generation.md)
