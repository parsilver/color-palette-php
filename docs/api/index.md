---
layout: default
title: API Reference - Color Palette PHP
description: Complete API documentation for Color Palette PHP library, including color manipulation, extraction, and theme generation
keywords: php color palette api, color manipulation api, color extraction api, theme generation api
---

# API Reference

Welcome to the Color Palette PHP API documentation. This comprehensive guide covers all the classes, methods, and features available in the library.

## Quick Navigation

<div class="quick-nav">
  <a href="#quick-reference" class="nav-card">📋 Quick Reference</a>
  <a href="#core-components" class="nav-card">🎨 Core Components</a>
  <a href="#recipes" class="nav-card">🍳 Common Recipes</a>
  <a href="#detailed-reference" class="nav-card">📚 Detailed Reference</a>
</div>

---

## Quick Reference

A searchable overview of all API components:

| Component | Type | Description | Link |
|-----------|------|-------------|------|
| **Color** | Class | Core color representation and manipulation | [Reference](reference/color) |
| **ColorPalette** | Class | Collection of colors with analysis tools | [Reference](reference/color-palette) |
| **Theme** | Class | Theme generation and management | [Reference](reference/theme) |
| **ImageLoader** | Class | Image loading and processing | [Reference](reference/image-loader) |
| **ColorExtractor** | Interface | Extract colors from images | [Reference](reference/color-extractor) |
| **ColorExtractorFactory** | Class | Factory for creating color extractors | [Reference](reference/color-extractor-factory) |
| **PaletteGenerator** | Class | Generate color palettes and schemes | [Reference](reference/palette-generation) |
| **Color Manipulation** | Methods | Adjust brightness, saturation, hue | [Reference](reference/color-manipulation) |
| **Color Spaces** | Conversions | RGB, HSL, HSV, CMYK conversions | [Reference](reference/color-spaces) |
| **Color Schemes** | Patterns | Complementary, analogous, triadic, etc. | [Reference](reference/color-schemes) |
| **Utilities** | Helpers | Helper functions and tools | [Reference](reference/utilities) |
| **Exceptions** | Error Handling | Custom exception classes | [Reference](reference/exceptions) |

---

## Quick Start

### Creating Colors

```php
use Farzai\ColorPalette\Color;

// From RGB values
$color = new Color(37, 99, 235);

// From hex string
$color = Color::fromHex('#2563eb');

// From HSL values
$color = Color::fromHsl(220, 84, 53);

// From HSV values
$color = Color::fromHsv(220, 84, 92);
```

### Extracting Colors from Images

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image
$image = ImageFactory::createFromPath('image.jpg');

// Extract colors
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd'); // or 'imagick'
$palette = $extractor->extract($image, 5);

// Get dominant colors
foreach ($palette->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

### Generating Color Schemes

```php
use Farzai\ColorPalette\PaletteGenerator;

// Create generator with base color
$generator = new PaletteGenerator($color);

// Generate different schemes
$analogous = $generator->analogous();
$complementary = $generator->complementary();
$triadic = $generator->triadic();
$websiteTheme = $generator->websiteTheme();
```

---

## Core Components

<div class="api-overview">
  <div class="api-section">
    <h3>🎨 Color Operations</h3>
    <ul>
      <li><a href="reference/color">Color</a> - Core color representation and manipulation</li>
      <li><a href="reference/color-palette">ColorPalette</a> - Collection of colors with analysis</li>
      <li><a href="reference/color-manipulation">Color Manipulation</a> - Adjust colors</li>
      <li><a href="reference/color-spaces">Color Spaces</a> - RGB, HSL, HSV, CMYK conversions</li>
    </ul>
  </div>

  <div class="api-section">
    <h3>🖼️ Image Processing</h3>
    <ul>
      <li><a href="reference/image-loader">ImageLoader</a> - Load and process images</li>
      <li><a href="reference/color-extractor">ColorExtractor</a> - Extract colors from images</li>
      <li><a href="reference/color-extractor-factory">ColorExtractorFactory</a> - Create extractors</li>
      <li><a href="reference/gd-color-extractor">GdColorExtractor</a> - GD-based extraction</li>
      <li><a href="reference/imagick-color-extractor">ImagickColorExtractor</a> - Imagick-based extraction</li>
    </ul>
  </div>

  <div class="api-section">
    <h3>🎯 Theme & Palette Generation</h3>
    <ul>
      <li><a href="reference/theme">Theme</a> - Theme generation and management</li>
      <li><a href="reference/palette-generation">PaletteGenerator</a> - Create color palettes</li>
      <li><a href="reference/color-schemes">Color Schemes</a> - Generate harmonious combinations</li>
    </ul>
  </div>

  <div class="api-section">
    <h3>🔧 Utilities & Helpers</h3>
    <ul>
      <li><a href="reference/utilities">Utilities</a> - Helper functions</li>
      <li><a href="reference/factories">Factories</a> - Factory classes</li>
      <li><a href="reference/exceptions">Exceptions</a> - Error handling</li>
    </ul>
  </div>
</div>

---

## Recipes

Common patterns and copy-paste solutions to typical problems:

<div class="recipes-grid">
  <div class="recipe-card">
    <h3>🎨 Extracting Dominant Colors</h3>
    <p>Learn how to extract and analyze dominant colors from images efficiently.</p>
    <a href="recipes/extracting-dominant-colors" class="recipe-link">View Recipe →</a>
  </div>

  <div class="recipe-card">
    <h3>🌈 Creating Color Schemes</h3>
    <p>Generate harmonious color schemes for your designs and themes.</p>
    <a href="recipes/creating-color-schemes" class="recipe-link">View Recipe →</a>
  </div>

  <div class="recipe-card">
    <h3>♿ Checking Accessibility</h3>
    <p>Ensure your color combinations meet WCAG accessibility standards.</p>
    <a href="recipes/checking-accessibility" class="recipe-link">View Recipe →</a>
  </div>

  <div class="recipe-card">
    <h3>🔄 Color Format Conversions</h3>
    <p>Convert between different color formats (HEX, RGB, HSL, HSV, CMYK).</p>
    <a href="recipes/color-format-conversions" class="recipe-link">View Recipe →</a>
  </div>

  <div class="recipe-card">
    <h3>⚡ Performance Optimization</h3>
    <p>Optimize color extraction and manipulation for better performance.</p>
    <a href="recipes/performance-optimization" class="recipe-link">View Recipe →</a>
  </div>
</div>

---

## Detailed Reference

### Core Classes

#### Color
The foundation of the library - represents a single color with comprehensive manipulation capabilities.

**Key Features:**
- Multiple creation methods (RGB, HEX, HSL, HSV)
- Color space conversions
- Brightness, saturation, hue adjustments
- Contrast ratio calculations
- Accessibility checking

[→ Full Color Documentation](reference/color)

#### ColorPalette
Manages collections of colors with powerful analysis tools.

**Key Features:**
- Dominant color analysis
- Color sorting and filtering
- Suggested text colors for backgrounds
- Surface color recommendations
- Statistical analysis

[→ Full ColorPalette Documentation](reference/color-palette)

#### Theme
Handles complete theme generation and management.

**Key Features:**
- Primary, secondary, accent colors
- Text and surface colors
- Export to various formats
- Accessibility-aware theme generation

[→ Full Theme Documentation](reference/theme)

---

### Image Processing

#### ImageLoader
Loads and prepares images for color extraction.

**Key Features:**
- Multiple image format support
- Memory-efficient processing
- Automatic format detection
- Path and URL loading

[→ Full ImageLoader Documentation](reference/image-loader)

#### ColorExtractor
Extracts dominant colors from images using various algorithms.

**Available Implementations:**
- **GdColorExtractor** - Fast, memory-efficient (recommended)
- **ImagickColorExtractor** - More accurate, requires ImageMagick

[→ ColorExtractor Documentation](reference/color-extractor)
[→ Factory Documentation](reference/color-extractor-factory)

---

### Color Schemes & Generation

#### PaletteGenerator
Creates harmonious color palettes based on color theory.

**Available Schemes:**
- Analogous (adjacent colors)
- Complementary (opposite colors)
- Triadic (evenly spaced)
- Tetradic (four colors)
- Split-complementary
- Website themes

[→ Full PaletteGenerator Documentation](reference/palette-generation)
[→ Color Schemes Guide](reference/color-schemes)

---

## Interface Reference

### Core Interfaces

```php
// ColorInterface - Base interface for color operations
interface ColorInterface {
    public function toHex(): string;
    public function toRgb(): array;
    public function toHsl(): array;
    public function toHsv(): array;
    public function brightness(int $amount): ColorInterface;
    public function saturation(int $amount): ColorInterface;
    public function contrastRatio(ColorInterface $color): float;
}

// ColorPaletteInterface - Interface for palette operations
interface ColorPaletteInterface {
    public function getColors(): array;
    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface;
    public function getSuggestedSurfaceColors(): array;
    public function getDominantColor(): ColorInterface;
}

// ThemeInterface - Interface for theme operations
interface ThemeInterface {
    public function getPrimaryColor(): ColorInterface;
    public function getSecondaryColor(): ColorInterface;
    public function getAccentColor(): ColorInterface;
    public function getTextColor(): ColorInterface;
    public function getSurfaceColors(): array;
    public function toArray(): array;
}
```

---

## Error Handling

The library uses custom exceptions for different types of errors:

```php
use Farzai\ColorPalette\Exceptions\ColorException;
use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\Exceptions\InvalidArgumentException;

try {
    $color = Color::fromHex($userInput);
} catch (ColorException $e) {
    // Handle color-related errors (invalid hex, RGB values, etc.)
    echo "Invalid color: " . $e->getMessage();
} catch (ImageException $e) {
    // Handle image-related errors (file not found, invalid format, etc.)
    echo "Image error: " . $e->getMessage();
} catch (InvalidArgumentException $e) {
    // Handle invalid argument errors
    echo "Invalid argument: " . $e->getMessage();
}
```

[→ Full Exception Documentation](reference/exceptions)

---

## Best Practices

### 1. Color Creation
```php
// ✅ Good - Use named constructors for clarity
$color = Color::fromHex('#2563eb');

// ❌ Avoid - Ambiguous RGB values
$color = new Color(37, 99, 235);

// ✅ Better - Clear intent with named constructor
$color = Color::fromRgb(37, 99, 235);
```

### 2. Color Extraction
```php
// ✅ Good - Cache extracted palettes
$cacheKey = md5_file($imagePath);
if (!$cache->has($cacheKey)) {
    $palette = $extractor->extract($image, 5);
    $cache->set($cacheKey, $palette);
}

// ✅ Good - Use appropriate sample size
$palette = $extractor->extract($image, 5, [
    'sample_size' => 1000, // Don't process all pixels
]);

// ✅ Good - Handle errors gracefully
try {
    $palette = $extractor->extract($image, 5);
} catch (ImageException $e) {
    // Fallback to default colors
    $palette = new ColorPalette([Color::fromHex('#000000')]);
}
```

### 3. Theme Generation
```php
// ✅ Good - Start with carefully chosen base color
$baseColor = Color::fromHex('#2563eb'); // Brand color
$generator = new PaletteGenerator($baseColor);

// ✅ Good - Test for accessibility
$theme = $generator->websiteTheme();
$contrastRatio = $theme->getTextColor()->contrastRatio($theme->getPrimaryColor());
if ($contrastRatio < 4.5) {
    // Adjust theme for better contrast
}

// ✅ Good - Validate generated colors
foreach ($theme->getSurfaceColors() as $surfaceColor) {
    $textColor = $theme->getTextColor();
    if ($textColor->contrastRatio($surfaceColor) < 4.5) {
        // Handle low contrast
    }
}
```

### 4. Performance
```php
// ✅ Good - Use GD backend for better performance
$extractor = $extractorFactory->make('gd');

// ✅ Good - Implement caching
class CachedColorExtractor {
    public function extract($image, $count) {
        $key = $this->getCacheKey($image, $count);
        return $this->cache->remember($key, function() use ($image, $count) {
            return $this->extractor->extract($image, $count);
        });
    }
}

// ✅ Good - Batch color operations
$colors = array_map(function($hex) {
    return Color::fromHex($hex);
}, $hexColors);
```

---

## Common Patterns

### Pattern 1: Image to Theme
```php
// Extract colors from an image and generate a complete theme
$image = ImageFactory::createFromPath('brand-logo.png');
$extractor = (new ColorExtractorFactory())->make('gd');
$palette = $extractor->extract($image, 1);

$baseColor = $palette->getDominantColor();
$generator = new PaletteGenerator($baseColor);
$theme = $generator->websiteTheme();
```

### Pattern 2: Accessible Color Pairs
```php
// Generate accessible text/background color pairs
function getAccessiblePair(Color $baseColor): array {
    $textColor = $baseColor->brightness(50);

    if ($textColor->contrastRatio($baseColor) < 4.5) {
        // Adjust until accessible
        $textColor = $baseColor->brightness() > 128
            ? Color::fromHex('#000000')
            : Color::fromHex('#ffffff');
    }

    return [
        'background' => $baseColor,
        'text' => $textColor,
        'contrast' => $textColor->contrastRatio($baseColor)
    ];
}
```

### Pattern 3: Dynamic Theme Switching
```php
// Generate light and dark themes from a single base color
function generateThemes(Color $brandColor): array {
    $generator = new PaletteGenerator($brandColor);

    return [
        'light' => $generator->websiteTheme(),
        'dark' => $generator->websiteTheme([
            'invert_brightness' => true,
            'preserve_hue' => true,
        ]),
    ];
}
```

---

## See Also

- [Quick Start Guide](../quick-start)
- [Installation Guide](../guides/installation)
- [Core Concepts](../concepts/color-spaces)
- [Examples](../examples/)
- [GitHub Repository](https://github.com/farzai/color-palette-php)

---

## Need Help?

- **Issues**: [Report a bug](https://github.com/farzai/color-palette-php/issues)
- **Discussions**: [Ask questions](https://github.com/farzai/color-palette-php/discussions)
- **Contributing**: [Contribution guide](https://github.com/farzai/color-palette-php/blob/main/CONTRIBUTING.md)
