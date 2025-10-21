---
layout: default
title: ColorPalette Class - Color Palette PHP API
description: Documentation for the ColorPalette class, including color collection management, analysis, and theme suggestions
keywords: php color palette, color collection, color analysis, theme suggestions
---

# ColorPalette Class

The `ColorPalette` class manages collections of colors and provides tools for color analysis and theme generation.

## Overview

```php
namespace Farzai\ColorPalette;

class ColorPalette implements ArrayAccess, ColorPaletteInterface, Countable
{
    // ...
}
```

The `ColorPalette` class provides functionality for:
- Managing collections of colors
- Analyzing color relationships
- Generating color suggestions
- Array-like color access

## Creating Color Palettes

<div class="method-doc">
  <div class="method-header">
    <h3>Constructor</h3>
    <div class="method-signature">public function __construct(array $colors)</div>
  </div>
  <div class="method-content">
    <div class="method-description">
      Creates a new ColorPalette instance from an array of colors.
    </div>
    <div class="parameters">
      <h4>Parameters</h4>
      <table>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Description</th>
        </tr>
        <tr>
          <td>$colors</td>
          <td>array&lt;string|int, ColorInterface&gt;</td>
          <td>Array of colors with optional keys</td>
        </tr>
      </table>
    </div>
  </div>
</div>

## Color Collection Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>getColors</h3>
      <div class="method-signature">public function getColors(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets all colors in the palette.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array of ColorInterface instances</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>count</h3>
      <div class="method-signature">public function count(): int</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the number of colors in the palette.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Number of colors</p>
      </div>
    </div>
  </div>
</div>

## Array Access Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>offsetExists</h3>
      <div class="method-signature">public function offsetExists(mixed $offset): bool</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Checks if a color exists at the specified offset.
      </div>
      <div class="parameters">
        <h4>Parameters</h4>
        <table>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
          </tr>
          <tr>
            <td>$offset</td>
            <td>mixed</td>
            <td>Array offset or key</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>offsetGet</h3>
      <div class="method-signature">public function offsetGet(mixed $offset): ?ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets a color at the specified offset.
      </div>
      <div class="parameters">
        <h4>Parameters</h4>
        <table>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
          </tr>
          <tr>
            <td>$offset</td>
            <td>mixed</td>
            <td>Array offset or key</td>
          </tr>
        </table>
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Color instance or null if not found</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>offsetSet</h3>
      <div class="method-signature">public function offsetSet(mixed $offset, mixed $value): void</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Sets a color at the specified offset.
      </div>
      <div class="parameters">
        <h4>Parameters</h4>
        <table>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
          </tr>
          <tr>
            <td>$offset</td>
            <td>mixed</td>
            <td>Array offset or key</td>
          </tr>
          <tr>
            <td>$value</td>
            <td>ColorInterface</td>
            <td>Color instance to set</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>offsetUnset</h3>
      <div class="method-signature">public function offsetUnset(mixed $offset): void</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Removes a color at the specified offset.
      </div>
      <div class="parameters">
        <h4>Parameters</h4>
        <table>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
          </tr>
          <tr>
            <td>$offset</td>
            <td>mixed</td>
            <td>Array offset or key</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

## Color Analysis Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>getSuggestedTextColor</h3>
      <div class="method-signature">public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets a suggested text color for the given background color.
      </div>
      <div class="parameters">
        <h4>Parameters</h4>
        <table>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
          </tr>
          <tr>
            <td>$backgroundColor</td>
            <td>ColorInterface</td>
            <td>Background color to analyze</td>
          </tr>
        </table>
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Suggested text color (black or white) for optimal contrast</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getSuggestedSurfaceColors</h3>
      <div class="method-signature">public function getSuggestedSurfaceColors(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets suggested surface colors based on the palette.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array of suggested colors for different surface types:</p>
        <ul>
          <li>'surface' - Main surface color</li>
          <li>'background' - Background color</li>
          <li>'accent' - Accent color</li>
          <li>'surface_variant' - Alternative surface color</li>
        </ul>
      </div>
    </div>
  </div>
</div>

## Examples

### Creating and Using a Color Palette

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

// Create colors
$blue = new Color(37, 99, 235);
$red = new Color(239, 68, 68);
$green = new Color(34, 197, 94);

// Create palette
$palette = new ColorPalette([$blue, $red, $green]);

// Access colors
$firstColor = $palette[0];
$count = count($palette);

// Add a new color
$palette[] = new Color(168, 85, 247); // Purple
```

### Color Analysis

```php
// Get suggested text color
$backgroundColor = $palette[0];
$textColor = $palette->getSuggestedTextColor($backgroundColor);

// Get surface colors
$surfaceColors = $palette->getSuggestedSurfaceColors();
$mainSurface = $surfaceColors['surface'];
$background = $surfaceColors['background'];
$accent = $surfaceColors['accent'];
```

### Array Access

```php
// Check if color exists
if (isset($palette[0])) {
    // Get color
    $color = $palette[0];
    
    // Update color
    $palette[0] = new Color(59, 130, 246);
    
    // Remove color
    unset($palette[0]);
}
```

## See Also

- [Color Class](color)
- [Theme Class](theme)
- [ColorExtractor Class](color-extractor)
- [Color Manipulation](color-manipulation)