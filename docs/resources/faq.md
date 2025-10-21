---
title: Frequently Asked Questions
description: Common questions and answers about Color Palette PHP
category: Resources
order: 1
tags: [faq, questions, help, support]
---

# Frequently Asked Questions (FAQ)

## Installation

### Q: What are the minimum PHP requirements?
**A:** Color Palette PHP requires PHP 7.4 or higher. PHP 8.0+ is recommended for better performance and modern features.

### Q: How do I install the package?
**A:** Install via Composer:
```bash
composer require farzai/color-palette-php
```

### Q: Can I use this package without Composer?
**A:** While not recommended, you can manually include the files. However, Composer is the officially supported installation method as it handles autoloading and dependencies.

### Q: Does this package have any dependencies?
**A:** No, Color Palette PHP is dependency-free. It uses only PHP's built-in GD extension for image processing.

### Q: What if GD extension is not installed?
**A:** Install the GD extension for your PHP version:
- Ubuntu/Debian: `sudo apt-get install php-gd`
- macOS (Homebrew): `brew install php-gd`
- Windows: Enable in `php.ini` by uncommenting `;extension=gd`

## Usage

### Q: How do I extract colors from an image?
**A:** Use the `ColorPalette` class:
```php
use Farzai\ColorPalette\ColorPalette;

$palette = ColorPalette::fromImage('/path/to/image.jpg');
$colors = $palette->getColors();
```

### Q: What image formats are supported?
**A:** The package supports all formats supported by PHP's GD extension:
- JPEG/JPG
- PNG (with transparency support)
- GIF
- WebP (PHP 7.0+)
- BMP (PHP 7.2+)

### Q: Can I process images from URLs?
**A:** Yes, if `allow_url_fopen` is enabled:
```php
$palette = ColorPalette::fromImage('https://example.com/image.jpg');
```

### Q: How many colors are extracted by default?
**A:** By default, 5 colors are extracted. You can customize this:
```php
$palette = ColorPalette::fromImage('image.jpg')
    ->setColorCount(10)
    ->extract();
```

### Q: Can I extract colors from a specific region of an image?
**A:** Yes, use region-based extraction:
```php
$palette = ColorPalette::fromImage('image.jpg')
    ->setRegion(x: 0, y: 0, width: 200, height: 200)
    ->extract();
```

### Q: How do I convert colors between formats?
**A:** Use the `Color` class conversion methods:
```php
use Farzai\ColorPalette\Color;

$color = Color::fromHex('#3498db');
$rgb = $color->toRgb();      // [52, 152, 219]
$hsl = $color->toHsl();      // [204, 70, 53]
$hsv = $color->toHsv();      // [204, 76, 86]
$cmyk = $color->toCmyk();    // [76, 31, 0, 14]
```

## Performance

### Q: How can I improve extraction speed?
**A:** Several optimization techniques:
1. Reduce image resolution: `->setMaxDimension(800)`
2. Use fewer colors: `->setColorCount(5)`
3. Lower quality: `->setQuality(5)` (1-10 scale)
4. Enable caching for repeated operations

### Q: How much memory does color extraction use?
**A:** Memory usage depends on image size and quality settings. For a 2000x2000px image:
- High quality (10): ~50-80 MB
- Medium quality (5): ~20-30 MB
- Low quality (1): ~5-10 MB

Use `setMaxDimension()` to limit memory usage for large images.

### Q: Can I process multiple images concurrently?
**A:** Yes, but be mindful of memory limits. Process images sequentially or use a queue system for large batches:
```php
foreach ($images as $image) {
    $palette = ColorPalette::fromImage($image)->extract();
    // Process palette
    unset($palette); // Free memory
}
```

### Q: What's the recommended quality setting?
**A:** For most use cases, quality 5-7 provides a good balance between accuracy and performance. Use higher quality (8-10) only when color precision is critical.

## Troubleshooting

### Q: Why am I getting "GD extension not found" error?
**A:** The PHP GD extension is required. Install it:
- Ubuntu/Debian: `sudo apt-get install php-gd`
- Verify installation: `php -m | grep gd`

See [Troubleshooting Guide](troubleshooting.md#gd-extension-errors) for details.

### Q: Why are extracted colors not accurate?
**A:** Several factors affect accuracy:
1. Low quality setting - increase quality (7-10)
2. Color quantization algorithm - try different algorithms
3. Image has few distinct colors - reduce color count
4. Color space conversion - ensure proper color space handling

### Q: How do I handle large images without memory errors?
**A:** Use dimension limiting:
```php
$palette = ColorPalette::fromImage('large.jpg')
    ->setMaxDimension(1000) // Resize to max 1000px
    ->setQuality(5)         // Reduce quality
    ->extract();
```

### Q: Why does fromImage() throw an exception?
**A:** Common causes:
- File not found - verify path exists
- Invalid image format - check file extension and MIME type
- Corrupted image - validate image integrity
- Insufficient permissions - check file read permissions
- Memory limit exceeded - increase `memory_limit` in php.ini

### Q: How do I debug extraction issues?
**A:** Enable error reporting and logging:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    $palette = ColorPalette::fromImage('image.jpg')->extract();
} catch (\Exception $e) {
    error_log($e->getMessage());
    var_dump($e->getTrace());
}
```

## API

### Q: What methods are available on ColorPalette?
**A:** Key methods:
- `fromImage(string $path)` - Load image
- `setColorCount(int $count)` - Set number of colors
- `setQuality(int $quality)` - Set quality (1-10)
- `setMaxDimension(int $pixels)` - Limit image size
- `setRegion(int $x, int $y, int $width, int $height)` - Extract from region
- `extract()` - Perform extraction
- `getColors()` - Get color array
- `getDominantColor()` - Get most prominent color

### Q: What properties does a Color object have?
**A:** Color objects provide:
- `getHex()` - Hex string (#RRGGBB)
- `getRgb()` - RGB array [R, G, B]
- `getHsl()` - HSL array [H, S, L]
- `getHsv()` - HSV array [H, S, V]
- `getCmyk()` - CMYK array [C, M, Y, K]
- `getLuminance()` - Perceived brightness (0-1)
- `isDark()` - Is color dark? (bool)
- `isLight()` - Is color light? (bool)

### Q: Can I filter colors by criteria?
**A:** Yes, use filtering methods:
```php
$palette = ColorPalette::fromImage('image.jpg')->extract();

// Filter by brightness
$darkColors = $palette->filterDark();
$lightColors = $palette->filterLight();

// Filter by saturation
$vibrantColors = $palette->filterBySaturation(min: 0.5);

// Custom filters
$filtered = $palette->filter(function($color) {
    return $color->getLuminance() > 0.5;
});
```

### Q: How do I sort colors?
**A:** Use built-in sorting methods:
```php
$palette->sortByLuminance();  // Brightest to darkest
$palette->sortByHue();         // Color wheel order
$palette->sortBySaturation();  // Most to least vibrant
$palette->sortByFrequency();   // Most to least common
```

## Integration

### Q: Can I use this with Laravel?
**A:** Yes, it works with any PHP framework. For Laravel:
```php
// In controller
use Farzai\ColorPalette\ColorPalette;

public function extractColors(Request $request)
{
    $image = $request->file('image');
    $palette = ColorPalette::fromImage($image->path())->extract();

    return response()->json([
        'colors' => $palette->getColors()
    ]);
}
```

### Q: Does this work with Symfony?
**A:** Yes, integrate in any Symfony service:
```php
use Farzai\ColorPalette\ColorPalette;

class ColorExtractionService
{
    public function extractFromUpload(UploadedFile $file): array
    {
        return ColorPalette::fromImage($file->getPathname())
            ->extract()
            ->getColors();
    }
}
```

### Q: Can I cache extraction results?
**A:** Yes, implement caching based on image hash:
```php
$imageHash = md5_file($imagePath);
$cacheKey = "palette:{$imageHash}";

$colors = cache()->remember($cacheKey, 3600, function() use ($imagePath) {
    return ColorPalette::fromImage($imagePath)
        ->extract()
        ->getColors();
});
```

### Q: How do I handle user-uploaded images?
**A:** Validate and process safely:
```php
// Validate
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file->getMimeType(), $allowedTypes)) {
    throw new \InvalidArgumentException('Invalid image type');
}

// Process with resource limits
$palette = ColorPalette::fromImage($file->path())
    ->setMaxDimension(2000)  // Prevent huge images
    ->setQuality(6)           // Balance speed/accuracy
    ->extract();
```

## Advanced

### Q: Can I implement custom color extraction algorithms?
**A:** Yes, extend the base extractor:
```php
use Farzai\ColorPalette\Extractors\AbstractExtractor;

class CustomExtractor extends AbstractExtractor
{
    public function extract(): array
    {
        // Custom extraction logic
        return $colors;
    }
}

$palette->setExtractor(new CustomExtractor());
```

### Q: How do I extract colors for accessibility compliance?
**A:** Use contrast ratio helpers:
```php
$palette = ColorPalette::fromImage('image.jpg')->extract();

foreach ($palette->getColors() as $color) {
    $contrastWithWhite = $color->getContrastRatio('#FFFFFF');
    if ($contrastWithWhite >= 4.5) {
        // WCAG AA compliant for normal text
    }
}
```

### Q: Can I extract colors while preserving alpha channel?
**A:** Yes, the package preserves PNG transparency:
```php
$palette = ColorPalette::fromImage('transparent.png')
    ->setPreserveAlpha(true)
    ->extract();

foreach ($palette->getColors() as $color) {
    $rgba = $color->toRgba(); // [R, G, B, A]
}
```

### Q: How do I generate color schemes from an image?
**A:** Use scheme generation methods:
```php
$dominant = $palette->getDominantColor();

// Generate complementary colors
$complementary = $dominant->getComplementary();

// Generate analogous colors
$analogous = $dominant->getAnalogous();

// Generate triadic scheme
$triadic = $dominant->getTriadic();
```

## See Also

- [Troubleshooting Guide](troubleshooting.md)
- [Migration Guide](migration-guide.md)
- [Contributing Guidelines](contributing.md)
- [API Documentation](../api/)
- [Examples](../examples/)
