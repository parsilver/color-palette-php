#!/usr/bin/env php
<?php

/**
 * Example 01: Basic Colors
 *
 * This example demonstrates:
 * - Creating colors from different formats (Hex, RGB, HSL, HSV, CMYK, LAB)
 * - Converting between color formats
 * - Accessing color properties
 * - Understanding brightness and luminance
 */

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;

// Helper function to print section headers
function section($title)
{
    echo "\n".str_repeat('=', 60)."\n";
    echo '  '.$title."\n";
    echo str_repeat('=', 60)."\n\n";
}

// Helper function to display color info
function displayColor($label, Color $color)
{
    echo sprintf('%-20s ', $label.':');
    echo sprintf("Hex: %-7s  RGB: %-15s  HSL: %s\n",
        $color->toHex(),
        sprintf('(%d, %d, %d)', $color->getRed(), $color->getGreen(), $color->getBlue()),
        sprintf('(%d°, %d%%, %d%%)',
            (int) $color->toHsl()['h'],
            (int) ($color->toHsl()['s'] * 100),
            (int) ($color->toHsl()['l'] * 100)
        )
    );
}

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║        Color Palette PHP - Basic Colors Example         ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";

// =============================================================================
// 1. Creating Colors from Different Formats
// =============================================================================

section('1. Creating Colors from Different Formats');

// From Hex (most common)
$red = Color::fromHex('#FF0000');
displayColor('From Hex', $red);

// From RGB
$green = Color::fromRgb([0, 255, 0]);
displayColor('From RGB', $green);

// From HSL (Hue, Saturation, Lightness)
$blue = Color::fromHsl(240, 1.0, 0.5);  // Hue: 240°, Sat: 100%, Light: 50%
displayColor('From HSL', $blue);

// From HSV (Hue, Saturation, Value)
$yellow = Color::fromHsv(60, 1.0, 1.0);  // Hue: 60°, Sat: 100%, Value: 100%
displayColor('From HSV', $yellow);

// From CMYK (Cyan, Magenta, Yellow, Black) - used in printing
$cyan = Color::fromCmyk(1.0, 0.0, 0.0, 0.0);  // 100% cyan
displayColor('From CMYK', $cyan);

// From LAB (perceptually uniform color space)
$purple = Color::fromLab(50, 50, -50);  // L: 50, a: 50, b: -50
displayColor('From LAB', $purple);

// =============================================================================
// 2. Color Format Conversions
// =============================================================================

section('2. Color Format Conversions');

$color = Color::fromHex('#FF6B35');  // Orange color
echo "Original Color: {$color->toHex()}\n\n";

// Convert to all formats
echo 'Hex:    '.$color->toHex()."\n";

$rgb = $color->toRgb();
echo sprintf("RGB:    rgb(%d, %d, %d)\n", $rgb['r'], $rgb['g'], $rgb['b']);

$hsl = $color->toHsl();
echo sprintf("HSL:    hsl(%d°, %.1f%%, %.1f%%)\n",
    (int) $hsl['h'], $hsl['s'] * 100, $hsl['l'] * 100);

$hsv = $color->toHsv();
echo sprintf("HSV:    hsv(%d°, %.1f%%, %.1f%%)\n",
    (int) $hsv['h'], $hsv['s'] * 100, $hsv['v'] * 100);

$cmyk = $color->toCmyk();
echo sprintf("CMYK:   cmyk(%.1f%%, %.1f%%, %.1f%%, %.1f%%)\n",
    $cmyk['c'] * 100, $cmyk['m'] * 100, $cmyk['y'] * 100, $cmyk['k'] * 100);

$lab = $color->toLab();
echo sprintf("LAB:    lab(%.1f, %.1f, %.1f)\n", $lab['l'], $lab['a'], $lab['b']);

// =============================================================================
// 3. Accessing Color Components
// =============================================================================

section('3. Accessing Color Components');

$color = Color::fromHex('#3498DB');  // Blue color
echo "Color: {$color->toHex()}\n\n";

echo "RGB Components:\n";
echo '  Red:   '.$color->getRed()."\n";
echo '  Green: '.$color->getGreen()."\n";
echo '  Blue:  '.$color->getBlue()."\n";

// =============================================================================
// 4. Color Properties
// =============================================================================

section('4. Color Properties');

$colors = [
    'White' => Color::fromHex('#FFFFFF'),
    'Light Gray' => Color::fromHex('#CCCCCC'),
    'Gray' => Color::fromHex('#808080'),
    'Dark Gray' => Color::fromHex('#333333'),
    'Black' => Color::fromHex('#000000'),
    'Red' => Color::fromHex('#FF0000'),
    'Blue' => Color::fromHex('#0000FF'),
];

echo sprintf("%-12s %-9s %-12s %-10s %-10s\n",
    'Color', 'Hex', 'Brightness', 'Luminance', 'Is Light?');
echo str_repeat('-', 60)."\n";

foreach ($colors as $name => $color) {
    echo sprintf("%-12s %-9s %-12.2f %-10.3f %-10s\n",
        $name,
        $color->toHex(),
        $color->getBrightness(),
        $color->getLuminance(),
        $color->isLight() ? 'Yes' : 'No'
    );
}

echo "\nNote: Brightness is a simple weighted average (0-255)\n";
echo "      Luminance is WCAG standard relative luminance (0-1)\n";

// =============================================================================
// 5. Practical Examples
// =============================================================================

section('5. Practical Examples');

// Example 1: Determine if white or black text is better on a background
$backgrounds = [
    '#FF6B35',  // Orange
    '#2C3E50',  // Dark Blue
    '#ECF0F1',  // Light Gray
    '#27AE60',  // Green
];

echo "Which text color (white or black) is better for these backgrounds?\n\n";
echo sprintf("%-12s %-10s %-10s\n", 'Background', 'Best Text', 'Reason');
echo str_repeat('-', 40)."\n";

foreach ($backgrounds as $bgHex) {
    $bg = Color::fromHex($bgHex);
    $bestText = $bg->isLight() ? 'Black' : 'White';
    $reason = $bg->isLight() ? 'Light BG' : 'Dark BG';

    echo sprintf("%-12s %-10s %-10s\n", $bgHex, $bestText, $reason);
}

// Example 2: Create color from user input (simulated)
echo "\n\nExample: Creating color from different user inputs:\n\n";

$userInputs = [
    ['type' => 'hex', 'value' => '#FF5733'],
    ['type' => 'rgb', 'value' => [255, 87, 51]],
    ['type' => 'name', 'value' => 'blue', 'actual' => [0, 0, 255]],
];

foreach ($userInputs as $input) {
    if ($input['type'] === 'hex') {
        $color = Color::fromHex($input['value']);
        echo "Input: {$input['value']} (hex) → Color: {$color->toHex()}\n";
    } elseif ($input['type'] === 'rgb') {
        $color = Color::fromRgb($input['value']);
        echo sprintf("Input: rgb(%d,%d,%d) → Color: %s\n",
            $input['value'][0], $input['value'][1], $input['value'][2],
            $color->toHex());
    } elseif ($input['type'] === 'name') {
        // For named colors, you'd typically have a mapping
        $color = Color::fromRgb($input['actual']);
        echo "Input: {$input['value']} (name) → Color: {$color->toHex()}\n";
    }
}

// =============================================================================
// Summary
// =============================================================================

section('Summary');

echo "✓ You can create colors from 6 different formats:\n";
echo "  Hex, RGB, HSL, HSV, CMYK, and LAB\n\n";

echo "✓ Colors can be converted between any format:\n";
echo "  Use toHex(), toRgb(), toHsl(), toHsv(), toCmyk(), toLab()\n\n";

echo "✓ Color properties help you understand the color:\n";
echo "  - Brightness: Simple weighted average (0-255)\n";
echo "  - Luminance: Perceptually accurate (0-1)\n";
echo "  - isLight()/isDark(): Quick light/dark detection\n\n";

echo "Next: Run 02-color-manipulation.php to learn about color transformations\n\n";
