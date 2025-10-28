#!/usr/bin/env php
<?php

/**
 * Example 06: Image Color Extraction
 *
 * This example demonstrates:
 * - Extracting dominant colors from images
 * - Using ColorPaletteBuilder for image extraction
 * - Getting suggested surface colors
 * - Extracting different numbers of colors
 * - Practical use cases for extracted palettes
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ColorPaletteBuilder;
use Farzai\ColorPalette\ImageLoaderFactory;

// Helper function to print section headers
function section($title)
{
    echo "\n".str_repeat('=', 70)."\n";
    echo '  '.$title."\n";
    echo str_repeat('=', 70)."\n\n";
}

// Helper function to display palette
function displayPalette($palette, $label = null)
{
    if ($label) {
        echo "{$label}:\n";
    }
    $colors = $palette->getColors();
    $count = 0;
    foreach ($colors as $key => $color) {
        $count++;
        if (is_string($key)) {
            printf("  [%-15s] %s\n", $key, $color->toHex());
        } else {
            printf("  Color %-2d: %s\n", $count, $color->toHex());
        }
    }
    echo '  Total: '.count($colors)." colors\n";
}

echo "\n╔══════════════════════════════════════════════════════════════════╗\n";
echo "║     Color Palette PHP - Image Color Extraction Example          ║\n";
echo "╚══════════════════════════════════════════════════════════════════╝\n";

$sampleImage = __DIR__.'/../assets/sample.jpg';

if (! file_exists($sampleImage)) {
    echo "\n⚠️  Sample image not found at: {$sampleImage}\n";
    echo "Please make sure the sample image exists to run this example.\n\n";
    exit(1);
}

echo "\nUsing sample image: ".basename($sampleImage)."\n";

// =============================================================================
// 1. Simple Extraction with ColorPaletteBuilder
// =============================================================================

section('1. Simple Extraction with ColorPaletteBuilder');

echo "The easiest way to extract colors is using ColorPaletteBuilder:\n\n";

$palette = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(5)
    ->build();

displayPalette($palette, 'Extracted 5 dominant colors');

// =============================================================================
// 2. Different Color Counts
// =============================================================================

section('2. Extracting Different Numbers of Colors');

echo "You can extract anywhere from 1 to many colors:\n\n";

foreach ([3, 5, 7, 10] as $count) {
    $p = ColorPaletteBuilder::create()
        ->fromImage($sampleImage)
        ->withCount($count)
        ->build();

    echo "{$count} colors: ";
    foreach ($p->getColors() as $color) {
        echo $color->toHex().' ';
    }
    echo "\n";
}

echo "\nTip: 5-6 colors usually provides a good balance\n";

// =============================================================================
// 3. Advanced Extraction with Factory Classes
// =============================================================================

section('3. Advanced Extraction with Factory Classes');

echo "For more control, use the factory classes directly:\n\n";

// Create image loader
$loaderFactory = new ImageLoaderFactory;
$loader = $loaderFactory->create();
$image = $loader->load($sampleImage);

echo "✓ Image loaded: {$image->getWidth()}x{$image->getHeight()} pixels\n\n";

// Create color extractor (using GD)
$extractorFactory = new ColorExtractorFactory;
$extractor = $extractorFactory->make('gd');

// Extract colors
$palette = $extractor->extract($image, 6);

echo "Extracted colors using GD extractor:\n";
displayPalette($palette);

// =============================================================================
// 4. Suggested Surface Colors
// =============================================================================

section('4. Suggested Surface Colors');

echo "The library can suggest surface colors from the extracted palette:\n\n";

$palette = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(6)
    ->build();

$surfaceColors = $palette->getSuggestedSurfaceColors();

echo "Suggested surface colors for UI design:\n\n";

foreach ($surfaceColors as $name => $color) {
    $hex = $color->toHex();
    $isLight = $color->isLight() ? 'Light' : 'Dark';
    $suggestedText = $color->isLight() ? 'Black' : 'White';

    printf("%-18s %s  (%s, use %s text)\n",
        ucfirst(str_replace('_', ' ', $name)).':',
        $hex,
        $isLight,
        $suggestedText
    );
}

echo "\nSurface color types:\n";
echo "  • surface        - Main surface/card background\n";
echo "  • background     - Page background\n";
echo "  • accent         - Accent/highlight color\n";
echo "  • surface_variant - Alternative surface color\n";

// =============================================================================
// 5. Combining Extraction with Manual Colors
// =============================================================================

section('5. Combining Extraction with Manual Colors');

echo "Extract colors from image and add semantic colors:\n\n";

$combinedPalette = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(3)
    ->addColor(Color::fromHex('#FFFFFF'), 'white')
    ->addColor(Color::fromHex('#000000'), 'black')
    ->addColor(Color::fromHex('#F8F9FA'), 'light-gray')
    ->build();

displayPalette($combinedPalette, 'Combined palette');

// =============================================================================
// 6. Building a Theme from Image
// =============================================================================

section('6. Building a Complete Theme from Image');

echo "Extract dominant colors and build a complete theme:\n\n";

// Extract dominant colors
$extracted = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(1)  // Get the most dominant color
    ->build();

$dominantColor = $extracted->getColors()[0];
echo "Dominant color from image: {$dominantColor->toHex()}\n\n";

// Generate theme from dominant color
$theme = ColorPaletteBuilder::create()
    ->withBaseColor($dominantColor)
    ->withScheme('website-theme')
    ->build();

echo "Generated website theme:\n";
displayPalette($theme);

// =============================================================================
// 7. Practical Use Case: Brand Color Extraction
// =============================================================================

section('7. Practical Use Case: Logo Color Extraction');

echo "Extract colors from a logo/brand image and create variations:\n\n";

$brandPalette = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(3)
    ->build();

echo "Extracted brand colors:\n";
$brandColors = $brandPalette->getColors();
$primaryBrand = $brandColors[0];
$secondaryBrand = $brandColors[1];

echo "  Primary:   {$primaryBrand->toHex()}\n";
echo "  Secondary: {$secondaryBrand->toHex()}\n\n";

echo "Create hover states:\n";
echo '  Primary hover:   '.$primaryBrand->lighten(0.1)->toHex()."\n";
echo '  Primary active:  '.$primaryBrand->darken(0.1)->toHex()."\n\n";

echo "Create muted versions:\n";
echo '  Primary muted:   '.$primaryBrand->desaturate(0.3)->toHex()."\n";
echo '  Secondary muted: '.$secondaryBrand->desaturate(0.3)->toHex()."\n";

// =============================================================================
// 8. Accessibility Check on Extracted Colors
// =============================================================================

section('8. Accessibility Check on Extracted Colors');

echo "Check if extracted colors are accessible for text:\n\n";

$palette = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(5)
    ->build();

$white = Color::fromHex('#FFFFFF');
$black = Color::fromHex('#000000');

echo sprintf("%-12s %-15s %-15s %-12s\n",
    'Color', 'White Text', 'Black Text', 'Best Choice');
echo str_repeat('-', 70)."\n";

foreach ($palette->getColors() as $color) {
    $whiteRatio = $color->getContrastRatio($white);
    $blackRatio = $color->getContrastRatio($black);
    $bestText = $color->isLight() ? 'Black' : 'White';
    $bestRatio = $color->isLight() ? $blackRatio : $whiteRatio;
    $passes = ($bestRatio >= 4.5) ? '✓' : '✗';

    printf("%-12s %-15s %-15s %-12s\n",
        $color->toHex(),
        sprintf('%.2f:1', $whiteRatio),
        sprintf('%.2f:1', $blackRatio),
        "{$bestText} {$passes}"
    );
}

// =============================================================================
// 9. Creating Harmonious Palettes from Image
// =============================================================================

section('9. Creating Harmonious Palettes from Image');

echo "Extract dominant color and generate color harmonies:\n\n";

$extracted = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(1)
    ->build();

$baseColor = $extracted->getColors()[0];
echo "Base color (from image): {$baseColor->toHex()}\n\n";

$harmonies = [
    'complementary',
    'analogous',
    'triadic',
    'monochromatic',
];

foreach ($harmonies as $harmony) {
    $harmonyPalette = ColorPaletteBuilder::create()
        ->withBaseColor($baseColor)
        ->withScheme($harmony)
        ->build();

    printf('%-18s ', ucfirst($harmony).':');
    foreach ($harmonyPalette->getColors() as $color) {
        echo $color->toHex().' ';
    }
    echo "\n";
}

// =============================================================================
// 10. Practical Tips
// =============================================================================

section('10. Practical Tips for Image Extraction');

echo "Best practices for extracting colors from images:\n\n";

echo "✓ Image Quality:\n";
echo "  • Use high-quality images for better color extraction\n";
echo "  • Avoid heavily compressed or low-resolution images\n";
echo "  • Images with clear, distinct colors work best\n\n";

echo "✓ Number of Colors:\n";
echo "  • 3-5 colors: Good for minimal, focused palettes\n";
echo "  • 5-7 colors: Ideal for most use cases\n";
echo "  • 8-10 colors: Comprehensive palette with more variety\n\n";

echo "✓ Using Extracted Colors:\n";
echo "  • The first color is usually the most dominant\n";
echo "  • Use getSuggestedSurfaceColors() for UI design\n";
echo "  • Combine with manual colors for complete themes\n";
echo "  • Generate harmonies from the dominant color\n\n";

echo "✓ Accessibility:\n";
echo "  • Always check contrast ratios for text colors\n";
echo "  • Extracted colors may not be accessible by default\n";
echo "  • Adjust lightness/saturation if needed for compliance\n";
echo "  • Use suggested text color methods\n\n";

// =============================================================================
// 11. Supported Image Formats
// =============================================================================

section('11. Supported Image Formats');

echo "The library supports multiple image formats:\n\n";

echo "✓ JPEG (.jpg, .jpeg) - Most common, good for photos\n";
echo "✓ PNG (.png) - Supports transparency\n";
echo "✓ GIF (.gif) - Animated and static images\n";
echo "✓ WebP (.webp) - Modern format with good compression\n\n";

echo "Backends:\n";
echo "  • GD Extension (built into most PHP installations)\n";
echo "  • ImageMagick (more advanced, requires extension)\n\n";

// =============================================================================
// 12. Export Example
// =============================================================================

section('12. Exporting Extracted Colors');

echo "Export extracted palette in different formats:\n\n";

$palette = ColorPaletteBuilder::create()
    ->fromImage($sampleImage)
    ->withCount(5)
    ->build();

// As CSS Variables
echo "CSS Variables:\n";
echo ":root {\n";
$i = 1;
foreach ($palette->getColors() as $color) {
    echo "  --color-{$i}: {$color->toHex()};\n";
    $i++;
}
echo "}\n\n";

// As JSON
echo "JSON:\n";
$jsonColors = array_map(fn ($color) => $color->toHex(), $palette->getColors());
echo json_encode($jsonColors, JSON_PRETTY_PRINT)."\n\n";

// As PHP Array
echo "PHP Array:\n";
echo "[\n";
foreach ($palette->getColors() as $color) {
    echo "  '{$color->toHex()}',\n";
}
echo "]\n";

// =============================================================================
// Summary
// =============================================================================

section('Summary');

echo "✓ Two ways to extract colors from images:\n";
echo "  1. ColorPaletteBuilder::create()->fromImage(\$path)->withCount(\$n)\n";
echo "  2. ImageLoaderFactory + ColorExtractorFactory (advanced)\n\n";

echo "✓ Key features:\n";
echo "  • Extract 1-N dominant colors from any image\n";
echo "  • Get suggested surface colors for UI design\n";
echo "  • Combine with manual colors or schemes\n";
echo "  • Generate themes from extracted colors\n";
echo "  • Check accessibility of extracted colors\n\n";

echo "✓ Practical applications:\n";
echo "  • Brand color extraction from logos\n";
echo "  • Theme generation from hero images\n";
echo "  • Creating cohesive designs from photos\n";
echo "  • Extracting product colors from images\n\n";

echo "Congratulations! You've completed all CLI examples.\n";
echo "Try the web examples next for interactive color exploration!\n\n";
