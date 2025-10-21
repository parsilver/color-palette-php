---
layout: default
title: Recipe - Extracting Dominant Colors
description: Copy-paste solutions for extracting dominant colors from images
---

# Recipe: Extracting Dominant Colors

Learn how to extract and analyze dominant colors from images efficiently.

## Table of Contents

- [Basic Extraction](#basic-extraction)
- [Advanced Extraction Options](#advanced-extraction-options)
- [Analyzing Extracted Colors](#analyzing-extracted-colors)
- [Working with Different Image Sources](#working-with-different-image-sources)
- [Performance Tips](#performance-tips)
- [Complete Examples](#complete-examples)

---

## Basic Extraction

### Extract 5 Dominant Colors from an Image

**Input:** Image file path
**Output:** Array of Color objects

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image
$image = ImageFactory::createFromPath('photo.jpg');

// Create extractor (GD is recommended for performance)
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Extract 5 dominant colors
$palette = $extractor->extract($image, 5);

// Get colors as hex strings
$hexColors = [];
foreach ($palette->getColors() as $color) {
    $hexColors[] = $color->toHex();
}

print_r($hexColors);
```

**Expected Output:**
```
Array (
    [0] => #2c5f8d
    [1] => #8bb4d9
    [2] => #1a3a52
    [3] => #c4d9e8
    [4] => #456b8a
)
```

---

## Advanced Extraction Options

### Extract with Custom Sample Size

Process fewer pixels for better performance on large images:

```php
$palette = $extractor->extract($image, 5, [
    'sample_size' => 1000, // Process only 1000 pixels
]);
```

### Extract with Minimum Color Difference

Ensure extracted colors are sufficiently different:

```php
$palette = $extractor->extract($image, 5, [
    'min_difference' => 30, // Colors must differ by at least 30 in RGB space
]);
```

### Extract with Brightness Filtering

Exclude very dark or very light colors:

```php
$palette = $extractor->extract($image, 5, [
    'min_brightness' => 30,  // Exclude colors darker than this
    'max_brightness' => 225, // Exclude colors brighter than this
]);
```

### Complete Advanced Extraction

```php
$palette = $extractor->extract($image, 10, [
    'sample_size' => 2000,
    'min_difference' => 25,
    'min_brightness' => 20,
    'max_brightness' => 230,
    'quality' => 10, // Higher = better quality but slower (1-10)
]);

// Get only the top 5 most dominant
$topColors = array_slice($palette->getColors(), 0, 5);
```

---

## Analyzing Extracted Colors

### Get the Single Most Dominant Color

```php
$dominantColor = $palette->getDominantColor();
echo "Dominant color: " . $dominantColor->toHex(); // #2c5f8d
```

### Sort Colors by Brightness

```php
$colors = $palette->getColors();
usort($colors, function($a, $b) {
    return $b->brightness() - $a->brightness();
});

// Lightest to darkest
foreach ($colors as $color) {
    echo $color->toHex() . " (brightness: " . $color->brightness() . ")\n";
}
```

**Expected Output:**
```
#c4d9e8 (brightness: 217)
#8bb4d9 (brightness: 180)
#456b8a (brightness: 107)
#2c5f8d (brightness: 89)
#1a3a52 (brightness: 52)
```

### Group Colors by Temperature

```php
$warmColors = [];
$coolColors = [];

foreach ($palette->getColors() as $color) {
    $hsl = $color->toHsl();
    $hue = $hsl['h'];

    // Warm colors: red, orange, yellow (0-60, 330-360)
    // Cool colors: green, blue, purple (60-330)
    if (($hue >= 0 && $hue <= 60) || ($hue >= 330 && $hue <= 360)) {
        $warmColors[] = $color;
    } else {
        $coolColors[] = $color;
    }
}

echo "Warm colors: " . count($warmColors) . "\n";
echo "Cool colors: " . count($coolColors) . "\n";
```

### Find Colors by Similarity

```php
// Find colors similar to a target color
$targetColor = Color::fromHex('#2563eb');
$similarColors = [];

foreach ($palette->getColors() as $color) {
    $distance = $this->colorDistance($color, $targetColor);

    if ($distance < 50) { // Threshold for "similar"
        $similarColors[] = [
            'color' => $color,
            'distance' => $distance,
        ];
    }
}

// Helper function to calculate color distance
function colorDistance($color1, $color2): float {
    $rgb1 = $color1->toRgb();
    $rgb2 = $color2->toRgb();

    return sqrt(
        pow($rgb1['r'] - $rgb2['r'], 2) +
        pow($rgb1['g'] - $rgb2['g'], 2) +
        pow($rgb1['b'] - $rgb2['b'], 2)
    );
}
```

---

## Working with Different Image Sources

### From Local File

```php
$image = ImageFactory::createFromPath('/path/to/image.jpg');
$palette = $extractor->extract($image, 5);
```

### From URL

```php
$imageUrl = 'https://example.com/image.jpg';

// Download image first
$imageData = file_get_contents($imageUrl);
$tempFile = tempnam(sys_get_temp_dir(), 'color_');
file_put_contents($tempFile, $imageData);

// Extract colors
$image = ImageFactory::createFromPath($tempFile);
$palette = $extractor->extract($image, 5);

// Cleanup
unlink($tempFile);
```

### From Base64 String

```php
$base64Image = 'data:image/jpeg;base64,/9j/4AAQSkZJRg...';

// Extract base64 data
preg_match('/data:image\/(\w+);base64,(.*)/', $base64Image, $matches);
$imageData = base64_decode($matches[2]);

// Save to temp file
$tempFile = tempnam(sys_get_temp_dir(), 'color_');
file_put_contents($tempFile, $imageData);

// Extract colors
$image = ImageFactory::createFromPath($tempFile);
$palette = $extractor->extract($image, 5);

// Cleanup
unlink($tempFile);
```

### From Upload

```php
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadedFile = $_FILES['image']['tmp_name'];

    // Validate image
    $imageInfo = getimagesize($uploadedFile);
    if ($imageInfo !== false) {
        $image = ImageFactory::createFromPath($uploadedFile);
        $palette = $extractor->extract($image, 5);

        // Process palette...
    }
}
```

---

## Performance Tips

### Caching Extracted Colors

```php
class CachedColorExtractor
{
    private $extractor;
    private $cache;

    public function __construct($extractor, $cache)
    {
        $this->extractor = $extractor;
        $this->cache = $cache;
    }

    public function extract($imagePath, $count = 5, array $options = [])
    {
        // Create cache key from image and options
        $cacheKey = $this->getCacheKey($imagePath, $count, $options);

        // Check cache
        if ($this->cache->has($cacheKey)) {
            return $this->cache->get($cacheKey);
        }

        // Extract and cache
        $image = ImageFactory::createFromPath($imagePath);
        $palette = $this->extractor->extract($image, $count, $options);

        $this->cache->set($cacheKey, $palette, 3600); // Cache for 1 hour

        return $palette;
    }

    private function getCacheKey($imagePath, $count, $options)
    {
        return 'palette_' . md5($imagePath . $count . serialize($options));
    }
}

// Usage
$cachedExtractor = new CachedColorExtractor($extractor, $cache);
$palette = $cachedExtractor->extract('photo.jpg', 5);
```

### Batch Processing Multiple Images

```php
function extractColorsFromMultipleImages(array $imagePaths, $colorsPerImage = 5)
{
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');

    $results = [];

    foreach ($imagePaths as $imagePath) {
        try {
            $image = ImageFactory::createFromPath($imagePath);
            $palette = $extractor->extract($image, $colorsPerImage, [
                'sample_size' => 1000, // Fast extraction
            ]);

            $results[$imagePath] = [
                'success' => true,
                'colors' => array_map(fn($c) => $c->toHex(), $palette->getColors()),
                'dominant' => $palette->getDominantColor()->toHex(),
            ];
        } catch (\Exception $e) {
            $results[$imagePath] = [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    return $results;
}

// Usage
$images = ['photo1.jpg', 'photo2.jpg', 'photo3.jpg'];
$results = extractColorsFromMultipleImages($images);
```

### Memory-Efficient Processing

```php
function extractColorsMemoryEfficient($imagePath, $count = 5)
{
    // Use GD (more memory efficient than Imagick)
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');

    $image = ImageFactory::createFromPath($imagePath);

    // Extract with lower sample size
    $palette = $extractor->extract($image, $count, [
        'sample_size' => 500,
        'quality' => 5,
    ]);

    // Get colors as simple array (less memory)
    $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

    // Free memory
    unset($image, $palette, $extractor);
    gc_collect_cycles();

    return $colors;
}
```

---

## Complete Examples

### Example 1: Extract and Display Color Palette

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function generateColorPaletteHTML($imagePath, $colorCount = 5)
{
    // Extract colors
    $image = ImageFactory::createFromPath($imagePath);
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');
    $palette = $extractor->extract($image, $colorCount);

    // Generate HTML
    $html = '<div class="color-palette">';
    $html .= '<img src="' . htmlspecialchars($imagePath) . '" alt="Source Image">';
    $html .= '<div class="colors">';

    foreach ($palette->getColors() as $color) {
        $hex = $color->toHex();
        $rgb = $color->toRgb();

        $html .= '<div class="color-swatch" style="background-color: ' . $hex . '">';
        $html .= '<span class="color-info">';
        $html .= '<strong>' . $hex . '</strong><br>';
        $html .= 'RGB(' . $rgb['r'] . ', ' . $rgb['g'] . ', ' . $rgb['b'] . ')';
        $html .= '</span>';
        $html .= '</div>';
    }

    $html .= '</div></div>';

    return $html;
}

// Usage
echo generateColorPaletteHTML('photo.jpg', 6);
```

**Expected Output:**
```html
<div class="color-palette">
    <img src="photo.jpg" alt="Source Image">
    <div class="colors">
        <div class="color-swatch" style="background-color: #2c5f8d">
            <span class="color-info">
                <strong>#2c5f8d</strong><br>
                RGB(44, 95, 141)
            </span>
        </div>
        <!-- More color swatches... -->
    </div>
</div>
```

### Example 2: Extract Colors for Theme Generation

```php
function extractThemeColors($imagePath)
{
    $image = ImageFactory::createFromPath($imagePath);
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');

    // Extract with filtering for good theme colors
    $palette = $extractor->extract($image, 10, [
        'min_brightness' => 30,
        'max_brightness' => 220,
        'min_difference' => 40,
    ]);

    $colors = $palette->getColors();

    // Categorize colors
    $primary = $colors[0]; // Most dominant
    $accent = $colors[1];  // Second most dominant

    // Find a good background color (lighter)
    $background = null;
    foreach ($colors as $color) {
        if ($color->brightness() > 180) {
            $background = $color;
            break;
        }
    }

    if (!$background) {
        $background = Color::fromHex('#ffffff');
    }

    // Find a good text color
    $textColor = $background->brightness() > 128
        ? Color::fromHex('#000000')
        : Color::fromHex('#ffffff');

    return [
        'primary' => $primary->toHex(),
        'accent' => $accent->toHex(),
        'background' => $background->toHex(),
        'text' => $textColor->toHex(),
    ];
}

// Usage
$theme = extractThemeColors('brand-image.jpg');
print_r($theme);
```

**Expected Output:**
```
Array (
    [primary] => #2c5f8d
    [accent] => #8bb4d9
    [background] => #c4d9e8
    [text] => #000000
)
```

### Example 3: RESTful API Endpoint

```php
// POST /api/extract-colors
// Body: { "image_url": "https://example.com/image.jpg", "count": 5 }

function handleExtractColors($request)
{
    try {
        $imageUrl = $request->input('image_url');
        $count = $request->input('count', 5);

        // Validate count
        if ($count < 1 || $count > 20) {
            return response()->json([
                'error' => 'Count must be between 1 and 20'
            ], 400);
        }

        // Download image
        $imageData = file_get_contents($imageUrl);
        if ($imageData === false) {
            return response()->json([
                'error' => 'Failed to download image'
            ], 400);
        }

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'color_');
        file_put_contents($tempFile, $imageData);

        // Extract colors
        $image = ImageFactory::createFromPath($tempFile);
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, $count);

        // Clean up
        unlink($tempFile);

        // Format response
        $colors = [];
        foreach ($palette->getColors() as $color) {
            $rgb = $color->toRgb();
            $hsl = $color->toHsl();

            $colors[] = [
                'hex' => $color->toHex(),
                'rgb' => $rgb,
                'hsl' => $hsl,
                'brightness' => $color->brightness(),
            ];
        }

        return response()->json([
            'success' => true,
            'colors' => $colors,
            'dominant' => $palette->getDominantColor()->toHex(),
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}
```

**Expected Response:**
```json
{
  "success": true,
  "colors": [
    {
      "hex": "#2c5f8d",
      "rgb": {"r": 44, "g": 95, "b": 141},
      "hsl": {"h": 209, "s": 52, "l": 36},
      "brightness": 89
    },
    {
      "hex": "#8bb4d9",
      "rgb": {"r": 139, "g": 180, "b": 217},
      "hsl": {"h": 209, "s": 52, "l": 70},
      "brightness": 180
    }
  ],
  "dominant": "#2c5f8d"
}
```

---

## Related Recipes

- [Creating Color Schemes](creating-color-schemes) - Use extracted colors to generate harmonious schemes
- [Checking Accessibility](checking-accessibility) - Ensure extracted colors meet accessibility standards
- [Performance Optimization](performance-optimization) - Advanced optimization techniques

---

## See Also

- [ColorExtractor Reference](../reference/color-extractor)
- [ImageLoader Reference](../reference/image-loader)
- [ColorPalette Reference](../reference/color-palette)
