# Core Concepts

This document explains the core concepts and components of the Color Palette PHP library.

## Color Representation

The library uses the `Color` class as the fundamental building block for color manipulation. Colors can be represented in multiple formats:

- RGB (Red, Green, Blue)
- HSL (Hue, Saturation, Lightness)
- Hexadecimal (#RRGGBB)

```php
use Farzai\ColorPalette\Color;

// Create from RGB values (0-255)
$color = new Color(33, 150, 243);

// Create from Hex
$color = Color::fromHex('#2196f3');

// Access color components
echo $color->getRed();   // 33
echo $color->getGreen(); // 150
echo $color->getBlue();  // 243

// Convert between formats
$rgb = $color->toRgb();  // Returns array with 'r', 'g', 'b' keys
$hex = $color->toHex();  // Returns string like '#2196f3'
$hsl = $color->toHsl();  // Returns array with 'h', 's', 'l' keys
```

## Image Processing

The library supports two image processing backends:

### GD (Default)
- Faster processing
- Lower memory usage
- Available in most PHP installations

### ImageMagick
- Better color accuracy
- Support for more image formats
- More advanced image manipulation capabilities

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Create image instance
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('image.jpg');

// Choose backend
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd');  // or 'imagick'
```

## Color Extraction

There are two ways to extract colors from images:

### 1. Using ColorExtractor (Recommended)
```php
// Extract colors directly
$colors = $extractor->extract($image);
$palette = new ColorPalette($colors);
```

### 2. Using PaletteGenerator
```php
use Farzai\ColorPalette\PaletteGenerator;

$generator = new PaletteGenerator();
$palette = $generator->generate($image);
```

## Color Palettes

A `ColorPalette` represents a collection of colors and implements `ArrayAccess` and `Countable`. It provides methods for:

```php
// Get all colors
$colors = $palette->getColors();

// Count colors
$count = count($palette);

// Access colors as array
$firstColor = $palette[0];

// Convert to array of hex values
$hexArray = $palette->toArray();

// Get suggested text color for a background
$textColor = $palette->getSuggestedTextColor($backgroundColor);

// Get surface colors for UI
$surfaceColors = $palette->getSuggestedSurfaceColors();
// Returns: surface, background, accent, surface_variant
```

## Theme Generation

Themes provide a structured way to organize colors for applications:

```php
use Farzai\ColorPalette\ThemeGenerator;

$generator = new ThemeGenerator();
$theme = $generator->generate($palette);

// Access theme colors
$primary = $theme->getPrimary();
$secondary = $theme->getSecondary();
$accent = $theme->getAccent();

// Check if theme has specific color
if ($theme->hasColor('primary')) {
    $color = $theme->getColor('primary');
}
```

## Color Relationships

The library considers several factors when working with colors:

### Contrast Ratio
- Used to ensure text readability
- Calculated according to WCAG guidelines
- Helps in selecting appropriate text colors

### Color Brightness
- Determines if a color is light or dark
- Used in surface color generation
- Helps in creating balanced color schemes

### Color Harmony
- Used in theme generation
- Helps create visually pleasing color combinations
- Considers color theory principles

## Error Handling

The library uses specific exceptions for different error cases:

```php
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Exceptions\ImageException;

try {
    $image = $imageFactory->createFromPath('image.jpg');
} catch (ImageLoadException $e) {
    // Handle image loading errors
} catch (ImageException $e) {
    // Handle other image processing errors
}

// Color validation
try {
    $color = new Color(300, 0, 0); // Will throw InvalidArgumentException
} catch (\InvalidArgumentException $e) {
    // Handle invalid color values
}
```