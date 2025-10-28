#!/usr/bin/env php
<?php

/**
 * Example 04: All Palette Generation Schemes
 *
 * This example demonstrates all 11 palette generation strategies:
 * - Harmony-based: Complementary, Analogous, Triadic, Tetradic, Split-Complementary
 * - Lightness-based: Monochromatic, Shades, Tints
 * - Style-based: Pastel, Vibrant
 * - Purpose-based: Website Theme
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

// Helper function to display scheme details
function displayScheme($name, $description, $palette, $useCase)
{
    echo '╔'.str_repeat('═', 68)."╗\n";
    echo '║ '.str_pad($name, 67)."║\n";
    echo '╠'.str_repeat('═', 68)."╣\n";
    echo '║ '.str_pad($description, 67)."║\n";
    echo '╚'.str_repeat('═', 68)."╝\n\n";

    echo 'Colors: ';
    foreach ($palette->getColors() as $color) {
        echo $color->toHex().' ';
    }
    echo '('.count($palette->getColors())." colors)\n\n";

    echo "Best for: {$useCase}\n";
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║   Color Palette PHP - All Palette Generation Schemes Example    ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";

$baseColor = Color::fromHex('#3498DB');  // Nice blue
echo "\nBase Color: {$baseColor->toHex()} (Blue)\n";
echo "All schemes will be generated from this base color.\n";

// =============================================================================
// HARMONY-BASED SCHEMES
// =============================================================================

section('HARMONY-BASED SCHEMES');
echo "These schemes use color theory harmony rules based on the color wheel.\n";

// 1. Complementary
$complementary = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('complementary')
    ->build();

displayScheme(
    '1. COMPLEMENTARY',
    'Base color + opposite color on the color wheel (180° apart)',
    $complementary,
    'High contrast designs, call-to-action buttons, creating visual impact'
);

// 2. Analogous
$analogous = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('analogous')
    ->build();

displayScheme(
    '2. ANALOGOUS',
    'Three adjacent colors on the color wheel (-30°, 0°, +30°)',
    $analogous,
    'Serene, comfortable designs, nature themes, harmonious layouts'
);

// 3. Triadic
$triadic = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('triadic')
    ->build();

displayScheme(
    '3. TRIADIC',
    'Three colors evenly spaced on the color wheel (120° apart)',
    $triadic,
    'Vibrant, balanced designs, playful interfaces, diverse content'
);

// 4. Tetradic
$tetradic = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('tetradic')
    ->build();

displayScheme(
    '4. TETRADIC (Rectangle)',
    'Four colors forming two complementary pairs (90° intervals)',
    $tetradic,
    'Rich color schemes, complex designs, multiple content categories'
);

// 5. Split-Complementary
$splitComp = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('split-complementary')
    ->build();

displayScheme(
    '5. SPLIT-COMPLEMENTARY',
    'Base + two colors adjacent to its complement (150°, 210°)',
    $splitComp,
    'Softer than complementary, visual interest with less tension'
);

// =============================================================================
// LIGHTNESS-BASED SCHEMES
// =============================================================================

section('LIGHTNESS-BASED SCHEMES');
echo "These schemes vary the lightness while maintaining the same or similar hue.\n";

// 6. Monochromatic
$monochromatic = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('monochromatic', ['count' => 5])
    ->build();

displayScheme(
    '6. MONOCHROMATIC',
    'Same hue with varying lightness levels (5 variations)',
    $monochromatic,
    'Elegant, unified designs, sophisticated interfaces, minimalist aesthetics'
);

// 7. Shades
$shades = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('shades', ['count' => 5])
    ->build();

displayScheme(
    '7. SHADES',
    'Progressively darker versions of the base color',
    $shades,
    'Depth and hierarchy, dark mode themes, creating emphasis through darkness'
);

// 8. Tints
$tints = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('tints', ['count' => 5])
    ->build();

displayScheme(
    '8. TINTS',
    'Progressively lighter versions of the base color',
    $tints,
    'Soft backgrounds, light mode themes, subtle variations, pastel effects'
);

// =============================================================================
// STYLE-BASED SCHEMES
// =============================================================================

section('STYLE-BASED SCHEMES');
echo "These schemes create specific aesthetic styles with controlled saturation.\n";

// 9. Pastel
$pastel = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('pastel')
    ->build();

displayScheme(
    '9. PASTEL',
    'Soft, muted colors with low saturation and high lightness',
    $pastel,
    'Gentle designs, baby products, spring themes, calming interfaces'
);

// 10. Vibrant
$vibrant = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('vibrant')
    ->build();

displayScheme(
    '10. VIBRANT',
    'Highly saturated, energetic colors with full saturation',
    $vibrant,
    'Bold designs, youth-oriented brands, energetic content, sports themes'
);

// =============================================================================
// PURPOSE-BASED SCHEMES
// =============================================================================

section('PURPOSE-BASED SCHEMES');
echo "These schemes are designed for specific purposes like website themes.\n";

// 11. Website Theme
$websiteTheme = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('website-theme')
    ->build();

echo '╔'.str_repeat('═', 68)."╗\n";
echo '║ '.str_pad('11. WEBSITE THEME', 67)."║\n";
echo '╠'.str_repeat('═', 68)."╣\n";
echo '║ '.str_pad('Complete semantic theme palette for websites', 67)."║\n";
echo '╚'.str_repeat('═', 68)."╝\n\n";

echo "Semantic colors:\n";
foreach ($websiteTheme->getColors() as $name => $color) {
    printf("  %-12s %s\n", ucfirst((string) $name).':', $color->toHex());
}

echo "\nBest for: Complete website themes, design systems, comprehensive color schemes\n";

// =============================================================================
// COMPARISON TABLE
// =============================================================================

section('QUICK COMPARISON TABLE');

$schemes = [
    ['name' => 'Complementary',         'colors' => 2,  'harmony' => 'High contrast'],
    ['name' => 'Analogous',             'colors' => 3,  'harmony' => 'Low contrast'],
    ['name' => 'Triadic',               'colors' => 3,  'harmony' => 'Medium contrast'],
    ['name' => 'Tetradic',              'colors' => 4,  'harmony' => 'High variety'],
    ['name' => 'Split-Complementary',   'colors' => 3,  'harmony' => 'Medium contrast'],
    ['name' => 'Monochromatic',         'colors' => 5,  'harmony' => 'Unified'],
    ['name' => 'Shades',                'colors' => 5,  'harmony' => 'Dark progression'],
    ['name' => 'Tints',                 'colors' => 5,  'harmony' => 'Light progression'],
    ['name' => 'Pastel',                'colors' => 5,  'harmony' => 'Soft, muted'],
    ['name' => 'Vibrant',               'colors' => 5,  'harmony' => 'Bold, energetic'],
    ['name' => 'Website Theme',         'colors' => 5,  'harmony' => 'Semantic purpose'],
];

printf("%-25s %-8s %s\n", 'Scheme', 'Colors', 'Character');
echo str_repeat('-', 70)."\n";

foreach ($schemes as $scheme) {
    printf("%-25s %-8s %s\n",
        $scheme['name'],
        $scheme['colors'],
        $scheme['harmony']
    );
}

// =============================================================================
// VISUAL COMPARISON WITH DIFFERENT BASE COLORS
// =============================================================================

section('SCHEMES WITH DIFFERENT BASE COLORS');

$testColors = [
    ['name' => 'Red',    'hex' => '#E74C3C'],
    ['name' => 'Green',  'hex' => '#27AE60'],
    ['name' => 'Purple', 'hex' => '#9B59B6'],
];

foreach ($testColors as $testColor) {
    echo "\nBase: {$testColor['name']} ({$testColor['hex']})\n";
    echo str_repeat('-', 70)."\n";

    $base = Color::fromHex($testColor['hex']);

    // Show a few key schemes
    $schemeNames = ['complementary', 'analogous', 'triadic'];

    foreach ($schemeNames as $schemeName) {
        $palette = ColorPaletteBuilder::create()
            ->withBaseColor($base)
            ->withScheme($schemeName)
            ->build();

        printf('  %-20s ', ucfirst($schemeName).':');
        foreach ($palette->getColors() as $color) {
            echo $color->toHex().' ';
        }
        echo "\n";
    }
}

// =============================================================================
// CUSTOMIZATION OPTIONS
// =============================================================================

section('CUSTOMIZATION OPTIONS');

echo "Some schemes accept options to customize the output:\n\n";

// Monochromatic with different counts
echo "Monochromatic with different color counts:\n";
foreach ([3, 5, 7, 10] as $count) {
    $palette = ColorPaletteBuilder::create()
        ->withBaseColor($baseColor)
        ->withScheme('monochromatic', ['count' => $count])
        ->build();

    echo "  {$count} colors: ";
    foreach ($palette->getColors() as $color) {
        echo $color->toHex().' ';
    }
    echo "\n";
}

echo "\nShades with different counts:\n";
foreach ([3, 5, 7] as $count) {
    $palette = ColorPaletteBuilder::create()
        ->withBaseColor($baseColor)
        ->withScheme('shades', ['count' => $count])
        ->build();

    echo "  {$count} shades: ";
    foreach ($palette->getColors() as $color) {
        echo $color->toHex().' ';
    }
    echo "\n";
}

// =============================================================================
// PRACTICAL RECOMMENDATIONS
// =============================================================================

section('PRACTICAL RECOMMENDATIONS');

echo "Choosing the right scheme for your project:\n\n";

$recommendations = [
    [
        'project' => 'Corporate Website',
        'schemes' => ['Monochromatic', 'Split-Complementary', 'Website Theme'],
        'why' => 'Professional, unified look',
    ],
    [
        'project' => 'E-commerce Site',
        'schemes' => ['Complementary', 'Website Theme'],
        'why' => 'Draw attention to CTAs and products',
    ],
    [
        'project' => 'Creative Portfolio',
        'schemes' => ['Triadic', 'Tetradic', 'Vibrant'],
        'why' => 'Showcase creativity and diversity',
    ],
    [
        'project' => 'Wellness App',
        'schemes' => ['Analogous', 'Pastel', 'Tints'],
        'why' => 'Calming, serene user experience',
    ],
    [
        'project' => 'Gaming Interface',
        'schemes' => ['Vibrant', 'Complementary', 'Triadic'],
        'why' => 'Energetic, exciting visual impact',
    ],
    [
        'project' => 'Admin Dashboard',
        'schemes' => ['Monochromatic', 'Shades', 'Website Theme'],
        'why' => 'Clear hierarchy, professional appearance',
    ],
];

foreach ($recommendations as $rec) {
    echo "Project: {$rec['project']}\n";
    echo '  Suggested: '.implode(', ', $rec['schemes'])."\n";
    echo "  Why: {$rec['why']}\n\n";
}

// =============================================================================
// Summary
// =============================================================================

section('Summary');

echo "✓ 11 palette generation schemes available:\n\n";

echo "  Harmony (Color Wheel):\n";
echo "    • Complementary (2 colors) - Opposite colors\n";
echo "    • Analogous (3 colors) - Adjacent colors\n";
echo "    • Triadic (3 colors) - Evenly spaced\n";
echo "    • Tetradic (4 colors) - Two complementary pairs\n";
echo "    • Split-Complementary (3 colors) - Softer complement\n\n";

echo "  Lightness Variations:\n";
echo "    • Monochromatic (5+ colors) - Same hue, different lightness\n";
echo "    • Shades (5+ colors) - Darker variations\n";
echo "    • Tints (5+ colors) - Lighter variations\n\n";

echo "  Style-Based:\n";
echo "    • Pastel (5 colors) - Soft, muted colors\n";
echo "    • Vibrant (5 colors) - Bold, saturated colors\n\n";

echo "  Purpose-Based:\n";
echo "    • Website Theme (5 colors) - Semantic color system\n\n";

echo "✓ Choose schemes based on your design goals and brand personality\n";
echo "✓ Some schemes accept options like 'count' to control output size\n";
echo "✓ Experiment with different base colors to find the perfect palette\n\n";

echo "Next: Run 05-accessibility-checker.php to learn about WCAG compliance\n\n";
