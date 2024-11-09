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