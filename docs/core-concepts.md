---
layout: default
title: Core Concepts - Color Palette PHP
description: Learn about color spaces, manipulation techniques, and best practices in Color Palette PHP
keywords: color spaces, color manipulation, color theory, color extraction, php color library
---

# Core Concepts

Understanding the core concepts of color manipulation and extraction is essential for getting the most out of Color Palette PHP.

<div class="quick-links">
  <a href="#color-spaces">Color Spaces</a> •
  <a href="#color-manipulation">Color Manipulation</a> •
  <a href="#color-extraction">Color Extraction</a> •
  <a href="#theme-generation">Theme Generation</a> •
  <a href="#best-practices">Best Practices</a>
</div>

## Color Spaces

Color Palette PHP supports multiple color spaces, each serving different purposes:

<div class="color-spaces">
  <div class="color-space">
    <h3>RGB (Red, Green, Blue)</h3>
    <p>The standard color space for digital displays. Each color is represented by its red, green, and blue components.</p>
    
    ```php
    use Farzai\ColorPalette\Color;
    
    // Create color from RGB values
    $color = new Color(37, 99, 235);
    
    // Get RGB components
    $rgb = $color->toRgb();
    echo "R: {$rgb['r']}, G: {$rgb['g']}, B: {$rgb['b']}";
    ```
  </div>

  <div class="color-space">
    <h3>HSL (Hue, Saturation, Lightness)</h3>
    <p>A more intuitive color space for adjustments. Hue represents the color, saturation the intensity, and lightness the brightness.</p>
    
    ```php
    // Convert to HSL
    $hsl = $color->toHsl();
    echo "H: {$hsl['h']}°, S: {$hsl['s']}%, L: {$hsl['l']}%";
    
    // Create from HSL
    $color = Color::fromHsl(220, 84, 53);
    ```
  </div>

  <div class="color-space">
    <h3>CMYK (Cyan, Magenta, Yellow, Key)</h3>
    <p>Used primarily for print media. Represents colors using cyan, magenta, yellow, and black (key) components.</p>
    
    ```php
    // Convert to CMYK
    $cmyk = $color->toCmyk();
    echo "C: {$cmyk['c']}%, M: {$cmyk['m']}%, Y: {$cmyk['y']}%, K: {$cmyk['k']}%";
    ```
  </div>

  <div class="color-space">
    <h3>LAB (Lightness, A, B)</h3>
    <p>A perceptually uniform color space. Useful for accurate color matching and calculations.</p>
    
    ```php
    // Convert to LAB
    $lab = $color->toLab();
    echo "L: {$lab['l']}, a: {$lab['a']}, b: {$lab['b']}";
    ```
  </div>
</div>

## Color Manipulation

Color Palette PHP provides various methods for manipulating colors:

### Basic Adjustments

```php
use Farzai\ColorPalette\Color;

$color = new Color(37, 99, 235);

// Lightness adjustments
$lighter = $color->lighten(0.2);    // Increase lightness by 20%
$darker = $color->darken(0.2);      // Decrease lightness by 20%
$withLightness = $color->withLightness(0.5); // Set specific lightness value

// Saturation adjustments
$saturated = $color->saturate(0.1);     // Increase saturation by 10%
$desaturated = $color->desaturate(0.1); // Decrease saturation by 10%

// Hue adjustments
$rotated = $color->rotate(180);     // Rotate hue by 180 degrees

// Color space conversions
$hsv = $color->toHsv();            // Convert to HSV
$newColor = Color::fromHsv(180, 50, 80); // Create from HSV (H: 0-360, S: 0-100, V: 0-100)

// Color analysis
$brightness = $color->getBrightness();
$luminance = $color->getLuminance();
$isLight = $color->isLight();
$isDark = $color->isDark();

// Contrast calculations
$otherColor = new Color(255, 255, 255);
$contrastRatio = $color->getContrastRatio($otherColor);
```

### Color Analysis and Comparison

```php
use Farzai\ColorPalette\Color;

$color1 = new Color(37, 99, 235);  // Blue
$color2 = new Color(239, 68, 68);  // Red

// Analyze color properties
$brightness1 = $color1->getBrightness(); // 0-255
$brightness2 = $color2->getBrightness();

// Check contrast between colors
$contrastRatio = $color1->getContrastRatio($color2);
```

### Color Analysis

```php
use Farzai\ColorPalette\Color;

$color = new Color(37, 99, 235);

// Brightness and contrast
$brightness = $color->getBrightness();
$isLight = $color->isLight();
$isDark = $color->isDark();

// Contrast ratio with another color
$otherColor = new Color(255, 255, 255);
$contrastRatio = $color->getContrastRatio($otherColor);

// Luminance
$luminance = $color->getLuminance();
```

## Color Extraction

Color Palette PHP uses advanced algorithms to extract dominant colors from images:

### Extraction Process

1. **Image Loading**: Support for various image formats
2. **Color Sampling**: Efficient pixel sampling
3. **Color Quantization**: Reducing colors to a manageable palette
4. **Color Clustering**: Grouping similar colors
5. **Palette Generation**: Creating the final color palette

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Load image (static method)
$image = ImageFactory::createFromPath('image.jpg');

// Create extractor
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd'); // or 'imagick'

// Extract colors (deterministic - same image always produces same results)
$palette = $extractor->extract($image, 5);
```

## Theme Generation

Create harmonious color schemes using various color theory principles:

### Color Schemes

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = new Color(37, 99, 235);
$generator = new PaletteGenerator($baseColor);

// Generate different schemes
$analogous = $generator->analogous();      // Similar colors
$complementary = $generator->complementary(); // Opposite colors
$triadic = $generator->triadic();          // Three evenly spaced colors
$tetradic = $generator->tetradic();        // Four evenly spaced colors

// Generate a complete website theme
$theme = $generator->websiteTheme();
```

### Theme Components

- **Primary Color**: Main brand color
- **Secondary Color**: Supporting color
- **Accent Color**: Highlight color
- **Background Color**: Page background
- **Surface Colors**: UI element backgrounds
- **Text Colors**: Automatically selected for contrast

## Best Practices

### Performance Optimization

1. **Image Processing**
   - Use appropriate image sizes
   - Choose the right backend (GD vs ImageMagick)
   - Implement caching for extracted palettes

2. **Color Manipulation**
   - Cache computed values
   - Batch color transformations
   - Use appropriate color spaces for specific operations

### Accessibility

1. **Contrast Ratios**
   ```php
   // Ensure text is readable
   $backgroundColor = $palette->getColors()[0];
   $textColor = $palette->getSuggestedTextColor($backgroundColor);
   
   // Check contrast ratio
   $contrastRatio = $backgroundColor->getContrastRatio($textColor);
   $isAccessible = $contrastRatio >= 4.5; // WCAG AA standard
   ```

2. **Color Blindness**
   - Test palettes with color blindness simulators
   - Use patterns or shapes alongside colors
   - Ensure sufficient contrast between adjacent colors

### Error Handling

```php
use Farzai\ColorPalette\Exceptions\ImageException;
use Farzai\ColorPalette\Exceptions\ColorException;

try {
    $image = $imageFactory->createFromPath('image.jpg');
    $palette = $extractor->extract($image);
} catch (ImageException $e) {
    // Handle image-related errors
    log_error('Image processing failed: ' . $e->getMessage());
} catch (ColorException $e) {
    // Handle color-related errors
    log_error('Color processing failed: ' . $e->getMessage());
}
```

## Factory Pattern Implementation

Color Palette PHP uses the Factory pattern to provide a flexible and maintainable way to create objects.

### Image Loading

```php
use Farzai\ColorPalette\ImageFactory;

// Static factory method for creating images
$image = ImageFactory::createFromPath('path/to/image.jpg'); // Uses GD by default
$image = ImageFactory::createFromPath('path/to/image.jpg', 'imagick'); // Use ImageMagick
```

### Color Extraction

```php
use Farzai\ColorPalette\ColorExtractorFactory;

$factory = new ColorExtractorFactory();

// Choose backend based on your needs
$gdExtractor = $factory->make('gd');       // GD backend (default)
$imagickExtractor = $factory->make('imagick'); // ImageMagick backend
```

### Benefits of Factory Pattern

- **Encapsulation**: Object creation logic is centralized
- **Backend Selection**: Switch between GD and ImageMagick at runtime
- **Extension Points**: Easy to add new backends
- **Testability**: Simplified dependency injection for testing

For more details, see the [API Documentation](api/).

## Further Reading

<div class="further-reading">
  <div class="resource">
    <h3>📚 Color Theory</h3>
    <p>Learn more about color theory and its application in design:</p>
    <ul>
      <li><a href="https://www.colormatters.com/color-and-design/basic-color-theory">Basic Color Theory</a></li>
      <li><a href="https://www.interaction-design.org/literature/topics/color-theory">Color Theory in Design</a></li>
    </ul>
  </div>
  
  <div class="resource">
    <h3>🎨 Color Accessibility</h3>
    <p>Understand color accessibility guidelines:</p>
    <ul>
      <li><a href="https://www.w3.org/WAI/WCAG21/Understanding/contrast-minimum.html">WCAG Color Contrast Guidelines</a></li>
      <li><a href="https://webaim.org/articles/contrast/">WebAIM: Color Contrast</a></li>
    </ul>
  </div>
  
  <div class="resource">
    <h3>🔧 Technical Resources</h3>
    <p>Dive deeper into color processing:</p>
    <ul>
      <li><a href="https://www.php.net/manual/en/book.image.php">PHP GD Documentation</a></li>
      <li><a href="https://www.php.net/manual/en/book.imagick.php">PHP ImageMagick Documentation</a></li>
    </ul>
  </div>
</div>