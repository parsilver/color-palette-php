---
layout: default
title: Color Palette PHP Documentation
---

# Color Palette PHP

A powerful PHP library for extracting color palettes from images and generating color themes. This package supports multiple image processing backends (GD and Imagick) and provides a rich set of color manipulation features.

## Features

- 🎨 Extract dominant colors from images using advanced color quantization
- 🖼️ Support for multiple image formats (JPEG, PNG, GIF, etc.)
- 🔄 Multiple image processing backends (GD and Imagick)
- 🎯 Generate color themes with surface, background, and accent colors
- 🌈 Color manipulation with RGB, HSL, and Hex support
- 📏 Color contrast ratio calculations
- 🎭 Automatic text color suggestions for optimal readability
- 🔍 Smart surface color recommendations based on color brightness

## Quick Start

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

## Documentation

- [Getting Started](getting-started) - Installation and basic usage
- [Core Concepts](core-concepts) - Learn about the core concepts
- [API Reference](api/) - Detailed API documentation
- [Examples](examples/) - Code examples and use cases

## Requirements

- PHP 8.1 or higher
- GD extension or ImageMagick extension
- Composer

## Contributing

Please see our [Contributing Guide](https://github.com/parsilver/color-palette-php/blob/main/CONTRIBUTING.md) for details on how to contribute to this project. 