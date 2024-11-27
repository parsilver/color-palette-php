<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Theme;

test('it can create theme from colors', function () {
    $theme = Theme::fromColors([
        'primary' => new Color(255, 0, 0),
        'secondary' => new Color(0, 255, 0),
        'accent' => new Color(0, 0, 255),
    ]);

    expect($theme->getColor('primary')->toHex())->toBe('#ff0000');
    expect($theme->getColor('secondary')->toHex())->toBe('#00ff00');
    expect($theme->getColor('accent')->toHex())->toBe('#0000ff');
});

test('it can get all colors', function () {
    $theme = Theme::fromColors([
        'primary' => new Color(255, 0, 0),
        'secondary' => new Color(0, 255, 0),
    ]);

    expect($theme->getColors())->toHaveCount(2);
    expect($theme->getColors())->toHaveKey('primary');
    expect($theme->getColors())->toHaveKey('secondary');
});

test('it can check if color exists', function () {
    $theme = Theme::fromColors([
        'primary' => new Color(255, 0, 0),
    ]);

    expect($theme->hasColor('primary'))->toBeTrue();
    expect($theme->hasColor('secondary'))->toBeFalse();
});

test('it throws exception when getting non-existent color', function () {
    $theme = Theme::fromColors([
        'primary' => new Color(255, 0, 0),
    ]);

    expect(fn () => $theme->getColor('non-existent'))
        ->toThrow(InvalidArgumentException::class);
});

test('it can convert theme to array', function () {
    $theme = Theme::fromColors([
        'primary' => new Color(255, 0, 0),
        'secondary' => new Color(0, 255, 0),
    ]);

    expect($theme->toArray())->toBe([
        'primary' => '#ff0000',
        'secondary' => '#00ff00',
    ]);
});
