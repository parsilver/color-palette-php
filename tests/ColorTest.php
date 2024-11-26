<?php

use Farzai\ColorPalette\Color;

test('it can create color from hex', function () {
    $color = new Color('#ff0000');
    expect($color->toHex())->toBe('#ff0000');
});

test('it can create color from rgb', function () {
    $color = Color::fromRgb(255, 0, 0);
    expect($color->toHex())->toBe('#ff0000');
});

test('it can create color from hsl', function () {
    $color = Color::fromHsl(0, 100, 50);
    expect($color->toHex())->toBe('#ff0000');
});

test('it can get rgb components', function () {
    $color = new Color('#ff0000');
    expect($color->getRed())->toBe(255)
        ->and($color->getGreen())->toBe(0)
        ->and($color->getBlue())->toBe(0);
});

test('it can get hsl components', function () {
    $color = new Color('#ff0000');
    expect($color->getHue())->toBe(0)
        ->and($color->getSaturation())->toBe(100)
        ->and($color->getLightness())->toBe(50);
});

test('it can lighten color', function () {
    $color = new Color('#ff0000');
    $lightened = $color->lighten(20);
    expect($lightened->toHex())->toBe('#ff6666');
});

test('it can darken color', function () {
    $color = new Color('#ff0000');
    $darkened = $color->darken(20);
    expect($darkened->toHex())->toBe('#990000');
});

test('it can saturate color', function () {
    $color = new Color('#ff8080');
    $saturated = $color->saturate(20);
    expect($saturated->toHex())->toBe('#ff4d4d');
});

test('it can desaturate color', function () {
    $color = new Color('#ff0000');
    $desaturated = $color->desaturate(20);
    expect($desaturated->toHex())->toBe('#f23333');
});

test('it can adjust hue', function () {
    $color = new Color('#ff0000');
    $adjusted = $color->adjustHue(180);
    expect($adjusted->toHex())->toBe('#00ffff');
});

test('it can get luminance', function () {
    $color = new Color('#ff0000');
    expect($color->getLuminance())->toBeFloat();
});

test('it can check if color is light', function () {
    $lightColor = new Color('#ffffff');
    $darkColor = new Color('#000000');
    
    expect($lightColor->isLight())->toBeTrue()
        ->and($darkColor->isLight())->toBeFalse();
});

test('it can check if color is dark', function () {
    $lightColor = new Color('#ffffff');
    $darkColor = new Color('#000000');
    
    expect($lightColor->isDark())->toBeFalse()
        ->and($darkColor->isDark())->toBeTrue();
});

test('it can get contrast ratio with another color', function () {
    $color1 = new Color('#ffffff');
    $color2 = new Color('#000000');
    
    expect($color1->getContrastRatio($color2))->toBeFloat()
        ->and($color1->getContrastRatio($color2))->toBeGreaterThan(20);
});

test('it throws exception for invalid hex color', function () {
    expect(fn() => new Color('invalid'))->toThrow(InvalidArgumentException::class);
    expect(fn() => new Color('#gggggg'))->toThrow(InvalidArgumentException::class);
});

test('it can handle shorthand hex colors', function () {
    $color = new Color('#f00');
    expect($color->toHex())->toBe('#ff0000');
});

test('it can mix with another color', function () {
    $color1 = new Color('#ff0000');
    $color2 = new Color('#0000ff');
    
    $mixed = $color1->mix($color2, 50);
    expect($mixed->toHex())->toBe('#800080');
}); 