<?php

use Farzai\ColorPalette\Color;

test('can create color from RGB values', function () {
    $color = new Color(255, 0, 0);
    
    expect($color->toRgb())->toBe([
        'r' => 255,
        'g' => 0,
        'b' => 0,
    ]);
});

test('can create color from hex string', function () {
    $color = Color::fromHex('#ff0000');
    
    expect($color->toRgb())->toBe([
        'r' => 255,
        'g' => 0,
        'b' => 0,
    ]);
    
    // Test without hash
    $color = Color::fromHex('ff0000');
    expect($color->toHex())->toBe('#ff0000');
});

test('can create color from RGB array', function () {
    $color = Color::fromRgb([
        'r' => 255,
        'g' => 0,
        'b' => 0,
    ]);
    
    expect($color->toHex())->toBe('#ff0000');
});

test('throws exception for invalid hex format', function () {
    Color::fromHex('invalid');
})->throws(InvalidArgumentException::class, 'Invalid hex color format');

test('throws exception for invalid RGB values', function () {
    new Color(256, 0, 0);
})->throws(InvalidArgumentException::class, 'Invalid red color component');

test('can determine if color is light or dark', function () {
    // White should be light
    $white = new Color(255, 255, 255);
    expect($white->isLight())->toBeTrue();
    expect($white->isDark())->toBeFalse();
    
    // Black should be dark
    $black = new Color(0, 0, 0);
    expect($black->isLight())->toBeFalse();
    expect($black->isDark())->toBeTrue();
});

test('can calculate brightness correctly', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);
    
    expect($white->getBrightness())->toBeGreaterThan(127.5);
    expect($black->getBrightness())->toBeLessThan(127.5);
});

test('can calculate luminance correctly', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);
    
    expect($white->getLuminance())->toBeGreaterThan(0.9);
    expect($black->getLuminance())->toBeLessThan(0.1);
});

test('can calculate contrast ratio between colors', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);
    
    // The contrast ratio between black and white should be 21:1
    expect($white->getContrastRatio($black))->toBeGreaterThan(20);
    expect($white->getContrastRatio($black))->toBeLessThan(22);
    
    // Contrast ratio should be the same regardless of order
    expect($white->getContrastRatio($black))->toBe($black->getContrastRatio($white));
});

test('handles default values for RGB array creation', function () {
    $color = Color::fromRgb([]);
    
    expect($color->toRgb())->toBe([
        'r' => 0,
        'g' => 0,
        'b' => 0,
    ]);
});

test('it can convert RGB to HSL', function () {
    $color = new Color(255, 0, 0); // Pure red
    $hsl = $color->toHsl();

    expect($hsl['h'])->toBe(0);
    expect($hsl['s'])->toBe(100);
    expect($hsl['l'])->toBe(50);
});

test('it can create color from HSL', function () {
    $color = Color::fromHsl(0, 100, 50); // Pure red
    expect($color->toHex())->toBe('#ff0000');
});

test('it can lighten color', function () {
    $color = new Color(255, 0, 0); // Pure red
    $lightened = $color->lighten(0.2);
    
    $hsl = $lightened->toHsl();
    expect($hsl['l'])->toBeGreaterThan(50);
});

test('it can darken color', function () {
    $color = new Color(255, 0, 0); // Pure red
    $darkened = $color->darken(0.2);
    
    $hsl = $darkened->toHsl();
    expect($hsl['l'])->toBeLessThan(50);
});

test('it can rotate hue', function () {
    $color = new Color(255, 0, 0); // Pure red
    $rotated = $color->rotate(120); // Should be green
    
    $hsl = $rotated->toHsl();
    expect($hsl['h'])->toBe(120);
});

test('it can saturate color', function () {
    $color = Color::fromHsl(0, 50, 50); // Semi-saturated red
    $saturated = $color->saturate(0.2);
    
    $hsl = $saturated->toHsl();
    expect($hsl['s'])->toBe(70);
});

test('it can desaturate color', function () {
    $color = Color::fromHsl(0, 100, 50); // Fully saturated red
    $desaturated = $color->desaturate(0.2);
    
    $hsl = $desaturated->toHsl();
    expect($hsl['s'])->toBe(80);
});

test('it can adjust lightness', function () {
    $color = new Color(255, 0, 0); // Pure red
    $adjusted = $color->withLightness(0.8);
    
    $hsl = $adjusted->toHsl();
    expect($hsl['l'])->toBe(80);
}); 