<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Theme;

/**
 * @return array<string, Color>
 */
function themeRoleColors(): array
{
    return [
        'primary' => new Color(255, 0, 0),
        'secondary' => new Color(0, 255, 0),
        'accent' => new Color(0, 0, 255),
        'background' => new Color(250, 250, 250),
        'surface' => new Color(255, 255, 255),
    ];
}

test('it builds a theme from the five named roles', function () {
    $theme = Theme::fromRoles(
        new Color(255, 0, 0),
        new Color(0, 255, 0),
        new Color(0, 0, 255),
        new Color(250, 250, 250),
        new Color(255, 255, 255),
    );

    expect($theme->getPrimaryColor()->toHex())->toBe('#ff0000');
    expect($theme->getSecondaryColor()->toHex())->toBe('#00ff00');
    expect($theme->getAccentColor()->toHex())->toBe('#0000ff');
    expect($theme->getBackgroundColor()->toHex())->toBe('#fafafa');
    expect($theme->getSurfaceColor()->toHex())->toBe('#ffffff');
});

test('all five role getters resolve without throwing on a valid theme', function () {
    $theme = Theme::fromColors(themeRoleColors());

    foreach (['getPrimaryColor', 'getSecondaryColor', 'getAccentColor', 'getBackgroundColor', 'getSurfaceColor'] as $getter) {
        expect($theme->{$getter}())->toBeInstanceOf(Color::class);
    }
});

test('it throws when a required role is missing', function () {
    expect(fn () => Theme::fromColors([
        'primary' => new Color(255, 0, 0),
        'secondary' => new Color(0, 255, 0),
        'accent' => new Color(0, 0, 255),
        // background + surface missing
    ]))->toThrow(InvalidArgumentException::class);
});

test('it lifts a role-keyed palette into a theme via fromPalette', function () {
    $theme = Theme::fromPalette(new ColorPalette(themeRoleColors()));

    expect($theme->getSurfaceColor()->toHex())->toBe('#ffffff');
    expect($theme->getColors())->toHaveCount(5);
});

test('it can check role presence and export to a hex array', function () {
    $theme = Theme::fromColors(themeRoleColors());

    expect($theme->hasColor('primary'))->toBeTrue();
    expect($theme->hasColor('non-existent'))->toBeFalse();
    expect($theme->toArray())->toHaveKey('surface', '#ffffff');
    expect($theme->getColors())->toHaveCount(5);
});

test('getColor throws for an unknown role name', function () {
    $theme = Theme::fromColors(themeRoleColors());

    expect(fn () => $theme->getColor('non-existent'))
        ->toThrow(InvalidArgumentException::class);
});
