# Working with Color Spaces

## Navigation

- [Home](../../README.md)
- [Getting Started](../../getting-started.md)
- [Core Concepts](../../core-concepts.md)
- [API Documentation](../../api/README.md)

### Examples
- [Examples Home](../README.md)
- [Basic Examples](../basic/README.md)
- [Advanced Examples](../advanced/README.md)
- [Applications](../applications/README.md)
- [Integration](../integration/README.md)

---

## Color Space Basics

### Understanding Color Spaces

```php
use Farzai\ColorPalette\Color;

// Create a color in different spaces
$rgbColor = Color::fromRgb(255, 0, 0);        // RGB
$hslColor = Color::fromHsl(0, 100, 50);       // HSL
$hsvColor = Color::fromHsv(0, 100, 100);      // HSV
$cmykColor = Color::fromCmyk(0, 100, 100, 0); // CMYK
$labColor = Color::fromLab(53.24, 80.09, 67.20); // LAB
```

### Converting Between Spaces

```php
$color = Color::fromHex('#ff0000');

// Convert to different formats
$rgb = $color->toRgb();   // [255, 0, 0]
$hsl = $color->toHsl();   // [0, 100, 50]
$hsv = $color->toHsv();   // [0, 100, 100]
$cmyk = $color->toCmyk(); // [0, 100, 100, 0]
$lab = $color->toLab();   // [53.24, 80.09, 67.20]
```

## Working with RGB Space

### RGB Components

```php
$color = Color::fromRgb(255, 0, 0);

// Access components
$red = $color->getRed();     // 255
$green = $color->getGreen(); // 0
$blue = $color->getBlue();   // 0

// Modify components
$newColor = $color
    ->withRed(128)    // Modify red
    ->withGreen(50)   // Modify green
    ->withBlue(50);   // Modify blue
```

### RGB Color Mixing

```php
$red = Color::fromRgb(255, 0, 0);
$blue = Color::fromRgb(0, 0, 255);

// Mix in RGB space
$purple = $red->mixRgb($blue, 0.5); // Equal mix
$redPurple = $red->mixRgb($blue, 0.25); // 25% blue
```

## Working with HSL Space

### HSL Components

```php
$color = Color::fromHsl(0, 100, 50);

// Access components
$hue = $color->getHue();               // 0
$saturation = $color->getSaturation(); // 100
$lightness = $color->getLightness();   // 50

// Modify components
$newColor = $color
    ->withHue(180)         // Cyan
    ->withSaturation(75)   // Less saturated
    ->withLightness(75);   // Lighter
```

### HSL Color Adjustments

```php
$color = Color::fromHsl(0, 100, 50);

// Rotate hue
$complementary = $color->rotateHue(180);
$triadic1 = $color->rotateHue(120);
$triadic2 = $color->rotateHue(240);

// Adjust saturation and lightness
$muted = $color->adjustSaturation(-20);
$brighter = $color->adjustLightness(20);
```

## Working with HSV Space

### HSV Components

```php
$color = Color::fromHsv(0, 100, 100);

// Access components
$hue = $color->getHueHsv();        // 0
$saturation = $color->getSaturationHsv(); // 100
$value = $color->getValue();       // 100

// Create variations
$darker = $color->withValue(80);
$desaturated = $color->withSaturationHsv(50);
```

## Working with CMYK Space

### CMYK Components

```php
$color = Color::fromCmyk(0, 100, 100, 0);

// Access components
$cyan = $color->getCyan();     // 0
$magenta = $color->getMagenta(); // 100
$yellow = $color->getYellow();   // 100
$key = $color->getKey();       // 0

// Create variations
$newColor = $color
    ->withCyan(20)
    ->withMagenta(80)
    ->withYellow(80)
    ->withKey(10);
```

## Working with LAB Space

### LAB Components

```php
$color = Color::fromLab(53.24, 80.09, 67.20);

// Access components
$lightness = $color->getLightness(); // 53.24
$a = $color->getA();                // 80.09
$b = $color->getB();                // 67.20

// Create variations
$newColor = $color
    ->withLightness(60)
    ->withA(70)
    ->withB(60);
```

## Color Space Utilities

### Gamma Correction

```php
$color = Color::fromRgb(255, 0, 0);

// Apply gamma correction
$corrected = $color->applyGamma(2.2);
```

### Color Space Interpolation

```php
$color1 = Color::fromRgb(255, 0, 0);
$color2 = Color::fromRgb(0, 0, 255);

// Interpolate in different spaces
$rgbMix = $color1->interpolateRgb($color2, 0.5);
$hslMix = $color1->interpolateHsl($color2, 0.5);
$labMix = $color1->interpolateLab($color2, 0.5);
```

## Best Practices

1. **Choose the Right Color Space**
   - Use RGB for screen display
   - Use CMYK for print
   - Use HSL for intuitive color adjustments
   - Use LAB for perceptual color operations

2. **Color Space Conversion**
   - Be aware of gamut limitations
   - Handle rounding errors appropriately
   - Cache converted values when doing multiple operations

3. **Performance Optimization**
   ```php
   // Cache converted values
   $color = Color::fromHex('#ff0000');
   $hsl = $color->toHsl(); // Cache this if using multiple times
   
   // Batch conversions
   $colors = array_map(function($hex) {
       return Color::fromHex($hex)->toHsl();
   }, $hexColors);
   ```

4. **Error Handling**
   ```php
   try {
       // Handle out-of-gamut colors
       $color = Color::fromRgb(300, 0, 0);
   } catch (\InvalidArgumentException $e) {
       // Handle invalid color values
   }
   ``` 