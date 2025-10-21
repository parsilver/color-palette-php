---
layout: default
title: ColorExtractor Class - Color Palette PHP API
description: Documentation for the ColorExtractor class, including color extraction from images using GD and ImageMagick backends
keywords: php color extraction, image color analysis, dominant colors, color quantization
---

# ColorExtractor Class

The `ColorExtractor` classes provide functionality for extracting dominant colors from images using different image processing backends.

## Overview

```php
namespace Farzai\ColorPalette;

abstract class AbstractColorExtractor implements ColorExtractorInterface
{
    protected const SAMPLE_SIZE = 50;
    protected const MIN_SATURATION = 0.05;
    protected const MIN_BRIGHTNESS = 0.05;
}

class GdColorExtractor extends AbstractColorExtractor
{
    // GD implementation
}

class ImagickColorExtractor extends AbstractColorExtractor
{
    // ImageMagick implementation
}
```

The color extractor system provides:
- Multiple backend support (GD and ImageMagick)
- Efficient color sampling with configurable sample size
- Color filtering based on saturation and brightness
- Color clustering for finding dominant colors
- Fallback behavior with default grayscale palette

## Factory Usage

```php
use Farzai\ColorPalette\ColorExtractorFactory;

// Create factory
$factory = new ColorExtractorFactory();

// Create extractor with GD backend (default)
$extractor = $factory->make();

// Create extractor with ImageMagick backend
$extractor = $factory->make('imagick');
```

## Color Extraction

### extract()

```php
public function extract(ImageInterface $image, int $count = 5): ColorPaletteInterface
```

Extracts dominant colors from an image.

**Parameters:**
- `$image` (ImageInterface): The image to extract colors from
- `$count` (int): Number of colors to extract (default: 5)

**Returns:**
- `ColorPaletteInterface`: A color palette containing the dominant colors

**Throws:**
- `InvalidArgumentException`: If count is less than 1

### Implementation Details

#### Sampling Algorithm
The extractor uses a sampling algorithm to efficiently process large images:
- Sample size is set to 50x50 pixels (configurable via `SAMPLE_SIZE` constant)
- For GD: Samples pixels at regular intervals
- For ImageMagick: Uses image histogram for color analysis

#### Color Filtering
Colors are filtered based on:
- Minimum saturation: 0.05 (5%)
- Minimum brightness: 0.05 (5%)
- Pure black and white are excluded by default

#### Color Clustering
Similar colors are clustered together to find truly dominant colors:
- Colors are grouped by similarity in RGB space
- Each cluster is represented by its average color
- Number of clusters equals requested color count

#### Error Handling
If color extraction fails, a default grayscale palette is returned:
- White (255, 255, 255)
- Light gray (200, 200, 200)
- Medium gray (150, 150, 150)
- Dark gray (100, 100, 100)
- Very dark gray (50, 50, 50)

## Backend-Specific Features

### GD Backend

```php
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\Images\GdImage;

$extractor = new GdColorExtractor();
$image = new GdImage($gdResource);
$palette = $extractor->extract($image);
```

- Requires GD extension
- Uses direct pixel sampling
- Memory efficient for large images
- Supports all GD-compatible image formats

### ImageMagick Backend

```php
use Farzai\ColorPalette\ImagickColorExtractor;
use Farzai\ColorPalette\Images\ImagickImage;

$extractor = new ImagickColorExtractor();
$image = new ImagickImage($imagickInstance);
$palette = $extractor->extract($image);
```

- Requires ImageMagick extension
- Uses histogram analysis
- Better color accuracy
- Supports wide range of image formats

## Examples

### Basic Usage

```php
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ImageLoaderFactory;

// Create factories
$extractorFactory = new ColorExtractorFactory();
$imageFactory = new ImageLoaderFactory();

// Load image and create extractor
$image = $imageFactory->make()->load('path/to/image.jpg');
$extractor = $extractorFactory->make();

// Extract 5 dominant colors
$palette = $extractor->extract($image, 5);

// Get colors as hex values
$colors = $palette->toArray();
```

### Error Handling

```php
try {
    $palette = $extractor->extract($image, 5);
} catch (\InvalidArgumentException $e) {
    // Handle invalid count
} catch (\Throwable $e) {
    // Will return default grayscale palette
    $palette = $extractor->extract($image);
}
```

## See Also

- [Color Class](color)
- [ColorPalette Class](color-palette)
- [ImageFactory Class](image-loader)
- [Color Analysis](color-manipulation)