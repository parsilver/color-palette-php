# ColorPalette Class API Reference

The `ColorPalette` class represents a collection of colors and provides methods for color manipulation and analysis.

## Class Synopsis

```php
namespace Farzai\ColorPalette;

class ColorPalette implements ArrayAccess, ColorPaletteInterface, Countable
{
    // Constructor
    public function __construct(array $colors)
    
    // Basic Operations
    public function getColors(): array
    public function count(): int
    public function isEmpty(): bool
    public function first(): ?ColorInterface
    public function last(): ?ColorInterface
    
    // Color Analysis
    public function getDominantColor(): ?ColorInterface
    public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface
    public function getSuggestedSurfaceColors(): array
    public function findSimilarColors(ColorInterface $targetColor, int $limit = 5): array
    
    // Color Manipulation
    public function sort(callable $callback = null): self
    public function filter(callable $callback): self
    public function map(callable $callback): self
    public function merge(ColorPaletteInterface $palette): self
    
    // ArrayAccess Implementation
    public function offsetExists($offset): bool
    public function offsetGet($offset): ?ColorInterface
    public function offsetSet($offset, $value): void
    public function offsetUnset($offset): void
}
```

## Constructor

### __construct()

Creates a new ColorPalette instance with an array of colors.

```php
public function __construct(array $colors)
```

#### Parameters
- `$colors` (array): Array of ColorInterface instances

#### Example
```php
use Farzai\ColorPalette\Color;

$colors = [
    Color::fromHex('#2196f3'),
    Color::fromHex('#f44336'),
    Color::fromHex('#4caf50')
];
$palette = new ColorPalette($colors);
```

## Basic Operations

### getColors()

Returns all colors in the palette.

```php
public function getColors(): array
```

#### Returns
- (array): Array of ColorInterface instances

#### Example
```php
$colors = $palette->getColors();
foreach ($colors as $color) {
    echo $color->toHex() . "\n";
}
```

### count()

Returns the number of colors in the palette.

```php
public function count(): int
```

#### Returns
- (int): Number of colors

### isEmpty()

Checks if the palette is empty.

```php
public function isEmpty(): bool
```

#### Returns
- (bool): True if the palette contains no colors

### first()

Gets the first color in the palette.

```php
public function first(): ?ColorInterface
```

#### Returns
- (?ColorInterface): First color or null if palette is empty

### last()

Gets the last color in the palette.

```php
public function last(): ?ColorInterface
```

#### Returns
- (?ColorInterface): Last color or null if palette is empty

## Color Analysis

### getDominantColor()

Returns the most dominant color in the palette.

```php
public function getDominantColor(): ?ColorInterface
```

#### Returns
- (?ColorInterface): The dominant color or null if palette is empty

### getSuggestedTextColor()

Suggests a text color that provides good contrast with the given background color.

```php
public function getSuggestedTextColor(ColorInterface $backgroundColor): ColorInterface
```

#### Parameters
- `$backgroundColor` (ColorInterface): The background color to compare against

#### Returns
- (ColorInterface): A color suitable for text

#### Example
```php
$backgroundColor = Color::fromHex('#2196f3');
$textColor = $palette->getSuggestedTextColor($backgroundColor);
```

### getSuggestedSurfaceColors()

Generates a set of surface colors that complement the palette.

```php
public function getSuggestedSurfaceColors(): array
```

#### Returns
- (array): Array of ColorInterface instances suitable for surface colors

### findSimilarColors()

Finds colors in the palette similar to the target color.

```php
public function findSimilarColors(ColorInterface $targetColor, int $limit = 5): array
```

#### Parameters
- `$targetColor` (ColorInterface): The color to compare against
- `$limit` (int): Maximum number of similar colors to return

#### Returns
- (array): Array of similar ColorInterface instances

## Color Manipulation

### sort()

Sorts the colors in the palette.

```php
public function sort(callable $callback = null): self
```

#### Parameters
- `$callback` (callable|null): Custom sorting function

#### Returns
- (self): New sorted ColorPalette instance

#### Example
```php
// Sort by lightness
$sorted = $palette->sort(function($a, $b) {
    return $a->getLightness() <=> $b->getLightness();
});
```

### filter()

Filters colors in the palette.

```php
public function filter(callable $callback): self
```

#### Parameters
- `$callback` (callable): Filter function

#### Returns
- (self): New filtered ColorPalette instance

#### Example
```php
// Keep only light colors
$lightColors = $palette->filter(function($color) {
    return $color->isLight();
});
```

### map()

Transforms colors in the palette.

```php
public function map(callable $callback): self
```

#### Parameters
- `$callback` (callable): Transform function

#### Returns
- (self): New transformed ColorPalette instance

#### Example
```php
// Lighten all colors by 20%
$lightened = $palette->map(function($color) {
    return $color->lighten(20);
});
```

### merge()

Merges another palette into this one.

```php
public function merge(ColorPaletteInterface $palette): self
```

#### Parameters
- `$palette` (ColorPaletteInterface): Palette to merge

#### Returns
- (self): New merged ColorPalette instance

## ArrayAccess Implementation

The ColorPalette class implements ArrayAccess, allowing you to work with the palette like an array:

```php
// Access colors by index
$firstColor = $palette[0];

// Check if index exists
if (isset($palette[1])) {
    // Do something
}

// Set a color at index
$palette[2] = Color::fromHex('#000000');

// Remove a color
unset($palette[1]);
``` 