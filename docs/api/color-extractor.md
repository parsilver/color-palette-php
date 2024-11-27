# ColorExtractor API Reference

The Color Extractor system consists of multiple classes that work together to extract colors from images. This document covers the main components of the color extraction system.

## Class Hierarchy

```php
AbstractColorExtractor
├── GdColorExtractor
└── ImagickColorExtractor

ColorExtractorFactory
```

## ColorExtractorFactory

The factory class for creating color extractors based on the desired driver.

### Class Synopsis

```php
namespace Farzai\ColorPalette;

class ColorExtractorFactory
{
    public function create(string $driver = null): ColorExtractorInterface
    public function createForImage(ImageInterface $image): ColorExtractorInterface
}
```

### Methods

#### create()

Creates a color extractor with the specified driver.

```php
public function create(string $driver = null): ColorExtractorInterface
```

##### Parameters
- `$driver` (?string): The driver to use ('gd' or 'imagick')

##### Returns
- (ColorExtractorInterface): The color extractor instance

##### Example
```php
$factory = new ColorExtractorFactory();
$extractor = $factory->create('gd');
```

#### createForImage()

Creates a color extractor suitable for the given image.

```php
public function createForImage(ImageInterface $image): ColorExtractorInterface
```

##### Parameters
- `$image` (ImageInterface): The image to extract colors from

##### Returns
- (ColorExtractorInterface): The color extractor instance

##### Example
```php
$image = $loader->load('image.jpg');
$extractor = $factory->createForImage($image);
```

## AbstractColorExtractor

The base class for all color extractors, providing common functionality.

### Class Synopsis

```php
namespace Farzai\ColorPalette;

abstract class AbstractColorExtractor implements ColorExtractorInterface
{
    // Configuration
    public function setMaxColors(int $maxColors): self
    public function getMaxColors(): int
    public function setQuality(int $quality): self
    public function getQuality(): int
    
    // Extraction
    abstract public function extract(ImageInterface $image): array
    protected function quantizeColors(array $pixels): array
    protected function findDominantColors(array $pixels): array
}
```

### Configuration Methods

#### setMaxColors()

Sets the maximum number of colors to extract.

```php
public function setMaxColors(int $maxColors): self
```

##### Parameters
- `$maxColors` (int): Maximum number of colors (1-256)

##### Returns
- (self): The extractor instance

#### setQuality()

Sets the quality of color extraction.

```php
public function setQuality(int $quality): self
```

##### Parameters
- `$quality` (int): Quality level (1-100)

##### Returns
- (self): The extractor instance

## GdColorExtractor

Color extractor implementation using the GD library.

### Class Synopsis

```php
namespace Farzai\ColorPalette;

class GdColorExtractor extends AbstractColorExtractor
{
    public function extract(ImageInterface $image): array
}
```

### Usage Example

```php
$extractor = new GdColorExtractor();
$extractor->setMaxColors(5)
          ->setQuality(75);

$colors = $extractor->extract($image);
```

## ImagickColorExtractor

Color extractor implementation using the ImageMagick library.

### Class Synopsis

```php
namespace Farzai\ColorPalette;

class ImagickColorExtractor extends AbstractColorExtractor
{
    public function extract(ImageInterface $image): array
}
```

### Usage Example

```php
$extractor = new ImagickColorExtractor();
$extractor->setMaxColors(5)
          ->setQuality(90);

$colors = $extractor->extract($image);
```

## Best Practices

### 1. Choosing the Right Extractor

```php
// For better performance
$extractor = new GdColorExtractor();
$extractor->setMaxColors(5)
          ->setQuality(75);

// For better accuracy
$extractor = new ImagickColorExtractor();
$extractor->setMaxColors(8)
          ->setQuality(90);
```

### 2. Optimizing Extraction

```php
// Balance between speed and accuracy
$extractor = $factory->create('gd');
$extractor->setMaxColors(5)  // Fewer colors = faster extraction
          ->setQuality(50);  // Lower quality = faster processing

$colors = $extractor->extract($image);
```

### 3. Error Handling

```php
use Farzai\ColorPalette\Exceptions\ExtractionException;

try {
    $colors = $extractor->extract($image);
} catch (ExtractionException $e) {
    // Handle extraction errors
    echo "Color extraction failed: " . $e->getMessage();
}
```

### 4. Memory Management

```php
// For large images
$extractor = $factory->create('gd'); // GD uses less memory
$extractor->setQuality(25);          // Lower quality for less memory usage

try {
    $colors = $extractor->extract($image);
} finally {
    // Clean up
    $image->destroy();
}
```

## Performance Considerations

1. **Driver Selection**
   - GD: Faster, less memory intensive
   - Imagick: More accurate, higher memory usage

2. **Quality Settings**
   - Higher quality = better results but slower processing
   - Lower quality = faster processing but less accurate results

3. **Number of Colors**
   - More colors = more processing time
   - Recommended: 5-8 colors for most use cases

4. **Image Size**
   - Large images should use lower quality settings
   - Consider resizing large images before processing 