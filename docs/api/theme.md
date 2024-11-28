---
layout: default
title: Theme Class - Color Palette PHP API
description: Documentation for the Theme class, including theme creation, color management, and theme generation
keywords: php color theme, theme generation, color scheme, theme management
---

# Theme Class

The `Theme` class provides functionality for creating and managing color themes with predefined roles and relationships.

## Overview

```php
namespace Farzai\ColorPalette;

class Theme implements ThemeInterface
{
    // ...
}
```

The `Theme` class provides functionality for:
- Managing themed color collections
- Accessing color roles (primary, secondary, accent, etc.)
- Converting themes to different formats
- Theme generation and customization

## Creating Themes

<div class="method-doc">
  <div class="method-header">
    <h3>Constructor</h3>
    <div class="method-signature">public function __construct(array $colors = [])</div>
  </div>
  <div class="method-content">
    <div class="method-description">
      Creates a new Theme instance with predefined colors.
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
          <td>array&lt;string, ColorInterface&gt;</td>
          <td>Array of colors with role keys</td>
        </tr>
      </table>
    </div>
  </div>
</div>

### Static Constructors

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>fromColors</h3>
      <div class="method-signature">public static function fromColors(array $colors): Theme</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Creates a theme from an array of colors with role assignments.
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
            <td>array&lt;string, ColorInterface&gt;</td>
            <td>Array of colors with role keys</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</div>

## Color Role Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>getPrimaryColor</h3>
      <div class="method-signature">public function getPrimaryColor(): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the primary color of the theme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Primary color instance</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getSecondaryColor</h3>
      <div class="method-signature">public function getSecondaryColor(): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the secondary color of the theme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Secondary color instance</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getAccentColor</h3>
      <div class="method-signature">public function getAccentColor(): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the accent color of the theme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Accent color instance</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getBackgroundColor</h3>
      <div class="method-signature">public function getBackgroundColor(): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the background color of the theme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Background color instance</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getSurfaceColor</h3>
      <div class="method-signature">public function getSurfaceColor(): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets the surface color of the theme.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Surface color instance</p>
      </div>
    </div>
  </div>
</div>

## Theme Management Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>getColor</h3>
      <div class="method-signature">public function getColor(string $name): ColorInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets a color by its role name.
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
            <td>$name</td>
            <td>string</td>
            <td>Color role name</td>
          </tr>
        </table>
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Color instance for the specified role</p>
      </div>
      <div class="throws">
        <h4>Throws</h4>
        <p>InvalidArgumentException if color role not found</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>hasColor</h3>
      <div class="method-signature">public function hasColor(string $name): bool</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Checks if a color role exists in the theme.
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
            <td>$name</td>
            <td>string</td>
            <td>Color role name</td>
          </tr>
        </table>
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>True if color role exists, false otherwise</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>getColors</h3>
      <div class="method-signature">public function getColors(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Gets all colors in the theme with their roles.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array of colors with role keys</p>
      </div>
    </div>
  </div>

  <div class="method-doc">
    <div class="method-header">
      <h3>toArray</h3>
      <div class="method-signature">public function toArray(): array</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Converts the theme to an array of hex color values.
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>Array of hex color values with role keys</p>
      </div>
    </div>
  </div>
</div>

## Examples

### Creating a Theme

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Theme;

// Create colors
$primary = new Color(37, 99, 235);    // Blue
$secondary = new Color(239, 68, 68);   // Red
$accent = new Color(34, 197, 94);      // Green
$background = new Color(249, 250, 251); // Light gray
$surface = new Color(255, 255, 255);    // White

// Create theme with constructor
$theme = new Theme([
    'primary' => $primary,
    'secondary' => $secondary,
    'accent' => $accent,
    'background' => $background,
    'surface' => $surface
]);

// Or use static constructor
$theme = Theme::fromColors([
    'primary' => $primary,
    'secondary' => $secondary,
    'accent' => $accent,
    'background' => $background,
    'surface' => $surface
]);
```

### Accessing Theme Colors

```php
// Get colors by role
$primary = $theme->getPrimaryColor();
$secondary = $theme->getSecondaryColor();
$accent = $theme->getAccentColor();

// Check if color role exists
if ($theme->hasColor('primary')) {
    $color = $theme->getColor('primary');
}

// Get all colors
$colors = $theme->getColors();

// Convert to hex values
$hexColors = $theme->toArray();
```

### Theme Generation

```php
use Farzai\ColorPalette\PaletteGenerator;

// Create a generator with base color
$generator = new PaletteGenerator($primary);

// Generate website theme
$websiteTheme = $generator->websiteTheme();

// Access generated theme colors
$background = $websiteTheme->getBackgroundColor();
$surface = $websiteTheme->getSurfaceColor();
```

## See Also

- [Color Class](color)
- [ColorPalette Class](color-palette)
- [PaletteGenerator Class](palette-generation)
- [Color Schemes](color-schemes) 