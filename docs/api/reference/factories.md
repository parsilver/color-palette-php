---
layout: default
title: Factory Classes - Color Palette PHP
description: Documentation for the factory classes in Color Palette PHP
keywords: color palette factory, image loader factory, color extractor factory, php factory pattern
---

# Factory Classes

Color Palette PHP uses the Factory pattern to create instances of image loaders and color extractors. This provides a clean and flexible way to instantiate these objects.

## ImageFactory

The `ImageFactory` class is responsible for creating image instances from various sources.

```php
use Farzai\ColorPalette\ImageFactory;

// Create an image factory instance
$factory = new ImageFactory();

// Create image from path
$image = $factory->createFromPath('path/to/image.jpg');

// Create image from string content
$image = $factory->createFromString($imageContent);
```

## ColorExtractorFactory

The `ColorExtractorFactory` class creates color extractor instances based on the specified backend (GD or ImageMagick).

```php
use Farzai\ColorPalette\ColorExtractorFactory;

// Create a factory instance
$factory = new ColorExtractorFactory();

// Create a GD-based extractor
$gdExtractor = $factory->make('gd');

// Create an ImageMagick-based extractor
$imagickExtractor = $factory->make('imagick');
```

### Available Backends

| Backend | Class | Description |
|---------|-------|-------------|
| gd | GdColorExtractor | Uses PHP's GD extension |
| imagick | ImagickColorExtractor | Uses PHP's ImageMagick extension |

## ImageLoaderFactory

The `ImageLoaderFactory` class creates image loader instances.

```php
use Farzai\ColorPalette\ImageLoaderFactory;

// Create a factory instance
$factory = new ImageLoaderFactory();

// Create an image loader
$loader = $factory->make();
```

## Best Practices

1. **Backend Selection**
   - Use GD for basic color extraction (faster, lower memory usage)
   - Use ImageMagick for advanced image processing needs

2. **Error Handling**
   ```php
   use Farzai\ColorPalette\Exceptions\UnsupportedBackendException;
   
   try {
       $extractor = $factory->make('unsupported_backend');
   } catch (UnsupportedBackendException $e) {
       // Handle unsupported backend error
   }
   ```

## See Also

- [Color Extractor Documentation](color-extractor.md)
- [Image Loader Documentation](image-loader.md)
- [Installation Guide](../../guides/installation.md) 