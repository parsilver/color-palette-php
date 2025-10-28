# Color Palette PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/farzai/color-palette.svg?style=flat-square)](https://packagist.org/packages/farzai/color-palette)
[![Tests](https://img.shields.io/github/actions/workflow/status/parsilver/color-palette-php/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/parsilver/color-palette-php/actions/workflows/run-tests.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/farzai/color-palette.svg?style=flat-square)](https://packagist.org/packages/farzai/color-palette)

A powerful PHP library for extracting color palettes from images and generating color themes. This package supports multiple image processing backends (GD and Imagick) and provides a rich set of color manipulation features.

![Color Palette Example](example/output.png)

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Quick Start](#quick-start)
- [Core Concepts](#core-concepts)
- [Common Use Cases](#common-use-cases)
  - [Generate Website Theme from Logo](#1-generate-website-theme-from-logo)
  - [Create Accessible Color Scheme](#2-create-accessible-color-scheme-wcag-compliant)
  - [Extract Colors to CSS Variables](#3-extract-colors-and-generate-css-variables)
  - [Find Complementary Colors](#4-find-complementary-colors-for-design)
- [API Reference](#api-reference)
  - [Working with Images](#working-with-images)
  - [Extracting Color Palettes](#extracting-color-palettes)
  - [Color Format Conversions](#color-format-conversions)
  - [Color Manipulation](#color-manipulation)
  - [Color Analysis](#color-analysis)
  - [Theme Generation](#theme-generation)
- [Troubleshooting & FAQ](#troubleshooting--faq)
- [Testing](#testing)
- [Contributing](#contributing)
- [License](#license)

## Features

- Extract dominant colors from images using advanced color quantization
- Support for multiple image formats (JPEG, PNG, GIF, etc.)
- Multiple image processing backends (GD and Imagick)
- Generate color themes with surface, background, and accent colors
- Color manipulation with RGB, HSL, HSV, CMYK, and LAB support
- Color contrast ratio calculations (WCAG compliance)
- Automatic text color suggestions for optimal readability
- Smart surface color recommendations based on color brightness
- Deterministic color extraction - same image always produces same results
- Immutable color objects - safe and predictable
- Memory efficient with support for large images

## Requirements

- PHP 8.1 or higher
- GD extension **OR** ImageMagick extension (at least one is required)
- Composer

## Installation

Install the package via composer:

```bash
composer require farzai/color-palette
```

## Quick Start

### Simple Approach (Recommended)

Extract colors in one line:

```php
use Farzai\ColorPalette\ColorPalette;

$palette = ColorPalette::fromImage('path/to/image.jpg', 5);
$colors = $palette->toArray();
// Result: ['#ff5733', '#33ff57', '#5733ff', '#f4a460', '#8b4513']
```

---

### Builder Pattern (Flexible)

Chain multiple operations with the builder:

```php
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Color;

// Extract from image
$palette = ColorPalette::builder()
    ->fromImage('path/to/image.jpg')
    ->withCount(8)
    ->build();

// Generate from a base color
$palette = ColorPalette::builder()
    ->withBaseColor(Color::fromHex('#3498db'))
    ->withScheme('monochromatic', ['count' => 7])
    ->build();
```

---

### Advanced (Full Control)

Use individual components for dependency injection and custom configuration:

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Static methods
$image = ImageFactory::fromPath('path/to/image.jpg');
$extractor = ColorExtractorFactory::gd();
$palette = $extractor->extract($image, 5);

// Instance methods with dependencies
$extractor = (new ColorExtractorFactory($logger))->make('gd');
$palette = $extractor->extract($image, 5);
```

## Core Concepts

### ImageFactory
Load images from files for color extraction.

```php
// Simple static method
$image = ImageFactory::fromPath('photo.jpg');

// Specify driver (gd or imagick)
$image = ImageFactory::fromPath('photo.jpg', 'imagick');
```

### ColorExtractor
Extract dominant colors using color quantization. Two drivers available:
- **GD** - Built into most PHP installations
- **Imagick** - More accurate, better for complex images

```php
// Simple static methods
$extractor = ColorExtractorFactory::gd();
$extractor = ColorExtractorFactory::imagick();
```

### ColorPalette
A collection of extracted colors with array-like access.

```php
$palette = ColorPalette::fromImage('photo.jpg', 5);

// Access colors
$colors = $palette->toArray();     // Returns ['#ff5733', ...]
$firstColor = $palette[0];         // Array access
$count = count($palette);          // Countable
```

### Color Objects
Immutable objects supporting multiple color spaces.

```php
$color = Color::fromHex('#ff5733');

// Manipulation returns NEW color objects
$lighter = $color->lighten(0.2);
$darker = $color->darken(0.3);
```

**Note:** Color objects are immutable - methods always return new instances.

## Common Use Cases

### 1. Generate Website Theme from Logo

Extract colors from your logo and create a complete theme:

```php
use Farzai\ColorPalette\ColorPalette;

// Extract colors from logo
$palette = ColorPalette::fromImage('assets/logo.png', 8);

// Get suggested theme colors
$theme = $palette->getSuggestedSurfaceColors();

echo "Primary: " . $theme['surface']->toHex();      // #f5f5f5
echo "Background: " . $theme['background']->toHex(); // #e8e8e8
echo "Accent: " . $theme['accent']->toHex();         // #ff5733

// Get appropriate text colors
$textOnPrimary = $palette->getSuggestedTextColor($theme['surface']);
$textOnAccent = $palette->getSuggestedTextColor($theme['accent']);
```

### 2. Create Accessible Color Scheme (WCAG Compliant)

Ensure your color combinations meet accessibility standards:

```php
use Farzai\ColorPalette\Color;

$background = Color::fromHex('#ffffff');
$primary = Color::fromHex('#1976d2');

// Check contrast ratio
$ratio = $background->getContrastRatio($primary);
echo "Contrast Ratio: {$ratio}:1\n";

// Validate WCAG compliance
if ($ratio >= 7.0) {
    echo "PASS: WCAG AAA - Normal Text\n";
} elseif ($ratio >= 4.5) {
    echo "PASS: WCAG AA - Normal Text\n";
} elseif ($ratio >= 3.0) {
    echo "PASS: WCAG AA - Large Text\n";
} else {
    echo "FAIL: Does not meet WCAG standards\n";

    // Automatically adjust for compliance
    $adjustedPrimary = $primary->darken(0.2);
    $newRatio = $background->getContrastRatio($adjustedPrimary);
    echo "Adjusted Color: " . $adjustedPrimary->toHex() . "\n";
    echo "New Ratio: {$newRatio}:1\n";
}
```

### 3. Generate CSS Variables from Image

Create CSS custom properties from image colors:

```php
use Farzai\ColorPalette\ColorPalette;

$palette = ColorPalette::fromImage('hero-image.jpg', 6);

echo ":root {\n";
foreach ($palette->getColors() as $index => $color) {
    $hex = $color->toHex();
    $rgb = $color->toRgb();

    echo "  --color-{$index}: {$hex};\n";
    echo "  --color-{$index}-rgb: {$rgb['r']}, {$rgb['g']}, {$rgb['b']};\n";
}
echo "}\n";

// Output:
// :root {
//   --color-0: #ff5733;
//   --color-0-rgb: 255, 87, 51;
//   --color-1: #33ff57;
//   --color-1-rgb: 51, 255, 87;
//   ...
// }
```

### 4. Generate Color Schemes

Create color harmonies using built-in schemes:

```php
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Color;

$brandColor = Color::fromHex('#3498db');

// Automatic scheme generation
$complementary = ColorPalette::fromColor($brandColor, 'complementary');
$triadic = ColorPalette::fromColor($brandColor, 'triadic');
$monochromatic = ColorPalette::fromColor($brandColor, 'monochromatic', ['count' => 7]);

// Manual color manipulation
$lighter = $brandColor->lighten(0.2);
$darker = $brandColor->darken(0.2);
$rotated = $brandColor->rotate(180);  // Complementary
```

## API Reference

### Working with Images

#### Simple: ColorPalette::fromImage()

```php
$palette = ColorPalette::fromImage('photo.jpg', 5);
```

**Parameters:**
- `$path` (string) - Path to image file
- `$count` (int) - Number of colors to extract (default: 5)
- `$driver` (string) - 'gd' or 'imagick' (default: 'gd')

**Returns:** `ColorPalette`

#### Advanced: ImageFactory

```php
// Static method
$image = ImageFactory::fromPath('photo.jpg', 'gd');

// Instance method (for dependency injection)
$factory = new ImageFactory($extensionChecker);
$image = $factory->createFromPath('photo.jpg');
```

### Extracting Color Palettes

#### Simple: ColorPalette::fromImage()

```php
$palette = ColorPalette::fromImage('photo.jpg', 5);
```

#### Advanced: Using ColorExtractor

```php
// Static methods
$extractor = ColorExtractorFactory::gd();
$extractor = ColorExtractorFactory::imagick();

// Extract colors
$palette = $extractor->extract($image, 5);
```

### Color Format Conversions

The `Color` class supports multiple color space conversions:

#### Creating Colors

```php
// From different formats
$color = Color::fromHex('#ff5733');                          // Hex string (with or without #)
$color = Color::fromRgb(['r' => 255, 'g' => 87, 'b' => 51]); // RGB array (or numeric: [255, 87, 51])
$color = Color::fromHsl(9, 100, 60);                         // HSL values (h: 0-360, s/l: 0-100)
$color = Color::fromHsv(9, 80, 100);                         // HSV values (h: 0-360, s/v: 0-100)
$color = Color::fromCmyk(0, 66, 80, 0);                      // CMYK values (0-100)
$color = Color::fromLab(62, 52, 51);                         // LAB values (l: 0-100, a/b: -128 to 127)
```

#### Converting Colors

```php
$hex = $color->toHex();    // Returns: '#ff5733'
$rgb = $color->toRgb();    // Returns: ['r' => 255, 'g' => 87, 'b' => 51]
$hsl = $color->toHsl();    // Returns: ['h' => 9, 's' => 100, 'l' => 60]
$hsv = $color->toHsv();    // Returns: ['h' => 9, 's' => 80, 'v' => 100]
$cmyk = $color->toCmyk();  // Returns: ['c' => 0, 'm' => 66, 'y' => 80, 'k' => 0]
$lab = $color->toLab();    // Returns: ['l' => 62, 'a' => 52, 'b' => 51]
```

### Color Manipulation

All manipulation methods return **new** Color instances (immutable):

```php
$color = Color::fromHex('#3498db');

// Lighten and darken
$lighter = $color->lighten(0.2);      // Lighten by 20% (0.0 to 1.0)
$darker = $color->darken(0.2);        // Darken by 20% (0.0 to 1.0)

// Adjust saturation
$saturated = $color->saturate(0.3);   // Increase saturation by 30%
$desaturated = $color->desaturate(0.3); // Decrease saturation by 30%

// Rotate hue (color wheel)
$rotated = $color->rotate(180);       // Rotate hue by 180 degrees (0-360)

// Set specific lightness
$withLightness = $color->withLightness(0.5); // Set lightness to 50% (0.0 to 1.0)
```

### Color Analysis

```php
$color = Color::fromHex('#3498db');

// Brightness (0-255)
$brightness = $color->getBrightness();  // Returns: float (0.0 to 255.0)
$isLight = $color->isLight();           // true if brightness > 128
$isDark = $color->isDark();             // true if brightness <= 128

// Luminance (0.0 to 1.0) - WCAG formula
$luminance = $color->getLuminance();    // Returns: float (0.0 to 1.0)

// Contrast ratio (for accessibility)
$textColor = Color::fromHex('#ffffff');
$ratio = $color->getContrastRatio($textColor); // Returns: float (1.0 to 21.0)
```

**WCAG Contrast Requirements:**
- **AAA Normal Text:** 7:1 or higher
- **AA Normal Text:** 4.5:1 or higher
- **AA Large Text:** 3:1 or higher

### Theme Generation

#### ColorPalette::getSuggestedSurfaceColors()

Get a complete color theme from the palette:

```php
$theme = $palette->getSuggestedSurfaceColors();
```

**Returns:** Array with keys:
- `'surface'` - Lightest color (main surface)
- `'background'` - Second lightest (secondary backgrounds)
- `'accent'` - Accent color with good contrast
- `'surface_variant'` - Variant of surface color

#### ColorPalette::getSuggestedTextColor()

Get optimal text color (black or white) for a background:

```php
$textColor = $palette->getSuggestedTextColor($backgroundColor);
```

**Parameters:**
- `$backgroundColor` (ColorInterface) - Background color

**Returns:** `ColorInterface` - Either black (#000000) or white (#ffffff) based on contrast

## Troubleshooting & FAQ

### Which image processing extension should I use?

**GD (Recommended for most users):**
- Pre-installed in most PHP environments
- Good color extraction quality
- Lower memory usage
- Slightly less accurate than Imagick

**Imagick (For better accuracy):**
- More accurate color extraction
- Better handling of complex images
- Supports more image formats
- Requires additional installation
- Higher memory usage

### How do I check which extension is installed?

```php
// Check for GD
if (extension_loaded('gd')) {
    echo "GD is installed\n";
}

// Check for Imagick
if (extension_loaded('imagick')) {
    echo "Imagick is installed\n";
}
```

Or use the built-in checker:

```php
try {
    $extractor = ColorExtractorFactory::gd();
    echo "GD is available\n";
} catch (\RuntimeException $e) {
    echo "GD is not available\n";
}
```

### How do I install GD or Imagick?

**Ubuntu/Debian:**
```bash
# Install GD
sudo apt-get install php-gd

# Install Imagick
sudo apt-get install php-imagick
```

**macOS (Homebrew):**
```bash
# Install GD (usually included with PHP)
brew install php

# Install Imagick
brew install imagemagick
pecl install imagick
```

**Restart your web server after installation!**

### The colors extracted don't look right

1. **Try the other driver** - Imagick often produces more accurate results:
   ```php
   $palette = ColorPalette::fromImage('photo.jpg', 5, 'imagick');
   ```

2. **Extract more colors** - Increase the count for better variety:
   ```php
   $palette = ColorPalette::fromImage('photo.jpg', 10);
   ```

3. **Check image quality** - Low quality or heavily compressed images may produce poor results

4. **Image has many similar colors** - This is expected behavior; the library finds dominant colors

### Memory issues with large images

For very large images (> 5MB), consider:

1. **Resize before processing** (using GD or Imagick directly)
2. **Increase PHP memory limit** in php.ini:
   ```ini
   memory_limit = 256M
   ```
3. **Use GD instead of Imagick** (lower memory usage)

### Supported image formats

**GD Driver:**
- JPEG/JPG
- PNG
- GIF
- WebP (PHP 7.1+)

**Imagick Driver:**
- All of the above plus:
- TIFF
- BMP
- PSD
- And many more

### Why are my Color objects not changing?

Color objects are **immutable**. Methods return new instances:

```php
$color = Color::fromHex('#3498db');

// WRONG - This doesn't work!
$color->lighten(0.2);
echo $color->toHex(); // Still #3498db

// CORRECT - Assign the result
$lighter = $color->lighten(0.2);
echo $lighter->toHex(); // Lighter color!
```

### How many colors should I extract?

- **5-8 colors** - Good for general use, themes
- **10-15 colors** - More variety, better for finding specific shades
- **3-5 colors** - Minimal, quick extraction

More colors = more processing time and memory usage.

## Security

### HTTP Client Security

The library implements comprehensive security measures when loading images from URLs to protect against SSRF (Server-Side Request Forgery) and other attacks.

#### SSRF Protection

All remote URLs are validated before making HTTP requests:

**Blocked Protocols:**
- Only `http://` and `https://` are allowed
- All other protocols (`file://`, `ftp://`, `gopher://`, etc.) are rejected

**Blocked IP Addresses:**
- Localhost: `127.0.0.1`, `::1`
- Private networks: `10.0.0.0/8`, `172.16.0.0/12`, `192.168.0.0/16`
- Link-local: `169.254.0.0/16`, `fe80::/10`
- Unique local (IPv6): `fc00::/7`, `fd00::/8`
- IPv4-mapped IPv6 addresses: `::ffff:0:0/96`

```php
use Farzai\ColorPalette\ImageLoaderFactory;

$factory = new ImageLoaderFactory;
$loader = $factory->create();

// These will throw SsrfException:
$loader->load('http://localhost/internal-image.jpg');  // Blocked
$loader->load('http://192.168.1.1/image.jpg');         // Blocked
$loader->load('http://[::1]/image.jpg');               // Blocked
$loader->load('file:///etc/passwd');                    // Blocked

// Only public HTTP/HTTPS URLs are allowed:
$loader->load('https://example.com/public-image.jpg'); // OK
```

#### File Size Limits

Downloaded files are limited to **10MB by default** to prevent denial-of-service attacks:

```php
use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\ImageLoaderFactory;

// Custom file size limit (5MB)
$config = new HttpClientConfig(
    maxFileSizeBytes: 5 * 1024 * 1024
);

$factory = new ImageLoaderFactory(httpConfig: $config);
$loader = $factory->create();

// Will throw HttpException if file exceeds 5MB
$loader->load('https://example.com/huge-image.jpg');
```

Files are streamed in chunks (8KB at a time) and validated during download, not loaded entirely into memory.

#### MIME Type Validation

Remote files are validated to ensure they're actual images:

1. **Content-Type Header:** Checked if present in HTTP response
2. **File Detection:** Actual file content verified using `finfo` after download

Only these MIME types are accepted:
- `image/jpeg`, `image/jpg`
- `image/png`
- `image/gif`
- `image/webp`
- `image/bmp`
- `image/tiff`
- `image/svg+xml`

#### HTTP Client Configuration

Customize HTTP client behavior for security and performance:

```php
use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\ImageLoaderFactory;

$config = new HttpClientConfig(
    timeoutSeconds: 30,           // Request timeout (default: 30)
    maxRedirects: 0,              // Follow redirects (default: 0 - disabled)
    maxFileSizeBytes: 10485760,   // Max file size in bytes (default: 10MB)
    userAgent: 'MyApp/1.0',       // Custom User-Agent header
    verifySsl: true               // SSL certificate verification (default: true)
);

$factory = new ImageLoaderFactory(httpConfig: $config);
$loader = $factory->create();
```

**Security Recommendations:**

1. **Keep `maxRedirects: 0`** - Redirects can bypass SSRF protection
2. **Keep `verifySsl: true`** - Prevents man-in-the-middle attacks
3. **Set appropriate timeout** - Prevents hanging on slow servers
4. **Limit file size** - Protects against DoS via large downloads

#### Exception Handling

Different exception types for different security scenarios:

```php
use Farzai\ColorPalette\Exceptions\SsrfException;
use Farzai\ColorPalette\Exceptions\HttpException;
use Farzai\ColorPalette\Exceptions\InvalidImageException;

try {
    $loader->load($userProvidedUrl);
} catch (SsrfException $e) {
    // URL validation failed (private IP, invalid protocol, etc.)
    log_security_event('SSRF attempt blocked', $userProvidedUrl);
} catch (HttpException $e) {
    // HTTP error (status code, file size, MIME type, etc.)
    log_error('Failed to download image', $e->getMessage());
} catch (InvalidImageException $e) {
    // Image processing error
    log_error('Invalid image', $e->getMessage());
}
```

### Best Practices for User-Provided URLs

When accepting URLs from users:

```php
// ✓ GOOD: Validate and handle errors
try {
    $palette = ColorPalette::fromImage($userUrl, count: 5);
} catch (SsrfException $e) {
    // Don't expose internal network structure in error messages
    throw new UserFacingException('Invalid URL provided');
}

// ✗ BAD: Don't blindly trust user input
$palette = ColorPalette::fromImage($_GET['url']); // Dangerous!

// ✓ GOOD: Add URL whitelist for extra security
$allowedDomains = ['cdn.example.com', 'images.example.com'];
$host = parse_url($userUrl, PHP_URL_HOST);

if (!in_array($host, $allowedDomains)) {
    throw new Exception('URL not from allowed domain');
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](https://github.com/parsilver/color-palette-php/security/policy) on how to report security vulnerabilities.

## Credits

- [All Contributors](https://github.com/parsilver/color-palette-php/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
