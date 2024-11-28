---
layout: default
title: Color Palette PHP Documentation
---

# Color Palette PHP

A powerful PHP library for extracting color palettes from images and generating color themes. This package supports multiple image processing backends (GD and Imagick) and provides a rich set of color manipulation features.

<div class="color-example">
    <div class="color-swatch" style="background: #2563eb">Primary</div>
    <div class="color-swatch" style="background: #3b82f6">Secondary</div>
    <div class="color-swatch" style="background: #1f2937">Text</div>
    <div class="color-swatch" style="background: #f3f4f6; color: #1f2937">Background</div>
</div>

## 🎨 Features

### Color Extraction
- Extract dominant colors from images using advanced color quantization
- Support for multiple image formats (JPEG, PNG, GIF, etc.)
- Multiple image processing backends (GD and Imagick)

### Color Spaces
- RGB color space manipulation
- HSL (Hue, Saturation, Lightness) color space
- HSV (Hue, Saturation, Value) color space
- CMYK color space conversion
- LAB color space support
- Hex color code support

### Color Manipulation
- Lighten and darken colors
- Saturate and desaturate colors
- Rotate hue
- Adjust color brightness
- Color mixing and blending

### Color Analysis
- Color contrast ratio calculations
- Luminance calculations
- Light/dark color detection
- Automatic text color suggestions
- Color accessibility checks

### Theme Generation
- Generate color themes with surface, background, and accent colors
- Smart surface color recommendations
- Automatic text color contrast optimization
- Theme variation generation

## 🚀 Quick Start

### Installation

```bash
composer require farzai/color-palette
```

### Basic Usage

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

// Get suggested text color for a background
$backgroundColor = $colors[0];
$textColor = $palette->getSuggestedTextColor($backgroundColor);

// Get suggested surface colors
$surfaceColors = $palette->getSuggestedSurfaceColors();
```

## 📚 Documentation

Explore our comprehensive documentation to learn more about Color Palette PHP:

- [Getting Started](getting-started) - Installation and basic setup
- [Core Concepts](core-concepts) - Understanding color spaces and manipulation
- [API Reference](api/) - Detailed API documentation
- [Examples](examples/) - Code examples and use cases
- [Color Playground](playground) - Interactive color manipulation demo

## 🛠 Requirements

- PHP 8.1 or higher
- GD extension or ImageMagick extension
- Composer

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](https://github.com/parsilver/color-palette-php/blob/main/CONTRIBUTING.md) for details on how to contribute to this project. 