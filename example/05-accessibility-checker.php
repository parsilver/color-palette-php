#!/usr/bin/env php
<?php

/**
 * Example 05: Accessibility Checker
 *
 * This example demonstrates:
 * - WCAG contrast ratio calculations
 * - AA and AAA compliance checking
 * - Suggested text colors for backgrounds
 * - Luminance and brightness analysis
 * - Creating accessible color combinations
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPaletteBuilder;

// Helper function to print section headers
function section($title)
{
    echo "\n".str_repeat('=', 70)."\n";
    echo '  '.$title."\n";
    echo str_repeat('=', 70)."\n\n";
}

// Helper function to get WCAG rating
function getWCAGRating($ratio, $isLargeText = false)
{
    if ($isLargeText) {
        if ($ratio >= 4.5) {
            return 'AAA';
        }
        if ($ratio >= 3.0) {
            return 'AA';
        }

        return 'FAIL';
    } else {
        if ($ratio >= 7.0) {
            return 'AAA';
        }
        if ($ratio >= 4.5) {
            return 'AA';
        }

        return 'FAIL';
    }
}

// Helper function to display contrast check
function displayContrast($bg, $fg, $label = '')
{
    $ratio = $bg->getContrastRatio($fg);
    $normalRating = getWCAGRating($ratio, false);
    $largeRating = getWCAGRating($ratio, true);

    if ($label) {
        echo "{$label}:\n";
    }
    printf("  %s on %s\n", $fg->toHex(), $bg->toHex());
    printf("  Contrast: %.2f:1  |  Normal: %-4s  |  Large: %-4s\n",
        $ratio, $normalRating, $largeRating);
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║      Color Palette PHP - Accessibility Checker Example          ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";

echo "\nWCAG (Web Content Accessibility Guidelines) Standards:\n";
echo "  • AA Normal Text:  4.5:1 minimum contrast ratio\n";
echo "  • AA Large Text:   3.0:1 minimum contrast ratio\n";
echo "  • AAA Normal Text: 7.0:1 minimum contrast ratio\n";
echo "  • AAA Large Text:  4.5:1 minimum contrast ratio\n";

// =============================================================================
// 1. Understanding Contrast Ratios
// =============================================================================

section('1. Understanding Contrast Ratios');

$white = Color::fromHex('#FFFFFF');
$black = Color::fromHex('#000000');

echo "Maximum contrast (Black on White):\n";
$maxRatio = $black->getContrastRatio($white);
printf("  Ratio: %.2f:1 (Perfect!)\n", $maxRatio);

echo "\nMinimum contrast (White on White):\n";
$minRatio = $white->getContrastRatio($white);
printf("  Ratio: %.2f:1 (No contrast)\n", $minRatio);

// =============================================================================
// 2. Common Color Combinations
// =============================================================================

section('2. Common Color Combinations');

$combinations = [
    ['bg' => '#FFFFFF', 'fg' => '#000000', 'name' => 'Black on White (Classic)'],
    ['bg' => '#000000', 'fg' => '#FFFFFF', 'name' => 'White on Black (Dark mode)'],
    ['bg' => '#3498DB', 'fg' => '#FFFFFF', 'name' => 'White on Blue'],
    ['bg' => '#E74C3C', 'fg' => '#FFFFFF', 'name' => 'White on Red'],
    ['bg' => '#27AE60', 'fg' => '#FFFFFF', 'name' => 'White on Green'],
    ['bg' => '#F39C12', 'fg' => '#FFFFFF', 'name' => 'White on Orange'],
    ['bg' => '#F39C12', 'fg' => '#000000', 'name' => 'Black on Orange'],
];

foreach ($combinations as $combo) {
    $bg = Color::fromHex($combo['bg']);
    $fg = Color::fromHex($combo['fg']);
    displayContrast($bg, $fg, $combo['name']);
    echo "\n";
}

// =============================================================================
// 3. Luminance and Brightness
// =============================================================================

section('3. Luminance vs Brightness');

$colors = [
    ['hex' => '#FF0000', 'name' => 'Pure Red'],
    ['hex' => '#00FF00', 'name' => 'Pure Green'],
    ['hex' => '#0000FF', 'name' => 'Pure Blue'],
    ['hex' => '#FFFF00', 'name' => 'Yellow'],
    ['hex' => '#FF00FF', 'name' => 'Magenta'],
    ['hex' => '#00FFFF', 'name' => 'Cyan'],
];

echo sprintf("%-15s %-9s %-12s %-12s %-10s\n",
    'Color', 'Hex', 'Brightness', 'Luminance', 'Is Light?');
echo str_repeat('-', 70)."\n";

foreach ($colors as $colorData) {
    $color = Color::fromHex($colorData['hex']);
    printf("%-15s %-9s %-12.2f %-12.4f %-10s\n",
        $colorData['name'],
        $color->toHex(),
        $color->getBrightness(),
        $color->getLuminance(),
        $color->isLight() ? 'Yes' : 'No'
    );
}

echo "\nNote: Luminance is perceptually accurate (green appears brightest)\n";
echo "      Brightness is a simple weighted average\n";

// =============================================================================
// 4. Suggested Text Colors
// =============================================================================

section('4. Suggested Text Colors for Backgrounds');

$backgrounds = [
    '#FFFFFF', '#ECF0F1', '#BDC3C7', '#95A5A6', '#7F8C8D',
    '#34495E', '#2C3E50', '#000000', '#E74C3C', '#3498DB',
    '#27AE60', '#F39C12', '#9B59B6', '#1ABC9C', '#E67E22',
];

echo "For each background, the library suggests either white or black text:\n\n";

echo sprintf("%-12s %-10s %-15s %-15s\n",
    'Background', 'Suggested', 'With White', 'With Black');
echo str_repeat('-', 70)."\n";

foreach ($backgrounds as $bgHex) {
    $bg = Color::fromHex($bgHex);
    $suggested = $bg->isLight() ? 'Black' : 'White';
    $whiteRatio = $bg->getContrastRatio($white);
    $blackRatio = $bg->getContrastRatio($black);

    printf("%-12s %-10s %-15s %-15s\n",
        $bgHex,
        $suggested,
        sprintf('%.2f:1 (%s)', $whiteRatio, getWCAGRating($whiteRatio)),
        sprintf('%.2f:1 (%s)', $blackRatio, getWCAGRating($blackRatio))
    );
}

// =============================================================================
// 5. Palette Accessibility Analysis
// =============================================================================

section('5. Palette Accessibility Analysis');

$palette = ColorPaletteBuilder::create()
    ->withBaseColor(Color::fromHex('#3498DB'))
    ->withScheme('website-theme')
    ->build();

echo "Analyzing website theme palette:\n\n";

$paletteColors = $palette->getColors();
echo "Colors in palette:\n";
foreach ($paletteColors as $name => $color) {
    printf("  %-12s %s\n", ucfirst((string) $name).':', $color->toHex());
}

echo "\n\nAccessible text combinations:\n";
echo str_repeat('-', 70)."\n";

$textColors = [$white, $black];
foreach ($paletteColors as $bgName => $bgColor) {
    echo "\nOn {$bgName} background ({$bgColor->toHex()}):\n";

    foreach ($textColors as $textColor) {
        $ratio = $bgColor->getContrastRatio($textColor);
        $rating = getWCAGRating($ratio);
        $textHex = $textColor->toHex();
        $status = ($rating === 'FAIL') ? '✗' : '✓';

        printf("  %s %s text: %.2f:1 (%s)\n",
            $status,
            ($textHex === '#FFFFFF') ? 'White' : 'Black',
            $ratio,
            $rating
        );
    }
}

// =============================================================================
// 6. Creating Accessible Color Pairs
// =============================================================================

section('6. Creating Accessible Color Pairs');

echo "Starting with a brand color, create accessible variations:\n\n";

$brandColor = Color::fromHex('#3498DB');
echo "Brand color: {$brandColor->toHex()}\n\n";

// Create darker version for white text
$darkVersion = $brandColor;
$attempts = 0;
while ($darkVersion->getContrastRatio($white) < 4.5 && $attempts < 10) {
    $darkVersion = $darkVersion->darken(0.05);
    $attempts++;
}

echo "Dark version for white text:\n";
printf("  Color: %s\n", $darkVersion->toHex());
printf("  Contrast with white: %.2f:1 (%s)\n",
    $darkVersion->getContrastRatio($white),
    getWCAGRating($darkVersion->getContrastRatio($white))
);

// Create lighter version for black text
$lightVersion = $brandColor;
$attempts = 0;
while ($lightVersion->getContrastRatio($black) < 4.5 && $attempts < 10) {
    $lightVersion = $lightVersion->lighten(0.05);
    $attempts++;
}

echo "\nLight version for black text:\n";
printf("  Color: %s\n", $lightVersion->toHex());
printf("  Contrast with black: %.2f:1 (%s)\n",
    $lightVersion->getContrastRatio($black),
    getWCAGRating($lightVersion->getContrastRatio($black))
);

// =============================================================================
// 7. Testing Popular Palettes
// =============================================================================

section('7. Testing Popular UI Palettes');

$testPalettes = [
    [
        'name' => 'Material Design Primary + White',
        'bg' => '#2196F3',
        'fg' => '#FFFFFF',
    ],
    [
        'name' => 'Bootstrap Primary + White',
        'bg' => '#007BFF',
        'fg' => '#FFFFFF',
    ],
    [
        'name' => 'GitHub Green + White',
        'bg' => '#2EA44F',
        'fg' => '#FFFFFF',
    ],
    [
        'name' => 'Tailwind Blue + White',
        'bg' => '#3B82F6',
        'fg' => '#FFFFFF',
    ],
];

foreach ($testPalettes as $test) {
    $bg = Color::fromHex($test['bg']);
    $fg = Color::fromHex($test['fg']);
    displayContrast($bg, $fg, $test['name']);
    echo "\n";
}

// =============================================================================
// 8. Practical Accessibility Tips
// =============================================================================

section('8. Practical Accessibility Tips');

echo "✓ Always aim for AA compliance (4.5:1) as a minimum\n";
echo "✓ AAA compliance (7.0:1) is preferred for body text\n";
echo "✓ Large text (18pt+ or 14pt+ bold) needs only 3.0:1 (AA) or 4.5:1 (AAA)\n";
echo "✓ Use the suggested text color methods for automatic selection\n";
echo "✓ Test with actual users who have visual impairments\n";
echo "✓ Don't rely on color alone to convey information\n\n";

echo "Common pitfalls:\n";
echo "  ✗ Light gray text on white (#999 on #FFF = 2.85:1 - FAIL)\n";
echo "  ✗ Yellow text on white (#FFFF00 on #FFF = 1.07:1 - FAIL)\n";
echo "  ✗ Light blue on white (#87CEEB on #FFF = 1.65:1 - FAIL)\n\n";

$lightGray = Color::fromHex('#999999');
$yellow = Color::fromHex('#FFFF00');
$lightBlue = Color::fromHex('#87CEEB');

printf("Actual ratios:\n");
printf("  #999 on white: %.2f:1\n", $lightGray->getContrastRatio($white));
printf("  Yellow on white: %.2f:1\n", $yellow->getContrastRatio($white));
printf("  Light blue on white: %.2f:1\n", $lightBlue->getContrastRatio($white));

// =============================================================================
// 9. Building an Accessible Theme
// =============================================================================

section('9. Building an Accessible Theme');

echo "Create a complete accessible color system:\n\n";

$primary = Color::fromHex('#1976D2');  // Darker blue
$success = Color::fromHex('#388E3C');  // Darker green
$warning = Color::fromHex('#F57C00');  // Darker orange
$error = Color::fromHex('#D32F2F');    // Darker red

$semanticColors = [
    'Primary' => $primary,
    'Success' => $success,
    'Warning' => $warning,
    'Error' => $error,
];

echo sprintf("%-12s %-9s %-15s %-15s %-10s\n",
    'Type', 'Color', 'White Text', 'Black Text', 'Passes');
echo str_repeat('-', 70)."\n";

foreach ($semanticColors as $name => $color) {
    $whiteRatio = $color->getContrastRatio($white);
    $blackRatio = $color->getContrastRatio($black);
    $bestText = $color->isLight() ? 'Black' : 'White';
    $bestRatio = $color->isLight() ? $blackRatio : $whiteRatio;
    $passes = ($bestRatio >= 4.5) ? 'AA ✓' : 'FAIL ✗';

    printf("%-12s %-9s %-15s %-15s %-10s\n",
        $name,
        $color->toHex(),
        sprintf('%.2f:1', $whiteRatio),
        sprintf('%.2f:1', $blackRatio),
        $passes
    );
}

// =============================================================================
// Summary
// =============================================================================

section('Summary');

echo "✓ WCAG Contrast Standards:\n";
echo "  • AA: 4.5:1 for normal text, 3.0:1 for large text\n";
echo "  • AAA: 7.0:1 for normal text, 4.5:1 for large text\n\n";

echo "✓ Key Methods:\n";
echo "  • getContrastRatio(\$color) - Calculate contrast ratio\n";
echo "  • getLuminance() - Get relative luminance (WCAG standard)\n";
echo "  • getBrightness() - Get simple brightness value\n";
echo "  • isLight() / isDark() - Quick light/dark detection\n\n";

echo "✓ Best Practices:\n";
echo "  • Always test color combinations for accessibility\n";
echo "  • Use suggested text colors for automatic selection\n";
echo "  • Aim for AA compliance minimum, AAA when possible\n";
echo "  • Create accessible variations of brand colors\n";
echo "  • Test with real users and assistive technologies\n\n";

echo "Next: Run 06-image-extraction.php to extract colors from images\n\n";
