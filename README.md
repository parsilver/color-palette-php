# Color Palette PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/color-palette.svg?style=flat-square)](https://packagist.org/packages/farzai/color-palette)
[![Tests](https://img.shields.io/github/actions/workflow/status/parsilver/color-palette-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/parsilver/color-palette-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/color-palette.svg?style=flat-square)](https://packagist.org/packages/farzai/color-palette)


A powerful PHP library for extracting color palettes from images and generating color themes. This package supports multiple image processing backends (GD and Imagick) and provides a rich set of color manipulation features.

![Color Palette Example](example/output.png)

## Documentation

📚 **[View Full Documentation](https://parsilver.github.io/color-palette-php/)**

## Features

- 🎨 Extract dominant colors from images using advanced color quantization
- 🖼️ Support for multiple image formats (JPEG, PNG, GIF, etc.)
- 🔄 Multiple image processing backends (GD and Imagick)
- 🎯 Generate color themes with surface, background, and accent colors
- 🌈 Color manipulation with RGB, HSL, and Hex support
- 📏 Color contrast ratio calculations
- 🎭 Automatic text color suggestions for optimal readability
- 🔍 Smart surface color recommendations based on color brightness
- ✅ Deterministic color extraction - same image always produces same results

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
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPalette;

// Create an image instance
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('path/to/image.jpg');

// Create a color extractor
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd'); // or 'imagick'

// Extract colors to create a palette
$colors = $extractor->extract($image);
$palette = new ColorPalette($colors);

// Get all colors
$colors = $palette->getColors();

// Get suggested text color for a background
$backgroundColor = $colors[0];
$textColor = $palette->getSuggestedTextColor($backgroundColor);

// Get suggested surface colors
$surfaceColors = $palette->getSuggestedSurfaceColors();
// Available keys: 'surface', 'background', 'accent', 'surface_variant'
```

## Documentation

For detailed documentation, please visit our [Documentation Site](https://parsilver.github.io/color-palette-php/).

## Testing

```bash
composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/parsilver/color-palette-php/security/policy) on how to report security vulnerabilities.

## Credits

- [All Contributors](https://github.com/parsilver/color-palette-php/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
