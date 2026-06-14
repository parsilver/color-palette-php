<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\WebsiteThemeStrategy;
use Farzai\ColorPalette\ThemeGenerator;

test('it derives a complete five-role theme from an arbitrary extraction palette', function () {
    // A typical 5-colour extraction palette (plain indexed keys). This used to
    // throw a count-mismatch and never produced background/surface roles.
    $palette = new ColorPalette([
        new Color(200, 30, 30),
        new Color(30, 200, 30),
        new Color(30, 30, 200),
        new Color(200, 200, 30),
        new Color(30, 200, 200),
    ]);

    $theme = (new ThemeGenerator)->generate($palette);

    // Every one of the five role getters resolves (previously background/surface threw).
    expect($theme->getPrimaryColor())->toBeInstanceOf(Color::class);
    expect($theme->getSecondaryColor())->toBeInstanceOf(Color::class);
    expect($theme->getAccentColor())->toBeInstanceOf(Color::class);
    expect($theme->getBackgroundColor())->toBeInstanceOf(Color::class);
    expect($theme->getSurfaceColor())->toBeInstanceOf(Color::class);
    expect($theme->getPrimaryColor()->toHex())->toBe('#c81e1e'); // first palette colour
});

test('it lifts a role-keyed palette (WebsiteThemeStrategy output) into a theme without corruption', function () {
    // String-keyed palettes previously corrupted (key-vs-index pairing): undefined
    // keys, dropped colours, an empty-string key. fromPalette must lift them cleanly.
    $palette = (new WebsiteThemeStrategy)->generate(new Color(33, 150, 243));

    $theme = (new ThemeGenerator)->generate($palette);

    expect($theme->getColors())->toHaveCount(5);
    expect($theme->getSurfaceColor()->toHex())->toBe('#ffffff');
    expect($theme->getPrimaryColor()->toHex())->toBe($palette['primary']->toHex());
});

test('it throws on an empty palette', function () {
    expect(fn () => (new ThemeGenerator)->generate(new ColorPalette([])))
        ->toThrow(InvalidArgumentException::class);
});
