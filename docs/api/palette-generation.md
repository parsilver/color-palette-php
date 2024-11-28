# Palette Generation

The library provides two main ways to generate color palettes:

1. Color extraction from images using `ColorExtractor`
2. Color scheme generation using `PaletteGenerator`

## PaletteGenerator Class

The `PaletteGenerator` class creates color schemes based on color theory principles.

```php
namespace Farzai\ColorPalette;

class PaletteGenerator
{
    public function __construct(ColorInterface $baseColor);
    public function monochromatic(int $count = 5): ColorPalette;
    public function complementary(): ColorPalette;
    public function analogous(): ColorPalette;
    public function triadic(): ColorPalette;
}
```

### Constructor

```php
public function __construct(ColorInterface $baseColor)
```

Creates a new palette generator with a base color.

- **Parameters:**
  - `$baseColor` (ColorInterface) - The base color to generate schemes from

### Color Scheme Methods

#### `monochromatic(int $count = 5): ColorPalette`

Generates a monochromatic color scheme by varying the lightness of the base color.

```php
$generator = new PaletteGenerator($color);
$palette = $generator->monochromatic(5);
// Returns 5 variations of the base color
```

#### `complementary(): ColorPalette`

Creates a complementary color scheme using colors opposite on the color wheel.

```php
$palette = $generator->complementary();
// Returns base color and its complement (180° rotation)
```

#### `analogous(): ColorPalette`

Generates an analogous color scheme using adjacent colors on the color wheel.

```php
$palette = $generator->analogous();
// Returns base color and colors at -30° and +30°
```

#### `triadic(): ColorPalette`

Creates a triadic color scheme using evenly spaced colors on the color wheel.

```php
$palette = $generator->triadic();
// Returns base color and colors at 120° intervals
```

## Examples

### Basic Usage

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

// Create a base color
$baseColor = new Color(33, 150, 243); // Blue

// Initialize generator
$generator = new PaletteGenerator($baseColor);

// Generate different schemes
$mono = $generator->monochromatic();
$comp = $generator->complementary();
$analog = $generator->analogous();
$triad = $generator->triadic();
```

### Custom Monochromatic Scheme

```php
// Generate 3 monochromatic variations
$palette = $generator->monochromatic(3);
foreach ($palette->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

### Working with Generated Palettes

```php
// Get complementary scheme
$palette = $generator->complementary();

// Convert to array of hex values
$hexColors = $palette->toArray();

// Access individual colors
$baseColor = $palette[0];
$complement = $palette[1];
```

## Integration with Color Extraction

You can combine palette generation with color extraction:

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Extract dominant color from image
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('image.jpg');

$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd');

$colors = $extractor->extract($image);
$dominantColor = $colors[0];

// Generate schemes from dominant color
$generator = new PaletteGenerator($dominantColor);
$scheme = $generator->analogous();
``` 