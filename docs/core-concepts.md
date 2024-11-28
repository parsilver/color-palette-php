# Core Concepts

This guide explains the core concepts of color theory and how they are implemented in Color Palette PHP.

## Color Spaces

### RGB (Red, Green, Blue)
RGB is an additive color model where red, green, and blue light are added together to create colors.

```php
use Farzai\ColorPalette\Color;

// Create a color using RGB values (0-255)
$color = new Color(37, 99, 235);

// Get RGB components
$red = $color->getRed();    // 37
$green = $color->getGreen(); // 99
$blue = $color->getBlue();   // 235
```

### HSL (Hue, Saturation, Lightness)
HSL represents colors using hue (color type), saturation (color intensity), and lightness (brightness).

```php
// Convert from RGB to HSL
$hsl = $color->toHsl();
// Returns: ['h' => 220, 's' => 83, 'l' => 53]

// Create a color from HSL
$color = Color::fromHsl(220, 83, 53);
```

### HSV (Hue, Saturation, Value)
Similar to HSL but uses value instead of lightness for brightness control.

```php
// Convert to HSV
$hsv = $color->toHsv();
// Returns: ['h' => 220, 's' => 84, 'v' => 92]

// Create from HSV
$color = Color::fromHsv(220, 84, 92);
```

### CMYK (Cyan, Magenta, Yellow, Key)
CMYK is a subtractive color model used in printing.

```php
// Convert to CMYK
$cmyk = $color->toCmyk();
// Returns: ['c' => 84, 'm' => 58, 'y' => 0, 'k' => 8]

// Create from CMYK
$color = Color::fromCmyk(84, 58, 0, 8);
```

### LAB (Lightness, A, B)
LAB color space is designed to be perceptually uniform.

```php
// Convert to LAB
$lab = $color->toLab();
// Returns: ['l' => 45, 'a' => 15, 'b' => -67]

// Create from LAB
$color = Color::fromLab(45, 15, -67);
```

## Color Manipulation

### Lightness Adjustments
```php
// Lighten by 20%
$lighter = $color->lighten(20);

// Darken by 20%
$darker = $color->darken(20);
```

### Saturation Adjustments
```php
// Increase saturation by 20%
$saturated = $color->saturate(20);

// Decrease saturation by 20%
$desaturated = $color->desaturate(20);
```

### Hue Rotation
```php
// Rotate hue by 45 degrees
$rotated = $color->rotate(45);
```

## Color Analysis

### Brightness and Contrast
```php
// Get color brightness (0-1)
$brightness = $color->getBrightness();

// Check if color is light or dark
$isLight = $color->isLight();
$isDark = $color->isDark();

// Get contrast ratio with another color
$contrastRatio = $color->getContrastRatio($otherColor);
```

### Luminance
```php
// Get relative luminance (used for contrast calculations)
$luminance = $color->getLuminance();
```

## Theme Generation

### Creating Color Palettes
```php
use Farzai\ColorPalette\ColorPalette;

// Create a palette from an array of colors
$palette = new ColorPalette([$color1, $color2, $color3]);

// Get suggested text color for a background
$textColor = $palette->getSuggestedTextColor($backgroundColor);

// Get suggested surface colors
$surfaceColors = $palette->getSuggestedSurfaceColors();
```

### Theme Generation
```php
use Farzai\ColorPalette\Theme;
use Farzai\ColorPalette\ThemeGenerator;

// Create a theme from a base color
$generator = new ThemeGenerator();
$theme = $generator->generate($baseColor);

// Access theme colors
$primary = $theme->getPrimary();
$secondary = $theme->getSecondary();
$background = $theme->getBackground();
$surface = $theme->getSurface();
```

## Image Color Extraction

### Using Different Backends
```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

// Create an image instance
$imageFactory = new ImageFactory();
$image = $imageFactory->createFromPath('path/to/image.jpg');

// Create a color extractor (GD or Imagick)
$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->create('gd'); // or 'imagick'

// Extract colors
$colors = $extractor->extract($image);
```

## Best Practices

1. **Color Accessibility**
   - Always check contrast ratios for text colors
   - Use `getSuggestedTextColor()` for readable text
   - Ensure color combinations meet WCAG guidelines

2. **Performance**
   - Cache extracted colors for frequently used images
   - Use the appropriate color space for your needs
   - Consider using GD for better performance

3. **Color Space Selection**
   - Use RGB for screen display
   - Use CMYK for print applications
   - Use HSL/HSV for intuitive color manipulation
   - Use LAB for perceptual color differences

4. **Theme Generation**
   - Start with a base color that represents your brand
   - Use color theory principles for harmony
   - Consider dark mode alternatives
   - Test themes across different devices and lighting conditions