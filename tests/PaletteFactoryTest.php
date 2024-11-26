<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\PaletteFactory;

test('it can create a palette from hex colors', function () {
    $factory = new PaletteFactory;

    $palette = $factory->create([
        '#ff0000',
        '#00ff00',
        '#0000ff',
    ]);

    expect($palette)->toBeInstanceOf(ColorPalette::class)
        ->and($palette->count())->toBe(3)
        ->and($palette->toArray())->toBe([
            '#ff0000',
            '#00ff00',
            '#0000ff',
        ]);
});

test('it can create a palette from Color objects', function () {
    $factory = new PaletteFactory;

    $palette = $factory->create([
        new Color('#ff0000'),
        new Color('#00ff00'),
    ]);

    expect($palette)->toBeInstanceOf(ColorPalette::class)
        ->and($palette->count())->toBe(2)
        ->and($palette->toArray())->toBe([
            '#ff0000',
            '#00ff00',
        ]);
});

test('it can create a palette from mixed input types', function () {
    $factory = new PaletteFactory;

    $palette = $factory->create([
        '#ff0000',
        new Color('#00ff00'),
    ]);

    expect($palette)->toBeInstanceOf(ColorPalette::class)
        ->and($palette->count())->toBe(2)
        ->and($palette->toArray())->toBe([
            '#ff0000',
            '#00ff00',
        ]);
});

test('it throws exception for invalid color format', function () {
    $factory = new PaletteFactory;

    expect(fn () => $factory->create([
        'invalid-color',
    ]))->toThrow(InvalidArgumentException::class);
});

test('it can create an empty palette', function () {
    $factory = new PaletteFactory;

    $palette = $factory->create([]);

    expect($palette)->toBeInstanceOf(ColorPalette::class)
        ->and($palette->count())->toBe(0)
        ->and($palette->toArray())->toBe([]);
});
