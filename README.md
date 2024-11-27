# Color Palette PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/color-palette.svg?style=flat-square)](https://packagist.org/packages/farzai/color-palette)
[![Tests](https://img.shields.io/github/actions/workflow/status/farzai/color-palette/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/farzai/color-palette/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/color-palette.svg?style=flat-square)](https://packagist.org/packages/farzai/color-palette)


A powerful PHP library for extracting color palettes from images and generating color themes. This package supports multiple image processing backends (GD and Imagick) and provides a rich set of color manipulation features.

![Color Palette Example](example/output.png)

## Features

- 🎨 Extract dominant colors from images
- 🖼️ Support for multiple image formats (JPEG, PNG, GIF, etc.)
- 🔄 Multiple image processing backends (GD and Imagick)
- 🎯 Generate color themes and palettes
- 🌈 Color manipulation and transformation
- 📏 Color distance and similarity calculations
- 🎭 Automatic text color suggestions for contrast
- 🔍 Surface color recommendations

## Requirements

- PHP 8.1 or higher
- GD extension or ImageMagick extension
- Composer

## Installation

You can install the package via composer:

```bash
composer require farzai/color-palette
```

## Basic Usage

```php
use Farzai\ColorPalette\ColorPaletteFactory;

// Create a new palette from an image
$factory = new ColorPaletteFactory();
$palette = $factory->createFromPath('path/to/image.jpg');

// Get all colors
$colors = $palette->getColors();

// Get suggested text color for a background
$textColor = $palette->getSuggestedTextColor($backgroundColor);

// Get suggested surface colors
$surfaceColors = $palette->getSuggestedSurfaceColors();
```

## Advanced Usage

### Custom Color Extraction

```php
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ImageLoader;

// Create an image loader
$imageLoader = new ImageLoader();
$image = $imageLoader->load('path/to/image.jpg');

// Create a color extractor (GD or Imagick)
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd'); // or 'imagick'

// Extract colors
$colors = $extractor->extract($image);
```

### Theme Generation

```php
use Farzai\ColorPalette\ThemeGenerator;

$generator = new ThemeGenerator();
$theme = $generator->generate($palette);

// Access theme colors
$primary = $theme->getPrimary();
$secondary = $theme->getSecondary();
$accent = $theme->getAccent();
```

## Documentation

For detailed documentation, please visit our [Wiki](docs/README.md).

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
