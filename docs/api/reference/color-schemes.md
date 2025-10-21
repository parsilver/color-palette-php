---
layout: default
title: Color Schemes - Color Palette PHP API
description: Documentation for color scheme generation and manipulation in Color Palette PHP
keywords: php color schemes, color harmonies, color combinations, color theory
---

# Color Schemes

Color Palette PHP provides comprehensive support for generating harmonious color schemes based on color theory principles.

## Overview

The color scheme functionality is primarily provided through the `PaletteGenerator` class:

```php
namespace Farzai\ColorPalette;

class PaletteGenerator
{
    public function __construct(ColorInterface $baseColor)
    {
        // ...
    }
}
```

## Color Harmony Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>analogous</h3>
      <div class="method-signature">public function analogous(): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Generates an analogous color scheme using colors adjacent on the color wheel.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>ColorPalette with three colors: base color and two adjacent colors (±30°)</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>complementary</h3>
      <div class="method-signature">public function complementary(): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a complementary color scheme using opposite colors on the color wheel.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>ColorPalette with two colors: base color and its complement (180°)</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>triadic</h3>
      <div class="method-signature">public function triadic(): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Generates a triadic color scheme using three evenly spaced colors.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>ColorPalette with three colors spaced 120° apart</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>tetradic</h3>
      <div class="method-signature">public function tetradic(): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a tetradic (double complementary) color scheme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>ColorPalette with four colors forming a rectangle on the color wheel</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>splitComplementary</h3>
      <div class="method-signature">public function splitComplementary(): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Generates a split-complementary color scheme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>ColorPalette with three colors: base and two colors adjacent to its complement</p>
      </div>
    </div>
  </div>
</div>

## Monochromatic Variations

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>monochromatic</h3>
      <div class="method-signature">public function monochromatic(int $count = 5): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates variations of the base color with different lightness values.
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
            <td>$count</td>
            <td>int</td>
            <td>Number of variations to generate</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>shades</h3>
      <div class="method-signature">public function shades(int $count = 5): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Generates darker variations of the base color.
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
            <td>$count</td>
            <td>int</td>
            <td>Number of shades to generate</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>tints</h3>
      <div class="method-signature">public function tints(int $count = 5): ColorPalette</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates lighter variations of the base color.
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
            <td>$count</td>
            <td>int</td>
            <td>Number of tints to generate</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

## Theme Generation

<div class="method-doc">
  <div class="method-header">
    <h3>websiteTheme</h3>
    <div class="method-signature">public function websiteTheme(): ColorPalette</div>
  </div>
  <div class="method-content">
    <div class="method-description">
      Generates a complete website color theme based on the base color.
    </div>
    <div class="return-value">
      <h4>Returns</h4>
      <p>ColorPalette containing:</p>
      <ul>
        <li>primary: Base color</li>
        <li>secondary: Desaturated complementary</li>
        <li>accent: Saturated complementary</li>
        <li>background: Light neutral</li>
        <li>surface: White or near-white</li>
      </ul>
    </div>
  </div>
</div>

## Examples

### Basic Color Schemes

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

// Create a base color
$baseColor = new Color(37, 99, 235); // Blue
$generator = new PaletteGenerator($baseColor);

// Generate different schemes
$analogous = $generator->analogous();
$complementary = $generator->complementary();
$triadic = $generator->triadic();
$tetradic = $generator->tetradic();
$splitComp = $generator->splitComplementary();
```

### Monochromatic Variations

```php
// Generate variations
$mono = $generator->monochromatic(5);
$shades = $generator->shades(5);
$tints = $generator->tints(5);

// Access variations
foreach ($mono->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

### Website Theme Generation

```php
// Generate website theme
$theme = $generator->websiteTheme();

// Access theme colors
$primary = $theme->getColors()['primary'];
$secondary = $theme->getColors()['secondary'];
$accent = $theme->getColors()['accent'];
$background = $theme->getColors()['background'];
$surface = $theme->getColors()['surface'];
```

## Color Theory Guide

### Analogous Colors
- Uses colors adjacent to each other on the color wheel
- Creates harmonious and serene color combinations
- Best for creating a unified look

### Complementary Colors
- Uses colors opposite each other on the color wheel
- Creates high contrast and vibrant combinations
- Best for creating emphasis and attention

### Triadic Colors
- Uses three colors equally spaced on the color wheel
- Creates balanced and vibrant combinations
- Best for creating dynamic and energetic designs

### Split-Complementary
- Uses a base color and two colors adjacent to its complement
- Creates high contrast but with less tension than complementary
- Best for beginners in color theory

### Tetradic (Double Complementary)
- Uses four colors arranged into two complementary pairs
- Creates rich and complex color schemes
- Best for creating sophisticated designs

## Best Practices

1. **Color Harmony**
   - Start with a meaningful base color
   - Use complementary colors for emphasis
   - Use analogous colors for harmony
   - Consider color psychology

2. **Accessibility**
   - Ensure sufficient contrast ratios
   - Test color combinations for color blindness
   - Provide alternative visual cues

3. **Implementation**
   - Cache generated color schemes
   - Validate color combinations
   - Consider context and purpose

## See Also

- [Color Class](color)
- [ColorPalette Class](color-palette)
- [Theme Class](theme)
- [Color Manipulation](color-manipulation) 