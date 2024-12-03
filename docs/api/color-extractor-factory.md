---
layout: default
title: ColorExtractorFactory Class - Color Palette PHP API
description: Documentation for the ColorExtractorFactory class, including usage examples and available backends
keywords: php color extractor factory, color extraction, gd, imagick
---

# ColorExtractorFactory Class

The `ColorExtractorFactory` class provides a convenient way to create color extractor instances based on the specified backend (GD or ImageMagick).

## Overview

```php
namespace Farzai\ColorPalette;

class ColorExtractorFactory
{
    /**
     * Create a new color extractor instance
     *
     * @throws InvalidArgumentException
     */
    public function make(string $driver = 'gd'): AbstractColorExtractor
    {
        return match ($driver) {
            'gd' => $this->createGdExtractor(),
            'imagick' => $this->createImagickExtractor(),
            default => throw new InvalidArgumentException("Unsupported driver: {$driver}"),
        };
    }

    /**
     * Create GD color extractor
     */
    private function createGdExtractor(): GdColorExtractor
    {
        if (! extension_loaded('gd')) {
            throw new InvalidArgumentException('GD extension is not available');
        }

        return new GdColorExtractor;
    }

    /**
     * Create Imagick color extractor
     */
    private function createImagickExtractor(): ImagickColorExtractor
    {
        if (! extension_loaded('imagick')) {
            throw new InvalidArgumentException('Imagick extension is not available');
        }

        return new ImagickColorExtractor;
    }
}
```

## Usage Examples

### Creating a GD-based Extractor

```php
use Farzai\ColorPalette\ColorExtractorFactory;

// Create factory
$factory = new ColorExtractorFactory();

// Create GD-based extractor
$extractor = $factory->make('gd');
```

### Creating an ImageMagick-based Extractor

```php
use Farzai\ColorPalette\ColorExtractorFactory;

// Create factory
$factory = new ColorExtractorFactory();

// Create ImageMagick-based extractor
$extractor = $factory->make('imagick');
```

## Available Backends

| Backend  | Class                  | Description                    |
|----------|------------------------|--------------------------------|
| gd       | GdColorExtractor       | Uses PHP's GD extension        |
| imagick  | ImagickColorExtractor  | Uses PHP's ImageMagick extension |

## See Also

- [GdColorExtractor Class](gd-color-extractor)
- [ImagickColorExtractor Class](imagick-color-extractor)
