---
layout: default
title: Quick Start - Color Palette PHP in 5 Minutes
description: Get started with Color Palette PHP in under 5 minutes. Copy-paste examples for color extraction, theme generation, and color manipulation.
keywords: php color palette quick start, 5 minute guide, color extraction tutorial, php color themes
---

# Quick Start Guide

Get up and running with Color Palette PHP in under 5 minutes. This guide provides copy-paste ready examples to extract colors, generate themes, and manipulate colors.

<div class="quick-links">
  <a href="#installation">📦 Installation</a> •
  <a href="#extract-colors">🎨 Extract Colors</a> •
  <a href="#generate-themes">🌈 Generate Themes</a> •
  <a href="#troubleshooting">🔧 Troubleshooting</a>
</div>

---

## Installation

### Step 1: Install via Composer

```bash
composer require farzai/color-palette
```

> **💡 Tip**: Make sure you have PHP 8.1+ and either GD or Imagick extension installed.

### Step 2: Verify Installation

Check if you have the required image processing extension:

```bash
php -m | grep -E 'gd|imagick'
```

**Expected output:**
```
gd
```
or
```
imagick
```

> **⚠️ Important**: You need at least one of these extensions. See [troubleshooting](#troubleshooting) if neither is installed.

---

## Extract Colors from Images

### Example 1: Basic Color Extraction (GD)

Copy this complete example and save it as `extract-colors.php`:

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image
$image = ImageFactory::createFromPath('path/to/your/image.jpg');

// Create extractor (using GD)
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Extract 5 dominant colors
$palette = $extractor->extract($image, 5);

// Display colors
echo "Extracted Colors:\n";
foreach ($palette->getColors() as $index => $color) {
    echo sprintf(
        "Color %d: %s (RGB: %d, %d, %d)\n",
        $index + 1,
        $color->toHex(),
        $color->toRgb()['r'],
        $color->toRgb()['g'],
        $color->toRgb()['b']
    );
}
```

**Run it:**
```bash
php extract-colors.php
```

**Expected output:**
```
Extracted Colors:
Color 1: #2c5f7e (RGB: 44, 95, 126)
Color 2: #8ba8b7 (RGB: 139, 168, 183)
Color 3: #c4d4db (RGB: 196, 212, 219)
Color 4: #1f3a4a (RGB: 31, 58, 74)
Color 5: #5d8399 (RGB: 93, 131, 153)
```

### Example 2: Using Imagick Backend

Simply change the backend from `'gd'` to `'imagick'`:

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('path/to/your/image.jpg');

// Use Imagick instead of GD
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('imagick');

$palette = $extractor->extract($image, 5);

// Get colors as hex array
$hexColors = $palette->toArray();
print_r($hexColors);
```

**Expected output:**
```php
Array
(
    [0] => #2c5f7e
    [1] => #8ba8b7
    [2] => #c4d4db
    [3] => #1f3a4a
    [4] => #5d8399
)
```

> **💡 Tip**: GD is faster for most use cases. Use Imagick if you need advanced image processing features or work with unusual image formats.

---

## Generate Color Themes

### Example 3: Create a Complete Theme

Generate a full color theme with surface, background, and accent colors:

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Extract colors from image
$image = ImageFactory::createFromPath('path/to/your/image.jpg');
$extractor = (new ColorExtractorFactory())->make('gd');
$palette = $extractor->extract($image, 8); // More colors = better theme variety

// Generate theme colors
$surfaceColors = $palette->getSuggestedSurfaceColors();

// Display theme
echo "🎨 Generated Color Theme:\n\n";

echo "Surface (Main):      " . $surfaceColors['surface']->toHex() . "\n";
echo "Background:          " . $surfaceColors['background']->toHex() . "\n";
echo "Accent:              " . $surfaceColors['accent']->toHex() . "\n";
echo "Surface Variant:     " . $surfaceColors['surface_variant']->toHex() . "\n";

// Get suggested text colors
echo "\n📝 Suggested Text Colors:\n\n";
echo "Text on Surface:     " . $palette->getSuggestedTextColor($surfaceColors['surface'])->toHex() . "\n";
echo "Text on Background:  " . $palette->getSuggestedTextColor($surfaceColors['background'])->toHex() . "\n";
echo "Text on Accent:      " . $palette->getSuggestedTextColor($surfaceColors['accent'])->toHex() . "\n";
```

**Expected output:**
```
🎨 Generated Color Theme:

Surface (Main):      #e8f0f5
Background:          #d1dfe7
Accent:              #2c5f7e
Surface Variant:     #c4d4db

📝 Suggested Text Colors:

Text on Surface:     #000000
Text on Background:  #000000
Text on Accent:      #ffffff
```

### Example 4: CSS Theme Generator

Generate ready-to-use CSS variables:

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('path/to/your/image.jpg');
$extractor = (new ColorExtractorFactory())->make('gd');
$palette = $extractor->extract($image, 8);

$surfaceColors = $palette->getSuggestedSurfaceColors();

// Generate CSS
echo ":root {\n";
echo "  --color-surface: " . $surfaceColors['surface']->toHex() . ";\n";
echo "  --color-background: " . $surfaceColors['background']->toHex() . ";\n";
echo "  --color-accent: " . $surfaceColors['accent']->toHex() . ";\n";
echo "  --color-surface-variant: " . $surfaceColors['surface_variant']->toHex() . ";\n";
echo "  --color-text-on-surface: " . $palette->getSuggestedTextColor($surfaceColors['surface'])->toHex() . ";\n";
echo "  --color-text-on-accent: " . $palette->getSuggestedTextColor($surfaceColors['accent'])->toHex() . ";\n";
echo "}\n";
```

**Expected output:**
```css
:root {
  --color-surface: #e8f0f5;
  --color-background: #d1dfe7;
  --color-accent: #2c5f7e;
  --color-surface-variant: #c4d4db;
  --color-text-on-surface: #000000;
  --color-text-on-accent: #ffffff;
}
```

> **💡 Tip**: Copy this CSS directly into your stylesheet for instant theme integration!

---

## Quick Color Manipulation

### Example 5: Create Color Variations

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\Color;

// Create a base color
$color = Color::fromHex('#3498db');

echo "Original:  " . $color->toHex() . "\n";
echo "Lighter:   " . $color->lighten(0.2)->toHex() . "\n";
echo "Darker:    " . $color->darken(0.2)->toHex() . "\n";
echo "Saturated: " . $color->saturate(0.3)->toHex() . "\n";
echo "Rotated:   " . $color->rotate(180)->toHex() . "\n";
```

**Expected output:**
```
Original:  #3498db
Lighter:   #69b3e5
Darker:    #206d9e
Saturated: #0d8ddb
Rotated:   #db7c34
```

### Example 6: Check Color Accessibility

Ensure your colors meet WCAG contrast requirements:

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\Color;

$background = Color::fromHex('#ffffff');
$text = Color::fromHex('#2c5f7e');

$contrastRatio = $background->getContrastRatio($text);

echo "Contrast Ratio: " . number_format($contrastRatio, 2) . ":1\n\n";

// Check WCAG compliance
if ($contrastRatio >= 7.0) {
    echo "✅ AAA Level (Normal & Large Text)\n";
} elseif ($contrastRatio >= 4.5) {
    echo "✅ AA Level (Normal Text)\n";
} elseif ($contrastRatio >= 3.0) {
    echo "⚠️  AA Level (Large Text Only)\n";
} else {
    echo "❌ Does not meet WCAG standards\n";
}
```

**Expected output:**
```
Contrast Ratio: 6.89:1

✅ AA Level (Normal Text)
```

> **💡 Tip**: Aim for at least 4.5:1 for normal text and 3:1 for large text to meet WCAG AA standards.

---

## Real-World Example: Complete Workflow

Here's a complete example that ties everything together:

```php
<?php
require 'vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// 1. Extract colors from brand image
$image = ImageFactory::createFromPath('logo.png');
$extractor = (new ColorExtractorFactory())->make('gd');
$palette = $extractor->extract($image, 10);

// 2. Generate theme
$surfaceColors = $palette->getSuggestedSurfaceColors();

// 3. Create color variations for UI
$accent = $surfaceColors['accent'];
$accentLight = $accent->lighten(0.2);
$accentDark = $accent->darken(0.2);

// 4. Generate complete CSS theme
$cssTheme = <<<CSS
/* Generated Theme from {$image->getPath()} */
:root {
  /* Surface Colors */
  --surface: {$surfaceColors['surface']->toHex()};
  --background: {$surfaceColors['background']->toHex()};
  --surface-variant: {$surfaceColors['surface_variant']->toHex()};

  /* Accent Colors */
  --accent: {$accent->toHex()};
  --accent-light: {$accentLight->toHex()};
  --accent-dark: {$accentDark->toHex()};

  /* Text Colors */
  --text-on-surface: {$palette->getSuggestedTextColor($surfaceColors['surface'])->toHex()};
  --text-on-accent: {$palette->getSuggestedTextColor($accent)->toHex()};
}

/* Apply theme */
body {
  background: var(--background);
  color: var(--text-on-surface);
}

.button-primary {
  background: var(--accent);
  color: var(--text-on-accent);
}

.button-primary:hover {
  background: var(--accent-dark);
}
CSS;

// Save to file
file_put_contents('theme.css', $cssTheme);

echo "✅ Theme generated successfully!\n";
echo "📄 Saved to: theme.css\n";
```

**Expected output:**
```
✅ Theme generated successfully!
📄 Saved to: theme.css
```

---

## Troubleshooting

### Extension Not Found

**Problem:** `Error: GD/ImageMagick extension not found`

**Solution:**

**Ubuntu/Debian:**
```bash
# For GD
sudo apt-get install php8.1-gd

# For Imagick
sudo apt-get install php8.1-imagick

# Restart web server
sudo service apache2 restart  # or nginx
```

**macOS (Homebrew):**
```bash
# For GD
brew install php
brew install gd

# For Imagick
brew install imagemagick
pecl install imagick
```

**Windows:**
1. Download PHP extension from [windows.php.net](https://windows.php.net/downloads/pecl/releases/)
2. Extract DLL to PHP `ext` directory
3. Add to `php.ini`: `extension=gd` or `extension=imagick`
4. Restart server

### Memory Limit Issues

**Problem:** `Allowed memory size exhausted`

**Solution:** Increase PHP memory limit in `php.ini`:

```ini
memory_limit = 256M
```

Or set it programmatically:
```php
ini_set('memory_limit', '256M');
```

### File Permission Errors

**Problem:** `Failed to open stream: Permission denied`

**Solution:**
```bash
# Make file readable
chmod 644 /path/to/image.jpg

# Make directory readable
chmod 755 /path/to/directory
```

### Image Not Loading

**Problem:** Colors extracted but image path seems wrong

**Quick check:**
```php
$image = ImageFactory::createFromPath('image.jpg');
var_dump($image->getPath());  // Verify path is correct
var_dump(file_exists($image->getPath()));  // Should be true
```

---

## Next Steps

You've mastered the basics! Now explore more advanced features:

<div class="next-steps">
  <div class="next-step">
    <h3><a href="guides/installation">📦 Full Installation Guide</a></h3>
    <p>Comprehensive installation for all platforms and environments</p>
  </div>

  <div class="next-step">
    <h3><a href="concepts/color-spaces">🎯 Core Concepts</a></h3>
    <p>Deep dive into color spaces and manipulation techniques</p>
  </div>

  <div class="next-step">
    <h3><a href="examples/">💡 Real-World Examples</a></h3>
    <p>See how to use Color Palette PHP in production applications</p>
  </div>

  <div class="next-step">
    <h3><a href="api/">📖 API Reference</a></h3>
    <p>Complete API documentation with all available methods</p>
  </div>
</div>

---

## Quick Reference

### Essential Classes

| Class | Purpose | Quick Example |
|-------|---------|---------------|
| `ImageFactory` | Load images | `ImageFactory::createFromPath('image.jpg')` |
| `ColorExtractorFactory` | Create extractors | `(new ColorExtractorFactory())->make('gd')` |
| `Color` | Color manipulation | `Color::fromHex('#3498db')` |
| `ColorPalette` | Work with palettes | `$palette->getColors()` |

### Common Methods

```php
// Color Creation
Color::fromHex('#3498db')
Color::fromRgb(['r' => 52, 'g' => 152, 'b' => 219])

// Color Conversion
$color->toHex()
$color->toRgb()
$color->toHsl()

// Color Manipulation
$color->lighten(0.2)
$color->darken(0.2)
$color->saturate(0.3)
$color->rotate(180)

// Palette Methods
$palette->getColors()
$palette->toArray()
$palette->getSuggestedTextColor($background)
$palette->getSuggestedSurfaceColors()
```

### Backend Selection

```php
// Use GD (faster, good for most cases)
$extractor = $extractorFactory->make('gd');

// Use Imagick (more features, slower)
$extractor = $extractorFactory->make('imagick');
```

---

<div class="info-box">
  <strong>🎉 You're all set!</strong> You can now extract colors, generate themes, and manipulate colors with Color Palette PHP. Check out the <a href="examples/">examples</a> for more real-world use cases.
</div>

---

## Get Help

- **GitHub Issues**: [Report bugs or request features](https://github.com/parsilver/color-palette-php/issues)
- **Documentation**: [Full documentation site](https://parsilver.github.io/color-palette-php/)
- **Examples**: [Browse code examples](https://github.com/parsilver/color-palette-php/tree/main/example)
