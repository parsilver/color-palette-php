# Color Class

The `Color` class is the fundamental building block for color manipulation in the library. It implements the `ColorInterface` and provides methods for creating, manipulating, and converting colors.

## Class Synopsis

```php
namespace Farzai\ColorPalette;

class Color implements ColorInterface
{
    public function __construct(int $red, int $green, int $blue);
    public static function fromHex(string $hex): self;
    public static function fromRgb(array $rgb): self;
    public static function fromHsl(array $hsl): self;
    
    // Color Components
    public function getRed(): int;
    public function getGreen(): int;
    public function getBlue(): int;
    public function getLightness(): float;
    
    // Color Conversions
    public function toHex(): string;
    public function toRgb(): array;
    public function toHsl(): array;
    
    // Color Analysis
    public function isLight(): bool;
    public function isDark(): bool;
    public function getBrightness(): float;
    public function getContrastRatio(ColorInterface $color): float;
    
    // Color Manipulation
    public function lighten(float $amount): self;
    public function darken(float $amount): self;
    public function saturate(float $amount): self;
    public function desaturate(float $amount): self;
}
```

## Constructor

### `__construct(int $red, int $green, int $blue)`

Creates a new Color instance from RGB values.

- **Parameters:**
  - `$red` (int) - Red component (0-255)
  - `$green` (int) - Green component (0-255)
  - `$blue` (int) - Blue component (0-255)
- **Throws:** `InvalidArgumentException` if values are out of range

```php
$color = new Color(33, 150, 243);
```

## Static Constructors

### `fromHex(string $hex): Color`

Creates a Color instance from a hexadecimal color string.

- **Parameters:**
  - `$hex` (string) - Hex color code (e.g., '#2196f3' or '2196f3')
- **Returns:** Color
- **Throws:** `InvalidArgumentException` if hex string is invalid

```php
$color = Color::fromHex('#2196f3');
```

### `fromRgb(array $rgb): Color`

Creates a Color instance from an RGB array.

- **Parameters:**
  - `$rgb` (array) - Array with 'r', 'g', 'b' keys (values 0-255)
- **Returns:** Color
- **Throws:** `InvalidArgumentException` if values are missing or invalid

```php
$color = Color::fromRgb(['r' => 33, 'g' => 150, 'b' => 243]);
```

### `fromHsl(array $hsl): Color`

Creates a Color instance from an HSL array.

- **Parameters:**
  - `$hsl` (array) - Array with 'h' (0-360), 's' (0-100), 'l' (0-100) keys
- **Returns:** Color
- **Throws:** `InvalidArgumentException` if values are missing or invalid

```php
$color = Color::fromHsl(['h' => 207, 's' => 90, 'l' => 54]);
```

## Color Components

### `getRed(): int`
Returns the red component (0-255).

### `getGreen(): int`
Returns the green component (0-255).

### `getBlue(): int`
Returns the blue component (0-255).

### `getLightness(): float`
Returns the lightness value (0-100) from HSL representation.

## Color Conversions

### `toHex(): string`
Converts the color to hexadecimal format.
- **Returns:** string - Hex color code (e.g., '#2196f3')

### `toRgb(): array`
Converts the color to RGB format.
- **Returns:** array - ['r' => int, 'g' => int, 'b' => int]

### `toHsl(): array`
Converts the color to HSL format.
- **Returns:** array - ['h' => float, 's' => float, 'l' => float]

## Color Analysis

### `isLight(): bool`
Determines if the color is considered light.
- **Returns:** bool - true if color is light

### `isDark(): bool`
Determines if the color is considered dark.
- **Returns:** bool - true if color is dark

### `getBrightness(): float`
Calculates the perceived brightness of the color.
- **Returns:** float - Brightness value (0-255)

### `getContrastRatio(ColorInterface $color): float`
Calculates the contrast ratio between this color and another color.
- **Parameters:**
  - `$color` (ColorInterface) - Color to compare against
- **Returns:** float - Contrast ratio (1-21)

## Color Manipulation

### `lighten(float $amount): Color`
Creates a lighter version of the color.
- **Parameters:**
  - `$amount` (float) - Amount to lighten (0-100)
- **Returns:** Color - New Color instance

### `darken(float $amount): Color`
Creates a darker version of the color.
- **Parameters:**
  - `$amount` (float) - Amount to darken (0-100)
- **Returns:** Color - New Color instance

### `saturate(float $amount): Color`
Increases the saturation of the color.
- **Parameters:**
  - `$amount` (float) - Amount to increase saturation (0-100)
- **Returns:** Color - New Color instance

### `desaturate(float $amount): Color`
Decreases the saturation of the color.
- **Parameters:**
  - `$amount` (float) - Amount to decrease saturation (0-100)
- **Returns:** Color - New Color instance

## Examples

### Basic Usage
```php
// Create a color
$color = new Color(33, 150, 243);

// Get components
echo $color->getRed();   // 33
echo $color->getGreen(); // 150
echo $color->getBlue();  // 243

// Convert formats
echo $color->toHex();    // '#2196f3'
$rgb = $color->toRgb();  // ['r' => 33, 'g' => 150, 'b' => 243]
$hsl = $color->toHsl();  // ['h' => 207, 's' => 90, 'l' => 54]
```

### Color Analysis
```php
// Check brightness
if ($color->isLight()) {
    echo "This is a light color";
}

// Get contrast ratio
$otherColor = new Color(255, 255, 255);
$ratio = $color->getContrastRatio($otherColor);
echo "Contrast ratio: {$ratio}";
```

### Color Manipulation
```php
// Create variations
$lighter = $color->lighten(20);
$darker = $color->darken(20);
$moreSaturated = $color->saturate(20);
$lessSaturated = $color->desaturate(20);
```