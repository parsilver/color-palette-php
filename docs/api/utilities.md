# Utilities

## Navigation

- [Home](../README.md)
- [Getting Started](../getting-started.md)
- [Core Concepts](../core-concepts.md)
- [Examples](../examples/README.md)

### API Documentation
- [API Home](./README.md)
- [Color Manipulation](./color-manipulation.md)
- [Palette Generation](./palette-generation.md)
- [Color Schemes](./color-schemes.md)
- [Color Spaces](./color-spaces.md)
- [Utilities](./utilities.md)

---

This section covers utility functions and helper methods available in the library.

## Color Validation

### Hex Color Validation

```php
use Farzai\ColorPalette\Utils\ColorValidator;

// Validate hex color
$isValid = ColorValidator::isValidHex('#ff0000'); // true
$isValid = ColorValidator::isValidHex('invalid'); // false

// Validate with alpha channel
$isValid = ColorValidator::isValidHex('#ff0000ff'); // true
```

### RGB Color Validation

```php
// Validate RGB values
$isValid = ColorValidator::isValidRgb(255, 0, 0); // true
$isValid = ColorValidator::isValidRgb(300, 0, 0); // false

// Validate RGB string
$isValid = ColorValidator::isValidRgbString('rgb(255, 0, 0)'); // true
```

## Color Formatting

### String Formatting

```php
use Farzai\ColorPalette\Utils\ColorFormatter;

// Format to hex
$hex = ColorFormatter::toHex(255, 0, 0); // '#ff0000'

// Format to RGB string
$rgb = ColorFormatter::toRgbString(255, 0, 0); // 'rgb(255, 0, 0)'

// Format to RGBA string
$rgba = ColorFormatter::toRgbaString(255, 0, 0, 0.5); // 'rgba(255, 0, 0, 0.5)'
```

### Value Normalization

```php
// Normalize RGB values (0-255 to 0-1)
$normalized = ColorFormatter::normalizeRgb(255, 0, 0); // [1, 0, 0]

// Normalize HSL values
$normalized = ColorFormatter::normalizeHsl(360, 100, 50); // [1, 1, 0.5]
```

## Color Math

### Color Calculations

```php
use Farzai\ColorPalette\Utils\ColorMath;

// Calculate relative luminance
$luminance = ColorMath::relativeLuminance(255, 0, 0);

// Calculate contrast ratio
$contrast = ColorMath::contrastRatio($color1, $color2);

// Calculate color difference (Delta E)
$difference = ColorMath::deltaE($color1, $color2);
```

### Color Interpolation

```php
// Linear interpolation between colors
$mixed = ColorMath::lerp($color1, $color2, 0.5);

// Bezier interpolation between multiple colors
$colors = [$color1, $color2, $color3];
$result = ColorMath::bezierInterpolation($colors, 0.5);
```

## Image Processing

### Color Extraction

```php
use Farzai\ColorPalette\Utils\ImageProcessor;

// Extract dominant colors from image
$colors = ImageProcessor::extractColors('path/to/image.jpg', 5);

// Extract colors with options
$colors = ImageProcessor::extractColors('path/to/image.jpg', [
    'count' => 5,
    'quality' => 10,
    'area' => [0, 0, 100, 100], // crop area
]);
```

### Color Quantization

```php
// Reduce number of colors in image
$reducedColors = ImageProcessor::quantizeColors($colors, 5);

// With specific algorithm
$reducedColors = ImageProcessor::quantizeColors($colors, [
    'count' => 5,
    'algorithm' => 'median-cut'
]);
```

## File Operations

### Color Import/Export

```php
use Farzai\ColorPalette\Utils\FileHandler;

// Export colors to file
FileHandler::exportColors($colors, 'palette.json');

// Import colors from file
$colors = FileHandler::importColors('palette.json');

// Export as CSS variables
FileHandler::exportAsCss($colors, 'colors.css', [
    'prefix' => '--color-'
]);
```

## Accessibility Utilities

### WCAG Compliance

```php
use Farzai\ColorPalette\Utils\AccessibilityChecker;

// Check WCAG contrast compliance
$isCompliant = AccessibilityChecker::meetsContrastGuidelines(
    $backgroundColor,
    $textColor,
    'AA'
);

// Get minimum required contrast
$minContrast = AccessibilityChecker::getMinimumContrast('AAA', 'large');
```

## Error Handling

```php
use Farzai\ColorPalette\Utils\ErrorHandler;

try {
    // Your color operations
} catch (\Exception $e) {
    // Log error with context
    ErrorHandler::logError($e, [
        'operation' => 'color_conversion',
        'input' => $inputColor
    ]);
}
``` 