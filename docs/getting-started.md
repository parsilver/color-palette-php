# Getting Started with Color Palette PHP

This guide will help you get started with Color Palette PHP, a powerful library for extracting and manipulating color palettes from images.

## Installation

### Requirements

Before installing, make sure your system meets these requirements:

- PHP 8.1 or higher
- One of the following image processing extensions:
  - GD extension (recommended)
  - ImageMagick extension
- Composer for dependency management

### Installing via Composer

```bash
composer require farzai/color-palette
```

## Basic Usage

### 1. Creating a Color Palette from an Image

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPalette;

// Create an image instance
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('path/to/image.jpg');

// Create a color extractor
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd'); // or 'imagick'

// Extract colors to create a palette
$colors = $extractor->extract($image);
$palette = new ColorPalette($colors);

// Get all colors in the palette
$colors = $palette->getColors();

// Work with individual colors
foreach ($colors as $color) {
    echo $color->toHex() . "\n";    // Get hex value (#RRGGBB)
    echo $color->toRgb() . "\n";    // Get RGB values
    echo $color->toHsl() . "\n";    // Get HSL values
}
```

### 2. Generating a Theme

```php
use Farzai\ColorPalette\ThemeGenerator;

// Create a theme generator
$generator = new ThemeGenerator();

// Generate a theme from your palette
$theme = $generator->generate($palette);

// Access theme colors
$primary = $theme->getPrimary();
$secondary = $theme->getSecondary();
$accent = $theme->getAccent();
```

### 3. Working with Colors

```php
use Farzai\ColorPalette\Color;

// Create a color from hex
$color = Color::fromHex('#2196f3');

// Get color properties
echo $color->getRed();      // Red component (0-255)
echo $color->getGreen();    // Green component (0-255)
echo $color->getBlue();     // Blue component (0-255)
echo $color->getLightness(); // Lightness value (0-100)

// Convert to different formats
echo $color->toHex();       // #2196f3
echo $color->toRgb();       // rgb(33, 150, 243)
echo $color->toHsl();       // hsl(207, 90%, 54%)
```

### 4. Surface Colors and Text Suggestions

```php
// Get suggested surface colors
$surfaceColors = $palette->getSuggestedSurfaceColors();
// Available keys: 'surface', 'background', 'accent', 'surface_variant'

// Get suggested text color for a background
$backgroundColor = $colors[0];
$textColor = $palette->getSuggestedTextColor($backgroundColor);
```

## Error Handling

It's important to handle potential errors when working with images:

```php
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\ImageException;

try {
    $image = $imageFactory->createFromPath('path/to/image.jpg');
} catch (ImageLoadException $e) {
    // Handle image loading errors
    echo "Failed to load image: " . $e->getMessage();
} catch (ImageException $e) {
    // Handle other image processing errors
    echo "Image processing error: " . $e->getMessage();
}
```

## Next Steps

Now that you're familiar with the basics, you can:

1. Learn about [Core Concepts](core-concepts.md)
2. Explore the [Examples](examples/README.md)
3. Read the [API Reference](api/README.md) 