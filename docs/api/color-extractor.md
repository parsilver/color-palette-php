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
    // ...
}

class GdColorExtractor extends AbstractColorExtractor
{
    // ...
}

class ImagickColorExtractor extends AbstractColorExtractor
{
    // ...
}
```

The color extractor system provides:
- Multiple backend support (GD and ImageMagick)
- Configurable color extraction
- Color filtering and clustering
- Efficient color sampling

## Factory Usage

<div class="method-doc">
  <div class="method-header">
    <h3>Creating an Extractor</h3>
    <div class="method-signature">ColorExtractorFactory::make(string $driver = 'gd', array $config = []): AbstractColorExtractor</div>
  </div>
  <div class="method-content">
    <div class="method-description">
      Creates a color extractor instance using the specified backend.
    </div>
    <div class="parameters">
      <h4>Parameters</h4>
      <table>
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Description</th>
        </tr>
        <tr>
          <td>$driver</td>
          <td>string</td>
          <td>'gd' or 'imagick'</td>
        </tr>
        <tr>
          <td>$config</td>
          <td>array</td>
          <td>Optional configuration settings</td>
        </tr>
      </table>
    </div>
  </div>
</div>

## Color Extraction Methods

<div class="method-grid">
  <div class="method-doc">
    <div class="method-header">
      <h3>extract</h3>
      <div class="method-signature">public function extract(ImageInterface $image, int $count = 5): ColorPaletteInterface</div>
    </div>
    <div class="method-content">
      <div class="method-description">
        Extracts dominant colors from an image.
      </div>
      <div class="parameters">
        <h4>Parameters</h4>
        <table>
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Description</th>
          </tr>
          <tr>
            <td>$image</td>
            <td>ImageInterface</td>
            <td>Image to analyze</td>
          </tr>
          <tr>
            <td>$count</td>
            <td>int</td>
            <td>Number of colors to extract (default: 5)</td>
          </tr>
        </table>
      </div>
      <div class="return-value">
        <h4>Returns</h4>
        <p>ColorPalette containing the extracted colors</p>
      </div>
    </div>
  </div>
</div>

## Configuration Options

The color extractor can be configured with the following options:

<div class="config-options">
  <table>
    <tr>
      <th>Option</th>
      <th>Type</th>
      <th>Default</th>
      <th>Description</th>
    </tr>
    <tr>
      <td>sample_size</td>
      <td>int</td>
      <td>50</td>
      <td>Number of pixels to sample in each dimension</td>
    </tr>
    <tr>
      <td>min_saturation</td>
      <td>float</td>
      <td>0.05</td>
      <td>Minimum color saturation (0-1)</td>
    </tr>
    <tr>
      <td>min_brightness</td>
      <td>float</td>
      <td>0.05</td>
      <td>Minimum color brightness (0-1)</td>
    </tr>
  </table>
</div>

## Backend-Specific Features

### GD Backend

<div class="backend-features">
  <div class="feature">
    <h3>Advantages</h3>
    <ul>
      <li>Faster processing for basic operations</li>
      <li>Lower memory usage</li>
      <li>Available in most PHP installations</li>
    </ul>
  </div>

  <div class="feature">
    <h3>Supported Formats</h3>
    <ul>
      <li>JPEG</li>
      <li>PNG</li>
      <li>GIF</li>
      <li>BMP</li>
      <li>WEBP</li>
    </ul>
  </div>
</div>

### ImageMagick Backend

<div class="backend-features">
  <div class="feature">
    <h3>Advantages</h3>
    <ul>
      <li>More accurate color sampling</li>
      <li>Support for more image formats</li>
      <li>Advanced image processing capabilities</li>
    </ul>
  </div>

  <div class="feature">
    <h3>Additional Formats</h3>
    <ul>
      <li>TIFF</li>
      <li>PSD</li>
      <li>SVG</li>
      <li>EPS</li>
      <li>And many more</li>
    </ul>
  </div>
</div>

## Examples

### Basic Color Extraction

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Create image instance
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('path/to/image.jpg');

// Create extractor with default settings
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');

// Extract 5 dominant colors
$palette = $extractor->extract($image, 5);

// Access extracted colors
foreach ($palette->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

### Custom Configuration

```php
// Create extractor with custom settings
$extractor = $extractorFactory->make('gd', [
    'sample_size' => 100,        // More accurate but slower
    'min_saturation' => 0.1,     // Ignore very unsaturated colors
    'min_brightness' => 0.1      // Ignore very dark colors
]);

// Extract colors with custom configuration
$palette = $extractor->extract($image, 8);
```

### Using ImageMagick Backend

```php
// Create ImageMagick extractor
$extractor = $extractorFactory->make('imagick');

// Extract colors from high-resolution image
$image = $imageFactory->createFromPath('path/to/large-image.tiff');
$palette = $extractor->extract($image, 10);
```

### Error Handling

```php
use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\Exceptions\ExtractorException;

try {
    $image = $imageFactory->createFromPath('path/to/image.jpg');
    $palette = $extractor->extract($image);
} catch (ImageException $e) {
    // Handle image loading errors
    echo "Failed to load image: " . $e->getMessage();
} catch (ExtractorException $e) {
    // Handle color extraction errors
    echo "Failed to extract colors: " . $e->getMessage();
}
```

## Best Practices

1. **Backend Selection**
   - Use GD for basic web images and better performance
   - Use ImageMagick for professional graphics and advanced formats

2. **Performance Optimization**
   - Adjust sample size based on image dimensions
   - Implement caching for frequently analyzed images
   - Process images in batches when possible

3. **Color Quality**
   - Use appropriate saturation and brightness thresholds
   - Consider image type when configuring extraction
   - Validate extracted colors for your use case

4. **Error Handling**
   - Always implement proper error handling
   - Provide fallback colors when extraction fails
   - Log extraction issues for debugging

## See Also

- [Color Class](color)
- [ColorPalette Class](color-palette)
- [ImageFactory Class](image-loader)
- [Color Analysis](color-manipulation)