---
layout: default
title: API Reference - Color Palette PHP
description: Complete API documentation for Color Palette PHP library, including color manipulation, extraction, and theme generation
keywords: php color palette api, color manipulation api, color extraction api, theme generation api
---

# API Reference

Welcome to the Color Palette PHP API documentation. This comprehensive guide covers all the classes, methods, and features available in the library.

<div class="api-overview">
  <div class="api-section">
    <h2>🎨 Core Components</h2>
    <ul>
      <li><a href="color">Color</a> - Core color representation and manipulation</li>
      <li><a href="color-palette">ColorPalette</a> - Collection of colors with analysis tools</li>
      <li><a href="theme">Theme</a> - Theme generation and management</li>
    </ul>
  </div>

  <div class="api-section">
    <h2>🖼️ Image Processing</h2>
    <ul>
      <li><a href="image-loader">ImageLoader</a> - Image loading and processing</li>
      <li><a href="color-extractor">ColorExtractor</a> - Color extraction from images</li>
    </ul>
  </div>

  <div class="api-section">
    <h2>🔧 Color Operations</h2>
    <ul>
      <li><a href="color-manipulation">Color Manipulation</a> - Adjusting colors</li>
      <li><a href="color-spaces">Color Spaces</a> - Working with different color spaces</li>
      <li><a href="color-schemes">Color Schemes</a> - Generating color combinations</li>
    </ul>
  </div>

  <div class="api-section">
    <h2>🎯 Advanced Features</h2>
    <ul>
      <li><a href="palette-generation">Palette Generation</a> - Creating color palettes</li>
      <li><a href="utilities">Utilities</a> - Helper functions and tools</li>
    </ul>
  </div>
</div>

## Quick Start

Here's a quick overview of the most commonly used features:

### Creating Colors

```php
use Farzai\ColorPalette\Color;

// From RGB values
$color = new Color(37, 99, 235);

// From hex string
$color = Color::fromHex('#2563eb');

// From HSL values
$color = Color::fromHsl(220, 84, 53);
```

### Extracting Colors from Images

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image (static method)
$image = ImageFactory::createFromPath('image.jpg');

// Extract colors
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5);
```

### Generating Themes

```php
use Farzai\ColorPalette\PaletteGenerator;

// Create generator with base color
$generator = new PaletteGenerator($color);

// Generate different schemes
$analogous = $generator->analogous();
$complementary = $generator->complementary();
$websiteTheme = $generator->websiteTheme();
```

## Class Reference

### Core Classes

<div class="class-grid">
  <div class="class-card">
    <h3>Color</h3>
    <p>Core class for color representation and manipulation.</p>
    <a href="color" class="api-link">View Documentation →</a>
  </div>

  <div class="class-card">
    <h3>ColorPalette</h3>
    <p>Manages collections of colors with analysis tools.</p>
    <a href="color-palette" class="api-link">View Documentation →</a>
  </div>

  <div class="class-card">
    <h3>Theme</h3>
    <p>Handles theme generation and management.</p>
    <a href="theme" class="api-link">View Documentation →</a>
  </div>

  <div class="class-card">
    <h3>ColorExtractor</h3>
    <p>Extracts dominant colors from images.</p>
    <a href="color-extractor" class="api-link">View Documentation →</a>
  </div>
</div>

## Interface Reference

### Core Interfaces

```php
// ColorInterface - Base interface for color operations
interface ColorInterface {
    public function toHex(): string;
    public function toRgb(): array;
    public function toHsl(): array;
    // ... more methods
}

// ColorPaletteInterface - Interface for palette operations
interface ColorPaletteInterface {
    public function getColors(): array;
    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface;
    public function getSuggestedSurfaceColors(): array;
}

// ThemeInterface - Interface for theme operations
interface ThemeInterface {
    public function getPrimaryColor(): ColorInterface;
    public function getSecondaryColor(): ColorInterface;
    public function getAccentColor(): ColorInterface;
    // ... more methods
}
```

## Error Handling

The library uses custom exceptions for different types of errors:

```php
use Farzai\ColorPalette\Exceptions\ColorException;
use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\Exceptions\InvalidArgumentException;

try {
    // Your code here
} catch (ColorException $e) {
    // Handle color-related errors
} catch (ImageException $e) {
    // Handle image-related errors
} catch (InvalidArgumentException $e) {
    // Handle invalid argument errors
}
```

## Best Practices

1. **Color Creation**
   - Use the most appropriate constructor for your use case
   - Validate color values before creation
   - Use named constructors for clarity

2. **Color Extraction**
   - Cache extracted palettes for frequently used images
   - Use appropriate sample sizes for performance
   - Handle extraction errors gracefully

3. **Theme Generation**
   - Start with a carefully chosen base color
   - Test themes across different contexts
   - Consider accessibility requirements

4. **Performance**
   - Use the GD backend for better performance
   - Implement caching where appropriate
   - Batch color operations when possible

## See Also

- [Getting Started Guide](../getting-started)
- [Core Concepts](../core-concepts)
- [Examples](../examples/)
- [Color Playground](../playground)