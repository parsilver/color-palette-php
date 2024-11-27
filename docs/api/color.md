# Color Class API Reference

The `Color` class is a fundamental component that represents and manipulates individual colors.

## Class Synopsis

```php
namespace Farzai\ColorPalette;

class Color implements ColorInterface
{
    // Factory Methods
    public static function fromHex(string $hex): self
    public static function fromRgb(int $red, int $green, int $blue): self
    public static function fromHsl(float $hue, float $saturation, float $lightness): self
    
    // Color Components
    public function getRed(): int
    public function getGreen(): int
    public function getBlue(): int
    public function getHue(): float
    public function getSaturation(): float
    public function getLightness(): float
    
    // Format Conversion
    public function toHex(): string
    public function toRgb(): string
    public function toHsl(): string
    
    // Color Operations
    public function lighten(float $amount): self
    public function darken(float $amount): self
    public function saturate(float $amount): self
    public function desaturate(float $amount): self
    public function mix(ColorInterface $color, float $weight = 0.5): self
    
    // Color Analysis
    public function getContrast(ColorInterface $color): float
    public function getDistance(ColorInterface $color): float
    public function isLight(): bool
    public function isDark(): bool
}
```

## Factory Methods

### fromHex()

Creates a new Color instance from a hexadecimal color string.

```php
public static function fromHex(string $hex): self
```

#### Parameters
- `$hex` (string): The hexadecimal color code (e.g., '#FF0000' or 'FF0000')

#### Example
```php
$color = Color::fromHex('#2196f3');
```

### fromRgb()

Creates a new Color instance from RGB values.

```php
public static function fromRgb(int $red, int $green, int $blue): self
```

#### Parameters
- `$red` (int): Red component (0-255)
- `$green` (int): Green component (0-255)
- `$blue` (int): Blue component (0-255)

#### Example
```php
$color = Color::fromRgb(33, 150, 243);
```

### fromHsl()

Creates a new Color instance from HSL values.

```php
public static function fromHsl(float $hue, float $saturation, float $lightness): self
```

#### Parameters
- `$hue` (float): Hue component (0-360)
- `$saturation` (float): Saturation component (0-100)
- `$lightness` (float): Lightness component (0-100)

#### Example
```php
$color = Color::fromHsl(207, 90, 54);
```

## Color Components

### getRed()

Gets the red component of the color.

```php
public function getRed(): int
```

#### Returns
- (int): Red component value (0-255)

### getGreen()

Gets the green component of the color.

```php
public function getGreen(): int
```

#### Returns
- (int): Green component value (0-255)

### getBlue()

Gets the blue component of the color.

```php
public function getBlue(): int
```

#### Returns
- (int): Blue component value (0-255)

## Format Conversion

### toHex()

Converts the color to hexadecimal format.

```php
public function toHex(): string
```

#### Returns
- (string): Hexadecimal color code (e.g., '#2196f3')

### toRgb()

Converts the color to RGB format.

```php
public function toRgb(): string
```

#### Returns
- (string): RGB color string (e.g., 'rgb(33, 150, 243)')

### toHsl()

Converts the color to HSL format.

```php
public function toHsl(): string
```

#### Returns
- (string): HSL color string (e.g., 'hsl(207, 90%, 54%)')

## Color Operations

### lighten()

Creates a lighter version of the color.

```php
public function lighten(float $amount): self
```

#### Parameters
- `$amount` (float): Amount to lighten (0-100)

#### Returns
- (self): A new Color instance

### darken()

Creates a darker version of the color.

```php
public function darken(float $amount): self
```

#### Parameters
- `$amount` (float): Amount to darken (0-100)

#### Returns
- (self): A new Color instance

### mix()

Mixes the color with another color.

```php
public function mix(ColorInterface $color, float $weight = 0.5): self
```

#### Parameters
- `$color` (ColorInterface): Color to mix with
- `$weight` (float): Weight of the mixing (0-1)

#### Returns
- (self): A new Color instance

## Color Analysis

### getContrast()

Calculates the contrast ratio between this color and another color.

```php
public function getContrast(ColorInterface $color): float
```

#### Parameters
- `$color` (ColorInterface): Color to compare with

#### Returns
- (float): Contrast ratio (1-21)

### isLight()

Determines if the color is considered light.

```php
public function isLight(): bool
```

#### Returns
- (bool): True if the color is light, false otherwise

### isDark()

Determines if the color is considered dark.

```php
public function isDark(): bool
```

#### Returns
- (bool): True if the color is dark, false otherwise 