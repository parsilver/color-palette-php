---
layout: default
title: Installation Guide - Color Palette PHP
description: Complete installation guide for Color Palette PHP across different environments and platforms
keywords: installation, setup, composer, php extensions, gd, imagick, requirements
---

# Installation Guide

This comprehensive guide will help you install and configure Color Palette PHP in various environments and hosting platforms.

<div class="quick-links">
  <a href="#system-requirements">Requirements</a> •
  <a href="#installation-methods">Installation</a> •
  <a href="#backend-setup">Backend Setup</a> •
  <a href="#verification">Verification</a> •
  <a href="#troubleshooting">Troubleshooting</a>
</div>

## System Requirements

### Minimum Requirements

- **PHP Version**: 8.1 or higher
- **Composer**: Latest stable version
- **Image Processing**: At least one of the following:
  - GD extension (recommended for most use cases)
  - ImageMagick extension (recommended for advanced features)

### Recommended Requirements

- **PHP Version**: 8.2 or higher
- **Memory**: 256MB or more for large images
- **Both Extensions**: GD and ImageMagick for maximum flexibility

### Checking Your Environment

Run these commands to verify your system meets the requirements:

```bash
# Check PHP version
php -v

# Check installed extensions
php -m | grep -E 'gd|imagick'

# Check memory limit
php -i | grep memory_limit

# Check Composer version
composer --version
```

Expected output:
```
PHP 8.1.0 or higher
gd          # or imagick, or both
memory_limit => 256M
Composer version 2.x.x
```

## Installation Methods

### Method 1: Via Composer (Recommended)

This is the recommended and simplest method to install Color Palette PHP.

#### Step 1: Install the Package

```bash
composer require farzai/color-palette
```

#### Step 2: Verify Installation

```bash
composer show farzai/color-palette
```

You should see package information including version, description, and dependencies.

#### Step 3: Test Basic Functionality

Create a test file `test-installation.php`:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Farzai\ColorPalette\Color;

// Test color creation
$color = new Color(255, 0, 0);
echo "Color created: " . $color->toHex() . "\n";

echo "Installation successful!\n";
```

Run the test:

```bash
php test-installation.php
```

### Method 2: Manual Installation

If you cannot use Composer, follow these steps:

#### Step 1: Download the Package

```bash
# Clone the repository
git clone https://github.com/parsilver/color-palette-php.git

# Or download the latest release
wget https://github.com/parsilver/color-palette-php/archive/refs/tags/v1.0.0.zip
unzip v1.0.0.zip
```

#### Step 2: Install Dependencies

```bash
cd color-palette-php
composer install --no-dev
```

#### Step 3: Include the Autoloader

```php
<?php

require_once '/path/to/color-palette-php/vendor/autoload.php';

use Farzai\ColorPalette\Color;

// Your code here
```

### Method 3: Development Installation

For contributing or development purposes:

#### Step 1: Clone the Repository

```bash
git clone https://github.com/parsilver/color-palette-php.git
cd color-palette-php
```

#### Step 2: Install All Dependencies

```bash
composer install
```

#### Step 3: Run Tests

```bash
composer test
```

## Backend Setup

Color Palette PHP supports two image processing backends. At least one must be installed.

### GD Extension (Recommended)

GD is commonly pre-installed with PHP and works well for most use cases.

#### Ubuntu/Debian

```bash
# PHP 8.1
sudo apt-get update
sudo apt-get install php8.1-gd

# PHP 8.2
sudo apt-get install php8.2-gd

# PHP 8.3
sudo apt-get install php8.3-gd

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

#### CentOS/RHEL

```bash
# PHP 8.1
sudo yum install php81-gd

# Restart PHP-FPM
sudo systemctl restart php-fpm
```

#### macOS (Homebrew)

```bash
# GD is usually included with PHP
brew install php@8.1

# Verify GD is enabled
php -m | grep gd
```

#### Windows

1. Open `php.ini` file (usually in `C:\php\php.ini`)
2. Find the line `;extension=gd`
3. Remove the semicolon: `extension=gd`
4. Restart your web server (Apache/Nginx)

#### Docker

```dockerfile
FROM php:8.1-fpm

# Install GD extension
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd
```

### ImageMagick Extension

ImageMagick provides advanced image processing capabilities.

#### Ubuntu/Debian

```bash
# Install ImageMagick library
sudo apt-get update
sudo apt-get install imagemagick libmagickwand-dev

# Install PHP extension
sudo apt-get install php8.1-imagick

# Restart PHP-FPM
sudo systemctl restart php8.1-fpm
```

#### CentOS/RHEL

```bash
# Install ImageMagick
sudo yum install ImageMagick ImageMagick-devel

# Install PHP extension via PECL
sudo yum install php-pear php-devel gcc
sudo pecl install imagick

# Enable extension
echo "extension=imagick.so" | sudo tee /etc/php.d/imagick.ini

# Restart PHP-FPM
sudo systemctl restart php-fpm
```

#### macOS (Homebrew)

```bash
# Install ImageMagick
brew install imagemagick

# Install PHP extension
pecl install imagick

# Add to php.ini
echo "extension=imagick.so" >> $(php --ini | grep "Loaded Configuration" | sed -e "s|.*:\s*||")
```

#### Windows

1. Download PHP Imagick DLL from [PECL](https://pecl.php.net/package/imagick)
2. Extract and copy `php_imagick.dll` to PHP's `ext` directory
3. Add `extension=imagick` to `php.ini`
4. Download [ImageMagick binaries](https://imagemagick.org/script/download.php#windows)
5. Restart your web server

#### Docker

```dockerfile
FROM php:8.1-fpm

# Install ImageMagick extension
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    && pecl install imagick \
    && docker-php-ext-enable imagick
```

## Verification

### Verify Extension Installation

Create a verification script `verify-extensions.php`:

```php
<?php

echo "PHP Version: " . PHP_VERSION . "\n\n";

// Check GD
if (extension_loaded('gd')) {
    echo "✓ GD Extension: Installed\n";
    $gdInfo = gd_info();
    echo "  - GD Version: " . $gdInfo['GD Version'] . "\n";
    echo "  - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
    echo "  - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ GD Extension: Not installed\n";
}

echo "\n";

// Check Imagick
if (extension_loaded('imagick')) {
    echo "✓ ImageMagick Extension: Installed\n";
    $imagick = new Imagick();
    echo "  - Version: " . $imagick->getVersion()['versionString'] . "\n";
} else {
    echo "✗ ImageMagick Extension: Not installed\n";
}

echo "\n";

// Check Color Palette PHP
if (class_exists('Farzai\\ColorPalette\\Color')) {
    echo "✓ Color Palette PHP: Installed\n";
} else {
    echo "✗ Color Palette PHP: Not installed\n";
}
```

Run the verification:

```bash
php verify-extensions.php
```

### Complete Installation Test

Create a complete test file `complete-test.php`:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

echo "Testing Color Palette PHP Installation\n";
echo str_repeat('=', 50) . "\n\n";

// Test 1: Color Creation
echo "Test 1: Color Creation... ";
try {
    $color = new Color(255, 0, 0);
    echo "✓ Passed\n";
    echo "  Created color: " . $color->toHex() . "\n";
} catch (Exception $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Color Conversion
echo "Test 2: Color Conversions... ";
try {
    $color = Color::fromHex('#3498db');
    $hsl = $color->toHsl();
    $rgb = $color->toRgb();
    echo "✓ Passed\n";
    echo "  RGB: " . json_encode($rgb) . "\n";
    echo "  HSL: " . json_encode($hsl) . "\n";
} catch (Exception $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Color Manipulation
echo "Test 3: Color Manipulation... ";
try {
    $color = new Color(100, 150, 200);
    $lighter = $color->lighten(0.2);
    $darker = $color->darken(0.2);
    echo "✓ Passed\n";
    echo "  Original: " . $color->toHex() . "\n";
    echo "  Lighter: " . $lighter->toHex() . "\n";
    echo "  Darker: " . $darker->toHex() . "\n";
} catch (Exception $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: GD Backend (if available)
if (extension_loaded('gd')) {
    echo "Test 4: GD Backend... ";
    try {
        $factory = new ColorExtractorFactory();
        $extractor = $factory->make('gd');
        echo "✓ Passed\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

// Test 5: Imagick Backend (if available)
if (extension_loaded('imagick')) {
    echo "Test 5: ImageMagick Backend... ";
    try {
        $factory = new ColorExtractorFactory();
        $extractor = $factory->make('imagick');
        echo "✓ Passed\n";
    } catch (Exception $e) {
        echo "✗ Failed: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo str_repeat('=', 50) . "\n";
echo "All tests completed!\n";
```

## Platform-Specific Installation

### Shared Hosting

Most shared hosting providers include GD by default:

1. Upload your project files via FTP/SFTP
2. Run `composer install` via SSH or hosting control panel
3. Verify GD is available in your hosting control panel's PHP settings

If ImageMagick is not available, stick with the GD backend.

### Laravel Integration

```bash
# Install the package
composer require farzai/color-palette

# Publish configuration (optional)
# Create config/color-palette.php if needed
```

Usage in Laravel:

```php
<?php

namespace App\Http\Controllers;

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function extractColors(Request $request)
    {
        $image = ImageFactory::createFromPath($request->file('image')->path());
        $extractor = (new ColorExtractorFactory())->make('gd');
        $palette = $extractor->extract($image, 5);

        return response()->json([
            'colors' => $palette->toArray()
        ]);
    }
}
```

### WordPress Integration

```php
<?php
/**
 * Plugin Name: Color Palette Extractor
 * Description: Extract colors from images using Color Palette PHP
 */

require_once __DIR__ . '/vendor/autoload.php';

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function extract_image_colors($image_path) {
    $image = ImageFactory::createFromPath($image_path);
    $extractor = (new ColorExtractorFactory())->make('gd');
    $palette = $extractor->extract($image, 5);

    return $palette->toArray();
}

// Hook into media upload
add_action('add_attachment', function($attachment_id) {
    $file = get_attached_file($attachment_id);
    $colors = extract_image_colors($file);
    update_post_meta($attachment_id, 'color_palette', $colors);
});
```

### Symfony Integration

```yaml
# composer.json
{
    "require": {
        "farzai/color-palette": "^1.0"
    }
}
```

```php
<?php
// src/Service/ColorService.php

namespace App\Service;

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class ColorService
{
    private ColorExtractorFactory $extractorFactory;

    public function __construct()
    {
        $this->extractorFactory = new ColorExtractorFactory();
    }

    public function extractFromImage(string $path, int $count = 5): array
    {
        $image = ImageFactory::createFromPath($path);
        $extractor = $this->extractorFactory->make('gd');
        $palette = $extractor->extract($image, $count);

        return $palette->toArray();
    }
}
```

## Troubleshooting

### Common Issues and Solutions

#### Issue 1: Extension Not Found

**Error:**
```
Fatal error: Uncaught Error: Class 'GD' not found
```

**Solution:**
```bash
# Verify extension is installed
php -m | grep gd

# If not found, install it
sudo apt-get install php8.1-gd
sudo systemctl restart php8.1-fpm
```

#### Issue 2: Memory Limit Exceeded

**Error:**
```
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Solution:**
```php
// Increase memory limit in php.ini
memory_limit = 256M

// Or temporarily in your script
ini_set('memory_limit', '256M');
```

#### Issue 3: Image Format Not Supported

**Error:**
```
ImageException: Unsupported image format
```

**Solution:**
```bash
# Verify supported formats
php -r "print_r(gd_info());"

# Install missing format support
sudo apt-get install libjpeg-dev libpng-dev
sudo docker-php-ext-configure gd --with-jpeg --with-png
sudo docker-php-ext-install gd
```

#### Issue 4: Permission Denied

**Error:**
```
Warning: imagecreatefromjpeg(): Unable to open 'image.jpg'
```

**Solution:**
```bash
# Check file permissions
ls -la image.jpg

# Fix permissions
chmod 644 image.jpg

# Ensure directory is readable
chmod 755 /path/to/directory
```

#### Issue 5: Composer Installation Fails

**Error:**
```
The requested package farzai/color-palette could not be found
```

**Solution:**
```bash
# Update Composer
composer self-update

# Clear cache
composer clear-cache

# Try again
composer require farzai/color-palette
```

#### Issue 6: Class Not Found After Installation

**Error:**
```
Fatal error: Class 'Farzai\ColorPalette\Color' not found
```

**Solution:**
```bash
# Regenerate autoload files
composer dump-autoload

# Verify autoloader is included
# In your PHP file:
require_once __DIR__ . '/vendor/autoload.php';
```

### Getting Help

If you encounter issues not covered here:

1. **Check the Documentation**: Visit [full documentation](https://parsilver.github.io/color-palette-php/)
2. **Search Issues**: Browse [GitHub Issues](https://github.com/parsilver/color-palette-php/issues)
3. **Ask for Help**: Create a [new issue](https://github.com/parsilver/color-palette-php/issues/new) with:
   - PHP version (`php -v`)
   - Extension info (`php -m`)
   - Error messages
   - Code samples

## Performance Optimization

### Configuration Tips

```php
<?php

// For better performance with large images:

// 1. Resize images before processing
$maxWidth = 800;
$maxHeight = 600;

// 2. Limit color extraction count
$palette = $extractor->extract($image, 5); // Instead of 20

// 3. Cache extracted palettes
$cacheKey = md5_file($imagePath);
if (!$cache->has($cacheKey)) {
    $palette = $extractor->extract($image, 5);
    $cache->set($cacheKey, $palette->toArray(), 3600);
}

// 4. Use appropriate backend
// GD: Faster for basic operations
// Imagick: Better for large images and advanced features
```

## Next Steps

Now that Color Palette PHP is installed:

- **[Basic Usage Guide](basic-usage)** - Learn fundamental operations
- **[Color Extraction Guide](color-extraction)** - Extract colors from images
- **[Color Manipulation Guide](color-manipulation)** - Transform and adjust colors
- **[Theme Generation Guide](theme-generation)** - Create color themes

## Additional Resources

- **[API Documentation](../api/)** - Complete API reference
- **[Examples](../examples/)** - Real-world code examples
- **[GitHub Repository](https://github.com/parsilver/color-palette-php)** - Source code and issues
