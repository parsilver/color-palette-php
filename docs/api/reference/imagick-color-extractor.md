---
layout: default
title: ImagickColorExtractor Class - Color Palette PHP API
description: Documentation for the ImagickColorExtractor class, including usage examples and features
keywords: php imagick color extractor, color extraction, image color analysis
---

# ImagickColorExtractor Class

The `ImagickColorExtractor` class provides functionality for extracting dominant colors from images using the ImageMagick extension.

## Overview

```php
namespace Farzai\ColorPalette;

class ImagickColorExtractor extends AbstractColorExtractor
{
    // ImageMagick implementation
}
```

The ImageMagick color extractor system provides:
- Efficient color sampling with configurable sample size
- Color filtering based on saturation and brightness
- Color clustering for finding dominant colors
- Fallback behavior with default grayscale palette

## extractColors Method

### extractColors()

```php
protected function extractColors(ImageInterface $image): array
```

Extracts raw colors from an image using the ImageMagick extension.

**Parameters:**
- `$image` (ImageInterface): The image to extract colors from

**Returns:**
- `array`: An array of extracted colors with their RGB values and counts

**Throws:**
- `InvalidArgumentException`: If the image is not a valid ImageMagick image

## Usage Examples

### Extracting Colors from an Image Using ImageMagick

```php
use Farzai\ColorPalette\ImagickColorExtractor;
use Farzai\ColorPalette\ImageLoaderFactory;

// Create image loader
$loader = (new ImageLoaderFactory)->create();
$image = $loader->load('path/to/image.jpg');

// Create ImageMagick color extractor
$extractor = new ImagickColorExtractor();

// Extract colors
$palette = $extractor->extract($image, 5);

// Get colors as hex values
$colors = $palette->toArray();
```

## See Also

- [ColorExtractorFactory Class](color-extractor-factory)
- [GdColorExtractor Class](gd-color-extractor)
