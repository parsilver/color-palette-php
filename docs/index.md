---
layout: default
title: Color Palette PHP - Image Color Extraction and Manipulation Library
description: A powerful PHP library for extracting color palettes from images, generating color themes, and manipulating colors in multiple color spaces
keywords: php color palette, image color extraction, color manipulation, color themes
---

# Color Palette PHP

A powerful PHP library for extracting color palettes from images and generating color themes. This package supports multiple image processing backends (GD and Imagick) and provides a rich set of color manipulation features.

<div class="badges">
  <a href="https://packagist.org/packages/farzai/color-palette"><img src="https://img.shields.io/packagist/v/farzai/color-palette.svg" alt="Latest Stable Version"></a>
  <a href="https://github.com/parsilver/color-palette-php/actions"><img src="https://github.com/parsilver/color-palette-php/workflows/tests/badge.svg" alt="Build Status"></a>
  <a href="https://packagist.org/packages/farzai/color-palette"><img src="https://img.shields.io/packagist/dt/farzai/color-palette.svg" alt="Total Downloads"></a>
  <a href="https://github.com/parsilver/color-palette-php/blob/main/LICENSE.md"><img src="https://img.shields.io/packagist/l/farzai/color-palette.svg" alt="License"></a>
</div>

<div class="color-example">
    <div class="color-swatch" style="background: #2563eb">Primary</div>
    <div class="color-swatch" style="background: #3b82f6">Secondary</div>
    <div class="color-swatch" style="background: #1f2937">Text</div>
    <div class="color-swatch" style="background: #f3f4f6; color: #1f2937">Background</div>
</div>

## 🎨 Key Features

### Advanced Color Extraction
- **Multiple Backends**: Support for both GD and Imagick image processing
- **Smart Sampling**: Efficient color sampling algorithms for accurate palette generation
- **Format Support**: Compatible with JPEG, PNG, GIF, and other common image formats
- **Customizable**: Adjustable color count and sampling parameters

### Comprehensive Color Space Support
- **RGB**: Standard RGB color manipulation
- **HSL/HSV**: Intuitive color adjustments with Hue, Saturation, and Lightness/Value
- **CMYK**: Print-ready color space conversion
- **LAB**: Perceptually uniform color space for accurate color matching
- **Hex**: Web-friendly hexadecimal color codes

### Powerful Color Manipulation
- **Color Adjustment**: Lighten, darken, saturate, and desaturate colors
- **Color Mixing**: Blend colors and create gradients
- **Color Rotation**: Rotate hues and create color variations
- **Brightness Control**: Fine-tune color brightness and contrast

### Intelligent Theme Generation
- **Automatic Palettes**: Generate harmonious color schemes
- **Accessibility**: Built-in contrast ratio calculations
- **Smart Defaults**: Automatic text color optimization
- **Theme Variations**: Create light and dark theme variants

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

// Extract colors
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd'); // or 'imagick'
$colors = $extractor->extract($image, 5); // Extract 5 dominant colors

// Create a palette and get color suggestions
$palette = new ColorPalette($colors);
$textColor = $palette->getSuggestedTextColor($colors[0]);
$surfaceColors = $palette->getSuggestedSurfaceColors();
```

## 📚 Documentation

Our comprehensive documentation covers everything you need:

<div class="doc-sections">
  <div class="doc-section">
    <h3><a href="getting-started">🏁 Getting Started</a></h3>
    <p>Quick installation and basic setup guide to get you up and running.</p>
  </div>
  
  <div class="doc-section">
    <h3><a href="core-concepts">🎯 Core Concepts</a></h3>
    <p>Deep dive into color spaces, manipulation, and best practices.</p>
  </div>
  
  <div class="doc-section">
    <h3><a href="api/">📖 API Reference</a></h3>
    <p>Complete API documentation with detailed examples and use cases.</p>
  </div>
  
  <div class="doc-section">
    <h3><a href="examples/">💡 Examples</a></h3>
    <p>Real-world examples and code snippets for common scenarios.</p>
  </div>
  
  <div class="doc-section">
    <h3><a href="playground">🎮 Color Playground</a></h3>
    <p>Interactive demo to experiment with color manipulation features.</p>
  </div>
</div>

## 🛠 Requirements

- PHP 8.1 or higher
- GD extension or ImageMagick extension
- Composer for dependency management

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guide](https://github.com/parsilver/color-palette-php/blob/main/CONTRIBUTING.md) for details.

## 📄 License

The Color Palette PHP library is open-sourced software licensed under the [MIT license](https://github.com/parsilver/color-palette-php/blob/main/LICENSE.md).

## 📦 Resources

- [GitHub Repository](https://github.com/parsilver/color-palette-php)
- [Issue Tracker](https://github.com/parsilver/color-palette-php/issues)
- [Packagist Page](https://packagist.org/packages/farzai/color-palette)
- [Release Notes](https://github.com/parsilver/color-palette-php/releases) 