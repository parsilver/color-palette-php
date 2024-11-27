# Theme Class API Reference

The `Theme` class represents a structured collection of colors designed for use in user interfaces and design systems.

## Class Synopsis

```php
namespace Farzai\ColorPalette;

class Theme
{
    // Factory Methods
    public static function fromColors(array $colors): self
    public static function fromPalette(ColorPaletteInterface $palette): self
    
    // Color Getters
    public function getPrimary(): ColorInterface
    public function getSecondary(): ColorInterface
    public function getAccent(): ColorInterface
    public function getBackground(): ColorInterface
    public function getSurface(): ColorInterface
    
    // Text Colors
    public function getOnPrimary(): ColorInterface
    public function getOnSecondary(): ColorInterface
    public function getOnBackground(): ColorInterface
    public function getOnSurface(): ColorInterface
    
    // Theme Variants
    public function getLightVariant(): self
    public function getDarkVariant(): self
    
    // Theme Operations
    public function toArray(): array
    public function toCssVariables(string $prefix = ''): string
    public function merge(Theme $theme): self
}
```

## Factory Methods

### fromColors()

Creates a new Theme instance from an array of colors.

```php
public static function fromColors(array $colors): self
```

#### Parameters
- `$colors` (array): Associative array of colors with keys:
  - primary
  - secondary
  - accent
  - background
  - surface

#### Example
```php
$theme = Theme::fromColors([
    'primary' => Color::fromHex('#2196f3'),
    'secondary' => Color::fromHex('#f44336'),
    'accent' => Color::fromHex('#4caf50'),
    'background' => Color::fromHex('#ffffff'),
    'surface' => Color::fromHex('#f5f5f5')
]);
```

### fromPalette()

Creates a new Theme instance from a ColorPalette.

```php
public static function fromPalette(ColorPaletteInterface $palette): self
```

#### Parameters
- `$palette` (ColorPaletteInterface): Color palette to generate theme from

#### Example
```php
$theme = Theme::fromPalette($palette);
```

## Color Getters

### getPrimary()

Gets the primary color of the theme.

```php
public function getPrimary(): ColorInterface
```

#### Returns
- (ColorInterface): Primary color

### getSecondary()

Gets the secondary color of the theme.

```php
public function getSecondary(): ColorInterface
```

#### Returns
- (ColorInterface): Secondary color

### getAccent()

Gets the accent color of the theme.

```php
public function getAccent(): ColorInterface
```

#### Returns
- (ColorInterface): Accent color

### getBackground()

Gets the background color of the theme.

```php
public function getBackground(): ColorInterface
```

#### Returns
- (ColorInterface): Background color

### getSurface()

Gets the surface color of the theme.

```php
public function getSurface(): ColorInterface
```

#### Returns
- (ColorInterface): Surface color

## Text Colors

### getOnPrimary()

Gets the text color for use on primary color backgrounds.

```php
public function getOnPrimary(): ColorInterface
```

#### Returns
- (ColorInterface): Text color for primary backgrounds

### getOnSecondary()

Gets the text color for use on secondary color backgrounds.

```php
public function getOnSecondary(): ColorInterface
```

#### Returns
- (ColorInterface): Text color for secondary backgrounds

### getOnBackground()

Gets the text color for use on the background color.

```php
public function getOnBackground(): ColorInterface
```

#### Returns
- (ColorInterface): Text color for the background

### getOnSurface()

Gets the text color for use on surface color backgrounds.

```php
public function getOnSurface(): ColorInterface
```

#### Returns
- (ColorInterface): Text color for surface backgrounds

## Theme Variants

### getLightVariant()

Creates a light variant of the theme.

```php
public function getLightVariant(): self
```

#### Returns
- (self): New Theme instance with light colors

### getDarkVariant()

Creates a dark variant of the theme.

```php
public function getDarkVariant(): self
```

#### Returns
- (self): New Theme instance with dark colors

## Theme Operations

### toArray()

Converts the theme to an array representation.

```php
public function toArray(): array
```

#### Returns
- (array): Associative array of theme colors

#### Example
```php
$colors = $theme->toArray();
/*
[
    'primary' => Color,
    'secondary' => Color,
    'accent' => Color,
    'background' => Color,
    'surface' => Color,
    'on_primary' => Color,
    'on_secondary' => Color,
    'on_background' => Color,
    'on_surface' => Color
]
*/
```

### toCssVariables()

Generates CSS custom properties (variables) from the theme.

```php
public function toCssVariables(string $prefix = ''): string
```

#### Parameters
- `$prefix` (string): Optional prefix for CSS variable names

#### Returns
- (string): CSS custom properties definition

#### Example
```php
echo $theme->toCssVariables('theme-');
/*
:root {
    --theme-primary: #2196f3;
    --theme-secondary: #f44336;
    --theme-accent: #4caf50;
    --theme-background: #ffffff;
    --theme-surface: #f5f5f5;
    --theme-on-primary: #ffffff;
    --theme-on-secondary: #ffffff;
    --theme-on-background: #000000;
    --theme-on-surface: #000000;
}
*/
```

### merge()

Merges another theme into this one.

```php
public function merge(Theme $theme): self
```

#### Parameters
- `$theme` (Theme): Theme to merge with

#### Returns
- (self): New merged Theme instance

#### Example
```php
$customTheme = Theme::fromColors([
    'primary' => Color::fromHex('#000000'),
    // ... other colors
]);

$mergedTheme = $theme->merge($customTheme);
``` 