---
layout: default
title: Accessibility
parent: Concepts
nav_order: 3
description: WCAG guidelines, contrast ratios, color blindness considerations, and creating inclusive color designs
keywords: accessibility, WCAG, contrast ratio, color blindness, inclusive design, a11y
---

# Color Accessibility

Accessible color design ensures that everyone, regardless of visual ability, can perceive and interact with your content. This includes considerations for contrast, color blindness, and following established guidelines like WCAG (Web Content Accessibility Guidelines).

## Table of Contents
{:.no_toc}

* TOC
{:toc}

## Why Color Accessibility Matters

### The Statistics

- **1 in 12 men** (8%) have some form of color vision deficiency
- **1 in 200 women** (0.5%) have color vision deficiency
- **4.5% of the global population** is color blind (~350 million people)
- **Approximately 15% of adults** in developed countries report some form of vision impairment
- **Low vision affects 246 million people** worldwide

### Legal and Ethical Considerations

Many jurisdictions require digital accessibility:
- **ADA** (Americans with Disabilities Act) in the US
- **EAA** (European Accessibility Act) in the EU
- **AODA** (Accessibility for Ontarians with Disabilities Act) in Canada
- **DDA** (Disability Discrimination Act) in Australia

**Beyond compliance**: Accessible design creates better experiences for everyone:
- Improves usability in bright sunlight or low-light conditions
- Benefits users with temporary impairments (eye fatigue, bright screens)
- Enhances readability on low-quality displays
- Provides clearer visual hierarchy

---

## WCAG Standards

### WCAG Levels

The Web Content Accessibility Guidelines define three conformance levels:

**Level A (Minimum)**
- Basic accessibility features
- Removes major barriers
- Required for basic accessibility

**Level AA (Recommended)**
- Addresses most common barriers
- Industry standard
- Required by many laws and policies
- **Target level for most websites**

**Level AAA (Enhanced)**
- Highest level of accessibility
- Not always achievable for all content
- Recommended for specialized applications

### WCAG 2.1 vs 2.2 vs 3.0

**WCAG 2.1** (Current Standard, 2018)
- Built on WCAG 2.0
- Added mobile, low vision, and cognitive considerations
- Industry standard

**WCAG 2.2** (Latest, 2023)
- Additional success criteria
- Focus on mobile and cognitive accessibility
- Backward compatible with 2.1

**WCAG 3.0** (Draft, Future)
- Complete redesign
- New scoring model
- Better support for low vision and cognitive disabilities

---

## Contrast Ratios

### Understanding Contrast Ratio

**Contrast ratio** measures the difference in luminance between two colors. It's expressed as a ratio from 1:1 (no contrast) to 21:1 (maximum contrast).

```
Formula:
Contrast Ratio = (L1 + 0.05) / (L2 + 0.05)

Where:
L1 = Relative luminance of lighter color
L2 = Relative luminance of darker color
Luminance = Perceived brightness (0 to 1)
```

### WCAG Contrast Requirements

**Normal Text (< 18pt or < 14pt bold)**
- **Level AA**: 4.5:1 minimum
- **Level AAA**: 7:1 minimum

**Large Text (≥ 18pt or ≥ 14pt bold)**
- **Level AA**: 3:1 minimum
- **Level AAA**: 4.5:1 minimum

**UI Components & Graphics**
- **Level AA**: 3:1 minimum (WCAG 2.1+)

**Incidental**
- No contrast requirement (decorative elements, logos, disabled controls)

### Visual Examples

```
Excellent Contrast (13:1)
Black text (#000000) on White background (#FFFFFF)
██████████████████████████
█  BLACK ON WHITE       █
██████████████████████████

Good Contrast (7.2:1) - AAA Normal, AA Large
Dark Gray (#595959) on White (#FFFFFF)
██████████████████████████
█  DARK GRAY ON WHITE   █
██████████████████████████

Acceptable Contrast (4.6:1) - AA Normal
Medium Gray (#767676) on White (#FFFFFF)
██████████████████████████
█  MEDIUM GRAY ON WHITE █
██████████████████████████

Fail (2.9:1) - Insufficient
Light Gray (#959595) on White (#FFFFFF)
██████████████████████████
█  LIGHT GRAY ON WHITE  █  ❌ FAILS
██████████████████████████
```

### Calculating Contrast with Color Palette PHP

```php
use Farzai\ColorPalette\Color;

// Calculate relative luminance
function relativeLuminance(Color $color): float {
    $rgb = [
        'r' => $color->getRed() / 255,
        'g' => $color->getGreen() / 255,
        'b' => $color->getBlue() / 255,
    ];

    // Apply gamma correction
    $convert = function ($value) {
        return $value <= 0.03928
            ? $value / 12.92
            : pow(($value + 0.055) / 1.055, 2.4);
    };

    $r = $convert($rgb['r']);
    $g = $convert($rgb['g']);
    $b = $convert($rgb['b']);

    // Calculate luminance using ITU-R BT.709 coefficients
    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

// Calculate contrast ratio
function contrastRatio(Color $color1, Color $color2): float {
    $lum1 = relativeLuminance($color1);
    $lum2 = relativeLuminance($color2);

    $lighter = max($lum1, $lum2);
    $darker = min($lum1, $lum2);

    return ($lighter + 0.05) / ($darker + 0.05);
}

// Check WCAG compliance
function meetsWCAG(Color $fg, Color $bg, string $level = 'AA', bool $largeText = false): bool {
    $ratio = contrastRatio($fg, $bg);

    if ($level === 'AAA') {
        return $largeText ? $ratio >= 4.5 : $ratio >= 7;
    }

    // Level AA (default)
    return $largeText ? $ratio >= 3 : $ratio >= 4.5;
}

// Example usage
$text = Color::fromHex('#333333');
$background = Color::fromHex('#FFFFFF');

$ratio = contrastRatio($text, $background);
echo "Contrast ratio: " . round($ratio, 2) . ":1\n";  // 12.6:1

if (meetsWCAG($text, $background, 'AA')) {
    echo "✓ Passes WCAG AA\n";
}

if (meetsWCAG($text, $background, 'AAA')) {
    echo "✓ Passes WCAG AAA\n";
}
```

### Finding Accessible Color Pairs

```php
// Find the darkest color that meets contrast requirements
function findAccessibleDark(Color $background, float $targetRatio = 4.5): Color {
    $bgHsl = $background->toHsl();

    // Binary search for lightness
    $minL = 0;
    $maxL = $bgHsl['l'];
    $result = null;

    while ($maxL - $minL > 0.5) {
        $testL = ($minL + $maxL) / 2;
        $testColor = Color::fromHsl($bgHsl['h'], $bgHsl['s'], $testL);

        $ratio = contrastRatio($testColor, $background);

        if ($ratio >= $targetRatio) {
            $result = $testColor;
            $maxL = $testL;  // Try lighter
        } else {
            $minL = $testL;  // Need darker
        }
    }

    return $result ?? Color::fromHsl($bgHsl['h'], $bgHsl['s'], 0);
}

// Find the lightest color that meets contrast requirements
function findAccessibleLight(Color $background, float $targetRatio = 4.5): Color {
    $bgHsl = $background->toHsl();

    $minL = $bgHsl['l'];
    $maxL = 100;
    $result = null;

    while ($maxL - $minL > 0.5) {
        $testL = ($minL + $maxL) / 2;
        $testColor = Color::fromHsl($bgHsl['h'], $bgHsl['s'], $testL);

        $ratio = contrastRatio($testColor, $background);

        if ($ratio >= $targetRatio) {
            $result = $testColor;
            $minL = $testL;  // Try darker
        } else {
            $maxL = $testL;  // Need lighter
        }
    }

    return $result ?? Color::fromHsl($bgHsl['h'], $bgHsl['s'], 100);
}

// Example: Generate accessible text color for any background
$background = Color::fromHex('#3498db');  // Medium blue

$darkText = findAccessibleDark($background);   // Will find dark blue
$lightText = findAccessibleLight($background); // Will find light blue/white

echo "Dark text: " . $darkText->toHex() . "\n";
echo "Light text: " . $lightText->toHex() . "\n";
```

---

## Color Blindness

### Types of Color Vision Deficiency

**Protanopia (1% of males)**
- Missing long-wavelength (red) cones
- Red appears dark gray/black
- Confusion: Red/Green, Red/Black, Blue/Purple

**Protanomaly (1% of males)**
- Defective red cones
- Muted red perception
- Most common form

**Deuteranopia (1% of males)**
- Missing medium-wavelength (green) cones
- Green appears tan/beige
- Confusion: Red/Green, Green/Brown

**Deuteranomaly (5% of males)**
- Defective green cones
- Most common overall
- Muted green perception

**Tritanopia (0.001%)**
- Missing short-wavelength (blue) cones
- Rare, affects men and women equally
- Confusion: Blue/Green, Yellow/Pink

**Tritanomaly (0.01%)**
- Defective blue cones
- Very rare

**Achromatopsia (0.003%)**
- Complete color blindness
- See only shades of gray
- Extremely rare

### Color Confusion Matrices

Colors often confused by people with color blindness:

```
Protanopia/Protanomaly (Red-Blind):
Red ←→ Dark Brown ←→ Black
Red ←→ Green ←→ Brown ←→ Gray
Orange ←→ Yellow-Green
Blue ←→ Purple

Deuteranopia/Deuteranomaly (Green-Blind):
Red ←→ Green ←→ Brown ←→ Gray
Purple ←→ Blue
Light Green ←→ White

Tritanopia/Tritanomaly (Blue-Blind):
Blue ←→ Green
Purple ←→ Red
Yellow ←→ Pink ←→ Gray
```

### Simulating Color Blindness

```php
// Simulate protanopia (red-blind)
function simulateProtanopia(Color $color): Color {
    $rgb = [
        'r' => $color->getRed(),
        'g' => $color->getGreen(),
        'b' => $color->getBlue(),
    ];

    // Brettel 1997 algorithm (simplified)
    $r = 0.56667 * $rgb['r'] + 0.43333 * $rgb['g'];
    $g = 0.55833 * $rgb['r'] + 0.44167 * $rgb['g'];
    $b = 0.24167 * $rgb['g'] + 0.75833 * $rgb['b'];

    return Color::fromRgb(
        (int) round(max(0, min(255, $r))),
        (int) round(max(0, min(255, $g))),
        (int) round(max(0, min(255, $b)))
    );
}

// Simulate deuteranopia (green-blind)
function simulateDeuteranopia(Color $color): Color {
    $rgb = [
        'r' => $color->getRed(),
        'g' => $color->getGreen(),
        'b' => $color->getBlue(),
    ];

    $r = 0.625 * $rgb['r'] + 0.375 * $rgb['g'];
    $g = 0.7 * $rgb['r'] + 0.3 * $rgb['g'];
    $b = 0.3 * $rgb['g'] + 0.7 * $rgb['b'];

    return Color::fromRgb(
        (int) round(max(0, min(255, $r))),
        (int) round(max(0, min(255, $g))),
        (int) round(max(0, min(255, $b)))
    );
}

// Simulate tritanopia (blue-blind)
function simulateTritanopia(Color $color): Color {
    $rgb = [
        'r' => $color->getRed(),
        'g' => $color->getGreen(),
        'b' => $color->getBlue(),
    ];

    $r = 0.95 * $rgb['r'] + 0.05 * $rgb['g'];
    $g = 0.43333 * $rgb['g'] + 0.56667 * $rgb['b'];
    $b = 0.475 * $rgb['g'] + 0.525 * $rgb['b'];

    return Color::fromRgb(
        (int) round(max(0, min(255, $r))),
        (int) round(max(0, min(255, $g))),
        (int) round(max(0, min(255, $b)))
    );
}

// Test if colors remain distinguishable
function testColorBlindSafety(Color $color1, Color $color2): array {
    $types = [
        'protanopia' => 'simulateProtanopia',
        'deuteranopia' => 'simulateDeuteranopia',
        'tritanopia' => 'simulateTritanopia',
    ];

    $results = [];

    foreach ($types as $type => $simulator) {
        $simulated1 = $simulator($color1);
        $simulated2 = $simulator($color2);

        $ratio = contrastRatio($simulated1, $simulated2);
        $results[$type] = [
            'ratio' => $ratio,
            'distinguishable' => $ratio >= 3,  // Minimum for UI elements
        ];
    }

    return $results;
}

// Example usage
$red = Color::fromHex('#D32F2F');
$green = Color::fromHex('#388E3C');

$safety = testColorBlindSafety($red, $green);

foreach ($safety as $type => $result) {
    echo ucfirst($type) . ": ";
    echo $result['distinguishable'] ? "✓ Safe" : "✗ Problematic";
    echo " (Ratio: " . round($result['ratio'], 2) . ":1)\n";
}
```

### Designing for Color Blindness

**DO:**
- ✅ Use multiple visual cues (color + shape/icon/pattern)
- ✅ Ensure sufficient contrast (luminance difference)
- ✅ Test with color blindness simulators
- ✅ Use color-blind-safe palettes
- ✅ Provide text labels in addition to color

**DON'T:**
- ❌ Rely on color alone to convey information
- ❌ Use red/green combinations without other differentiation
- ❌ Use light colors that may appear similar
- ❌ Use subtle color differences for critical information

### Color-Blind-Safe Palettes

```php
// IBM Color Blind Safe Palette
function ibmColorBlindSafePalette(): array {
    return [
        Color::fromHex('#648FFF'),  // Blue
        Color::fromHex('#785EF0'),  // Purple
        Color::fromHex('#DC267F'),  // Magenta
        Color::fromHex('#FE6100'),  // Orange
        Color::fromHex('#FFB000'),  // Yellow
    ];
}

// Paul Tol's Bright Palette
function tolBrightPalette(): array {
    return [
        Color::fromHex('#4477AA'),  // Blue
        Color::fromHex('#EE6677'),  // Red
        Color::fromHex('#228833'),  // Green
        Color::fromHex('#CCBB44'),  // Yellow
        Color::fromHex('#66CCEE'),  // Cyan
        Color::fromHex('#AA3377'),  // Purple
        Color::fromHex('#BBBBBB'),  // Grey
    ];
}

// Okabe-Ito Palette (optimized for all color blindness types)
function okabeItoPalette(): array {
    return [
        Color::fromHex('#E69F00'),  // Orange
        Color::fromHex('#56B4E9'),  // Sky Blue
        Color::fromHex('#009E73'),  // Bluish Green
        Color::fromHex('#F0E442'),  // Yellow
        Color::fromHex('#0072B2'),  // Blue
        Color::fromHex('#D55E00'),  // Vermillion
        Color::fromHex('#CC79A7'),  // Reddish Purple
        Color::fromHex('#000000'),  // Black
    ];
}
```

---

## Non-Color Differentiators

### Patterns and Textures

```php
// Assign patterns to data series
$visualIndicators = [
    'solid' => '████',
    'stripes' => '┃┃┃┃',
    'dots' => '••••',
    'cross' => '╳╳╳╳',
    'diagonal' => '/////',
];

// In SVG or images:
// - Use different fill patterns
// - Apply different stroke styles (solid, dashed, dotted)
// - Add texture overlays
```

### Shapes and Icons

```php
// Chart data points
$shapeIndicators = [
    'circle' => '●',
    'square' => '■',
    'triangle' => '▲',
    'diamond' => '◆',
    'cross' => '✖',
    'star' => '★',
];

// Status indicators
$statusIcons = [
    'success' => '✓ Green background',
    'error' => '✖ Red background',
    'warning' => '⚠ Yellow background',
    'info' => 'ⓘ Blue background',
];
```

### Text Labels

```php
// Always provide text alongside color
function colorWithLabel($color, $label) {
    return [
        'color' => $color,
        'label' => $label,
        'display' => $label . ' (' . $color . ')',
    ];
}

// Example: Traffic light status
$statuses = [
    colorWithLabel('#22C55E', 'Active'),
    colorWithLabel('#EAB308', 'Pending'),
    colorWithLabel('#EF4444', 'Error'),
];
```

### Position and Size

```php
// Use visual hierarchy beyond color
function visualHierarchy() {
    return [
        'critical' => [
            'color' => '#EF4444',
            'size' => 'large',
            'position' => 'top',
            'weight' => 'bold',
        ],
        'important' => [
            'color' => '#F59E0B',
            'size' => 'medium',
            'position' => 'middle',
            'weight' => 'semi-bold',
        ],
        'normal' => [
            'color' => '#3B82F6',
            'size' => 'small',
            'position' => 'bottom',
            'weight' => 'normal',
        ],
    ];
}
```

---

## Testing for Accessibility

### Automated Testing Tools

```php
// Comprehensive accessibility check
function checkAccessibility(Color $foreground, Color $background): array {
    $ratio = contrastRatio($foreground, $background);

    return [
        'ratio' => round($ratio, 2),
        'compliance' => [
            'aa_normal' => $ratio >= 4.5,
            'aa_large' => $ratio >= 3,
            'aaa_normal' => $ratio >= 7,
            'aaa_large' => $ratio >= 4.5,
        ],
        'recommendations' => generateRecommendations($ratio, $foreground, $background),
    ];
}

function generateRecommendations(float $ratio, Color $fg, Color $bg): array {
    $recommendations = [];

    if ($ratio < 3) {
        $recommendations[] = 'CRITICAL: Insufficient contrast for any use';
        $recommendations[] = 'Suggestion: Increase lightness difference by at least 50%';
    } elseif ($ratio < 4.5) {
        $recommendations[] = 'WARNING: Only suitable for large text (18pt+ or 14pt+ bold)';
        $recommendations[] = 'Suggestion: Darken text or lighten background';
    } elseif ($ratio < 7) {
        $recommendations[] = 'GOOD: Meets WCAG AA for all text sizes';
        $recommendations[] = 'Consider: Increase to 7:1 for AAA compliance';
    } else {
        $recommendations[] = 'EXCELLENT: Meets WCAG AAA standards';
    }

    return $recommendations;
}

// Batch test entire palette
function auditPalette(array $colors): array {
    $results = [];

    foreach ($colors as $i => $color1) {
        foreach ($colors as $j => $color2) {
            if ($i >= $j) continue;  // Skip duplicates

            $key = "color{$i}_vs_color{$j}";
            $results[$key] = checkAccessibility($color1, $color2);
        }
    }

    return $results;
}
```

### Manual Testing Checklist

**Visual Inspection:**
- [ ] View design on different monitors (brightness, color accuracy)
- [ ] Test in different lighting conditions (bright, dim, outdoors)
- [ ] Check on mobile devices (smaller screens, varied displays)
- [ ] Review printed versions (if applicable)

**Color Blindness Testing:**
- [ ] Use browser extensions (Colorblindly, Spectrum)
- [ ] Test with simulators (Coblis, Color Oracle)
- [ ] Get feedback from color-blind users
- [ ] Verify all information is perceivable without color

**Contrast Testing:**
- [ ] Use automated checkers (WAVE, axe DevTools)
- [ ] Verify all text meets minimum ratios
- [ ] Check UI components (buttons, form fields, borders)
- [ ] Test focus indicators and active states

### Browser Developer Tools

Most modern browsers include accessibility checkers:

```javascript
// Chrome DevTools Accessibility Panel
// 1. Open DevTools (F12)
// 2. Select "Accessibility" tab
// 3. Inspect element contrast ratios
// 4. View accessibility tree

// Firefox Accessibility Inspector
// 1. Open DevTools (F12)
// 2. Select "Accessibility" tab
// 3. Check for issues
// 4. Simulate different color vision types
```

---

## Practical Guidelines

### Dark Mode Considerations

```php
// Adjust contrast ratios for dark mode
function darkModeColors(): array {
    return [
        // Light mode: dark text on light background (needs 4.5:1+)
        'light' => [
            'background' => Color::fromHex('#FFFFFF'),
            'text' => Color::fromHex('#1F2937'),  // Dark gray
        ],

        // Dark mode: light text on dark background (needs 4.5:1+)
        // Note: Pure white on pure black can cause "halation" (blurring)
        'dark' => [
            'background' => Color::fromHex('#111827'),  // Very dark gray
            'text' => Color::fromHex('#F3F4F6'),        // Off-white (not pure white)
        ],
    ];
}

// Generate dark mode variant
function toDarkMode(Color $lightColor): Color {
    $hsl = $lightColor->toHsl();

    // Invert lightness while preserving hue
    $newLightness = 100 - $hsl['l'];

    // Slightly reduce saturation for comfort
    $newSaturation = $hsl['s'] * 0.8;

    return Color::fromHsl($hsl['h'], $newSaturation, $newLightness);
}
```

### Interactive Elements

```php
// Button states need sufficient differentiation
function buttonStates(Color $base): array {
    $hsl = $base->toHsl();

    return [
        'default' => $base,
        'hover' => Color::fromHsl($hsl['h'], $hsl['s'], $hsl['l'] - 10),
        'active' => Color::fromHsl($hsl['h'], $hsl['s'], $hsl['l'] - 15),
        'disabled' => Color::fromHsl($hsl['h'], $hsl['s'] * 0.4, $hsl['l'] + 20),
        'focus' => $base,  // Use border/outline for focus, not just color
    ];
}

// Focus indicators (WCAG 2.2)
function focusIndicator(): array {
    return [
        'color' => Color::fromHex('#2563EB'),  // Blue
        'width' => '2px',
        'style' => 'solid',
        'offset' => '2px',
        'contrast' => 'minimum 3:1 with adjacent colors',
    ];
}
```

### Form Validation

```php
// Never rely on color alone for form feedback
function formValidationStates(): array {
    return [
        'error' => [
            'color' => Color::fromHex('#DC2626'),
            'icon' => '✖',
            'border' => 'solid 2px',
            'message' => 'Error: [specific issue]',
        ],
        'success' => [
            'color' => Color::fromHex('#16A34A'),
            'icon' => '✓',
            'border' => 'solid 2px',
            'message' => 'Success: [confirmation]',
        ],
        'warning' => [
            'color' => Color::fromHex('#CA8A04'),
            'icon' => '⚠',
            'border' => 'solid 2px',
            'message' => 'Warning: [concern]',
        ],
    ];
}
```

### Data Visualization

```php
// Accessible chart colors
function chartPalette(): array {
    // Use perceptually distinct colors with high contrast
    return [
        Color::fromHex('#003f5c'),  // Dark blue
        Color::fromHex('#58508d'),  // Purple
        Color::fromHex('#bc5090'),  // Magenta
        Color::fromHex('#ff6361'),  // Coral
        Color::fromHex('#ffa600'),  // Orange
    ];
}

// Ensure adjacent series are distinguishable
function validateChartSeries(array $colors): bool {
    for ($i = 0; $i < count($colors) - 1; $i++) {
        $ratio = contrastRatio($colors[$i], $colors[$i + 1]);

        if ($ratio < 3) {
            return false;  // Adjacent colors too similar
        }
    }

    return true;
}
```

---

## Common Accessibility Mistakes

### Mistake 1: Color as Only Indicator

```php
// ❌ WRONG: Status indicated by color only
function badStatusBadge($status) {
    $colors = [
        'success' => '#22C55E',
        'error' => '#EF4444',
        'pending' => '#F59E0B',
    ];

    return "<span style='color: {$colors[$status]}'></span>";
}

// ✅ CORRECT: Multiple indicators
function goodStatusBadge($status) {
    $indicators = [
        'success' => ['color' => '#22C55E', 'icon' => '✓', 'text' => 'Complete'],
        'error' => ['color' => '#EF4444', 'icon' => '✖', 'text' => 'Failed'],
        'pending' => ['color' => '#F59E0B', 'icon' => '⏳', 'text' => 'Pending'],
    ];

    $indicator = $indicators[$status];

    return "<span style='color: {$indicator['color']}' aria-label='{$indicator['text']}'>
        {$indicator['icon']} {$indicator['text']}
    </span>";
}
```

### Mistake 2: Low Contrast Text

```php
// ❌ WRONG: Insufficient contrast
$background = Color::fromHex('#FFFFFF');
$text = Color::fromHex('#999999');  // Ratio: 2.85:1 - FAILS

// ✅ CORRECT: Meets WCAG AA
$background = Color::fromHex('#FFFFFF');
$text = Color::fromHex('#595959');  // Ratio: 7.0:1 - PASSES
```

### Mistake 3: Red-Green Confusion

```php
// ❌ WRONG: Red/green without differentiation
function badComparisonChart() {
    return [
        'increase' => Color::fromHex('#22C55E'),  // Green
        'decrease' => Color::fromHex('#EF4444'),  // Red
        // Indistinguishable to color-blind users
    ];
}

// ✅ CORRECT: Additional visual cues
function goodComparisonChart() {
    return [
        'increase' => [
            'color' => Color::fromHex('#22C55E'),
            'icon' => '▲',
            'pattern' => 'diagonal-up',
        ],
        'decrease' => [
            'color' => Color::fromHex('#EF4444'),
            'icon' => '▼',
            'pattern' => 'diagonal-down',
        ],
    ];
}
```

### Mistake 4: Invisible Focus States

```php
// ❌ WRONG: Removing default focus outline
// CSS: button:focus { outline: none; }

// ✅ CORRECT: Custom, visible focus indicator
function accessibleFocusStyle() {
    return [
        'outline' => '2px solid #2563EB',
        'outline-offset' => '2px',
        'box-shadow' => '0 0 0 3px rgba(37, 99, 235, 0.2)',
    ];
}
```

---

## Tools and Resources

### Online Contrast Checkers

- **[WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)** - WCAG compliance checker
- **[Contrast Ratio](https://contrast-ratio.com/)** - Simple, real-time checker by Lea Verou
- **[Accessible Colors](https://accessible-colors.com/)** - Find accessible alternatives
- **[Colorable](https://colorable.jxnblk.com/)** - Contrast checker with palette generation

### Color Blindness Simulators

- **[Coblis](https://www.color-blindness.com/coblis-color-blindness-simulator/)** - Upload and test images
- **[Color Oracle](https://colororacle.org/)** - Free desktop simulator (Windows, Mac, Linux)
- **[Chromatic Vision Simulator](https://asada.website/cvsimulator/)** - Mobile app (iOS, Android)

### Browser Extensions

- **Chrome/Edge:**
  - Colorblindly (color blindness simulation)
  - WAVE (comprehensive accessibility testing)
  - axe DevTools (automated accessibility scanner)

- **Firefox:**
  - Built-in Accessibility Inspector
  - WCAG Contrast Checker

### Design Tools

- **[Adobe Color](https://color.adobe.com/create/color-accessibility)** - Accessibility checker
- **[Figma Contrast Plugin](https://www.figma.com/community/plugin/733159460536249875)** - Real-time contrast checking
- **[Stark](https://www.getstark.co/)** - Comprehensive accessibility plugin

### Automated Testing Libraries

```bash
# PHP Testing
composer require squizlabs/php_codesniffer  # With WCAG standards

# JavaScript/Node.js
npm install axe-core                        # Accessibility testing engine
npm install pa11y                           # Automated accessibility testing
npm install accessibility-checker           # IBM accessibility checker
```

---

## Related Guides

- [Color Spaces](./color-spaces.md) - Understanding LAB for perceptual uniformity
- [Color Theory](./color-theory.md) - Choosing harmonious color combinations
- [Color Manipulation](/guides/color-manipulation/) - Adjusting colors programmatically
- [Generating Color Schemes](/guides/color-schemes/) - Creating accessible palettes

---

## Further Reading

### Official Standards

- **[WCAG 2.1](https://www.w3.org/TR/WCAG21/)** - Web Content Accessibility Guidelines
- **[WCAG 2.2](https://www.w3.org/TR/WCAG22/)** - Latest version (2023)
- **[Section 508](https://www.section508.gov/)** - US federal accessibility standard
- **[EN 301 549](https://www.etsi.org/deliver/etsi_en/301500_301599/301549/)** - European accessibility standard

### Books and Articles

- **"Inclusive Design Patterns" by Heydon Pickering** - Practical accessibility patterns
- **"Color Accessibility Workflows" by Geri Coady** - A Book Apart
- **[WebAIM Color Contrast](https://webaim.org/articles/contrast/)** - In-depth article

### Research Papers

- **Brettel et al. (1997)** - "Computerized simulation of color appearance for dichromats"
- **Sharpe et al. (2005)** - "Opsin genes and color vision"
- **Fairchild (2013)** - "Color Appearance Models" (technical reference)

---

## Summary

Creating accessible color designs requires:

1. **Sufficient Contrast**
   - Minimum 4.5:1 for normal text (WCAG AA)
   - Minimum 3:1 for large text and UI components
   - Test with contrast calculators

2. **Color Blindness Considerations**
   - Never rely on color alone
   - Use patterns, shapes, icons, and labels
   - Test with simulators
   - Use color-blind-safe palettes

3. **Multiple Visual Cues**
   - Combine color with other indicators
   - Provide text labels
   - Use iconography
   - Implement patterns and textures

4. **Testing and Validation**
   - Use automated tools
   - Manual testing with simulators
   - Get feedback from users with disabilities
   - Test across devices and conditions

5. **Continuous Improvement**
   - Accessibility is an ongoing process
   - Stay updated with WCAG changes
   - Monitor user feedback
   - Regular audits

**Key Principle**: Good accessibility makes better design for everyone. What helps users with disabilities also improves usability, readability, and user experience for all users.
