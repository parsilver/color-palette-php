---
layout: default
title: GdColorExtractor Class - Color Palette PHP API
description: Documentation for the GdColorExtractor class, including usage examples and features
keywords: php gd color extractor, color extraction, image color analysis
---

# GdColorExtractor Class

The `GdColorExtractor` class provides functionality for extracting dominant colors from images using the GD extension.

## Overview

```php
namespace Farzai\ColorPalette;

class GdColorExtractor extends AbstractColorExtractor
{
    // GD implementation
}
```

The GD color extractor system provides:
- Efficient color sampling with configurable sample size
- Color filtering based on saturation and brightness
- Color clustering for finding dominant colors
- Fallback behavior with default grayscale palette

## extractColors Method

### extractColors()

```php
protected function extractColors(ImageInterface $image): array
```

Extracts raw colors from an image using the GD extension.

**Parameters:**
- `$image` (ImageInterface): The image to extract colors from

**Returns:**
- `array`: An array of extracted colors with their RGB values and counts

**Throws:**
- `InvalidArgumentException`: If the image is not a valid GD image

## Usage Examples

### Extracting Colors from an Image Using GD

```php
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImageLoaderFactory;

// Create image loader
$loader = (new ImageLoaderFactory)->create();
$image = $loader->load('path/to/image.jpg');

// Create GD color extractor
$extractor = new GdColorExtractor();

// Extract colors
$palette = $extractor->extract($image, 5);

// Get colors as hex values
$colors = $palette->toArray();
```

## See Also

- [ColorExtractorFactory Class](color-extractor-factory)
- [ImagickColorExtractor Class](imagick-color-extractor)
