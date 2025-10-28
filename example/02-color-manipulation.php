#!/usr/bin/env php
<?php

/**
 * Example 02: Color Manipulation
 *
 * This example demonstrates:
 * - Lightening and darkening colors
 * - Adjusting saturation
 * - Rotating hues
 * - Chaining multiple transformations
 * - Practical use cases
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

// Helper function to visualize color change
function showTransformation($label, Color $original, Color $modified, $operation)
{
    echo sprintf("%-25s %s → %s  (%s)\n",
        $label,
        $original->toHex(),
        $modified->toHex(),
        $operation
    );
}

echo "\n╔══════════════════════════════════════════════════════════╗\n";
echo "║     Color Palette PHP - Color Manipulation Example      ║\n";
echo "╚══════════════════════════════════════════════════════════╝\n";

// =============================================================================
// 1. Lighten and Darken
// =============================================================================

section('1. Lighten and Darken Colors');

$blue = Color::fromHex('#3498DB');
echo "Original: {$blue->toHex()} (Blue)\n\n";

echo "Lightening by different amounts:\n";
showTransformation('Lighten 10%', $blue, $blue->lighten(0.1), '+10%');
showTransformation('Lighten 20%', $blue, $blue->lighten(0.2), '+20%');
showTransformation('Lighten 30%', $blue, $blue->lighten(0.3), '+30%');
showTransformation('Lighten 50%', $blue, $blue->lighten(0.5), '+50%');

echo "\nDarkening by different amounts:\n";
showTransformation('Darken 10%', $blue, $blue->darken(0.1), '-10%');
showTransformation('Darken 20%', $blue, $blue->darken(0.2), '-20%');
showTransformation('Darken 30%', $blue, $blue->darken(0.3), '-30%');
showTransformation('Darken 50%', $blue, $blue->darken(0.5), '-50%');

// =============================================================================
// 2. Saturation Adjustments
// =============================================================================

section('2. Saturation Adjustments');

$orange = Color::fromHex('#FF6B35');
echo "Original: {$orange->toHex()} (Orange)\n\n";

echo "Increasing saturation:\n";
showTransformation('Saturate 10%', $orange, $orange->saturate(0.1), '+10%');
showTransformation('Saturate 20%', $orange, $orange->saturate(0.2), '+20%');
showTransformation('Saturate 30%', $orange, $orange->saturate(0.3), '+30%');

echo "\nDecreasing saturation:\n";
showTransformation('Desaturate 10%', $orange, $orange->desaturate(0.1), '-10%');
showTransformation('Desaturate 20%', $orange, $orange->desaturate(0.2), '-20%');
showTransformation('Desaturate 50%', $orange, $orange->desaturate(0.5), '-50%');
showTransformation('Desaturate 100%', $orange, $orange->desaturate(1.0), '-100% (grayscale)');

// =============================================================================
// 3. Hue Rotation
// =============================================================================

section('3. Hue Rotation');

$red = Color::fromHex('#E74C3C');
echo "Original: {$red->toHex()} (Red)\n\n";

echo "Rotating hue around the color wheel:\n";
showTransformation('Rotate 30°', $red, $red->rotate(30), '+30°');
showTransformation('Rotate 60°', $red, $red->rotate(60), '+60°');
showTransformation('Rotate 90°', $red, $red->rotate(90), '+90°');
showTransformation('Rotate 120°', $red, $red->rotate(120), '+120°');
showTransformation('Rotate 180°', $red, $red->rotate(180), '+180° (complementary)');
showTransformation('Rotate -60°', $red, $red->rotate(-60), '-60°');

echo "\nNote: Rotating 180° gives you the complementary color\n";

// =============================================================================
// 4. Direct Lightness Control
// =============================================================================

section('4. Direct Lightness Control');

$green = Color::fromHex('#27AE60');
echo "Original: {$green->toHex()} (Green)\n\n";

echo "Setting specific lightness levels:\n";
showTransformation('Set lightness 10%', $green, $green->withLightness(0.1), 'L=10%');
showTransformation('Set lightness 30%', $green, $green->withLightness(0.3), 'L=30%');
showTransformation('Set lightness 50%', $green, $green->withLightness(0.5), 'L=50%');
showTransformation('Set lightness 70%', $green, $green->withLightness(0.7), 'L=70%');
showTransformation('Set lightness 90%', $green, $green->withLightness(0.9), 'L=90%');

// =============================================================================
// 5. Chaining Transformations
// =============================================================================

section('5. Chaining Multiple Transformations');

$base = Color::fromHex('#9B59B6');  // Purple
echo "Original: {$base->toHex()} (Purple)\n\n";

// Chain 1: Lighten and saturate
$chain1 = $base->lighten(0.2)->saturate(0.3);
echo "Lighten 20% → Saturate 30%:\n";
echo "  {$base->toHex()} → {$chain1->toHex()}\n\n";

// Chain 2: Rotate and desaturate
$chain2 = $base->rotate(60)->desaturate(0.4);
echo "Rotate 60° → Desaturate 40%:\n";
echo "  {$base->toHex()} → {$chain2->toHex()}\n\n";

// Chain 3: Complex transformation
$chain3 = $base->lighten(0.1)->rotate(45)->saturate(0.2);
echo "Lighten 10% → Rotate 45° → Saturate 20%:\n";
echo "  {$base->toHex()} → {$chain3->toHex()}\n\n";

// Chain 4: Create a muted version
$muted = $base->desaturate(0.3)->lighten(0.15);
echo "Create muted version (Desaturate 30% → Lighten 15%):\n";
echo "  {$base->toHex()} → {$muted->toHex()}\n";

// =============================================================================
// 6. Practical Use Cases
// =============================================================================

section('6. Practical Use Cases');

// Use Case 1: Create hover states
echo "Use Case 1: Button Hover States\n";
echo str_repeat('-', 60)."\n";

$buttonColor = Color::fromHex('#3498DB');
$hoverColor = $buttonColor->lighten(0.1);
$activeColor = $buttonColor->darken(0.1);

echo "Normal:  {$buttonColor->toHex()}\n";
echo "Hover:   {$hoverColor->toHex()}  (10% lighter)\n";
echo "Active:  {$activeColor->toHex()}  (10% darker)\n";

// Use Case 2: Create color variations
echo "\n\nUse Case 2: Create Brand Color Variations\n";
echo str_repeat('-', 60)."\n";

$brandColor = Color::fromHex('#E74C3C');
echo "Primary:   {$brandColor->toHex()}\n";
echo 'Light:     '.$brandColor->lighten(0.3)->toHex()."  (30% lighter)\n";
echo 'Dark:      '.$brandColor->darken(0.2)->toHex()."  (20% darker)\n";
echo 'Muted:     '.$brandColor->desaturate(0.4)->toHex()."  (40% less saturated)\n";
echo 'Vibrant:   '.$brandColor->saturate(0.3)->toHex()."  (30% more saturated)\n";

// Use Case 3: Create analogous colors
echo "\n\nUse Case 3: Create Analogous Color Scheme\n";
echo str_repeat('-', 60)."\n";

$primary = Color::fromHex('#3498DB');
$analogous1 = $primary->rotate(-30);
$analogous2 = $primary->rotate(30);

echo "Color 1:  {$analogous1->toHex()}  (Primary -30°)\n";
echo "Primary:  {$primary->toHex()}\n";
echo "Color 2:  {$analogous2->toHex()}  (Primary +30°)\n";

// Use Case 4: Create a monochromatic palette
echo "\n\nUse Case 4: Monochromatic Palette (5 shades)\n";
echo str_repeat('-', 60)."\n";

$base = Color::fromHex('#27AE60');
echo "Base:    {$base->toHex()}\n";

for ($i = 1; $i <= 4; $i++) {
    $shade = $base->darken($i * 0.15);
    echo "Shade {$i}:  {$shade->toHex()}  (".($i * 15)."% darker)\n";
}

// Use Case 5: Disable state for UI
echo "\n\nUse Case 5: Disabled UI State\n";
echo str_repeat('-', 60)."\n";

$enabledColor = Color::fromHex('#3498DB');
$disabledColor = $enabledColor->desaturate(0.6)->lighten(0.2);

echo "Enabled:   {$enabledColor->toHex()}\n";
echo "Disabled:  {$disabledColor->toHex()}  (Desaturated + Lightened)\n";

// Use Case 6: Success, Warning, Error variations
echo "\n\nUse Case 6: Alert Color System\n";
echo str_repeat('-', 60)."\n";

$colors = [
    'Success' => Color::fromHex('#27AE60'),
    'Warning' => Color::fromHex('#F39C12'),
    'Error' => Color::fromHex('#E74C3C'),
];

foreach ($colors as $type => $color) {
    $bg = $color->lighten(0.4)->desaturate(0.2);
    $border = $color->darken(0.1);
    $text = $color->darken(0.3);

    echo "\n{$type}:\n";
    echo "  Background: {$bg->toHex()}\n";
    echo "  Border:     {$border->toHex()}\n";
    echo "  Text:       {$text->toHex()}\n";
}

// =============================================================================
// 7. Before and After Comparisons
// =============================================================================

section('7. Before and After Transformations');

$examples = [
    ['name' => 'Make it pop',     'op' => fn ($c) => $c->saturate(0.3)],
    ['name' => 'Subtle tone',     'op' => fn ($c) => $c->desaturate(0.3)->lighten(0.1)],
    ['name' => 'Dark mode',       'op' => fn ($c) => $c->darken(0.4)],
    ['name' => 'Light mode',      'op' => fn ($c) => $c->lighten(0.4)],
    ['name' => 'Complementary',   'op' => fn ($c) => $c->rotate(180)],
    ['name' => 'Warm shift',      'op' => fn ($c) => $c->rotate(-30)],
    ['name' => 'Cool shift',      'op' => fn ($c) => $c->rotate(30)],
];

$testColor = Color::fromHex('#9B59B6');
echo "Starting with: {$testColor->toHex()}\n\n";

foreach ($examples as $example) {
    $result = $example['op']($testColor);
    echo sprintf("%-20s %s → %s\n", $example['name'].':', $testColor->toHex(), $result->toHex());
}

// =============================================================================
// Summary
// =============================================================================

section('Summary');

echo "✓ Color manipulation methods:\n";
echo "  - lighten(amount)      - Make color lighter (0.0 - 1.0)\n";
echo "  - darken(amount)       - Make color darker (0.0 - 1.0)\n";
echo "  - saturate(amount)     - Increase color intensity (0.0 - 1.0)\n";
echo "  - desaturate(amount)   - Decrease color intensity (0.0 - 1.0)\n";
echo "  - rotate(degrees)      - Shift hue around color wheel (-360 to 360)\n";
echo "  - withLightness(value) - Set specific lightness level (0.0 - 1.0)\n\n";

echo "✓ All methods return new Color objects (immutable)\n";
echo "✓ Methods can be chained: \$color->lighten(0.2)->saturate(0.3)\n";
echo "✓ Perfect for creating UI variations, themes, and color systems\n\n";

echo "Next: Run 03-palette-builder.php to learn about the ColorPaletteBuilder\n\n";
