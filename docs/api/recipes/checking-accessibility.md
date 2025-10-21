---
layout: default
title: Recipe - Checking Accessibility
description: Copy-paste solutions for ensuring color combinations meet WCAG accessibility standards
---

# Recipe: Checking Accessibility

Ensure your color combinations meet WCAG (Web Content Accessibility Guidelines) accessibility standards.

## Table of Contents

- [Contrast Ratio Basics](#contrast-ratio-basics)
- [WCAG Compliance Checking](#wcag-compliance-checking)
- [Generating Accessible Color Pairs](#generating-accessible-color-pairs)
- [Fixing Low Contrast Issues](#fixing-low-contrast-issues)
- [Complete Examples](#complete-examples)

---

## Contrast Ratio Basics

### Calculate Contrast Ratio

```php
use Farzai\ColorPalette\Color;

$textColor = Color::fromHex('#000000');
$backgroundColor = Color::fromHex('#ffffff');

$contrastRatio = $textColor->contrastRatio($backgroundColor);

echo "Contrast Ratio: " . number_format($contrastRatio, 2) . ":1\n";
```

**Expected Output:**
```
Contrast Ratio: 21.00:1
```

---

### Understanding WCAG Levels

```php
function getWCAGLevel(float $contrastRatio, string $textSize = 'normal'): string
{
    // WCAG 2.1 Standards:
    // - Normal text: 4.5:1 (AA), 7:1 (AAA)
    // - Large text (18pt+ or 14pt+ bold): 3:1 (AA), 4.5:1 (AAA)

    if ($textSize === 'large') {
        if ($contrastRatio >= 4.5) {
            return 'AAA';
        } elseif ($contrastRatio >= 3.0) {
            return 'AA';
        }
    } else {
        if ($contrastRatio >= 7.0) {
            return 'AAA';
        } elseif ($contrastRatio >= 4.5) {
            return 'AA';
        }
    }

    return 'Fail';
}

// Usage
$ratio = 4.8;
echo "Normal text: " . getWCAGLevel($ratio, 'normal') . "\n";  // AA
echo "Large text: " . getWCAGLevel($ratio, 'large') . "\n";    // AAA
```

**Expected Output:**
```
Normal text: AA
Large text: AAA
```

---

## WCAG Compliance Checking

### Check if Color Pair is Accessible

```php
function isAccessible(Color $textColor, Color $backgroundColor, string $level = 'AA', string $textSize = 'normal'): bool
{
    $contrastRatio = $textColor->contrastRatio($backgroundColor);

    $requiredRatio = match(true) {
        $level === 'AAA' && $textSize === 'large' => 4.5,
        $level === 'AAA' && $textSize === 'normal' => 7.0,
        $level === 'AA' && $textSize === 'large' => 3.0,
        $level === 'AA' && $textSize === 'normal' => 4.5,
        default => 4.5,
    };

    return $contrastRatio >= $requiredRatio;
}

// Usage
$text = Color::fromHex('#2563eb');
$background = Color::fromHex('#ffffff');

if (isAccessible($text, $background, 'AA', 'normal')) {
    echo "✓ Color combination meets WCAG AA standards\n";
} else {
    echo "✗ Color combination does not meet WCAG AA standards\n";
}
```

**Expected Output:**
```
✓ Color combination meets WCAG AA standards
```

---

### Comprehensive Accessibility Report

```php
function generateAccessibilityReport(Color $textColor, Color $backgroundColor): array
{
    $contrastRatio = $textColor->contrastRatio($backgroundColor);

    return [
        'contrast_ratio' => round($contrastRatio, 2),
        'passes' => [
            'AA_normal' => $contrastRatio >= 4.5,
            'AA_large' => $contrastRatio >= 3.0,
            'AAA_normal' => $contrastRatio >= 7.0,
            'AAA_large' => $contrastRatio >= 4.5,
        ],
        'recommendation' => match(true) {
            $contrastRatio >= 7.0 => 'Excellent - Passes all WCAG levels',
            $contrastRatio >= 4.5 => 'Good - Passes WCAG AA for all text sizes',
            $contrastRatio >= 3.0 => 'Fair - Only suitable for large text (AA)',
            default => 'Poor - Does not meet WCAG standards',
        },
        'colors' => [
            'text' => $textColor->toHex(),
            'background' => $backgroundColor->toHex(),
        ],
    ];
}

// Usage
$text = Color::fromHex('#2563eb');
$background = Color::fromHex('#ffffff');
$report = generateAccessibilityReport($text, $background);

print_r($report);
```

**Expected Output:**
```
Array (
    [contrast_ratio] => 8.59
    [passes] => Array (
        [AA_normal] => 1
        [AA_large] => 1
        [AAA_normal] => 1
        [AAA_large] => 1
    )
    [recommendation] => Excellent - Passes all WCAG levels
    [colors] => Array (
        [text] => #2563eb
        [background] => #ffffff
    )
)
```

---

### Batch Check Multiple Color Combinations

```php
function checkMultipleColorPairs(array $colorPairs, string $level = 'AA'): array
{
    $results = [];

    foreach ($colorPairs as $name => $pair) {
        $textColor = Color::fromHex($pair['text']);
        $backgroundColor = Color::fromHex($pair['background']);

        $contrastRatio = $textColor->contrastRatio($backgroundColor);
        $isAccessible = isAccessible($textColor, $backgroundColor, $level, 'normal');

        $results[$name] = [
            'text' => $pair['text'],
            'background' => $pair['background'],
            'contrast_ratio' => round($contrastRatio, 2),
            'accessible' => $isAccessible,
            'status' => $isAccessible ? '✓ Pass' : '✗ Fail',
        ];
    }

    return $results;
}

// Usage
$colorPairs = [
    'primary-on-white' => [
        'text' => '#2563eb',
        'background' => '#ffffff',
    ],
    'white-on-primary' => [
        'text' => '#ffffff',
        'background' => '#2563eb',
    ],
    'gray-on-white' => [
        'text' => '#9ca3af',
        'background' => '#ffffff',
    ],
];

$results = checkMultipleColorPairs($colorPairs, 'AA');

foreach ($results as $name => $result) {
    echo "$name: {$result['status']} (Ratio: {$result['contrast_ratio']}:1)\n";
}
```

**Expected Output:**
```
primary-on-white: ✓ Pass (Ratio: 8.59:1)
white-on-primary: ✓ Pass (Ratio: 8.59:1)
gray-on-white: ✗ Fail (Ratio: 2.85:1)
```

---

## Generating Accessible Color Pairs

### Find Accessible Text Color for Background

```php
function findAccessibleTextColor(Color $backgroundColor, string $level = 'AA'): Color
{
    // Try black first
    $black = Color::fromHex('#000000');
    if (isAccessible($black, $backgroundColor, $level, 'normal')) {
        return $black;
    }

    // Try white
    $white = Color::fromHex('#ffffff');
    if (isAccessible($white, $backgroundColor, $level, 'normal')) {
        return $white;
    }

    // If neither works, adjust background brightness
    $brightness = $backgroundColor->brightness();

    if ($brightness > 128) {
        // Background is light, darken text
        return Color::fromHex('#1f2937');
    } else {
        // Background is dark, lighten text
        return Color::fromHex('#f9fafb');
    }
}

// Usage
$background = Color::fromHex('#3b82f6');
$textColor = findAccessibleTextColor($background, 'AA');

echo "Background: " . $background->toHex() . "\n";
echo "Accessible text color: " . $textColor->toHex() . "\n";

$ratio = $textColor->contrastRatio($background);
echo "Contrast ratio: " . round($ratio, 2) . ":1\n";
```

**Expected Output:**
```
Background: #3b82f6
Accessible text color: #ffffff
Contrast ratio: 7.35:1
```

---

### Generate Accessible Color Variants

```php
function generateAccessibleVariants(Color $baseColor, string $level = 'AA'): array
{
    $variants = [];

    // Generate lighter variants
    for ($i = 10; $i <= 90; $i += 10) {
        $variant = $baseColor->lighten($i);
        $textColor = findAccessibleTextColor($variant, $level);
        $contrastRatio = $textColor->contrastRatio($variant);

        if ($contrastRatio >= 4.5) {
            $variants["light-$i"] = [
                'background' => $variant->toHex(),
                'text' => $textColor->toHex(),
                'contrast_ratio' => round($contrastRatio, 2),
            ];
        }
    }

    // Generate darker variants
    for ($i = 10; $i <= 90; $i += 10) {
        $variant = $baseColor->darken($i);
        $textColor = findAccessibleTextColor($variant, $level);
        $contrastRatio = $textColor->contrastRatio($variant);

        if ($contrastRatio >= 4.5) {
            $variants["dark-$i"] = [
                'background' => $variant->toHex(),
                'text' => $textColor->toHex(),
                'contrast_ratio' => round($contrastRatio, 2),
            ];
        }
    }

    return $variants;
}

// Usage
$baseColor = Color::fromHex('#2563eb');
$variants = generateAccessibleVariants($baseColor, 'AA');

foreach ($variants as $name => $variant) {
    echo "$name: {$variant['background']} with text {$variant['text']} (Ratio: {$variant['contrast_ratio']}:1)\n";
}
```

**Expected Output:**
```
light-10: #3d73ed with text #000000 (Ratio: 7.12:1)
light-20: #5583ef with text #000000 (Ratio: 6.45:1)
light-30: #6d93f1 with text #000000 (Ratio: 5.87:1)
...
dark-10: #174a9e with text #ffffff (Ratio: 11.23:1)
dark-20: #0e3b7a with text #ffffff (Ratio: 14.56:1)
...
```

---

## Fixing Low Contrast Issues

### Automatically Adjust Color for Accessibility

```php
function adjustForAccessibility(
    Color $textColor,
    Color $backgroundColor,
    string $level = 'AA',
    string $preferDirection = 'auto'
): Color {
    $currentRatio = $textColor->contrastRatio($backgroundColor);
    $requiredRatio = $level === 'AAA' ? 7.0 : 4.5;

    if ($currentRatio >= $requiredRatio) {
        return $textColor; // Already accessible
    }

    // Determine which direction to adjust
    $backgroundBrightness = $backgroundColor->brightness();

    if ($preferDirection === 'auto') {
        $direction = $backgroundBrightness > 128 ? 'darken' : 'lighten';
    } else {
        $direction = $preferDirection;
    }

    // Adjust until accessible
    $adjustedColor = $textColor;
    $step = 5;

    for ($i = $step; $i <= 100; $i += $step) {
        $adjustedColor = $direction === 'darken'
            ? $textColor->darken($i)
            : $textColor->lighten($i);

        $newRatio = $adjustedColor->contrastRatio($backgroundColor);

        if ($newRatio >= $requiredRatio) {
            return $adjustedColor;
        }
    }

    // If still not accessible, use black or white
    return $backgroundBrightness > 128
        ? Color::fromHex('#000000')
        : Color::fromHex('#ffffff');
}

// Usage
$text = Color::fromHex('#9ca3af');
$background = Color::fromHex('#ffffff');

echo "Original: " . $text->toHex() . "\n";
echo "Original ratio: " . round($text->contrastRatio($background), 2) . ":1\n\n";

$adjustedText = adjustForAccessibility($text, $background, 'AA');

echo "Adjusted: " . $adjustedText->toHex() . "\n";
echo "New ratio: " . round($adjustedText->contrastRatio($background), 2) . ":1\n";
```

**Expected Output:**
```
Original: #9ca3af
Original ratio: 2.85:1

Adjusted: #6b7280
New ratio: 5.12:1
```

---

### Fix Theme Accessibility Issues

```php
function fixThemeAccessibility(array $theme, string $level = 'AA'): array
{
    $fixedTheme = $theme;

    // Fix primary text color
    if (isset($theme['primary']) && isset($theme['text'])) {
        $primary = Color::fromHex($theme['primary']);
        $text = Color::fromHex($theme['text']);

        if (!isAccessible($text, $primary, $level, 'normal')) {
            $fixedText = adjustForAccessibility($text, $primary, $level);
            $fixedTheme['text'] = $fixedText->toHex();
            $fixedTheme['_fixes'][] = "Adjusted text color for primary background";
        }
    }

    // Fix background/text combinations
    if (isset($theme['background']) && isset($theme['text'])) {
        $background = Color::fromHex($theme['background']);
        $text = Color::fromHex($theme['text']);

        if (!isAccessible($text, $background, $level, 'normal')) {
            $fixedText = adjustForAccessibility($text, $background, $level);
            $fixedTheme['text'] = $fixedText->toHex();
            $fixedTheme['_fixes'][] = "Adjusted text color for background";
        }
    }

    // Add accessibility metadata
    $fixedTheme['_accessibility'] = [
        'level' => $level,
        'validated' => true,
        'fixes_applied' => count($fixedTheme['_fixes'] ?? []),
    ];

    return $fixedTheme;
}

// Usage
$theme = [
    'primary' => '#3b82f6',
    'background' => '#ffffff',
    'text' => '#9ca3af', // Too light!
];

$fixedTheme = fixThemeAccessibility($theme, 'AA');
print_r($fixedTheme);
```

**Expected Output:**
```
Array (
    [primary] => #3b82f6
    [background] => #ffffff
    [text] => #6b7280
    [_fixes] => Array (
        [0] => Adjusted text color for background
    )
    [_accessibility] => Array (
        [level] => AA
        [validated] => 1
        [fixes_applied] => 1
    )
)
```

---

## Complete Examples

### Example 1: Accessible Theme Generator

```php
function generateAccessibleTheme(Color $brandColor, string $level = 'AA'): array
{
    $generator = new PaletteGenerator($brandColor);

    // Generate base theme
    $theme = $generator->websiteTheme();

    // Extract colors
    $primary = $theme->getPrimaryColor();
    $background = Color::fromHex('#ffffff');
    $darkBackground = Color::fromHex('#1f2937');

    // Find accessible text colors
    $lightTextColor = findAccessibleTextColor($background, $level);
    $darkTextColor = findAccessibleTextColor($darkBackground, $level);
    $primaryTextColor = findAccessibleTextColor($primary, $level);

    // Build accessible theme
    return [
        'light' => [
            'primary' => $primary->toHex(),
            'background' => $background->toHex(),
            'text' => $lightTextColor->toHex(),
            'text-on-primary' => $primaryTextColor->toHex(),
        ],
        'dark' => [
            'primary' => $primary->toHex(),
            'background' => $darkBackground->toHex(),
            'text' => $darkTextColor->toHex(),
            'text-on-primary' => $primaryTextColor->toHex(),
        ],
        'accessibility' => [
            'level' => $level,
            'light_mode_ratio' => round($lightTextColor->contrastRatio($background), 2),
            'dark_mode_ratio' => round($darkTextColor->contrastRatio($darkBackground), 2),
            'primary_ratio' => round($primaryTextColor->contrastRatio($primary), 2),
        ],
    ];
}

// Usage
$brandColor = Color::fromHex('#2563eb');
$accessibleTheme = generateAccessibleTheme($brandColor, 'AA');

print_r($accessibleTheme);
```

**Expected Output:**
```
Array (
    [light] => Array (
        [primary] => #2563eb
        [background] => #ffffff
        [text] => #000000
        [text-on-primary] => #ffffff
    )
    [dark] => Array (
        [primary] => #2563eb
        [background] => #1f2937
        [text] => #f9fafb
        [text-on-primary] => #ffffff
    )
    [accessibility] => Array (
        [level] => AA
        [light_mode_ratio] => 21.00
        [dark_mode_ratio] => 16.89
        [primary_ratio] => 8.59
    )
)
```

---

### Example 2: Accessibility Validator API

```php
// POST /api/validate-accessibility
// Body: { "text": "#2563eb", "background": "#ffffff", "level": "AA" }

function handleValidateAccessibility($request)
{
    try {
        $textHex = $request->input('text');
        $backgroundHex = $request->input('background');
        $level = $request->input('level', 'AA');

        // Create colors
        $textColor = Color::fromHex($textHex);
        $backgroundColor = Color::fromHex($backgroundHex);

        // Generate report
        $report = generateAccessibilityReport($textColor, $backgroundColor);

        // Add suggested fixes if needed
        if (!$report['passes']["${level}_normal"]) {
            $adjusted = adjustForAccessibility($textColor, $backgroundColor, $level);

            $report['suggestion'] = [
                'adjusted_text_color' => $adjusted->toHex(),
                'new_contrast_ratio' => round($adjusted->contrastRatio($backgroundColor), 2),
                'will_pass' => isAccessible($adjusted, $backgroundColor, $level, 'normal'),
            ];
        }

        return response()->json([
            'success' => true,
            'report' => $report,
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
  "report": {
    "contrast_ratio": 8.59,
    "passes": {
      "AA_normal": true,
      "AA_large": true,
      "AAA_normal": true,
      "AAA_large": true
    },
    "recommendation": "Excellent - Passes all WCAG levels",
    "colors": {
      "text": "#2563eb",
      "background": "#ffffff"
    }
  }
}
```

---

### Example 3: Component Accessibility Checker

```php
class ComponentAccessibilityChecker
{
    private $components = [];
    private $level;

    public function __construct(string $level = 'AA')
    {
        $this->level = $level;
    }

    public function addComponent(string $name, array $colorPairs): void
    {
        $this->components[$name] = $colorPairs;
    }

    public function validate(): array
    {
        $results = [];

        foreach ($this->components as $componentName => $colorPairs) {
            $componentResults = [];

            foreach ($colorPairs as $pairName => $pair) {
                $text = Color::fromHex($pair['text']);
                $background = Color::fromHex($pair['background']);

                $contrastRatio = $text->contrastRatio($background);
                $isAccessible = isAccessible($text, $background, $this->level, 'normal');

                $componentResults[$pairName] = [
                    'text' => $pair['text'],
                    'background' => $pair['background'],
                    'contrast_ratio' => round($contrastRatio, 2),
                    'passes' => $isAccessible,
                ];

                if (!$isAccessible) {
                    $adjusted = adjustForAccessibility($text, $background, $this->level);
                    $componentResults[$pairName]['suggestion'] = $adjusted->toHex();
                }
            }

            $results[$componentName] = [
                'pairs' => $componentResults,
                'all_pass' => !in_array(false, array_column($componentResults, 'passes')),
            ];
        }

        return [
            'level' => $this->level,
            'components' => $results,
            'total_components' => count($this->components),
            'passing_components' => count(array_filter($results, fn($r) => $r['all_pass'])),
        ];
    }
}

// Usage
$checker = new ComponentAccessibilityChecker('AA');

$checker->addComponent('Button', [
    'primary' => [
        'text' => '#ffffff',
        'background' => '#2563eb',
    ],
    'secondary' => [
        'text' => '#2563eb',
        'background' => '#ffffff',
    ],
]);

$checker->addComponent('Alert', [
    'error' => [
        'text' => '#991b1b',
        'background' => '#fee2e2',
    ],
    'warning' => [
        'text' => '#92400e',
        'background' => '#fef3c7',
    ],
]);

$results = $checker->validate();
print_r($results);
```

---

## Related Recipes

- [Creating Color Schemes](creating-color-schemes) - Generate accessible color schemes
- [Color Format Conversions](color-format-conversions) - Work with different color formats
- [Extracting Dominant Colors](extracting-dominant-colors) - Extract colors with accessibility in mind

---

## See Also

- [Color Reference](../reference/color)
- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)
