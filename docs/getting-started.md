---
layout: default
title: Getting Started with Color Palette PHP
description: Learn how to install and start using Color Palette PHP for image color extraction and manipulation
keywords: php color palette installation, setup guide, quickstart, color extraction tutorial
---

# Getting Started

This guide will help you get up and running with Color Palette PHP quickly. We'll cover installation, basic setup, and common usage patterns.

<div class="quick-links">
  <a href="#prerequisites">Prerequisites</a> •
  <a href="#installation">Installation</a> •
  <a href="#basic-usage">Basic Usage</a> •
  <a href="#configuration">Configuration</a> •
  <a href="#next-steps">Next Steps</a>
</div>

## Prerequisites

Before you begin, ensure your system meets the following requirements:

- PHP 8.1 or higher
- One of the following image processing extensions:
  - GD extension (recommended for most use cases)
  - ImageMagick extension (recommended for advanced image processing)
- Composer for dependency management

You can check your PHP version and installed extensions using:

```bash
php -v
php -m | grep -E 'gd|imagick'
```

## Installation

### Via Composer

The recommended way to install Color Palette PHP is through Composer:

```bash
composer require farzai/color-palette
```

### Manual Installation

If you're not using Composer, you can download the library directly:

1. Download the [latest release](https://github.com/parsilver/color-palette-php/releases)
2. Extract the files into your project
3. Include the autoloader:

```php
require_once 'path/to/color-palette-php/vendor/autoload.php';
```

## Basic Usage

### 1. Extract Colors from an Image

Here's a simple example of extracting dominant colors from an image:

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Create image instance
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('path/to/image.jpg');

// Create color extractor
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd'); // or 'imagick'

// Extract 5 dominant colors
$palette = $extractor->extract($image, 5);

// Get colors as hex values
foreach ($palette->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

### 2. Generate Color Themes

Create harmonious color themes from a base color:

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

// Create a base color
$baseColor = new Color(37, 99, 235); // #2563eb (blue)

// Create a palette generator
$generator = new PaletteGenerator($baseColor);

// Generate different color schemes
$analogous = $generator->analogous();
$complementary = $generator->complementary();
$triadic = $generator->triadic();
$websiteTheme = $generator->websiteTheme();
```

### 3. Color Manipulation

Perform various color manipulations:

```php
use Farzai\ColorPalette\Color;

// Create a color
$color = new Color(37, 99, 235);

// Color transformations
$lighter = $color->lighten(0.2);        // Lighten by 20%
$darker = $color->darken(0.2);          // Darken by 20%
$withLight = $color->withLightness(0.5); // Set specific lightness
$saturated = $color->saturate(0.1);     // Increase saturation by 10%
$desaturated = $color->desaturate(0.1); // Decrease saturation by 10%
$rotated = $color->rotate(180);         // Rotate hue by 180 degrees

// Color format conversions
$hex = $color->toHex();           // #2563eb
$rgb = $color->toRgb();           // ['r' => 37, 'g' => 99, 'b' => 235]
$hsl = $color->toHsl();           // ['h' => 220, 's' => 84, 'l' => 53]
$hsv = $color->toHsv();           // ['h' => 220, 's' => 84, 'v' => 92]
$cmyk = $color->toCmyk();         // ['c' => 84, 'm' => 58, 'y' => 0, 'k' => 8]
$lab = $color->toLab();           // ['l' => 45, 'a' => 8, 'b' => -65]

// Create colors from different formats
$fromHex = Color::fromHex('#2563eb');
$fromRgb = Color::fromRgb(['r' => 37, 'g' => 99, 'b' => 235]);
$fromHsl = Color::fromHsl(220, 84, 53);
$fromHsv = Color::fromHsv(220, 0.84, 0.92);
$fromCmyk = Color::fromCmyk(84, 58, 0, 8);
$fromLab = Color::fromLab(45, 8, -65);
```

## Configuration

### Customizing Color Extraction

You can customize the color extraction process:

```php
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\Config;

// Create a custom configuration
$config = new Config([
    'sample_size' => 50,           // Number of pixels to sample
    'min_saturation' => 0.05,      // Minimum color saturation (0-1)
    'min_brightness' => 0.05,      // Minimum color brightness (0-1)
    'max_colors' => 10,            // Maximum number of colors to extract
]);

// Create extractor with custom config
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd', $config);
```

### Backend Selection

Choose between GD and ImageMagick backends based on your needs:

```php
// Using GD (default)
$extractor = $extractorFactory->make('gd');

// Using ImageMagick
$extractor = $extractorFactory->make('imagick');
```

## Next Steps

Now that you have Color Palette PHP set up, you can:

<div class="next-steps">
  <div class="next-step">
    <h3><a href="core-concepts">📚 Learn Core Concepts</a></h3>
    <p>Understand color spaces, manipulation techniques, and best practices.</p>
  </div>
  
  <div class="next-step">
    <h3><a href="examples/">💡 Explore Examples</a></h3>
    <p>See real-world examples and common use cases.</p>
  </div>
  
  <div class="next-step">
    <h3><a href="api/">📖 Browse API Reference</a></h3>
    <p>Dive into detailed API documentation.</p>
  </div>
  
  <div class="next-step">
    <h3><a href="playground">🎮 Try Color Playground</a></h3>
    <p>Experiment with color manipulation in our interactive playground.</p>
  </div>
</div>

## Troubleshooting

### Common Issues

1. **Missing Extensions**
   ```bash
   Error: GD/ImageMagick extension not found
   ```
   Solution: Install the required PHP extension:
   ```bash
   # For GD
   sudo apt-get install php8.1-gd
   
   # For ImageMagick
   sudo apt-get install php8.1-imagick
   ```

2. **Memory Limits**
   ```bash
   Error: Allowed memory size exhausted
   ```
   Solution: Increase PHP memory limit in php.ini:
   ```ini
   memory_limit = 256M
   ```

3. **Permission Issues**
   ```bash
   Error: Failed to open stream: Permission denied
   ```
   Solution: Ensure proper file permissions:
   ```bash
   chmod 644 /path/to/image.jpg
   ```

### Getting Help

If you encounter any issues:

1. Check our [GitHub Issues](https://github.com/parsilver/color-palette-php/issues) for similar problems
2. Review the [API Documentation](api/) for detailed information
3. [Create a new issue](https://github.com/parsilver/color-palette-php/issues/new) if you need help 