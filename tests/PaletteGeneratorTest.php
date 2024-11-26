<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

test('it can generate monochromatic colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->monochromatic(5);
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(5);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate complementary colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->complementary();
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(2);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate analogous colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->analogous();
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(3);
    expect($colors[1]->toHex())->toBe('#2196F3');
});

test('it can generate triadic colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->triadic();
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(3);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate tetradic colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->tetradic();
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(4);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate split complementary colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->splitComplementary();
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(3);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate shades', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->shades(3);
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(3);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate tints', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->tints(3);
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(3);
    expect($colors[0]->toHex())->toBe('#2196F3');
});

test('it can generate pastel colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->pastel(4);
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(4);
});

test('it can generate vibrant colors', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->vibrant(4);
    $colors = $palette->getColors();

    expect($colors)->toHaveCount(4);
});

test('it can generate website theme', function () {
    $baseColor = Color::fromHex('#2196F3');
    $generator = new PaletteGenerator($baseColor);

    $palette = $generator->websiteTheme();
    $colors = $palette->getColors();

    expect($colors)->toHaveKeys([
        'primary',
        'secondary',
        'accent',
        'background',
        'surface',
        'text',
        'text_light',
    ]);
});
