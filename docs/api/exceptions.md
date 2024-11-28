---
layout: default
title: Exceptions - Color Palette PHP
description: Documentation for the exception classes in Color Palette PHP
keywords: color palette exceptions, error handling, php exceptions
---

# Exceptions

Color Palette PHP provides a set of specific exceptions to help you handle errors gracefully. All exceptions are located in the `Farzai\ColorPalette\Exceptions` namespace.

## Exception Hierarchy

```
Exception
└── ColorPaletteException
    ├── ImageException
    │   ├── InvalidImageException
    │   └── UnsupportedImageTypeException
    ├── ColorException
    │   ├── InvalidColorException
    │   └── UnsupportedColorSpaceException
    └── ExtractorException
        ├── UnsupportedBackendException
        └── ExtractionFailedException
```

## Common Exceptions

### ImageException

Thrown when there are issues with image processing.

```php
use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\ImageFactory;

try {
    $imageFactory = new ImageFactory();
    $image = $imageFactory->createFromPath('non_existent.jpg');
} catch (ImageException $e) {
    echo "Image error: " . $e->getMessage();
}
```

### ColorException

Thrown when there are issues with color operations.

```php
use Farzai\ColorPalette\Exceptions\ColorException;
use Farzai\ColorPalette\Color;

try {
    $color = new Color(300, 0, 0); // Invalid RGB values
} catch (ColorException $e) {
    echo "Color error: " . $e->getMessage();
}
```

### ExtractorException

Thrown when there are issues with color extraction.

```php
use Farzai\ColorPalette\Exceptions\ExtractorException;
use Farzai\ColorPalette\ColorExtractorFactory;

try {
    $factory = new ColorExtractorFactory();
    $extractor = $factory->make('unsupported');
} catch (ExtractorException $e) {
    echo "Extractor error: " . $e->getMessage();
}
```

## Best Practices

1. **Specific Exception Handling**
   ```php
   use Farzai\ColorPalette\Exceptions\InvalidImageException;
   use Farzai\ColorPalette\Exceptions\UnsupportedImageTypeException;
   
   try {
       $image = $imageFactory->createFromPath('image.jpg');
   } catch (InvalidImageException $e) {
       // Handle invalid image
   } catch (UnsupportedImageTypeException $e) {
       // Handle unsupported image type
   }
   ```

2. **Logging and Debugging**
   ```php
   try {
       $palette = $extractor->extract($image);
   } catch (ExtractorException $e) {
       // Log the error
       error_log("Color extraction failed: " . $e->getMessage());
       // Include debug information
       error_log("Stack trace: " . $e->getTraceAsString());
       // Rethrow or handle gracefully
       throw $e;
   }
   ```

3. **Graceful Fallbacks**
   ```php
   try {
       $color = new Color(255, 0, 0);
       $complementary = $color->getComplementary();
   } catch (ColorException $e) {
       // Fallback to a default color
       $complementary = new Color(0, 255, 255);
   }
   ```

## See Also

- [Getting Started Guide](../getting-started.html)
- [Color Manipulation](color-manipulation.html)
- [Image Loading](image-loader.html) 