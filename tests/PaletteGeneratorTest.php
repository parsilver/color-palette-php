<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

test('it can generate complementary colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);
    
    $palette = $generator->complementary();
    $colors = $palette->getColors();
    
    expect($colors)->toHaveCount(2);
    expect($colors[0]->toHex())->toBe('#2196F3');
    expect($colors[1]->toHex())->not()->toBe('#2196F3');
});

test('it can generate analogous colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);
    
    $palette = $generator->analogous();
    $colors = $palette->getColors();
    
    expect($colors)->toHaveCount(3);
    expect($colors[1]->toHex())->toBe('#2196F3'); // Middle color should be base color
});

test('it can generate triadic colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);
    
    $palette = $generator->triadic();
    $colors = $palette->getColors();
    
    expect($colors)->toHaveCount(3);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate monochromatic colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);
    
    $palette = $generator->monochromatic(3);
    $colors = $palette->getColors();
    
    expect($colors)->toHaveCount(3);
});

test('it can generate shades', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);
    
    $palette = $generator->shades(3);
    $colors = $palette->getColors();
    
    expect($colors)->toHaveCount(3);
});

test('it can generate tints', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);
    
    $palette = $generator->tints(3);
    $colors = $palette->getColors();
    
    expect($colors)->toHaveCount(3);
}); 