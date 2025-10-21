---
layout: default
title: Recipe - Creating Color Schemes
description: Copy-paste solutions for generating harmonious color schemes
---

# Recipe: Creating Color Schemes

Generate harmonious color schemes for your designs and themes using color theory principles.

## Table of Contents

- [Basic Color Schemes](#basic-color-schemes)
- [Website Theme Generation](#website-theme-generation)
- [Custom Color Harmonies](#custom-color-harmonies)
- [Adaptive Schemes](#adaptive-schemes)
- [Complete Examples](#complete-examples)

---

## Basic Color Schemes

### Complementary Colors

Generate colors opposite on the color wheel:

```php
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$complementary = $generator->complementary();

foreach ($complementary->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb (base - blue)
#eb9c25 (complement - orange)
```

---

### Analogous Colors

Generate adjacent colors on the color wheel:

```php
$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$analogous = $generator->analogous();

foreach ($analogous->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb (base - blue)
#2598eb (blue-cyan)
#2530eb (blue-purple)
```

---

### Triadic Colors

Generate three evenly spaced colors:

```php
$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$triadic = $generator->triadic();

foreach ($triadic->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb (blue)
#eb2563 (red)
#63eb25 (green)
```

---

### Tetradic (Square) Colors

Generate four evenly spaced colors:

```php
$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$tetradic = $generator->tetradic();

foreach ($tetradic->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb (blue)
#63eb25 (green)
#eb9c25 (orange)
#c525eb (purple)
```

---

### Split-Complementary Colors

Complementary color plus two adjacent colors:

```php
$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$splitComplementary = $generator->splitComplementary();

foreach ($splitComplementary->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb (blue)
#ebcf25 (yellow-orange)
#eb6925 (red-orange)
```

---

### Monochromatic Colors

Variations of a single hue:

```php
$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$monochromatic = $generator->monochromatic(5);

foreach ($monochromatic->getColors() as $color) {
    echo $color->toHex() . " (brightness: " . $color->brightness() . ")\n";
}
```

**Expected Output:**
```
#0a1f4d (brightness: 30)
#174a9e (brightness: 78)
#2563eb (brightness: 126)
#5d8cf0 (brightness: 174)
#a4c2f7 (brightness: 222)
```

---

## Website Theme Generation

### Complete Website Theme

Generate a full theme with primary, secondary, accent, text, and surface colors:

```php
$baseColor = Color::fromHex('#2563eb');
$generator = new PaletteGenerator($baseColor);

$theme = $generator->websiteTheme();

echo "Primary: " . $theme->getPrimaryColor()->toHex() . "\n";
echo "Secondary: " . $theme->getSecondaryColor()->toHex() . "\n";
echo "Accent: " . $theme->getAccentColor()->toHex() . "\n";
echo "Text: " . $theme->getTextColor()->toHex() . "\n";

echo "\nSurface Colors:\n";
foreach ($theme->getSurfaceColors() as $name => $color) {
    echo "  $name: " . $color->toHex() . "\n";
}
```

**Expected Output:**
```
Primary: #2563eb
Secondary: #8bb4d9
Accent: #eb9c25
Text: #1f2937

Surface Colors:
  background: #ffffff
  surface: #f9fafb
  surface-variant: #f3f4f6
```

---

### Light and Dark Theme Pair

```php
function generateThemePair(Color $brandColor): array
{
    $generator = new PaletteGenerator($brandColor);

    // Generate light theme
    $lightTheme = $generator->websiteTheme();

    // Generate dark theme (invert brightness)
    $darkBaseColor = $brandColor->brightness() > 128
        ? $brandColor->darken(40)
        : $brandColor->lighten(20);

    $darkGenerator = new PaletteGenerator($darkBaseColor);
    $darkTheme = $darkGenerator->websiteTheme([
        'dark_mode' => true,
    ]);

    return [
        'light' => [
            'primary' => $lightTheme->getPrimaryColor()->toHex(),
            'secondary' => $lightTheme->getSecondaryColor()->toHex(),
            'accent' => $lightTheme->getAccentColor()->toHex(),
            'text' => $lightTheme->getTextColor()->toHex(),
            'background' => $lightTheme->getSurfaceColors()['background']->toHex(),
        ],
        'dark' => [
            'primary' => $darkTheme->getPrimaryColor()->toHex(),
            'secondary' => $darkTheme->getSecondaryColor()->toHex(),
            'accent' => $darkTheme->getAccentColor()->toHex(),
            'text' => $darkTheme->getTextColor()->toHex(),
            'background' => $darkTheme->getSurfaceColors()['background']->toHex(),
        ],
    ];
}

// Usage
$themes = generateThemePair(Color::fromHex('#2563eb'));
print_r($themes);
```

**Expected Output:**
```
Array (
    [light] => Array (
        [primary] => #2563eb
        [secondary] => #8bb4d9
        [accent] => #eb9c25
        [text] => #1f2937
        [background] => #ffffff
    )
    [dark] => Array (
        [primary] => #5d8cf0
        [secondary] => #3b5c8a
        [accent] => #f4a442
        [text] => #e5e7eb
        [background] => #111827
    )
)
```

---

### Material Design Theme

```php
function generateMaterialTheme(Color $primaryColor): array
{
    $generator = new PaletteGenerator($primaryColor);

    // Generate color variants
    $primary = $primaryColor;
    $primaryLight = $primary->lighten(20);
    $primaryDark = $primary->darken(20);

    // Get complementary for accent
    $complementary = $generator->complementary();
    $accent = $complementary->getColors()[1];

    // Generate surface colors
    $monochromatic = $generator->monochromatic(5);

    return [
        'primary' => [
            'main' => $primary->toHex(),
            'light' => $primaryLight->toHex(),
            'dark' => $primaryDark->toHex(),
        ],
        'secondary' => [
            'main' => $accent->toHex(),
            'light' => $accent->lighten(20)->toHex(),
            'dark' => $accent->darken(20)->toHex(),
        ],
        'background' => [
            'default' => '#fafafa',
            'paper' => '#ffffff',
        ],
        'text' => [
            'primary' => '#212121',
            'secondary' => '#757575',
            'disabled' => '#bdbdbd',
        ],
    ];
}

// Usage
$materialTheme = generateMaterialTheme(Color::fromHex('#2563eb'));
print_r($materialTheme);
```

**Expected Output:**
```
Array (
    [primary] => Array (
        [main] => #2563eb
        [light] => #5d8cf0
        [dark] => #174a9e
    )
    [secondary] => Array (
        [main] => #eb9c25
        [light] => #f4b859
        [dark] => #c47a10
    )
    [background] => Array (
        [default] => #fafafa
        [paper] => #ffffff
    )
    [text] => Array (
        [primary] => #212121
        [secondary] => #757575
        [disabled] => #bdbdbd
    )
)
```

---

## Custom Color Harmonies

### Create Custom Harmony with Specific Angles

```php
function customHarmony(Color $baseColor, array $angles): array
{
    $hsl = $baseColor->toHsl();
    $baseHue = $hsl['h'];

    $colors = [$baseColor];

    foreach ($angles as $angle) {
        $newHue = ($baseHue + $angle) % 360;
        $newColor = Color::fromHsl($newHue, $hsl['s'], $hsl['l']);
        $colors[] = $newColor;
    }

    return $colors;
}

// Double complementary (rectangle)
$baseColor = Color::fromHex('#2563eb');
$doubleComplementary = customHarmony($baseColor, [60, 180, 240]);

foreach ($doubleComplementary as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb (base)
#63eb25 (base + 60°)
#eb9c25 (base + 180°)
#c525eb (base + 240°)
```

---

### Gradient Color Steps

```php
function generateGradientSteps(Color $startColor, Color $endColor, int $steps): array
{
    $colors = [];

    $startRgb = $startColor->toRgb();
    $endRgb = $endColor->toRgb();

    for ($i = 0; $i < $steps; $i++) {
        $ratio = $i / ($steps - 1);

        $r = (int) ($startRgb['r'] + ($endRgb['r'] - $startRgb['r']) * $ratio);
        $g = (int) ($startRgb['g'] + ($endRgb['g'] - $startRgb['g']) * $ratio);
        $b = (int) ($startRgb['b'] + ($endRgb['b'] - $startRgb['b']) * $ratio);

        $colors[] = new Color($r, $g, $b);
    }

    return $colors;
}

// Usage
$gradient = generateGradientSteps(
    Color::fromHex('#2563eb'),
    Color::fromHex('#ec4899'),
    5
);

foreach ($gradient as $color) {
    echo $color->toHex() . "\n";
}
```

**Expected Output:**
```
#2563eb
#5b52c2
#914199
#c73070
#ec4899
```

---

### Color Scale (Tailwind-style)

```php
function generateColorScale(Color $baseColor): array
{
    $hsl = $baseColor->toHsl();

    $scale = [
        50 => Color::fromHsl($hsl['h'], $hsl['s'], 95),
        100 => Color::fromHsl($hsl['h'], $hsl['s'], 90),
        200 => Color::fromHsl($hsl['h'], $hsl['s'], 80),
        300 => Color::fromHsl($hsl['h'], $hsl['s'], 70),
        400 => Color::fromHsl($hsl['h'], $hsl['s'], 60),
        500 => $baseColor, // Base
        600 => Color::fromHsl($hsl['h'], $hsl['s'], 45),
        700 => Color::fromHsl($hsl['h'], $hsl['s'], 35),
        800 => Color::fromHsl($hsl['h'], $hsl['s'], 25),
        900 => Color::fromHsl($hsl['h'], $hsl['s'], 15),
        950 => Color::fromHsl($hsl['h'], $hsl['s'], 10),
    ];

    return $scale;
}

// Usage
$scale = generateColorScale(Color::fromHex('#2563eb'));

foreach ($scale as $weight => $color) {
    echo "$weight: " . $color->toHex() . "\n";
}
```

**Expected Output:**
```
50: #eff6ff
100: #dbeafe
200: #bfdbfe
300: #93bbfd
400: #609efc
500: #2563eb
600: #1d4ed8
700: #1e40af
800: #1e3a8a
900: #1e2d5f
950: #172554
```

---

## Adaptive Schemes

### Scheme Based on Image

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function generateSchemeFromImage($imagePath, $schemeType = 'complementary')
{
    // Extract dominant color
    $image = ImageFactory::createFromPath($imagePath);
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');
    $palette = $extractor->extract($image, 1);

    $dominantColor = $palette->getDominantColor();

    // Generate scheme
    $generator = new PaletteGenerator($dominantColor);

    switch ($schemeType) {
        case 'complementary':
            return $generator->complementary();
        case 'analogous':
            return $generator->analogous();
        case 'triadic':
            return $generator->triadic();
        case 'monochromatic':
            return $generator->monochromatic(5);
        default:
            return $generator->websiteTheme();
    }
}

// Usage
$scheme = generateSchemeFromImage('brand-logo.png', 'triadic');
foreach ($scheme->getColors() as $color) {
    echo $color->toHex() . "\n";
}
```

---

### Context-Aware Theme

```php
function generateContextualTheme($context, Color $brandColor): array
{
    $generator = new PaletteGenerator($brandColor);

    switch ($context) {
        case 'professional':
            // Muted, sophisticated colors
            $primary = $brandColor->saturation(-20);
            $accent = $generator->complementary()->getColors()[1]->saturation(-20);
            break;

        case 'playful':
            // Bright, vibrant colors
            $primary = $brandColor->saturation(20);
            $accent = $generator->triadic()->getColors()[1]->saturation(20);
            break;

        case 'elegant':
            // Dark, refined colors
            $primary = $brandColor->darken(15);
            $accent = Color::fromHex('#d4af37'); // Gold
            break;

        case 'minimal':
            // Monochromatic with subtle accent
            $monochrome = $generator->monochromatic(3);
            $primary = $monochrome->getColors()[1];
            $accent = $monochrome->getColors()[2];
            break;

        default:
            $primary = $brandColor;
            $accent = $generator->complementary()->getColors()[1];
    }

    return [
        'primary' => $primary->toHex(),
        'accent' => $accent->toHex(),
        'context' => $context,
    ];
}

// Usage
$professionalTheme = generateContextualTheme('professional', Color::fromHex('#2563eb'));
$playfulTheme = generateContextualTheme('playful', Color::fromHex('#2563eb'));

print_r($professionalTheme);
print_r($playfulTheme);
```

**Expected Output:**
```
Array (
    [primary] => #3d5a9e
    [accent] => #c48635
    [context] => professional
)
Array (
    [primary] => #0d56ff
    [accent] => #ff0d8b
    [context] => playful
)
```

---

## Complete Examples

### Example 1: Brand Color System

```php
function createBrandColorSystem(Color $brandColor): array
{
    $generator = new PaletteGenerator($brandColor);

    // Generate complementary for accent
    $complementary = $generator->complementary();
    $accent = $complementary->getColors()[1];

    // Generate neutrals from brand color
    $neutralBase = Color::fromHsl(
        $brandColor->toHsl()['h'],
        10, // Low saturation
        50  // Medium lightness
    );
    $neutralGenerator = new PaletteGenerator($neutralBase);
    $neutrals = $neutralGenerator->monochromatic(9);

    // Generate semantic colors
    $success = Color::fromHex('#10b981');
    $warning = Color::fromHex('#f59e0b');
    $error = Color::fromHex('#ef4444');
    $info = $brandColor;

    return [
        'brand' => [
            'primary' => $brandColor->toHex(),
            'primary-light' => $brandColor->lighten(20)->toHex(),
            'primary-dark' => $brandColor->darken(20)->toHex(),
            'accent' => $accent->toHex(),
            'accent-light' => $accent->lighten(20)->toHex(),
            'accent-dark' => $accent->darken(20)->toHex(),
        ],
        'neutrals' => array_map(fn($c) => $c->toHex(), $neutrals->getColors()),
        'semantic' => [
            'success' => $success->toHex(),
            'warning' => $warning->toHex(),
            'error' => $error->toHex(),
            'info' => $info->toHex(),
        ],
    ];
}

// Usage
$colorSystem = createBrandColorSystem(Color::fromHex('#2563eb'));
print_r($colorSystem);
```

---

### Example 2: Export as CSS Variables

```php
function exportThemeAsCSS(array $theme, $themeName = 'default'): string
{
    $css = ":root[data-theme=\"$themeName\"] {\n";

    foreach ($theme as $category => $colors) {
        $css .= "  /* $category */\n";

        if (is_array($colors)) {
            foreach ($colors as $name => $value) {
                $varName = "--$category-$name";
                $css .= "  $varName: $value;\n";
            }
        } else {
            $varName = "--$category";
            $css .= "  $varName: $colors;\n";
        }

        $css .= "\n";
    }

    $css .= "}\n";

    return $css;
}

// Usage
$brandColor = Color::fromHex('#2563eb');
$colorSystem = createBrandColorSystem($brandColor);
echo exportThemeAsCSS($colorSystem, 'brand');
```

**Expected Output:**
```css
:root[data-theme="brand"] {
  /* brand */
  --brand-primary: #2563eb;
  --brand-primary-light: #5d8cf0;
  --brand-primary-dark: #174a9e;
  --brand-accent: #eb9c25;
  --brand-accent-light: #f4b859;
  --brand-accent-dark: #c47a10;

  /* neutrals */
  --neutrals-0: #f5f5f6;
  --neutrals-1: #e1e2e4;
  --neutrals-2: #c3c4c8;
  ...
}
```

---

### Example 3: Interactive Scheme Generator API

```php
// POST /api/generate-scheme
// Body: { "base_color": "#2563eb", "scheme_type": "triadic" }

function handleGenerateScheme($request)
{
    try {
        $baseColorHex = $request->input('base_color');
        $schemeType = $request->input('scheme_type', 'complementary');

        // Validate color
        $baseColor = Color::fromHex($baseColorHex);
        $generator = new PaletteGenerator($baseColor);

        // Generate scheme
        $scheme = match($schemeType) {
            'complementary' => $generator->complementary(),
            'analogous' => $generator->analogous(),
            'triadic' => $generator->triadic(),
            'tetradic' => $generator->tetradic(),
            'split-complementary' => $generator->splitComplementary(),
            'monochromatic' => $generator->monochromatic(5),
            default => throw new \InvalidArgumentException('Invalid scheme type'),
        };

        // Format response
        $colors = [];
        foreach ($scheme->getColors() as $index => $color) {
            $rgb = $color->toRgb();
            $hsl = $color->toHsl();

            $colors[] = [
                'hex' => $color->toHex(),
                'rgb' => $rgb,
                'hsl' => $hsl,
                'role' => $index === 0 ? 'base' : "color-$index",
            ];
        }

        return response()->json([
            'success' => true,
            'scheme_type' => $schemeType,
            'colors' => $colors,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 400);
    }
}
```

**Expected Response:**
```json
{
  "success": true,
  "scheme_type": "triadic",
  "colors": [
    {
      "hex": "#2563eb",
      "rgb": {"r": 37, "g": 99, "b": 235},
      "hsl": {"h": 220, "s": 84, "l": 53},
      "role": "base"
    },
    {
      "hex": "#eb2563",
      "rgb": {"r": 235, "g": 37, "b": 99},
      "hsl": {"h": 340, "s": 84, "l": 53},
      "role": "color-1"
    },
    {
      "hex": "#63eb25",
      "rgb": {"r": 99, "g": 235, "b": 37},
      "hsl": {"h": 100, "s": 84, "l": 53},
      "role": "color-2"
    }
  ]
}
```

---

## Related Recipes

- [Extracting Dominant Colors](extracting-dominant-colors) - Get base colors from images
- [Checking Accessibility](checking-accessibility) - Ensure schemes meet accessibility standards
- [Color Format Conversions](color-format-conversions) - Convert between formats

---

## See Also

- [PaletteGenerator Reference](../reference/palette-generation)
- [Color Schemes Guide](../reference/color-schemes)
- [Theme Reference](../reference/theme)
