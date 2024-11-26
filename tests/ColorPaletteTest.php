<?php

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Color;

test('can create color palette from hex colors', function () {
    $palette = ColorPalette::fromHexColors(['#ff0000', '#00ff00']);
    expect($palette)->toBeInstanceOf(ColorPalette::class);
    expect($palette->getColors())->toHaveCount(2);
});

test('can create color palette from RGB colors', function () {
    $palette = ColorPalette::fromRgbColors([
        ['r' => 255, 'g' => 0, 'b' => 0],
        ['r' => 0, 'g' => 255, 'b' => 0]
    ]);
    expect($palette)->toBeInstanceOf(ColorPalette::class);
    expect($palette->getColors())->toHaveCount(2);
});

test('can get dominant color', function () {
    $palette = ColorPalette::fromHexColors(['#FF0000', '#00FF00']);
    $colors = $palette->getColors();
    expect($colors[0])->toBeInstanceOf(Color::class);
    expect($colors[0]->toHex())->toBe('#FF0000');
});

test('can get suggested text color', function () {
    $palette = ColorPalette::fromHexColors(['#ffffff', '#000000']);
    $backgroundColor = new Color(255, 255, 255); // white
    $textColor = $palette->getSuggestedTextColor($backgroundColor);
    expect($textColor)->toBeInstanceOf(Color::class);
    expect($textColor->toHex())->toBe('#000000'); // should be black for white background
});

test('can get suggested surface colors', function () {
    $palette = ColorPalette::fromHexColors(['#ff0000', '#00ff00']);
    $surfaceColors = $palette->getSuggestedSurfaceColors();
    expect($surfaceColors)->toBeArray();
    expect($surfaceColors)->toHaveCount(6); // primary, secondary, and their variants
}); 