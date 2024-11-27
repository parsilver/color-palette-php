<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\ThemeGenerator;

test('it can generate theme from color palette', function () {
    $palette = new ColorPalette([
        new Color(255, 0, 0),
        new Color(0, 255, 0),
        new Color(0, 0, 255),
    ]);

    $generator = new ThemeGenerator;
    $theme = $generator->generate($palette);

    expect($theme->getColors())->toHaveCount(3);
    expect($theme->hasColor('primary'))->toBeTrue();
    expect($theme->hasColor('secondary'))->toBeTrue();
    expect($theme->hasColor('accent'))->toBeTrue();
});

test('it can generate theme with custom color names', function () {
    $palette = new ColorPalette([
        new Color(255, 0, 0),
        new Color(0, 255, 0),
    ]);

    $generator = new ThemeGenerator;
    $theme = $generator->generate($palette, ['background', 'foreground']);

    expect($theme->getColors())->toHaveCount(2);
    expect($theme->hasColor('background'))->toBeTrue();
    expect($theme->hasColor('foreground'))->toBeTrue();
});

test('it throws exception when color names count does not match palette size', function () {
    $palette = new ColorPalette([
        new Color(255, 0, 0),
        new Color(0, 255, 0),
    ]);

    $generator = new ThemeGenerator;

    expect(fn () => $generator->generate($palette, ['background']))
        ->toThrow(InvalidArgumentException::class);
});
