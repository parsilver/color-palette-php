# Core Concepts

This document explains the fundamental concepts and components of the Color Palette PHP package.

## Color Representation

### The Color Class

The `Color` class is the fundamental building block of our package. It represents a single color and provides various methods for color manipulation and conversion.

```php
use Farzai\ColorPalette\Color;

// Create a color
$color = Color::fromHex('#2196f3');
```

Colors can be represented in multiple formats:
- Hexadecimal (`#RRGGBB`)
- RGB (`rgb(r, g, b)`)
- HSL (`hsl(h, s, l)`)

### Color Properties

Each color has several properties:
- Red, Green, Blue components (0-255)
- Hue (0-360), Saturation (0-100), Lightness (0-100)
- Alpha/Opacity (0-1)

## Color Palettes

A color palette is a collection of colors that work well together. The `ColorPalette` class manages these collections.

### Creating Palettes

```php
use Farzai\ColorPalette\ColorPaletteFactory;

$factory = new ColorPaletteFactory();
$palette = $factory->createFromPath('image.jpg');
```

### Palette Operations

Palettes provide methods for:
- Getting all colors
- Finding dominant colors
- Suggesting text colors for contrast
- Generating surface colors
- Color similarity calculations

## Themes

Themes are structured color collections designed for specific use cases (e.g., web interfaces).

### Theme Structure

A theme typically includes:
- Primary color
- Secondary color
- Accent color
- Background colors
- Surface colors
- Text colors

### Theme Generation

```php
use Farzai\ColorPalette\ThemeGenerator;

$generator = new ThemeGenerator();
$theme = $generator->generate($palette);
```

## Color Extraction

Color extraction is the process of identifying key colors from an image.

### Extraction Methods

1. **GD Extractor**
   - Uses PHP's GD library
   - Faster processing
   - Lower memory usage
   - Suitable for most use cases

2. **Imagick Extractor**
   - Uses ImageMagick
   - More accurate color detection
   - Better handling of complex images
   - Higher memory usage

### Extraction Process

1. Image loading
2. Color quantization
3. Color clustering
4. Dominant color selection
5. Color refinement

```php
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ImageLoader;

$loader = new ImageLoader();
$image = $loader->load('image.jpg');

$factory = new ColorExtractorFactory();
$extractor = $factory->create('gd');
$colors = $extractor->extract($image);
```

## Color Relationships

### Color Distance

Color distance is calculated using various algorithms:
- Euclidean distance in RGB space
- CIE76 Delta E
- Weighted RGB distance

### Color Harmony

The package considers color harmony principles:
- Complementary colors
- Analogous colors
- Triadic colors
- Split-complementary colors

## Best Practices

1. **Image Processing**
   - Use GD for general purposes
   - Use Imagick for professional-grade color accuracy
   - Process images at appropriate sizes for performance

2. **Color Management**
   - Store colors in a consistent format
   - Use appropriate color spaces for different operations
   - Consider color accessibility guidelines

3. **Performance**
   - Cache extracted palettes when possible
   - Optimize image sizes before processing
   - Use appropriate color quantization levels

4. **Error Handling**
   - Always handle image loading exceptions
   - Validate color values and formats
   - Provide fallback colors when needed 