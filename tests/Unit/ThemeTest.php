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

describe('Theme Convenience Methods', function () {
    test('it can get primary color', function () {
        $theme = Theme::fromColors([
            'primary' => new Color(255, 0, 0),
            'secondary' => new Color(0, 255, 0),
        ]);

        expect($theme->getPrimaryColor()->toHex())->toBe('#ff0000');
    });

    test('it can get secondary color', function () {
        $theme = Theme::fromColors([
            'primary' => new Color(255, 0, 0),
            'secondary' => new Color(0, 255, 0),
        ]);

        expect($theme->getSecondaryColor()->toHex())->toBe('#00ff00');
    });

    test('it can get accent color', function () {
        $theme = Theme::fromColors([
            'accent' => new Color(0, 0, 255),
        ]);

        expect($theme->getAccentColor()->toHex())->toBe('#0000ff');
    });

    test('it can get background color', function () {
        $theme = Theme::fromColors([
            'background' => new Color(240, 240, 240),
        ]);

        expect($theme->getBackgroundColor()->toHex())->toBe('#f0f0f0');
    });

    test('it can get surface color', function () {
        $theme = Theme::fromColors([
            'surface' => new Color(255, 255, 255),
        ]);

        expect($theme->getSurfaceColor()->toHex())->toBe('#ffffff');
    });

    test('it throws exception when getting primary color without primary key', function () {
        $theme = Theme::fromColors([
            'secondary' => new Color(0, 255, 0),
        ]);

        expect(fn () => $theme->getPrimaryColor())
            ->toThrow(InvalidArgumentException::class, "Color 'primary' not found in theme");
    });

    test('it throws exception when getting secondary color without secondary key', function () {
        $theme = Theme::fromColors([
            'primary' => new Color(255, 0, 0),
        ]);

        expect(fn () => $theme->getSecondaryColor())
            ->toThrow(InvalidArgumentException::class, "Color 'secondary' not found in theme");
    });

    test('it throws exception when getting accent color without accent key', function () {
        $theme = Theme::fromColors([
            'primary' => new Color(255, 0, 0),
        ]);

        expect(fn () => $theme->getAccentColor())
            ->toThrow(InvalidArgumentException::class, "Color 'accent' not found in theme");
    });

    test('it throws exception when getting background color without background key', function () {
        $theme = Theme::fromColors([
            'primary' => new Color(255, 0, 0),
        ]);

        expect(fn () => $theme->getBackgroundColor())
            ->toThrow(InvalidArgumentException::class, "Color 'background' not found in theme");
    });

    test('it throws exception when getting surface color without surface key', function () {
        $theme = Theme::fromColors([
            'primary' => new Color(255, 0, 0),
        ]);

        expect(fn () => $theme->getSurfaceColor())
            ->toThrow(InvalidArgumentException::class, "Color 'surface' not found in theme");
    });
});
