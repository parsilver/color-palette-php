---
layout: default
title: Theme Class - Color Palette PHP API
description: Documentation for the Theme class, including theme creation and color management
keywords: php color theme, theme management, color scheme
---

# Theme Class

The `Theme` class provides functionality for managing named color collections.

## Overview

```php
namespace Farzai\ColorPalette;

class Theme implements ThemeInterface
{
    // ...
}
```

The `Theme` class provides functionality for:
- Managing named color collections
- Accessing colors by name
- Converting themes to array format

## Creating Themes

### Constructor

```php
public function __construct(array $colors = [])
```

Creates a new Theme instance with named colors.

**Parameters:**
- `$colors` (array<string, ColorInterface>): Associative array of named colors

### Static Factory Methods

```php
public static function fromColors(array $colors): self
```

Creates a new Theme instance from an array of colors.

**Parameters:**
- `$colors` (array<string, ColorInterface>): Associative array of named colors

## Color Management

### getColor()

```php
public function getColor(string $name): ColorInterface
```

Gets a color by its name.

**Parameters:**
- `$name` (string): The name of the color to retrieve

**Returns:**
- `ColorInterface`: The requested color

**Throws:**
- `InvalidArgumentException`: If the color name doesn't exist

### hasColor()

```php
public function hasColor(string $name): bool
```

Checks if a color exists in the theme.

**Parameters:**
- `$name` (string): The name of the color to check

**Returns:**
- `bool`: True if the color exists, false otherwise

### getColors()

```php
public function getColors(): array
```

Gets all colors in the theme.

**Returns:**
- `array<string, ColorInterface>`: Associative array of all colors

### toArray()

```php
public function toArray(): array
```

Converts the theme to an array of hex color values.

**Returns:**
- `array<string, string>`: Associative array of color names and their hex values

## Examples

### Basic Usage

```php
use Farzai\ColorPalette\Theme;
use Farzai\ColorPalette\Color;

// Create a theme with named colors
$theme = new Theme([
    'primary' => new Color(37, 99, 235),    // Blue
    'secondary' => new Color(244, 63, 94),   // Red
    'background' => new Color(255, 255, 255) // White
]);

// Access colors by name
$primaryColor = $theme->getColor('primary');
echo $primaryColor->toHex(); // "#2563eb"

// Check if a color exists
if ($theme->hasColor('primary')) {
    // Use the color
}

// Get all colors as hex values
$colors = $theme->toArray();
// [
//     'primary' => '#2563eb',
//     'secondary' => '#f43f5e',
//     'background' => '#ffffff'
// ]
```

### Creating Themes from Colors

```php
// Create a theme using the static factory method
$theme = Theme::fromColors([
    'text' => new Color(0, 0, 0),
    'background' => new Color(255, 255, 255)
]);

// Access colors
$textColor = $theme->getColor('text');
$backgroundColor = $theme->getColor('background');
``` 