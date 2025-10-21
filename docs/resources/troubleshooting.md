---
title: Troubleshooting Guide
description: Solutions for common issues and debugging techniques
category: Resources
order: 2
tags: [troubleshooting, errors, debugging, solutions]
---

# Troubleshooting Guide

This guide helps you diagnose and resolve common issues with Color Palette PHP.

## Common Errors

### GD Extension Errors

#### Error: "GD extension is not installed"

**Symptoms:**
```
Fatal error: Call to undefined function imagecreatefromjpeg()
```

**Cause:** PHP GD extension is not installed or enabled.

**Solutions:**

1. **Install GD Extension:**

   **Ubuntu/Debian:**
   ```bash
   sudo apt-get update
   sudo apt-get install php-gd
   sudo systemctl restart apache2  # or php-fpm
   ```

   **CentOS/RHEL:**
   ```bash
   sudo yum install php-gd
   sudo systemctl restart httpd
   ```

   **macOS (Homebrew):**
   ```bash
   brew install php-gd
   brew services restart php
   ```

   **Windows:**
   - Edit `php.ini`
   - Uncomment: `extension=gd`
   - Restart web server

2. **Verify Installation:**
   ```bash
   php -m | grep gd
   php -i | grep -i gd
   ```

3. **Check PHP Info:**
   ```php
   <?php
   phpinfo();
   // Search for "gd" section
   ```

#### Error: "GD library version is too old"

**Cause:** Outdated GD library.

**Solution:**
```bash
# Update PHP to latest version
sudo apt-get install php8.1-gd  # Ubuntu
brew upgrade php                 # macOS
```

### Image Loading Errors

#### Error: "Failed to load image"

**Symptoms:**
```
RuntimeException: Unable to create image resource from file
```

**Causes and Solutions:**

1. **File Not Found:**
   ```php
   // Check file exists
   if (!file_exists($imagePath)) {
       throw new Exception("Image not found: {$imagePath}");
   }
   ```

2. **Invalid Permissions:**
   ```bash
   # Fix permissions
   chmod 644 /path/to/image.jpg

   # Check ownership
   ls -l /path/to/image.jpg
   ```

3. **Corrupted Image:**
   ```php
   // Validate image
   $imageInfo = @getimagesize($imagePath);
   if ($imageInfo === false) {
       throw new Exception("Corrupted or invalid image file");
   }
   ```

4. **Unsupported Format:**
   ```php
   // Check supported formats
   $supportedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
   $mimeType = mime_content_type($imagePath);

   if (!in_array($mimeType, $supportedTypes)) {
       throw new Exception("Unsupported image format: {$mimeType}");
   }
   ```

#### Error: "URL file-access is disabled"

**Symptoms:**
```
Warning: file_get_contents(): URL file-access is disabled in the server configuration
```

**Cause:** `allow_url_fopen` is disabled in `php.ini`.

**Solutions:**

1. **Enable in php.ini:**
   ```ini
   allow_url_fopen = On
   ```
   Restart web server after change.

2. **Use cURL Alternative:**
   ```php
   function downloadImage($url, $saveTo) {
       $ch = curl_init($url);
       $fp = fopen($saveTo, 'wb');
       curl_setopt($ch, CURLOPT_FILE, $fp);
       curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
       curl_exec($ch);
       curl_close($ch);
       fclose($fp);
       return $saveTo;
   }

   $tempFile = sys_get_temp_dir() . '/temp_image.jpg';
   downloadImage($imageUrl, $tempFile);
   $palette = ColorPalette::fromImage($tempFile)->extract();
   unlink($tempFile);
   ```

### Memory Errors

#### Error: "Allowed memory size exhausted"

**Symptoms:**
```
Fatal error: Allowed memory size of 134217728 bytes exhausted
```

**Cause:** Large images exceeding PHP memory limit.

**Solutions:**

1. **Increase Memory Limit:**
   ```php
   // In script
   ini_set('memory_limit', '256M');

   // Or in php.ini
   memory_limit = 256M
   ```

2. **Reduce Image Dimensions:**
   ```php
   $palette = ColorPalette::fromImage('large.jpg')
       ->setMaxDimension(1000)  // Resize to max 1000px
       ->extract();
   ```

3. **Lower Quality Setting:**
   ```php
   $palette = ColorPalette::fromImage('large.jpg')
       ->setQuality(3)  // Lower quality = less memory
       ->extract();
   ```

4. **Process in Batches:**
   ```php
   foreach ($images as $image) {
       $palette = ColorPalette::fromImage($image)->extract();
       // Process results
       unset($palette);  // Free memory
       gc_collect_cycles();  // Force garbage collection
   }
   ```

#### Error: "Maximum execution time exceeded"

**Symptoms:**
```
Fatal error: Maximum execution time of 30 seconds exceeded
```

**Cause:** Processing takes too long.

**Solutions:**

1. **Increase Execution Time:**
   ```php
   set_time_limit(120);  // 120 seconds

   // Or in php.ini
   max_execution_time = 120
   ```

2. **Optimize Processing:**
   ```php
   $palette = ColorPalette::fromImage('image.jpg')
       ->setMaxDimension(800)   // Reduce size
       ->setQuality(5)          // Lower quality
       ->setColorCount(5)       // Fewer colors
       ->extract();
   ```

3. **Use Background Processing:**
   ```php
   // Queue job for async processing
   dispatch(new ExtractColorsJob($imagePath));
   ```

### Color Extraction Issues

#### Issue: "Extracted colors are not accurate"

**Symptoms:** Colors don't match visual perception.

**Causes and Solutions:**

1. **Low Quality Setting:**
   ```php
   // Increase quality
   $palette = ColorPalette::fromImage('image.jpg')
       ->setQuality(9)  // Higher quality (1-10)
       ->extract();
   ```

2. **Insufficient Color Count:**
   ```php
   // Extract more colors
   $palette = ColorPalette::fromImage('image.jpg')
       ->setColorCount(10)  // More colors for accuracy
       ->extract();
   ```

3. **Color Space Issues:**
   ```php
   // Use proper color space conversion
   $color = Color::fromRgb(52, 152, 219);
   // Ensure sRGB color space
   ```

4. **Image Preprocessing:**
   ```php
   // Remove noise before extraction
   $palette = ColorPalette::fromImage('image.jpg')
       ->setDenoise(true)
       ->extract();
   ```

#### Issue: "Too many similar colors extracted"

**Symptoms:** Colors are too close to each other.

**Solution:**
```php
// Increase color distance threshold
$palette = ColorPalette::fromImage('image.jpg')
    ->setColorCount(5)
    ->setMinDistance(20)  // Minimum color distance
    ->extract();

// Or filter after extraction
$uniqueColors = $palette->filterBySimilarity(threshold: 0.15);
```

#### Issue: "Dominant color is wrong"

**Symptoms:** Dominant color doesn't match visual perception.

**Causes and Solutions:**

1. **Frequency vs Prominence:**
   ```php
   // Use prominence instead of frequency
   $dominant = $palette->getDominantColor(method: 'prominence');
   ```

2. **Ignore Background:**
   ```php
   // Exclude likely background colors
   $palette = ColorPalette::fromImage('image.jpg')
       ->setIgnoreEdges(true)  // Ignore edge pixels
       ->extract();
   ```

3. **Weight by Location:**
   ```php
   // Focus on center region
   $palette = ColorPalette::fromImage('image.jpg')
       ->setCenterWeighted(true)
       ->extract();
   ```

## Debugging Techniques

### Enable Debug Mode

```php
use Farzai\ColorPalette\ColorPalette;

// Enable verbose error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Enable debug logging
$palette = ColorPalette::fromImage('image.jpg')
    ->setDebug(true)
    ->extract();
```

### Inspect Image Properties

```php
// Check image details
$imagePath = 'image.jpg';
$imageInfo = getimagesize($imagePath);

echo "Dimensions: {$imageInfo[0]}x{$imageInfo[1]}\n";
echo "Type: {$imageInfo['mime']}\n";
echo "Channels: {$imageInfo['channels']}\n";
echo "Bits: {$imageInfo['bits']}\n";
echo "File size: " . filesize($imagePath) . " bytes\n";
```

### Test Color Conversions

```php
use Farzai\ColorPalette\Color;

// Test color conversion accuracy
$original = Color::fromHex('#3498db');
$rgb = $original->toRgb();
$converted = Color::fromRgb(...$rgb);

echo "Original: " . $original->getHex() . "\n";
echo "Converted: " . $converted->getHex() . "\n";

// Should match
assert($original->getHex() === $converted->getHex());
```

### Memory Profiling

```php
// Track memory usage
$memoryBefore = memory_get_usage(true);

$palette = ColorPalette::fromImage('image.jpg')->extract();

$memoryAfter = memory_get_usage(true);
$memoryUsed = ($memoryAfter - $memoryBefore) / 1024 / 1024;

echo "Memory used: {$memoryUsed} MB\n";
echo "Peak memory: " . (memory_get_peak_usage(true) / 1024 / 1024) . " MB\n";
```

### Performance Profiling

```php
// Measure extraction time
$startTime = microtime(true);

$palette = ColorPalette::fromImage('image.jpg')->extract();

$endTime = microtime(true);
$duration = $endTime - $startTime;

echo "Extraction took: {$duration} seconds\n";
```

### Validate Color Output

```php
// Ensure valid color values
foreach ($palette->getColors() as $color) {
    $rgb = $color->toRgb();

    // Validate RGB ranges
    assert($rgb[0] >= 0 && $rgb[0] <= 255, "Invalid red value");
    assert($rgb[1] >= 0 && $rgb[1] <= 255, "Invalid green value");
    assert($rgb[2] >= 0 && $rgb[2] <= 255, "Invalid blue value");

    // Validate hex format
    assert(preg_match('/^#[0-9A-F]{6}$/i', $color->getHex()), "Invalid hex format");
}
```

## Environment Setup Issues

### Docker Environment

**Issue:** GD extension missing in Docker container.

**Solution:**
```dockerfile
# Dockerfile
FROM php:8.1-fpm

# Install GD extension
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Verify installation
RUN php -m | grep gd
```

### Composer Issues

**Issue:** Package not installing correctly.

**Solutions:**

1. **Clear Composer Cache:**
   ```bash
   composer clear-cache
   composer install
   ```

2. **Update Dependencies:**
   ```bash
   composer update farzai/color-palette-php
   ```

3. **Require Specific Version:**
   ```bash
   composer require farzai/color-palette-php:^2.0
   ```

4. **Check Minimum Stability:**
   ```json
   {
       "minimum-stability": "stable",
       "prefer-stable": true
   }
   ```

### CI/CD Pipeline Issues

**Issue:** Tests failing in CI environment.

**Solution:**
```yaml
# .github/workflows/test.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest

    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.1'
          extensions: gd, mbstring
          coverage: xdebug

      - name: Install dependencies
        run: composer install --prefer-dist --no-progress

      - name: Run tests
        run: vendor/bin/phpunit
```

## Performance Optimization

### Slow Extraction

**Issue:** Color extraction is too slow.

**Solutions:**

1. **Optimize Image Size:**
   ```php
   $palette = ColorPalette::fromImage('large.jpg')
       ->setMaxDimension(800)  // Resize before processing
       ->extract();
   ```

2. **Reduce Quality:**
   ```php
   $palette = ColorPalette::fromImage('image.jpg')
       ->setQuality(4)  // Lower quality = faster
       ->extract();
   ```

3. **Limit Color Count:**
   ```php
   $palette = ColorPalette::fromImage('image.jpg')
       ->setColorCount(5)  // Fewer colors = faster
       ->extract();
   ```

4. **Use Caching:**
   ```php
   $cacheKey = 'palette:' . md5_file($imagePath);

   $colors = cache()->remember($cacheKey, 3600, function() use ($imagePath) {
       return ColorPalette::fromImage($imagePath)->extract()->getColors();
   });
   ```

### High Memory Usage

**Issue:** Processing consumes too much memory.

**Solutions:**

1. **Limit Image Dimensions:**
   ```php
   $palette = ColorPalette::fromImage('image.jpg')
       ->setMaxDimension(1000)
       ->extract();
   ```

2. **Process in Chunks:**
   ```php
   $images = array_chunk($allImages, 10);

   foreach ($images as $chunk) {
       foreach ($chunk as $image) {
           $palette = ColorPalette::fromImage($image)->extract();
           // Process
           unset($palette);
       }
       gc_collect_cycles();
   }
   ```

3. **Use Streaming:**
   ```php
   // Process large batches with generator
   function processImages(array $paths): \Generator {
       foreach ($paths as $path) {
           yield ColorPalette::fromImage($path)->extract();
       }
   }

   foreach (processImages($imagePaths) as $palette) {
       // Handle palette
   }
   ```

## Getting Help

If you're still experiencing issues:

1. **Check Documentation:**
   - [FAQ](faq.md)
   - [API Reference](../api/)
   - [Examples](../examples/)

2. **Search Existing Issues:**
   - [GitHub Issues](https://github.com/farzai/color-palette-php/issues)

3. **Create New Issue:**
   - Use issue template
   - Include PHP version, OS, error messages
   - Provide minimal reproducible example

4. **Community Support:**
   - [Discussions](https://github.com/farzai/color-palette-php/discussions)
   - Stack Overflow tag: `color-palette-php`

## See Also

- [FAQ](faq.md)
- [Migration Guide](migration-guide.md)
- [Contributing Guidelines](contributing.md)
