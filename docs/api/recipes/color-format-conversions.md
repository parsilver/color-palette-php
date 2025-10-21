---
layout: default
title: Recipe - Color Format Conversions
description: Copy-paste solutions for converting between different color formats
---

# Recipe: Color Format Conversions

Convert between different color formats (HEX, RGB, HSL, HSV, CMYK) with ease.

## Table of Contents

- [Basic Conversions](#basic-conversions)
- [Batch Conversions](#batch-conversions)
- [Format Validation](#format-validation)
- [Advanced Conversions](#advanced-conversions)
- [Complete Examples](#complete-examples)

---

## Basic Conversions

### HEX to RGB

```php
use Farzai\ColorPalette\Color;

$color = Color::fromHex('#2563eb');
$rgb = $color->toRgb();

echo "R: {$rgb['r']}, G: {$rgb['g']}, B: {$rgb['b']}\n";
```

**Expected Output:**
```
R: 37, G: 99, B: 235
```

---

### RGB to HEX

```php
$color = new Color(37, 99, 235);
$hex = $color->toHex();

echo "HEX: $hex\n";
```

**Expected Output:**
```
HEX: #2563eb
```

---

### HEX to HSL

```php
$color = Color::fromHex('#2563eb');
$hsl = $color->toHsl();

echo "H: {$hsl['h']}°, S: {$hsl['s']}%, L: {$hsl['l']}%\n";
```

**Expected Output:**
```
H: 220°, S: 84%, L: 53%
```

---

### RGB to HSL

```php
$color = new Color(37, 99, 235);
$hsl = $color->toHsl();

echo "H: {$hsl['h']}°, S: {$hsl['s']}%, L: {$hsl['l']}%\n";
```

**Expected Output:**
```
H: 220°, S: 84%, L: 53%
```

---

### HSL to RGB

```php
$color = Color::fromHsl(220, 84, 53);
$rgb = $color->toRgb();

echo "R: {$rgb['r']}, G: {$rgb['g']}, B: {$rgb['b']}\n";
```

**Expected Output:**
```
R: 37, G: 99, B: 235
```

---

### HSL to HEX

```php
$color = Color::fromHsl(220, 84, 53);
$hex = $color->toHex();

echo "HEX: $hex\n";
```

**Expected Output:**
```
HEX: #2563eb
```

---

### RGB to HSV

```php
$color = new Color(37, 99, 235);
$hsv = $color->toHsv();

echo "H: {$hsv['h']}°, S: {$hsv['s']}%, V: {$hsv['v']}%\n";
```

**Expected Output:**
```
H: 220°, S: 84%, V: 92%
```

---

### HSV to RGB

```php
$color = Color::fromHsv(220, 84, 92);
$rgb = $color->toRgb();

echo "R: {$rgb['r']}, G: {$rgb['g']}, B: {$rgb['b']}\n";
```

**Expected Output:**
```
R: 37, G: 99, B: 235
```

---

### RGB to CMYK

```php
$color = new Color(37, 99, 235);
$cmyk = $color->toCmyk();

echo "C: {$cmyk['c']}%, M: {$cmyk['m']}%, Y: {$cmyk['y']}%, K: {$cmyk['k']}%\n";
```

**Expected Output:**
```
C: 84%, M: 58%, Y: 0%, K: 8%
```

---

### All Formats at Once

```php
$color = Color::fromHex('#2563eb');

$formats = [
    'hex' => $color->toHex(),
    'rgb' => $color->toRgb(),
    'hsl' => $color->toHsl(),
    'hsv' => $color->toHsv(),
    'cmyk' => $color->toCmyk(),
];

print_r($formats);
```

**Expected Output:**
```
Array (
    [hex] => #2563eb
    [rgb] => Array (
        [r] => 37
        [g] => 99
        [b] => 235
    )
    [hsl] => Array (
        [h] => 220
        [s] => 84
        [l] => 53
    )
    [hsv] => Array (
        [h] => 220
        [s] => 84
        [v] => 92
    )
    [cmyk] => Array (
        [c] => 84
        [m] => 58
        [y] => 0
        [k] => 8
    )
)
```

---

## Batch Conversions

### Convert Multiple Colors to Different Format

```php
function convertColorsToFormat(array $hexColors, string $targetFormat): array
{
    $converted = [];

    foreach ($hexColors as $hex) {
        $color = Color::fromHex($hex);

        $converted[$hex] = match($targetFormat) {
            'rgb' => $color->toRgb(),
            'hsl' => $color->toHsl(),
            'hsv' => $color->toHsv(),
            'cmyk' => $color->toCmyk(),
            'hex' => $color->toHex(),
            default => throw new \InvalidArgumentException("Unknown format: $targetFormat"),
        };
    }

    return $converted;
}

// Usage
$colors = ['#2563eb', '#10b981', '#f59e0b', '#ef4444'];
$rgbColors = convertColorsToFormat($colors, 'rgb');

print_r($rgbColors);
```

**Expected Output:**
```
Array (
    [#2563eb] => Array (
        [r] => 37
        [g] => 99
        [b] => 235
    )
    [#10b981] => Array (
        [r] => 16
        [g] => 185
        [b] => 129
    )
    [#f59e0b] => Array (
        [r] => 245
        [g] => 158
        [b] => 11
    )
    [#ef4444] => Array (
        [r] => 239
        [g] => 68
        [b] => 68
    )
)
```

---

### Convert Palette to CSS Variables

```php
function paletteToCSS(array $colors, string $prefix = 'color'): string
{
    $css = ":root {\n";

    foreach ($colors as $name => $hex) {
        $color = Color::fromHex($hex);
        $rgb = $color->toRgb();
        $hsl = $color->toHsl();

        $css .= "  --{$prefix}-{$name}: $hex;\n";
        $css .= "  --{$prefix}-{$name}-rgb: {$rgb['r']}, {$rgb['g']}, {$rgb['b']};\n";
        $css .= "  --{$prefix}-{$name}-hsl: {$hsl['h']}, {$hsl['s']}%, {$hsl['l']}%;\n";
    }

    $css .= "}\n";

    return $css;
}

// Usage
$colors = [
    'primary' => '#2563eb',
    'success' => '#10b981',
    'warning' => '#f59e0b',
    'danger' => '#ef4444',
];

echo paletteToCSS($colors);
```

**Expected Output:**
```css
:root {
  --color-primary: #2563eb;
  --color-primary-rgb: 37, 99, 235;
  --color-primary-hsl: 220, 84%, 53%;
  --color-success: #10b981;
  --color-success-rgb: 16, 185, 129;
  --color-success-hsl: 161, 84%, 39%;
  --color-warning: #f59e0b;
  --color-warning-rgb: 245, 158, 11;
  --color-warning-hsl: 38, 92%, 50%;
  --color-danger: #ef4444;
  --color-danger-rgb: 239, 68, 68;
  --color-danger-hsl: 0, 84%, 60%;
}
```

---

### Export as JSON

```php
function exportColorsAsJSON(array $colors): string
{
    $data = [];

    foreach ($colors as $name => $hex) {
        $color = Color::fromHex($hex);

        $data[$name] = [
            'hex' => $color->toHex(),
            'rgb' => $color->toRgb(),
            'hsl' => $color->toHsl(),
            'hsv' => $color->toHsv(),
            'cmyk' => $color->toCmyk(),
        ];
    }

    return json_encode($data, JSON_PRETTY_PRINT);
}

// Usage
$colors = [
    'primary' => '#2563eb',
    'success' => '#10b981',
];

echo exportColorsAsJSON($colors);
```

**Expected Output:**
```json
{
    "primary": {
        "hex": "#2563eb",
        "rgb": {
            "r": 37,
            "g": 99,
            "b": 235
        },
        "hsl": {
            "h": 220,
            "s": 84,
            "l": 53
        },
        "hsv": {
            "h": 220,
            "s": 84,
            "v": 92
        },
        "cmyk": {
            "c": 84,
            "m": 58,
            "y": 0,
            "k": 8
        }
    },
    "success": {
        "hex": "#10b981",
        "rgb": {
            "r": 16,
            "g": 185,
            "b": 129
        },
        "hsl": {
            "h": 161,
            "s": 84,
            "l": 39
        },
        "hsv": {
            "h": 161,
            "s": 91,
            "v": 73
        },
        "cmyk": {
            "c": 91,
            "m": 0,
            "y": 30,
            "k": 27
        }
    }
}
```

---

## Format Validation

### Validate HEX Color

```php
function isValidHex(string $hex): bool
{
    return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', $hex);
}

// Usage
echo isValidHex('#2563eb') ? 'Valid' : 'Invalid'; // Valid
echo "\n";
echo isValidHex('#2563') ? 'Valid' : 'Invalid';   // Invalid
echo "\n";
echo isValidHex('2563eb') ? 'Valid' : 'Invalid';  // Invalid
```

**Expected Output:**
```
Valid
Invalid
Invalid
```

---

### Validate RGB Values

```php
function isValidRGB(int $r, int $g, int $b): bool
{
    return $r >= 0 && $r <= 255
        && $g >= 0 && $g <= 255
        && $b >= 0 && $b <= 255;
}

// Usage
echo isValidRGB(37, 99, 235) ? 'Valid' : 'Invalid';  // Valid
echo "\n";
echo isValidRGB(256, 99, 235) ? 'Valid' : 'Invalid'; // Invalid
```

**Expected Output:**
```
Valid
Invalid
```

---

### Normalize HEX Format

```php
function normalizeHex(string $hex): string
{
    // Remove # if present
    $hex = ltrim($hex, '#');

    // Convert 3-digit to 6-digit
    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    // Add # prefix
    return '#' . strtoupper($hex);
}

// Usage
echo normalizeHex('2563eb') . "\n";   // #2563EB
echo normalizeHex('#2563eb') . "\n";  // #2563EB
echo normalizeHex('fff') . "\n";      // #FFFFFF
echo normalizeHex('#fff') . "\n";     // #FFFFFF
```

**Expected Output:**
```
#2563EB
#2563EB
#FFFFFF
#FFFFFF
```

---

## Advanced Conversions

### Parse CSS Color String

```php
function parseCSSColor(string $cssColor): ?Color
{
    // Trim whitespace
    $cssColor = trim($cssColor);

    // HEX format
    if (preg_match('/^#?[0-9A-Fa-f]{3,6}$/', $cssColor)) {
        return Color::fromHex(normalizeHex($cssColor));
    }

    // RGB format: rgb(r, g, b)
    if (preg_match('/^rgb\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)$/i', $cssColor, $matches)) {
        return new Color((int)$matches[1], (int)$matches[2], (int)$matches[3]);
    }

    // HSL format: hsl(h, s%, l%)
    if (preg_match('/^hsl\(\s*(\d+)\s*,\s*(\d+)%\s*,\s*(\d+)%\s*\)$/i', $cssColor, $matches)) {
        return Color::fromHsl((int)$matches[1], (int)$matches[2], (int)$matches[3]);
    }

    return null;
}

// Usage
$colors = [
    '#2563eb',
    'rgb(37, 99, 235)',
    'hsl(220, 84%, 53%)',
];

foreach ($colors as $cssColor) {
    $color = parseCSSColor($cssColor);
    if ($color) {
        echo "$cssColor => " . $color->toHex() . "\n";
    }
}
```

**Expected Output:**
```
#2563eb => #2563eb
rgb(37, 99, 235) => #2563eb
hsl(220, 84%, 53%) => #2563eb
```

---

### Convert Named Colors

```php
function getNamedColor(string $name): ?Color
{
    $namedColors = [
        'red' => '#ff0000',
        'green' => '#00ff00',
        'blue' => '#0000ff',
        'white' => '#ffffff',
        'black' => '#000000',
        'yellow' => '#ffff00',
        'cyan' => '#00ffff',
        'magenta' => '#ff00ff',
        'gray' => '#808080',
        'orange' => '#ffa500',
        'purple' => '#800080',
        'pink' => '#ffc0cb',
    ];

    $name = strtolower($name);

    if (isset($namedColors[$name])) {
        return Color::fromHex($namedColors[$name]);
    }

    return null;
}

// Usage
$color = getNamedColor('blue');
if ($color) {
    $rgb = $color->toRgb();
    echo "Blue RGB: R:{$rgb['r']}, G:{$rgb['g']}, B:{$rgb['b']}\n";
}
```

**Expected Output:**
```
Blue RGB: R:0, G:0, B:255
```

---

### Format as CSS String

```php
function formatAsCSS(Color $color, string $format = 'hex'): string
{
    return match($format) {
        'hex' => $color->toHex(),
        'rgb' => function() use ($color) {
            $rgb = $color->toRgb();
            return "rgb({$rgb['r']}, {$rgb['g']}, {$rgb['b']})";
        }(),
        'rgba' => function() use ($color) {
            $rgb = $color->toRgb();
            return "rgba({$rgb['r']}, {$rgb['g']}, {$rgb['b']}, 1)";
        }(),
        'hsl' => function() use ($color) {
            $hsl = $color->toHsl();
            return "hsl({$hsl['h']}, {$hsl['s']}%, {$hsl['l']}%)";
        }(),
        'hsla' => function() use ($color) {
            $hsl = $color->toHsl();
            return "hsla({$hsl['h']}, {$hsl['s']}%, {$hsl['l']}%, 1)";
        }(),
        default => $color->toHex(),
    };
}

// Usage
$color = Color::fromHex('#2563eb');

echo "HEX: " . formatAsCSS($color, 'hex') . "\n";
echo "RGB: " . formatAsCSS($color, 'rgb') . "\n";
echo "RGBA: " . formatAsCSS($color, 'rgba') . "\n";
echo "HSL: " . formatAsCSS($color, 'hsl') . "\n";
echo "HSLA: " . formatAsCSS($color, 'hsla') . "\n";
```

**Expected Output:**
```
HEX: #2563eb
RGB: rgb(37, 99, 235)
RGBA: rgba(37, 99, 235, 1)
HSL: hsl(220, 84%, 53%)
HSLA: hsla(220, 84%, 53%, 1)
```

---

## Complete Examples

### Example 1: Color Conversion API

```php
// POST /api/convert-color
// Body: { "color": "#2563eb", "from": "hex", "to": "rgb" }

function handleConvertColor($request)
{
    try {
        $colorValue = $request->input('color');
        $fromFormat = $request->input('from', 'hex');
        $toFormat = $request->input('to', 'rgb');

        // Parse input color
        $color = match($fromFormat) {
            'hex' => Color::fromHex($colorValue),
            'rgb' => function() use ($colorValue) {
                $parts = explode(',', $colorValue);
                return new Color((int)$parts[0], (int)$parts[1], (int)$parts[2]);
            }(),
            'hsl' => function() use ($colorValue) {
                $parts = explode(',', $colorValue);
                return Color::fromHsl((int)$parts[0], (int)$parts[1], (int)$parts[2]);
            }(),
            'hsv' => function() use ($colorValue) {
                $parts = explode(',', $colorValue);
                return Color::fromHsv((int)$parts[0], (int)$parts[1], (int)$parts[2]);
            }(),
            default => throw new \InvalidArgumentException("Unknown format: $fromFormat"),
        };

        // Convert to target format
        $result = match($toFormat) {
            'hex' => $color->toHex(),
            'rgb' => $color->toRgb(),
            'hsl' => $color->toHsl(),
            'hsv' => $color->toHsv(),
            'cmyk' => $color->toCmyk(),
            'all' => [
                'hex' => $color->toHex(),
                'rgb' => $color->toRgb(),
                'hsl' => $color->toHsl(),
                'hsv' => $color->toHsv(),
                'cmyk' => $color->toCmyk(),
            ],
            default => throw new \InvalidArgumentException("Unknown format: $toFormat"),
        };

        return response()->json([
            'success' => true,
            'input' => [
                'format' => $fromFormat,
                'value' => $colorValue,
            ],
            'output' => [
                'format' => $toFormat,
                'value' => $result,
            ],
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
  "input": {
    "format": "hex",
    "value": "#2563eb"
  },
  "output": {
    "format": "rgb",
    "value": {
      "r": 37,
      "g": 99,
      "b": 235
    }
  }
}
```

---

### Example 2: Batch Color Converter

```php
class ColorConverter
{
    public function convertBatch(array $colors, string $targetFormat): array
    {
        $results = [];

        foreach ($colors as $key => $colorData) {
            try {
                $color = $this->parseColor($colorData);
                $converted = $this->convertToFormat($color, $targetFormat);

                $results[$key] = [
                    'success' => true,
                    'input' => $colorData,
                    'output' => $converted,
                ];
            } catch (\Exception $e) {
                $results[$key] = [
                    'success' => false,
                    'input' => $colorData,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    private function parseColor($colorData): Color
    {
        if (is_string($colorData)) {
            return parseCSSColor($colorData) ?? Color::fromHex($colorData);
        }

        if (isset($colorData['hex'])) {
            return Color::fromHex($colorData['hex']);
        }

        if (isset($colorData['rgb'])) {
            return new Color($colorData['rgb']['r'], $colorData['rgb']['g'], $colorData['rgb']['b']);
        }

        if (isset($colorData['hsl'])) {
            return Color::fromHsl($colorData['hsl']['h'], $colorData['hsl']['s'], $colorData['hsl']['l']);
        }

        throw new \InvalidArgumentException('Invalid color data');
    }

    private function convertToFormat(Color $color, string $format)
    {
        return match($format) {
            'hex' => $color->toHex(),
            'rgb' => $color->toRgb(),
            'hsl' => $color->toHsl(),
            'hsv' => $color->toHsv(),
            'cmyk' => $color->toCmyk(),
            'css-rgb' => formatAsCSS($color, 'rgb'),
            'css-hsl' => formatAsCSS($color, 'hsl'),
            default => throw new \InvalidArgumentException("Unknown format: $format"),
        };
    }
}

// Usage
$converter = new ColorConverter();

$colors = [
    'primary' => '#2563eb',
    'success' => 'rgb(16, 185, 129)',
    'warning' => 'hsl(38, 92%, 50%)',
];

$results = $converter->convertBatch($colors, 'rgb');
print_r($results);
```

---

### Example 3: Multi-format Color Export

```php
class ColorExporter
{
    public function export(array $colors, string $format): string
    {
        return match($format) {
            'css' => $this->exportAsCSS($colors),
            'scss' => $this->exportAsSCSS($colors),
            'json' => $this->exportAsJSON($colors),
            'xml' => $this->exportAsXML($colors),
            'swift' => $this->exportAsSwift($colors),
            'kotlin' => $this->exportAsKotlin($colors),
            default => throw new \InvalidArgumentException("Unknown format: $format"),
        };
    }

    private function exportAsCSS(array $colors): string
    {
        return paletteToCSS($colors);
    }

    private function exportAsSCSS(array $colors): string
    {
        $scss = "// Color Variables\n";

        foreach ($colors as $name => $hex) {
            $color = Color::fromHex($hex);
            $rgb = $color->toRgb();

            $scss .= "\$$name: $hex;\n";
            $scss .= "\$$name-rgb: ({$rgb['r']}, {$rgb['g']}, {$rgb['b']});\n";
        }

        return $scss;
    }

    private function exportAsJSON(array $colors): string
    {
        return exportColorsAsJSON($colors);
    }

    private function exportAsXML(array $colors): string
    {
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        $xml .= "<colors>\n";

        foreach ($colors as $name => $hex) {
            $color = Color::fromHex($hex);
            $rgb = $color->toRgb();

            $xml .= "  <color name=\"$name\">\n";
            $xml .= "    <hex>$hex</hex>\n";
            $xml .= "    <rgb r=\"{$rgb['r']}\" g=\"{$rgb['g']}\" b=\"{$rgb['b']}\" />\n";
            $xml .= "  </color>\n";
        }

        $xml .= "</colors>\n";

        return $xml;
    }

    private function exportAsSwift(array $colors): string
    {
        $swift = "import UIKit\n\n";
        $swift .= "extension UIColor {\n";

        foreach ($colors as $name => $hex) {
            $color = Color::fromHex($hex);
            $rgb = $color->toRgb();

            $r = round($rgb['r'] / 255, 3);
            $g = round($rgb['g'] / 255, 3);
            $b = round($rgb['b'] / 255, 3);

            $swiftName = str_replace('-', '', ucwords($name, '-'));

            $swift .= "    static var $swiftName: UIColor {\n";
            $swift .= "        return UIColor(red: $r, green: $g, blue: $b, alpha: 1.0)\n";
            $swift .= "    }\n";
        }

        $swift .= "}\n";

        return $swift;
    }

    private function exportAsKotlin(array $colors): string
    {
        $kotlin = "object Colors {\n";

        foreach ($colors as $name => $hex) {
            $kotlinName = str_replace('-', '_', strtoupper($name));
            $kotlin .= "    const val $kotlinName = \"$hex\"\n";
        }

        $kotlin .= "}\n";

        return $kotlin;
    }
}

// Usage
$colors = [
    'primary' => '#2563eb',
    'success' => '#10b981',
    'warning' => '#f59e0b',
];

$exporter = new ColorExporter();
echo $exporter->export($colors, 'swift');
```

---

## Related Recipes

- [Creating Color Schemes](creating-color-schemes) - Use converted colors in schemes
- [Checking Accessibility](checking-accessibility) - Check converted colors for accessibility
- [Extracting Dominant Colors](extracting-dominant-colors) - Extract colors in various formats

---

## See Also

- [Color Reference](../reference/color)
- [Color Spaces Guide](../reference/color-spaces)
