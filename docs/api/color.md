---
layout: default
title: Color Class - Color Palette PHP API
description: Documentation for the Color class in Color Palette PHP, including color creation, manipulation, and conversion methods
keywords: php color class, color manipulation, color conversion, color spaces
---

# Color Class

The `Color` class is the core component for representing and manipulating colors in Color Palette PHP.

## Overview

```php
namespace Farzai\ColorPalette;

class Color implements ColorInterface
{
    // ...
}
```

The `Color` class provides comprehensive functionality for:
- Color creation and representation
- Color space conversions
- Color manipulation and adjustments
- Color analysis and comparison

## Creating Colors

<div class="method-doc">
  <div class="method-header">
    <h3>Constructor</h3>
    <div class="method-signature">public function __construct(int $red, int $green, int $blue)</div>
  </div>
  <div class="method-content">
    <div class="method-description">
      Creates a new Color instance from RGB values.
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
          <td>$red</td>
          <td>int</td>
          <td>Red component (0-255)</td>
        </tr>
        <tr>
          <td>$green</td>
          <td>int</td>
          <td>Green component (0-255)</td>
        </tr>
        <tr>
          <td>$blue</td>
          <td>int</td>
          <td>Blue component (0-255)</td>
        </tr>
      </table>
    </div>
  </div>
</div>

### Named Constructors

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>fromHex</h3>
      <div class="method-signature">public static function fromHex(string $hex): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a Color instance from a hexadecimal color string.
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
            <td>$hex</td>
            <td>string</td>
            <td>Hexadecimal color code (e.g., "#ff0000" or "ff0000")</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>fromHsl</h3>
      <div class="method-signature">public static function fromHsl(float $hue, float $saturation, float $lightness): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a Color instance from HSL values.
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
            <td>$hue</td>
            <td>float</td>
            <td>Hue component (0-360)</td>
          </tr>
          <tr>
            <td>$saturation</td>
            <td>float</td>
            <td>Saturation component (0-100)</td>
          </tr>
          <tr>
            <td>$lightness</td>
            <td>float</td>
            <td>Lightness component (0-100)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

## Color Space Conversions

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>toHex</h3>
      <div class="method-signature">public function toHex(): string</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Converts the color to hexadecimal format.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Hexadecimal color code (e.g., "#ff0000")</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>toRgb</h3>
      <div class="method-signature">public function toRgb(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the RGB components of the color.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array with 'r', 'g', 'b' keys containing values 0-255</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>toHsl</h3>
      <div class="method-signature">public function toHsl(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Converts the color to HSL format.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array with 'h' (0-360), 's' (0-100), 'l' (0-100) components</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>toCmyk</h3>
      <div class="method-signature">public function toCmyk(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Converts the color to CMYK format.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array with 'c', 'm', 'y', 'k' keys containing values 0-100</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>toLab</h3>
      <div class="method-signature">public function toLab(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Converts the color to LAB color space.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array with 'l', 'a', 'b' components</p>
      </div>
    </div>
  </div>
</div>

## Color Manipulation

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>lighten</h3>
      <div class="method-signature">public function lighten(float $amount): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a lighter version of the color.
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
            <td>$amount</td>
            <td>float</td>
            <td>Amount to lighten (0-1)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>darken</h3>
      <div class="method-signature">public function darken(float $amount): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a darker version of the color.
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
            <td>$amount</td>
            <td>float</td>
            <td>Amount to darken (0-1)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>saturate</h3>
      <div class="method-signature">public function saturate(float $amount): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Increases the saturation of the color.
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
            <td>$amount</td>
            <td>float</td>
            <td>Amount to increase saturation (0-1)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>desaturate</h3>
      <div class="method-signature">public function desaturate(float $amount): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Decreases the saturation of the color.
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
            <td>$amount</td>
            <td>float</td>
            <td>Amount to decrease saturation (0-1)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>rotate</h3>
      <div class="method-signature">public function rotate(float $degrees): Color</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Rotates the hue of the color.
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
            <td>$degrees</td>
            <td>float</td>
            <td>Degrees to rotate the hue (-360 to 360)</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

## Color Analysis

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>getBrightness</h3>
      <div class="method-signature">public function getBrightness(): float</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the perceived brightness of the color.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Brightness value between 0 and 255</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getLuminance</h3>
      <div class="method-signature">public function getLuminance(): float</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the relative luminance of the color.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Luminance value between 0 and 1</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getContrastRatio</h3>
      <div class="method-signature">public function getContrastRatio(ColorInterface $color): float</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Calculates the contrast ratio between this color and another color.
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
            <td>$color</td>
            <td>ColorInterface</td>
            <td>Color to compare against</td>
          </tr>
        </table>
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Contrast ratio between 1 and 21</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>isLight</h3>
      <div class="method-signature">public function isLight(): bool</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Determines if the color is considered light.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>True if the color is light, false otherwise</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>isDark</h3>
      <div class="method-signature">public function isDark(): bool</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Determines if the color is considered dark.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>True if the color is dark, false otherwise</p>
      </div>
    </div>
  </div>
</div>

## Examples

### Basic Color Creation and Manipulation

```php
use Farzai\ColorPalette\Color;

// Create a color
$color = new Color(37, 99, 235);  // Blue
echo $color->toHex();             // "#2563eb"

// Manipulate the color
$lighter = $color->lighten(0.2);
$darker = $color->darken(0.2);
$rotated = $color->rotate(180);   // Complementary color
```

### Color Space Conversions

```php
// Convert between color spaces
$rgb = $color->toRgb();   // ['r' => 37, 'g' => 99, 'b' => 235]
$hsl = $color->toHsl();   // ['h' => 220, 's' => 84, 'l' => 53]
$cmyk = $color->toCmyk(); // ['c' => 84, 'm' => 58, 'y' => 0, 'k' => 8]
$lab = $color->toLab();   // ['l' => 45, 'a' => 8, 'b' => -65]
```

### Color Analysis

```php
// Analyze color properties
$brightness = $color->getBrightness();
$luminance = $color->getLuminance();

// Check color characteristics
if ($color->isLight()) {
    echo "This is a light color";
}

// Calculate contrast ratio
$white = new Color(255, 255, 255);
$contrastRatio = $color->getContrastRatio($white);
$isAccessible = $contrastRatio >= 4.5; // WCAG AA standard
```

## See Also

- [ColorPalette Class](color-palette)
- [Theme Class](theme)
- [Color Manipulation](color-manipulation)
- [Color Spaces](color-spaces)