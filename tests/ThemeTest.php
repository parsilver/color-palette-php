<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Theme;

test('it can create a theme with colors', function () {
    $theme = new Theme([
        'primary' => new Color('#ff0000'),
        'secondary' => new Color('#00ff00'),
        'accent' => new Color('#0000ff'),
    ]);

    expect($theme->getPrimary()->toHex())->toBe('#ff0000')
        ->and($theme->getSecondary()->toHex())->toBe('#00ff00')
        ->and($theme->getAccent()->toHex())->toBe('#0000ff');
});

test('it can get all colors as array', function () {
    $theme = new Theme([
        'primary' => new Color('#ff0000'),
        'secondary' => new Color('#00ff00'),
    ]);

    expect($theme->toArray())->toBe([
        'primary' => '#ff0000',
        'secondary' => '#00ff00',
    ]);
});

test('it can check if color exists', function () {
    $theme = new Theme([
        'primary' => new Color('#ff0000'),
    ]);

    expect($theme->has('primary'))->toBeTrue()
        ->and($theme->has('secondary'))->toBeFalse();
});

test('it can get color by key', function () {
    $theme = new Theme([
        'primary' => new Color('#ff0000'),
    ]);

    expect($theme->get('primary')->toHex())->toBe('#ff0000')
        ->and($theme->get('secondary'))->toBeNull();
});

test('it can be converted to json', function () {
    $theme = new Theme([
        'primary' => new Color('#ff0000'),
        'secondary' => new Color('#00ff00'),
    ]);

    expect(json_encode($theme))->toBe(json_encode([
        'primary' => '#ff0000',
        'secondary' => '#00ff00',
    ]));
});

test('it can be created from array', function () {
    $theme = Theme::fromArray([
        'primary' => '#ff0000',
        'secondary' => '#00ff00',
    ]);

    expect($theme->getPrimary()->toHex())->toBe('#ff0000')
        ->and($theme->getSecondary()->toHex())->toBe('#00ff00');
});
