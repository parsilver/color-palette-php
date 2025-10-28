#!/usr/bin/env php
<?php

/**
 * Example 03: ColorPaletteBuilder
 *
 * This example demonstrates:
 * - Using the fluent ColorPaletteBuilder API
 * - Manual color addition
 * - Strategy-based palette generation
 * - Image-based color extraction
 * - Method chaining patterns
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPaletteBuilder;

// Helper function to print section headers
function section($title)
{
    echo "\n".str_repeat('=', 60)."\n";
    echo '  '.$title."\n";
    echo str_repeat('=', 60)."\n\n";
}

// Helper function to display palette
function displayPalette($palette, $label = null)
{
    if ($label) {
        echo "{$label}:\n";
    }
    $colors = $palette->getColors();
    echo '  Colors: ';
    foreach ($colors as $color) {
        echo $color->toHex().' ';
    }
    echo '('.count($colors)." colors)\n";
}

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║    Color Palette PHP - ColorPaletteBuilder Example      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";

// =============================================================================
// 1. Basic Builder Usage
// =============================================================================

section('1. Basic Builder Usage - Manual Color Addition');

// Create a palette by adding colors manually
$palette1 = ColorPaletteBuilder::create()
    ->addColor(Color::fromHex('#E74C3C'))  // Red
    ->addColor(Color::fromHex('#3498DB'))  // Blue
    ->addColor(Color::fromHex('#2ECC71'))  // Green
    ->build();

displayPalette($palette1, 'Simple palette');

// Adding multiple colors at once
$palette2 = ColorPaletteBuilder::create()
    ->addColors([
        Color::fromHex('#1ABC9C'),
        Color::fromHex('#16A085'),
        Color::fromHex('#27AE60'),
        Color::fromHex('#229954'),
    ])
    ->build();

displayPalette($palette2, "\nBulk color addition");

// Adding colors with keys for semantic naming
$palette3 = ColorPaletteBuilder::create()
    ->addColor(Color::fromHex('#E74C3C'), 'primary')
    ->addColor(Color::fromHex('#95A5A6'), 'secondary')
    ->addColor(Color::fromHex('#3498DB'), 'accent')
    ->addColor(Color::fromHex('#ECF0F1'), 'background')
    ->build();

echo "\nNamed colors palette:\n";
$colors = $palette3->getColors();
foreach (['primary', 'secondary', 'accent', 'background'] as $key) {
    if (isset($colors[$key])) {
        echo "  {$key}: {$colors[$key]->toHex()}\n";
    }
}

// =============================================================================
// 2. Strategy-Based Palette Generation
// =============================================================================

section('2. Strategy-Based Palette Generation');

$baseColor = Color::fromHex('#3498DB');  // Blue
echo "Base color: {$baseColor->toHex()}\n\n";

// Complementary scheme
$complementary = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('complementary')
    ->build();
displayPalette($complementary, 'Complementary');

// Analogous scheme
$analogous = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('analogous')
    ->build();
displayPalette($analogous, 'Analogous');

// Triadic scheme
$triadic = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('triadic')
    ->build();
displayPalette($triadic, 'Triadic');

// Monochromatic with custom count
$monochromatic = ColorPaletteBuilder::create()
    ->withBaseColor($baseColor)
    ->withScheme('monochromatic', ['count' => 7])
    ->build();
displayPalette($monochromatic, 'Monochromatic (7 colors)');

// =============================================================================
// 3. All Available Schemes
// =============================================================================

section('3. All Available Schemes');

$schemes = [
    'monochromatic',
    'complementary',
    'analogous',
    'triadic',
    'tetradic',
    'split-complementary',
    'shades',
    'tints',
    'pastel',
    'vibrant',
    'website-theme',
];

$base = Color::fromHex('#E74C3C');  // Red
echo "Base color: {$base->toHex()}\n";
echo str_repeat('-', 60)."\n\n";

foreach ($schemes as $scheme) {
    $palette = ColorPaletteBuilder::create()
        ->withBaseColor($base)
        ->withScheme($scheme)
        ->build();

    printf('%-22s ', ucwords(str_replace('-', ' ', $scheme)).':');
    $colors = $palette->getColors();
    foreach ($colors as $color) {
        echo $color->toHex().' ';
    }
    echo '('.count($colors).")\n";
}

// =============================================================================
// 4. Image-Based Palette Extraction
// =============================================================================

section('4. Image-Based Palette Extraction');

$sampleImage = __DIR__.'/../assets/sample.jpg';

if (file_exists($sampleImage)) {
    echo "Extracting colors from: {$sampleImage}\n\n";

    // Extract 5 colors from image
    $palette = ColorPaletteBuilder::create()
        ->fromImage($sampleImage)
        ->withCount(5)
        ->build();

    displayPalette($palette, 'Extracted palette');

    // Extract different counts
    echo "\nDifferent extraction counts:\n";
    foreach ([3, 5, 7, 10] as $count) {
        $p = ColorPaletteBuilder::create()
            ->fromImage($sampleImage)
            ->withCount($count)
            ->build();
        echo "  {$count} colors: ";
        foreach ($p->getColors() as $color) {
            echo $color->toHex().' ';
        }
        echo "\n";
    }
} else {
    echo "Sample image not found at: {$sampleImage}\n";
    echo "Skipping image extraction examples.\n";
}

// =============================================================================
// 5. Combining Approaches
// =============================================================================

section('5. Combining Different Approaches');

// Start with image extraction, then add custom colors
if (file_exists($sampleImage)) {
    $palette = ColorPaletteBuilder::create()
        ->fromImage($sampleImage)
        ->withCount(3)
        ->addColor(Color::fromHex('#FFFFFF'), 'white')  // Add white
        ->addColor(Color::fromHex('#000000'), 'black')  // Add black
        ->build();

    echo "Image extraction + manual additions:\n";
    displayPalette($palette);
}

// Start with a scheme, then add more colors
$palette = ColorPaletteBuilder::create()
    ->withBaseColor(Color::fromHex('#9B59B6'))
    ->withScheme('complementary')
    ->addColor(Color::fromHex('#ECF0F1'), 'background')
    ->addColor(Color::fromHex('#34495E'), 'text')
    ->build();

echo "\nScheme + manual additions:\n";
displayPalette($palette);

// =============================================================================
// 6. Practical Use Cases
// =============================================================================

section('6. Practical Use Cases');

// Use Case 1: Build a complete website theme
echo "Use Case 1: Website Theme Builder\n";
echo str_repeat('-', 60)."\n";

$brandColor = Color::fromHex('#3498DB');
$theme = ColorPaletteBuilder::create()
    ->withBaseColor($brandColor)
    ->withScheme('website-theme')
    ->build();

echo "Theme colors:\n";
foreach ($theme->getColors() as $name => $color) {
    printf("  %-15s %s\n", ucfirst((string) $name).':', $color->toHex());
}

// Use Case 2: Create UI state variations
echo "\n\nUse Case 2: Button State Variations\n";
echo str_repeat('-', 60)."\n";

$buttonBase = Color::fromHex('#E74C3C');
$buttonStates = ColorPaletteBuilder::create()
    ->addColor($buttonBase, 'default')
    ->addColor($buttonBase->lighten(0.1), 'hover')
    ->addColor($buttonBase->darken(0.1), 'active')
    ->addColor($buttonBase->desaturate(0.5)->lighten(0.2), 'disabled')
    ->build();

foreach ($buttonStates->getColors() as $state => $color) {
    printf("  %-10s %s\n", ucfirst((string) $state).':', $color->toHex());
}

// Use Case 3: Social media brand colors
echo "\n\nUse Case 3: Social Media Brand Palette\n";
echo str_repeat('-', 60)."\n";

$socialPalette = ColorPaletteBuilder::create()
    ->addColors([
        Color::fromHex('#1DA1F2'),  // Twitter
        Color::fromHex('#4267B2'),  // Facebook
        Color::fromHex('#E1306C'),  // Instagram
        Color::fromHex('#FF0000'),  // YouTube
        Color::fromHex('#0077B5'),  // LinkedIn
    ])
    ->build();

displayPalette($socialPalette, 'Social media colors');

// Use Case 4: Semantic color system
echo "\n\nUse Case 4: Semantic Color System\n";
echo str_repeat('-', 60)."\n";

$semanticColors = ColorPaletteBuilder::create()
    ->addColor(Color::fromHex('#27AE60'), 'success')
    ->addColor(Color::fromHex('#F39C12'), 'warning')
    ->addColor(Color::fromHex('#E74C3C'), 'error')
    ->addColor(Color::fromHex('#3498DB'), 'info')
    ->addColor(Color::fromHex('#95A5A6'), 'neutral')
    ->build();

foreach ($semanticColors->getColors() as $type => $color) {
    printf("  %-10s %s\n", ucfirst((string) $type).':', $color->toHex());
}

// =============================================================================
// 7. Method Chaining Examples
// =============================================================================

section('7. Method Chaining Best Practices');

echo "Example 1: Fluent theme creation\n";
echo str_repeat('-', 60)."\n\n";

$fullTheme = ColorPaletteBuilder::create()
    ->withBaseColor(Color::fromHex('#9B59B6'))
    ->withScheme('website-theme')
    ->addColor(Color::fromHex('#FFFFFF'), 'white')
    ->addColor(Color::fromHex('#000000'), 'black')
    ->addColors([
        Color::fromHex('#27AE60'),  // Success
        Color::fromHex('#E74C3C'),  // Error
    ])
    ->build();

echo 'Total colors in theme: '.count($fullTheme->getColors())."\n";

// The builder pattern allows for readable, maintainable code
echo "\n\nExample 2: Conditional palette building\n";
echo str_repeat('-', 60)."\n\n";

$builder = ColorPaletteBuilder::create()
    ->withBaseColor(Color::fromHex('#3498DB'));

// You can conditionally add colors based on requirements
$isDarkMode = true;
if ($isDarkMode) {
    $builder->addColor(Color::fromHex('#2C3E50'), 'dark-bg')
        ->addColor(Color::fromHex('#ECF0F1'), 'light-text');
} else {
    $builder->addColor(Color::fromHex('#ECF0F1'), 'light-bg')
        ->addColor(Color::fromHex('#2C3E50'), 'dark-text');
}

$conditionalPalette = $builder->build();
echo "Dark mode palette:\n";
displayPalette($conditionalPalette);

// =============================================================================
// Summary
// =============================================================================

section('Summary');

echo "✓ ColorPaletteBuilder provides a fluent API for palette creation\n\n";

echo "✓ Three main approaches:\n";
echo "  1. Manual: addColor() and addColors()\n";
echo "  2. Strategy: withBaseColor() + withScheme()\n";
echo "  3. Image: fromImage() + withCount()\n\n";

echo "✓ Available schemes:\n";
echo "  Harmony: complementary, analogous, triadic, tetradic, split-complementary\n";
echo "  Lightness: monochromatic, shades, tints\n";
echo "  Style: pastel, vibrant\n";
echo "  Purpose: website-theme\n\n";

echo "✓ Methods can be chained for readable code\n";
echo "✓ Colors can be named with keys for semantic meaning\n";
echo "✓ Perfect for building design systems and themes\n\n";

echo "Next: Run 04-all-schemes.php to see visual examples of all schemes\n\n";
