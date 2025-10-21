---
layout: default
title: Color Extraction
parent: Guides
nav_order: 3
description: Complete guide to extracting dominant colors from images using Color Palette PHP
keywords: color extraction, image analysis, dominant colors, palette generation, gd, imagick
---

# Color Extraction Guide

Learn how to extract dominant colors from images using Color Palette PHP's powerful color quantization algorithms. This guide covers everything from basic extraction to advanced optimization techniques.

<div class="quick-links">
  <a href="#basics">Extraction Basics</a> •
  <a href="#backends">Backend Selection</a> •
  <a href="#image-loading">Image Loading</a> •
  <a href="#advanced-extraction">Advanced Extraction</a> •
  <a href="#optimization">Optimization</a>
</div>

## Extraction Basics

### Simple Color Extraction

The most straightforward way to extract colors from an image:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load the image
$image = ImageFactory::createFromPath('path/to/image.jpg');

// Create an extractor
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd'); // or 'imagick'

// Extract 5 dominant colors
$palette = $extractor->extract($image, 5);

// Display colors
foreach ($palette->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected output:**
```
#3498db
#2ecc71
#e74c3c
#f1c40f
#9b59b6
```

> **Note:** Color extraction is deterministic - the same image will always produce the same colors in the same order.

### Understanding the Process

Color extraction involves several steps:

1. **Image Loading** - Read the image file into memory
2. **Color Sampling** - Sample pixels from the image
3. **Color Quantization** - Reduce colors to dominant palette
4. **Sorting** - Order colors by dominance/brightness
5. **Palette Creation** - Return a ColorPalette object

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('photo.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Extract more colors for better representation
$palette = $extractor->extract($image, 10);

echo "Extracted " . count($palette) . " colors\n";
echo "Primary color: " . $palette[0]->toHex() . "\n";
echo "Secondary color: " . $palette[1]->toHex() . "\n";
```

## Backend Selection

Color Palette PHP supports two image processing backends, each with different strengths.

### GD Backend

The GD backend is included with most PHP installations and works well for most use cases.

**Advantages:**
- Widely available (included with PHP by default)
- Fast for small to medium images
- Lower memory usage
- Good for web applications

**Best For:**
- Web applications
- Standard image formats (JPG, PNG, GIF)
- Images under 2MB
- When ImageMagick is not available

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Using GD backend
$image = ImageFactory::createFromPath('image.jpg', 'gd');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5);
```

### ImageMagick Backend

ImageMagick provides more advanced image processing capabilities.

**Advantages:**
- Better performance with large images
- More image format support
- Advanced color quantization
- Better color accuracy

**Best For:**
- Large images (>2MB)
- Professional photography
- Unusual image formats
- When color accuracy is critical

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Using Imagick backend
$image = ImageFactory::createFromPath('large-photo.jpg', 'imagick');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('imagick');
$palette = $extractor->extract($image, 5);
```

### Choosing the Right Backend

Use this decision tree to select the appropriate backend:

```php
<?php

use Farzai\ColorPalette\ColorExtractorFactory;

function chooseBackend(string $imagePath): string {
    $fileSize = filesize($imagePath);

    // For large images, prefer Imagick if available
    if ($fileSize > 2 * 1024 * 1024 && extension_loaded('imagick')) {
        return 'imagick';
    }

    // For standard images, GD is perfect
    if (extension_loaded('gd')) {
        return 'gd';
    }

    // Fallback to whatever is available
    return extension_loaded('imagick') ? 'imagick' : 'gd';
}

// Usage
$backend = chooseBackend('photo.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make($backend);
```

## Image Loading

### Loading from File Path

The most common way to load images:

```php
<?php

use Farzai\ColorPalette\ImageFactory;

// Load with default backend (GD)
$image = ImageFactory::createFromPath('photo.jpg');

// Load with specific backend
$imageGd = ImageFactory::createFromPath('photo.jpg', 'gd');
$imageImagick = ImageFactory::createFromPath('photo.jpg', 'imagick');

// Load different formats
$jpeg = ImageFactory::createFromPath('photo.jpg');
$png = ImageFactory::createFromPath('logo.png');
$gif = ImageFactory::createFromPath('animation.gif');
```

### Loading from URL

Extract colors from remote images:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Download image to temporary file
$imageUrl = 'https://example.com/image.jpg';
$tempFile = sys_get_temp_dir() . '/' . uniqid() . '.jpg';

file_put_contents($tempFile, file_get_contents($imageUrl));

// Extract colors
$image = ImageFactory::createFromPath($tempFile);
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5);

// Clean up
unlink($tempFile);

// Display results
foreach ($palette->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

### Loading from Upload

Extract colors from uploaded images:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// In your upload handler
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $uploadedFile = $_FILES['image']['tmp_name'];

    try {
        // Load uploaded image
        $image = ImageFactory::createFromPath($uploadedFile);

        // Extract colors
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, 5);

        // Return as JSON
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'colors' => $palette->toArray()
        ]);

    } catch (\Exception $e) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}
```

### Error Handling

Always handle potential image loading errors:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\Exceptions\ImageLoadException;

try {
    $image = ImageFactory::createFromPath('image.jpg');

} catch (ImageLoadException $e) {
    // Image file couldn't be loaded
    echo "Failed to load image: " . $e->getMessage();

} catch (ImageException $e) {
    // General image processing error
    echo "Image error: " . $e->getMessage();

} catch (\Exception $e) {
    // Other errors (file not found, permissions, etc.)
    echo "Error: " . $e->getMessage();
}
```

## Advanced Extraction

### Extracting Different Numbers of Colors

The number of colors you extract affects the results:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('photo.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Few colors - only most dominant
$fewColors = $extractor->extract($image, 3);
echo "3 colors: " . implode(', ', $fewColors->toArray()) . "\n";

// Balanced - good for most use cases
$balanced = $extractor->extract($image, 5);
echo "5 colors: " . implode(', ', $balanced->toArray()) . "\n";

// Many colors - more variety
$manyColors = $extractor->extract($image, 10);
echo "10 colors: " . implode(', ', $manyColors->toArray()) . "\n";
```

**Guidelines:**
- **3 colors**: Brand color extraction, minimal palettes
- **5 colors**: General purpose, UI themes
- **8-10 colors**: Rich themes, detailed analysis
- **15+ colors**: Color analysis, gradients

### Working with Extracted Palettes

Manipulate and analyze extracted color palettes:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('landscape.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 8);

// Get the most dominant color
$primaryColor = $palette[0];
echo "Primary color: " . $primaryColor->toHex() . "\n";

// Get surface colors for UI design
$surfaces = $palette->getSuggestedSurfaceColors();
echo "Surface: " . $surfaces['surface']->toHex() . "\n";
echo "Background: " . $surfaces['background']->toHex() . "\n";
echo "Accent: " . $surfaces['accent']->toHex() . "\n";

// Find colors suitable for text backgrounds
foreach ($palette->getColors() as $color) {
    $textColor = $palette->getSuggestedTextColor($color);
    echo "BG: " . $color->toHex() . " → Text: " . $textColor->toHex() . "\n";
}
```

### Filtering Extracted Colors

Filter colors based on specific criteria:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

$image = ImageFactory::createFromPath('photo.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 15);

// Filter only light colors
$lightColors = array_filter($palette->getColors(), function($color) {
    return $color->isLight();
});

echo "Light colors: " . count($lightColors) . "\n";
foreach ($lightColors as $color) {
    echo $color->toHex() . " (brightness: " . $color->getBrightness() . ")\n";
}

// Filter only dark colors
$darkColors = array_filter($palette->getColors(), function($color) {
    return $color->isDark();
});

echo "\nDark colors: " . count($darkColors) . "\n";

// Filter by saturation
$vibrантColors = array_filter($palette->getColors(), function($color) {
    $hsl = $color->toHsl();
    return $hsl['s'] > 50; // More than 50% saturation
});

echo "\nVibrant colors: " . count($vibrantColors) . "\n";
```

### Color Grouping

Group similar colors together:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function groupColorsByHue(array $colors): array {
    $groups = [
        'red' => [],
        'orange' => [],
        'yellow' => [],
        'green' => [],
        'blue' => [],
        'purple' => [],
    ];

    foreach ($colors as $color) {
        $hsl = $color->toHsl();
        $hue = $hsl['h'];

        if ($hue < 30 || $hue >= 330) {
            $groups['red'][] = $color;
        } elseif ($hue < 60) {
            $groups['orange'][] = $color;
        } elseif ($hue < 90) {
            $groups['yellow'][] = $color;
        } elseif ($hue < 150) {
            $groups['green'][] = $color;
        } elseif ($hue < 270) {
            $groups['blue'][] = $color;
        } else {
            $groups['purple'][] = $color;
        }
    }

    return array_filter($groups); // Remove empty groups
}

// Usage
$image = ImageFactory::createFromPath('colorful-image.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 15);

$grouped = groupColorsByHue($palette->getColors());

foreach ($grouped as $hueName => $colors) {
    echo ucfirst($hueName) . " colors: " . count($colors) . "\n";
    foreach ($colors as $color) {
        echo "  " . $color->toHex() . "\n";
    }
}
```

## Optimization

### Performance Tips

Optimize color extraction for better performance:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Tip 1: Extract fewer colors for faster processing
$startTime = microtime(true);
$image = ImageFactory::createFromPath('large-image.jpg');
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5); // Not 20
$duration = microtime(true) - $startTime;
echo "Extraction took: " . round($duration, 3) . " seconds\n";

// Tip 2: Cache extracted palettes
function getCachedPalette(string $imagePath, int $colorCount = 5): array {
    $cacheKey = md5($imagePath . $colorCount);
    $cacheFile = sys_get_temp_dir() . '/palette_' . $cacheKey . '.json';

    // Check cache
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // Extract colors
    $image = ImageFactory::createFromPath($imagePath);
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');
    $palette = $extractor->extract($image, $colorCount);
    $colors = $palette->toArray();

    // Save to cache
    file_put_contents($cacheFile, json_encode($colors));

    return $colors;
}

// Usage
$colors = getCachedPalette('photo.jpg', 5);
print_r($colors);
```

### Memory Management

Handle large images efficiently:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function extractColorsFromLargeImage(string $imagePath): array {
    // Check file size
    $fileSize = filesize($imagePath);
    $fileSizeMB = $fileSize / 1024 / 1024;

    if ($fileSizeMB > 5) {
        // Create thumbnail for very large images
        $image = ImageFactory::createFromPath($imagePath);
        // Note: Image resizing would need to be implemented
        // This is a conceptual example
    }

    // Increase memory limit temporarily
    $oldLimit = ini_get('memory_limit');
    ini_set('memory_limit', '512M');

    try {
        $image = ImageFactory::createFromPath($imagePath);
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, 5);
        $colors = $palette->toArray();

        // Restore memory limit
        ini_set('memory_limit', $oldLimit);

        return $colors;

    } catch (\Exception $e) {
        ini_set('memory_limit', $oldLimit);
        throw $e;
    }
}

// Usage
try {
    $colors = extractColorsFromLargeImage('huge-photo.jpg');
    print_r($colors);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### Batch Processing

Extract colors from multiple images efficiently:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function batchExtractColors(array $imagePaths, int $colorCount = 5): array {
    $results = [];
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');

    foreach ($imagePaths as $imagePath) {
        try {
            $image = ImageFactory::createFromPath($imagePath);
            $palette = $extractor->extract($image, $colorCount);

            $results[$imagePath] = [
                'success' => true,
                'colors' => $palette->toArray(),
                'count' => count($palette)
            ];

        } catch (\Exception $e) {
            $results[$imagePath] = [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    return $results;
}

// Usage
$images = [
    'photo1.jpg',
    'photo2.jpg',
    'photo3.jpg',
];

$results = batchExtractColors($images);

foreach ($results as $image => $result) {
    echo basename($image) . ": ";
    if ($result['success']) {
        echo implode(', ', $result['colors']) . "\n";
    } else {
        echo "Error - " . $result['error'] . "\n";
    }
}
```

## Real-World Examples

### Example 1: Product Image Analyzer

Extract colors from product images for e-commerce:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class ProductColorAnalyzer {
    private ColorExtractorFactory $extractorFactory;

    public function __construct() {
        $this->extractorFactory = new ColorExtractorFactory();
    }

    public function analyzeProduct(string $imagePath): array {
        $image = ImageFactory::createFromPath($imagePath);
        $extractor = $this->extractorFactory->make('gd');
        $palette = $extractor->extract($image, 8);

        // Get dominant color
        $primaryColor = $palette[0];

        // Categorize by color family
        $hsl = $primaryColor->toHsl();
        $colorFamily = $this->getColorFamily($hsl['h']);

        return [
            'primary_color' => $primaryColor->toHex(),
            'color_family' => $colorFamily,
            'palette' => $palette->toArray(),
            'is_light' => $primaryColor->isLight(),
            'brightness' => $primaryColor->getBrightness(),
        ];
    }

    private function getColorFamily(float $hue): string {
        if ($hue < 30 || $hue >= 330) return 'red';
        if ($hue < 60) return 'orange';
        if ($hue < 90) return 'yellow';
        if ($hue < 150) return 'green';
        if ($hue < 270) return 'blue';
        return 'purple';
    }
}

// Usage
$analyzer = new ProductColorAnalyzer();
$productInfo = $analyzer->analyzeProduct('product-shirt.jpg');

echo "Product Color Analysis:\n";
echo "Primary Color: {$productInfo['primary_color']}\n";
echo "Color Family: {$productInfo['color_family']}\n";
echo "Brightness: {$productInfo['brightness']}\n";
```

### Example 2: Website Theme Generator

Generate website themes from hero images:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPalette;

class WebsiteThemeGenerator {
    public function generateTheme(string $heroImagePath): array {
        $image = ImageFactory::createFromPath($heroImagePath);
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, 10);

        // Get surface colors
        $surfaces = $palette->getSuggestedSurfaceColors();

        // Generate theme
        return [
            'primary' => $palette[0]->toHex(),
            'secondary' => $palette[1]->toHex(),
            'accent' => $surfaces['accent']->toHex(),
            'background' => $surfaces['background']->toHex(),
            'surface' => $surfaces['surface']->toHex(),
            'text_on_primary' => $palette->getSuggestedTextColor($palette[0])->toHex(),
            'text_on_background' => $palette->getSuggestedTextColor($surfaces['background'])->toHex(),
        ];
    }

    public function generateCss(array $theme): string {
        $css = ":root {\n";
        foreach ($theme as $name => $color) {
            $css .= "  --{$name}: {$color};\n";
        }
        $css .= "}\n";
        return $css;
    }
}

// Usage
$generator = new WebsiteThemeGenerator();
$theme = $generator->generateTheme('hero-image.jpg');
echo $generator->generateCss($theme);
```

**Expected output:**
```css
:root {
  --primary: #3498db;
  --secondary: #2ecc71;
  --accent: #e74c3c;
  --background: #ecf0f1;
  --surface: #ffffff;
  --text_on_primary: #ffffff;
  --text_on_background: #000000;
}
```

## Next Steps

Continue learning about color manipulation:

- **[Color Manipulation Guide](color-manipulation)** - Transform extracted colors
- **[Theme Generation Guide](theme-generation)** - Create color schemes
- **[Advanced Techniques](advanced-techniques)** - Optimize and extend
- **[API Reference](../api/)** - Detailed API documentation

## Quick Reference

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image
$image = ImageFactory::createFromPath('image.jpg');
$image = ImageFactory::createFromPath('image.jpg', 'imagick');

// Create extractor
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$extractor = $extractorFactory->make('imagick');

// Extract colors
$palette = $extractor->extract($image, 5);

// Access colors
$primaryColor = $palette[0];
$allColors = $palette->getColors();
$hexArray = $palette->toArray();
$surfaces = $palette->getSuggestedSurfaceColors();
$textColor = $palette->getSuggestedTextColor($bgColor);
```
