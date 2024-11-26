<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Theme;
use Farzai\ColorPalette\ThemeGenerator;

test('can create theme generator', function () {
    $generator = new ThemeGenerator;
    expect($generator)->toBeInstanceOf(ThemeGenerator::class);
});

test('can generate theme from color palette', function () {
    $generator = new ThemeGenerator;
    $palette = ColorPalette::fromHexColors(['#ff0000', '#00ff00', '#0000ff']);

    $theme = $generator->generate($palette);

    expect($theme)->toBeInstanceOf(Theme::class);
    expect($theme->getPrimaryColor())->toBeInstanceOf(Color::class);
    expect($theme->getSecondaryColor())->toBeInstanceOf(Color::class);
    expect($theme->getBackgroundColor())->toBeInstanceOf(Color::class);
});

test('can generate theme with custom options', function () {
    $generator = new ThemeGenerator;
    $palette = ColorPalette::fromHexColors(['#ff0000', '#00ff00', '#0000ff']);

    $theme = $generator->generate($palette, [
        'saturation' => 0.5,
        'brightness' => 0.5,
    ]);

    expect($theme)->toBeInstanceOf(Theme::class);
});

test('theme can be converted to array', function () {
    $generator = new ThemeGenerator;
    $palette = ColorPalette::fromHexColors(['#ff0000', '#00ff00', '#0000ff']);

    $theme = $generator->generate($palette);
    $array = $theme->toArray();

    expect($array)->toBeArray();
    expect($array)->toHaveKeys([
        'primary', 'secondary', 'accent', 'background', 'surface',
        'on_primary', 'on_secondary', 'on_accent', 'on_background', 'on_surface',
    ]);
    expect($array['primary'])->toBeString();
    expect($array['primary'])->toStartWith('#');
});
