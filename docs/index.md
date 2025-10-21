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

// Load an image (static method)
$image = ImageFactory::createFromPath('path/to/image.jpg');

// Extract colors
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd'); // or 'imagick'
$palette = $extractor->extract($image, 5); // Extract 5 dominant colors (returns ColorPalette)

// Get color suggestions
$colors = $palette->getColors();
$textColor = $palette->getSuggestedTextColor($colors[0]);
$surfaceColors = $palette->getSuggestedSurfaceColors();
```

## 📚 Documentation

Explore our comprehensive documentation with clear pathways to help you get started and master the library:

<div class="doc-sections">
  <div class="doc-section">
    <h3><a href="quick-start">⚡ Quick Start</a></h3>
    <p>Get up and running in under 5 minutes with copy-paste ready examples.</p>
    <ul class="doc-links">
      <li><a href="quick-start#installation">Installation</a></li>
      <li><a href="quick-start#extract-colors">Extract Colors</a></li>
      <li><a href="quick-start#generate-themes">Generate Themes</a></li>
      <li><a href="quick-start#troubleshooting">Troubleshooting</a></li>
    </ul>
  </div>

  <div class="doc-section">
    <h3><a href="guides/installation">📦 Installation Guide</a></h3>
    <p>Comprehensive installation instructions for all environments and platforms.</p>
    <ul class="doc-links">
      <li><a href="guides/installation#system-requirements">System Requirements</a></li>
      <li><a href="guides/installation#installation-methods">Installation Methods</a></li>
      <li><a href="guides/installation#backend-setup">Backend Setup (GD/Imagick)</a></li>
      <li><a href="guides/installation#platform-specific-installation">Platform-Specific Setup</a></li>
    </ul>
  </div>

  <div class="doc-section">
    <h3><a href="concepts/color-spaces">🎯 Core Concepts</a></h3>
    <p>Deep dive into color theory, spaces, and advanced manipulation techniques.</p>
    <ul class="doc-links">
      <li><a href="concepts/color-spaces">Color Spaces</a> - RGB, HSL, HSV, CMYK, LAB</li>
      <li><a href="concepts/color-theory">Color Harmony & Theory</a> - Design principles</li>
      <li><a href="concepts/accessibility">Accessibility</a> - WCAG compliance</li>
      <li><a href="concepts/performance">Performance Optimization</a> - Best practices</li>
    </ul>
  </div>

  <div class="doc-section">
    <h3><a href="examples/">💡 Examples & Tutorials</a></h3>
    <p>Searchable catalog of real-world examples organized by complexity and use case.</p>
    <ul class="doc-links">
      <li><a href="examples/#basic">Basic Examples</a> - Color extraction, conversions, manipulation</li>
      <li><a href="examples/#advanced">Advanced Examples</a> - Custom implementations, integrations</li>
      <li><a href="examples/#integration">Integration Examples</a> - Web apps, APIs, frameworks</li>
    </ul>
  </div>

  <div class="doc-section">
    <h3><a href="api/">📖 API Reference</a></h3>
    <p>Complete API documentation with method signatures, parameters, and return types.</p>
    <ul class="doc-links">
      <li><a href="api/reference/color">Color Class</a> - Core color operations</li>
      <li><a href="api/reference/color-palette">ColorPalette</a> - Palette management</li>
      <li><a href="api/reference/palette-generation">PaletteGenerator</a> - Theme generation</li>
      <li><a href="api/reference/color-extractor">Color Extractors</a> - GD & Imagick</li>
    </ul>
  </div>
</div>

## 🎯 Popular Use Cases

<div class="use-cases">
  <div class="use-case">
    <h4>🎨 Extract Colors from Images</h4>
    <p>Generate color palettes from uploaded images for design systems</p>
    <a href="examples/basic/color-extraction">View Example →</a>
  </div>

  <div class="use-case">
    <h4>🌈 Generate Color Themes</h4>
    <p>Create harmonious color schemes for websites and applications</p>
    <a href="examples/basic/theme-generation">View Example →</a>
  </div>

  <div class="use-case">
    <h4>🔄 Color Space Conversions</h4>
    <p>Convert between RGB, HSL, HSV, CMYK, and LAB color spaces</p>
    <a href="examples/basic/color-spaces">View Example →</a>
  </div>

  <div class="use-case">
    <h4>⚙️ Custom Color Schemes</h4>
    <p>Build advanced custom color schemes with full control</p>
    <a href="examples/advanced/custom-schemes">View Example →</a>
  </div>
</div>

## 🆕 Recent Updates

<div class="recent-updates">
  <div class="update-item">
    <strong>v2.1.0</strong> - Enhanced color harmony algorithms and improved LAB color space support
  </div>
  <div class="update-item">
    <strong>v2.0.0</strong> - Major release with new theme generation features and performance improvements
  </div>
  <div class="update-item">
    <strong>v1.5.0</strong> - Added CMYK and LAB color space conversions
  </div>
</div>

<p><a href="https://github.com/parsilver/color-palette-php/releases">View All Releases →</a></p>

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