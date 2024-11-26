<?php

use Farzai\ColorPalette\ThemeFactory;
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Theme;

test('it can create a theme from hex colors', function () {
    $factory = new ThemeFactory();
    
    $theme = $factory->create([
        'primary' => '#ff0000',
        'secondary' => '#00ff00',
    ]);

    expect($theme)->toBeInstanceOf(Theme::class)
        ->and($theme->getPrimary()->toHex())->toBe('#ff0000')
        ->and($theme->getSecondary()->toHex())->toBe('#00ff00');
});

test('it can create a theme from Color objects', function () {
    $factory = new ThemeFactory();
    
    $theme = $factory->create([
        'primary' => new Color('#ff0000'),
        'secondary' => new Color('#00ff00'),
    ]);

    expect($theme)->toBeInstanceOf(Theme::class)
        ->and($theme->getPrimary()->toHex())->toBe('#ff0000')
        ->and($theme->getSecondary()->toHex())->toBe('#00ff00');
});

test('it can create a theme from mixed input types', function () {
    $factory = new ThemeFactory();
    
    $theme = $factory->create([
        'primary' => '#ff0000',
        'secondary' => new Color('#00ff00'),
    ]);

    expect($theme)->toBeInstanceOf(Theme::class)
        ->and($theme->getPrimary()->toHex())->toBe('#ff0000')
        ->and($theme->getSecondary()->toHex())->toBe('#00ff00');
});

test('it throws exception for invalid color format', function () {
    $factory = new ThemeFactory();
    
    expect(fn() => $factory->create([
        'primary' => 'invalid-color',
    ]))->toThrow(InvalidArgumentException::class);
}); 