<?php

use Farzai\ColorPalette\Color;

test('can create color from RGB values', function () {
    $color = new Color(255, 0, 0);

    expect($color->toRgb())->toBe([
        'r' => 255,
        'g' => 0,
        'b' => 0,
    ]);
});

test('can create color from hex string', function () {
    $color = Color::fromHex('#ff0000');

    expect($color->toRgb())->toBe([
        'r' => 255,
        'g' => 0,
        'b' => 0,
    ]);

    // Test without hash
    $color = Color::fromHex('ff0000');
    expect($color->toHex())->toBe('#ff0000');
});

test('can create color from RGB array', function () {
    $color = Color::fromRgb([
        'r' => 255,
        'g' => 0,
        'b' => 0,
    ]);

    expect($color->toHex())->toBe('#ff0000');
});

test('throws exception for invalid hex format', function () {
    Color::fromHex('invalid');
})->throws(InvalidArgumentException::class, 'Invalid hex color format');

test('throws exception for invalid RGB values', function () {
    new Color(256, 0, 0);
})->throws(InvalidArgumentException::class, 'Invalid red color component');

test('can determine if color is light or dark', function () {
    // White should be light
    $white = new Color(255, 255, 255);
    expect($white->isLight())->toBeTrue();
    expect($white->isDark())->toBeFalse();

    // Black should be dark
    $black = new Color(0, 0, 0);
    expect($black->isLight())->toBeFalse();
    expect($black->isDark())->toBeTrue();
});

test('can calculate brightness correctly', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);

    expect($white->getBrightness())->toBeGreaterThan(127.5);
    expect($black->getBrightness())->toBeLessThan(127.5);
});

test('can calculate luminance correctly', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);

    expect($white->getLuminance())->toBeGreaterThan(0.9);
    expect($black->getLuminance())->toBeLessThan(0.1);
});

test('can calculate contrast ratio between colors', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);

    // The contrast ratio between black and white should be 21:1
    expect($white->getContrastRatio($black))->toBeGreaterThan(20);
    expect($white->getContrastRatio($black))->toBeLessThan(22);

    // Contrast ratio should be the same regardless of order
    expect($white->getContrastRatio($black))->toBe($black->getContrastRatio($white));
});

test('throws exception for invalid RGB array', function () {
    expect(fn () => Color::fromRgb([]))
        ->toThrow(InvalidArgumentException::class, 'RGB array must have either string keys');
});

test('it can convert RGB to HSL', function () {
    $color = new Color(255, 0, 0); // Pure red
    $hsl = $color->toHsl();

    expect($hsl['h'])->toBe(0);
    expect($hsl['s'])->toBe(100);
    expect($hsl['l'])->toBe(50);
});

test('it can create color from HSL', function () {
    $color = Color::fromHsl(0, 100, 50); // Pure red
    expect($color->toHex())->toBe('#ff0000');
});

test('it can lighten color', function () {
    $color = new Color(255, 0, 0); // Pure red
    $lightened = $color->lighten(0.2);

    $hsl = $lightened->toHsl();
    expect($hsl['l'])->toBeGreaterThan(50);
});

test('it can darken color', function () {
    $color = new Color(255, 0, 0); // Pure red
    $darkened = $color->darken(0.2);

    $hsl = $darkened->toHsl();
    expect($hsl['l'])->toBeLessThan(50);
});

test('it can rotate hue', function () {
    $color = new Color(255, 0, 0); // Pure red
    $rotated = $color->rotate(120); // Should be green

    $hsl = $rotated->toHsl();
    expect($hsl['h'])->toBe(120);
});

test('it can saturate color', function () {
    $color = Color::fromHsl(0, 50, 50); // Semi-saturated red
    $saturated = $color->saturate(0.2);

    $hsl = $saturated->toHsl();
    expect($hsl['s'])->toBe(70);
});

test('it can desaturate color', function () {
    $color = Color::fromHsl(0, 100, 50); // Fully saturated red
    $desaturated = $color->desaturate(0.2);

    $hsl = $desaturated->toHsl();
    expect($hsl['s'])->toBe(80);
});

test('it can adjust lightness', function () {
    $color = new Color(255, 0, 0); // Pure red
    $adjusted = $color->withLightness(0.8);

    $hsl = $adjusted->toHsl();
    expect($hsl['l'])->toBe(80);
});

test('it can convert RGB to HSV', function () {
    // Test pure red
    $red = new Color(255, 0, 0);
    $hsv = $red->toHsv();
    expect($hsv['h'])->toBe(0);
    expect($hsv['s'])->toBe(100);
    expect($hsv['v'])->toBe(100);

    // Test pure green
    $green = new Color(0, 255, 0);
    $hsv = $green->toHsv();
    expect($hsv['h'])->toBe(120);
    expect($hsv['s'])->toBe(100);
    expect($hsv['v'])->toBe(100);

    // Test pure blue
    $blue = new Color(0, 0, 255);
    $hsv = $blue->toHsv();
    expect($hsv['h'])->toBe(240);
    expect($hsv['s'])->toBe(100);
    expect($hsv['v'])->toBe(100);

    // Test white (no saturation)
    $white = new Color(255, 255, 255);
    $hsv = $white->toHsv();
    expect($hsv['h'])->toBe(0);
    expect($hsv['s'])->toBe(0);
    expect($hsv['v'])->toBe(100);

    // Test black (no value)
    $black = new Color(0, 0, 0);
    $hsv = $black->toHsv();
    expect($hsv['h'])->toBe(0);
    expect($hsv['s'])->toBe(0);
    expect($hsv['v'])->toBe(0);
});

test('it can create color from HSV', function () {
    // Test pure red
    $red = Color::fromHsv(0, 100, 100);
    expect($red->toHex())->toBe('#ff0000');

    // Test pure green
    $green = Color::fromHsv(120, 100, 100);
    expect($green->toHex())->toBe('#00ff00');

    // Test pure blue
    $blue = Color::fromHsv(240, 100, 100);
    expect($blue->toHex())->toBe('#0000ff');

    // Test white
    $white = Color::fromHsv(0, 0, 100);
    expect($white->toHex())->toBe('#ffffff');

    // Test black
    $black = Color::fromHsv(0, 0, 0);
    expect($black->toHex())->toBe('#000000');

    // Test gray (no saturation)
    $gray = Color::fromHsv(0, 0, 50);
    expect($gray->toRgb()['r'])->toBe(128);
    expect($gray->toRgb()['g'])->toBe(128);
    expect($gray->toRgb()['b'])->toBe(128);
});

test('HSV conversion is reversible', function () {
    $originalColor = new Color(123, 45, 67);
    $hsv = $originalColor->toHsv();
    $convertedColor = Color::fromHsv($hsv['h'], $hsv['s'], $hsv['v']);

    // Allow for small rounding differences
    expect($convertedColor->getRed())->toBeGreaterThanOrEqual($originalColor->getRed() - 1);
    expect($convertedColor->getRed())->toBeLessThanOrEqual($originalColor->getRed() + 1);
    expect($convertedColor->getGreen())->toBeGreaterThanOrEqual($originalColor->getGreen() - 1);
    expect($convertedColor->getGreen())->toBeLessThanOrEqual($originalColor->getGreen() + 1);
    expect($convertedColor->getBlue())->toBeGreaterThanOrEqual($originalColor->getBlue() - 1);
    expect($convertedColor->getBlue())->toBeLessThanOrEqual($originalColor->getBlue() + 1);
});

test('it can convert RGB to CMYK', function () {
    // Test pure red (100% magenta and yellow)
    $red = new Color(255, 0, 0);
    $cmyk = $red->toCmyk();
    expect($cmyk['c'])->toBe(0);
    expect($cmyk['m'])->toBe(100);
    expect($cmyk['y'])->toBe(100);
    expect($cmyk['k'])->toBe(0);

    // Test pure green (100% cyan and yellow)
    $green = new Color(0, 255, 0);
    $cmyk = $green->toCmyk();
    expect($cmyk['c'])->toBe(100);
    expect($cmyk['m'])->toBe(0);
    expect($cmyk['y'])->toBe(100);
    expect($cmyk['k'])->toBe(0);

    // Test pure blue (100% cyan and magenta)
    $blue = new Color(0, 0, 255);
    $cmyk = $blue->toCmyk();
    expect($cmyk['c'])->toBe(100);
    expect($cmyk['m'])->toBe(100);
    expect($cmyk['y'])->toBe(0);
    expect($cmyk['k'])->toBe(0);

    // Test white (no color)
    $white = new Color(255, 255, 255);
    $cmyk = $white->toCmyk();
    expect($cmyk['c'])->toBe(0);
    expect($cmyk['m'])->toBe(0);
    expect($cmyk['y'])->toBe(0);
    expect($cmyk['k'])->toBe(0);

    // Test black (100% key)
    $black = new Color(0, 0, 0);
    $cmyk = $black->toCmyk();
    expect($cmyk['c'])->toBe(0);
    expect($cmyk['m'])->toBe(0);
    expect($cmyk['y'])->toBe(0);
    expect($cmyk['k'])->toBe(100);
});

test('it can create color from CMYK', function () {
    // Test pure red
    $red = Color::fromCmyk(0, 100, 100, 0);
    expect($red->toHex())->toBe('#ff0000');

    // Test pure green
    $green = Color::fromCmyk(100, 0, 100, 0);
    expect($green->toHex())->toBe('#00ff00');

    // Test pure blue
    $blue = Color::fromCmyk(100, 100, 0, 0);
    expect($blue->toHex())->toBe('#0000ff');

    // Test white
    $white = Color::fromCmyk(0, 0, 0, 0);
    expect($white->toHex())->toBe('#ffffff');

    // Test black
    $black = Color::fromCmyk(0, 0, 0, 100);
    expect($black->toHex())->toBe('#000000');
});

test('it throws exception for invalid CMYK values', function () {
    Color::fromCmyk(101, 0, 0, 0);
})->throws(InvalidArgumentException::class, 'CMYK values must be between 0 and 100');

test('CMYK conversion is reversible', function () {
    $originalColor = new Color(123, 45, 67);
    $cmyk = $originalColor->toCmyk();
    $convertedColor = Color::fromCmyk(
        $cmyk['c'],
        $cmyk['m'],
        $cmyk['y'],
        $cmyk['k']
    );

    // Allow for small rounding differences
    expect($convertedColor->getRed())->toBeGreaterThanOrEqual($originalColor->getRed() - 1);
    expect($convertedColor->getRed())->toBeLessThanOrEqual($originalColor->getRed() + 1);
    expect($convertedColor->getGreen())->toBeGreaterThanOrEqual($originalColor->getGreen() - 1);
    expect($convertedColor->getGreen())->toBeLessThanOrEqual($originalColor->getGreen() + 1);
    expect($convertedColor->getBlue())->toBeGreaterThanOrEqual($originalColor->getBlue() - 1);
    expect($convertedColor->getBlue())->toBeLessThanOrEqual($originalColor->getBlue() + 1);
});

test('it throws exception for invalid HSV values', function () {
    // Test invalid hue
    expect(fn () => Color::fromHsv(360, 100, 100))
        ->toThrow(InvalidArgumentException::class, 'Hue must be between 0 and 360');
    expect(fn () => Color::fromHsv(-1, 100, 100))
        ->toThrow(InvalidArgumentException::class, 'Hue must be between 0 and 360');

    // Test invalid saturation
    expect(fn () => Color::fromHsv(0, 101, 100))
        ->toThrow(InvalidArgumentException::class, 'Saturation must be between 0 and 100');
    expect(fn () => Color::fromHsv(0, -1, 100))
        ->toThrow(InvalidArgumentException::class, 'Saturation must be between 0 and 100');

    // Test invalid value
    expect(fn () => Color::fromHsv(0, 100, 101))
        ->toThrow(InvalidArgumentException::class, 'Value must be between 0 and 100');
    expect(fn () => Color::fromHsv(0, 100, -1))
        ->toThrow(InvalidArgumentException::class, 'Value must be between 0 and 100');
});

test('it can convert RGB to LAB', function () {
    // Test pure red
    $red = new Color(255, 0, 0);
    $lab = $red->toLab();
    expect($lab['l'])->toBe(53);
    expect($lab['a'])->toBe(80);
    expect($lab['b'])->toBe(67);

    // Test pure green
    $green = new Color(0, 255, 0);
    $lab = $green->toLab();
    expect($lab['l'])->toBe(88);
    expect($lab['a'])->toBe(-86);
    expect($lab['b'])->toBe(83);

    // Test pure blue
    $blue = new Color(0, 0, 255);
    $lab = $blue->toLab();
    expect($lab['l'])->toBe(32);
    expect($lab['a'])->toBe(79);
    expect($lab['b'])->toBe(-108);

    // Test white
    $white = new Color(255, 255, 255);
    $lab = $white->toLab();
    expect($lab['l'])->toBe(100);
    expect($lab['a'])->toBe(0);
    expect($lab['b'])->toBe(0);

    // Test black
    $black = new Color(0, 0, 0);
    $lab = $black->toLab();
    expect($lab['l'])->toBe(0);
    expect($lab['a'])->toBe(0);
    expect($lab['b'])->toBe(0);
});

test('it can create color from LAB', function () {
    // Test pure red
    $red = Color::fromLab(53, 80, 67);
    expect($red->getRed())->toBeGreaterThan(240);
    expect($red->getGreen())->toBeLessThan(20);
    expect($red->getBlue())->toBeLessThan(20);

    // Test pure green
    $green = Color::fromLab(88, -86, 83);
    expect($green->getRed())->toBeLessThan(20);
    expect($green->getGreen())->toBeGreaterThan(240);
    expect($green->getBlue())->toBeLessThan(20);

    // Test pure blue
    $blue = Color::fromLab(32, 79, -108);
    expect($blue->getRed())->toBeLessThan(20);
    expect($blue->getGreen())->toBeLessThan(20);
    expect($blue->getBlue())->toBeGreaterThan(240);

    // Test white
    $white = Color::fromLab(100, 0, 0);
    expect($white->getRed())->toBeGreaterThan(240);
    expect($white->getGreen())->toBeGreaterThan(240);
    expect($white->getBlue())->toBeGreaterThan(240);

    // Test black
    $black = Color::fromLab(0, 0, 0);
    expect($black->getRed())->toBeLessThan(20);
    expect($black->getGreen())->toBeLessThan(20);
    expect($black->getBlue())->toBeLessThan(20);
});

test('it throws exception for invalid LAB values', function () {
    // Test invalid lightness
    expect(fn () => Color::fromLab(-1, 0, 0))
        ->toThrow(InvalidArgumentException::class, 'Lightness must be between 0 and 100');
    expect(fn () => Color::fromLab(101, 0, 0))
        ->toThrow(InvalidArgumentException::class, 'Lightness must be between 0 and 100');

    // Test invalid a value
    expect(fn () => Color::fromLab(50, -129, 0))
        ->toThrow(InvalidArgumentException::class, 'A value must be between -128 and 127');
    expect(fn () => Color::fromLab(50, 128, 0))
        ->toThrow(InvalidArgumentException::class, 'A value must be between -128 and 127');

    // Test invalid b value
    expect(fn () => Color::fromLab(50, 0, -129))
        ->toThrow(InvalidArgumentException::class, 'B value must be between -128 and 127');
    expect(fn () => Color::fromLab(50, 0, 128))
        ->toThrow(InvalidArgumentException::class, 'B value must be between -128 and 127');
});

test('LAB conversion is reversible', function () {
    $originalColor = new Color(123, 45, 67);
    $lab = $originalColor->toLab();
    $convertedColor = Color::fromLab($lab['l'], $lab['a'], $lab['b']);

    // Allow for small rounding differences
    expect($convertedColor->getRed())->toBeGreaterThanOrEqual($originalColor->getRed() - 1);
    expect($convertedColor->getRed())->toBeLessThanOrEqual($originalColor->getRed() + 1);
    expect($convertedColor->getGreen())->toBeGreaterThanOrEqual($originalColor->getGreen() - 1);
    expect($convertedColor->getGreen())->toBeLessThanOrEqual($originalColor->getGreen() + 1);
    expect($convertedColor->getBlue())->toBeGreaterThanOrEqual($originalColor->getBlue() - 1);
    expect($convertedColor->getBlue())->toBeLessThanOrEqual($originalColor->getBlue() + 1);
});

// Edge Cases and Boundary Conditions
test('it handles pure black (RGB 0,0,0)', function () {
    $black = new Color(0, 0, 0);

    expect($black->toHex())->toBe('#000000');
    expect($black->getRed())->toBe(0);
    expect($black->getGreen())->toBe(0);
    expect($black->getBlue())->toBe(0);
    expect($black->isDark())->toBeTrue();
    expect($black->isLight())->toBeFalse();
    expect($black->getBrightness())->toBe(0.0);
});

test('it handles pure white (RGB 255,255,255)', function () {
    $white = new Color(255, 255, 255);

    expect($white->toHex())->toBe('#ffffff');
    expect($white->getRed())->toBe(255);
    expect($white->getGreen())->toBe(255);
    expect($white->getBlue())->toBe(255);
    expect($white->isLight())->toBeTrue();
    expect($white->isDark())->toBeFalse();
    expect($white->getBrightness())->toBe(255.0);
});

test('it throws exception for negative RGB values', function () {
    expect(fn () => new Color(-1, 0, 0))
        ->toThrow(InvalidArgumentException::class, 'Invalid red color component');
    expect(fn () => new Color(0, -1, 0))
        ->toThrow(InvalidArgumentException::class, 'Invalid green color component');
    expect(fn () => new Color(0, 0, -1))
        ->toThrow(InvalidArgumentException::class, 'Invalid blue color component');
});

test('it throws exception for RGB values above 255', function () {
    expect(fn () => new Color(256, 0, 0))
        ->toThrow(InvalidArgumentException::class, 'Invalid red color component');
    expect(fn () => new Color(0, 256, 0))
        ->toThrow(InvalidArgumentException::class, 'Invalid green color component');
    expect(fn () => new Color(0, 0, 256))
        ->toThrow(InvalidArgumentException::class, 'Invalid blue color component');
});

test('it throws exception for 3-character hex (shorthand not supported)', function () {
    expect(fn () => Color::fromHex('#f00'))
        ->toThrow(InvalidArgumentException::class, 'Invalid hex color format');
    expect(fn () => Color::fromHex('f00'))
        ->toThrow(InvalidArgumentException::class, 'Invalid hex color format');
});

test('it throws exception for 5-character hex', function () {
    expect(fn () => Color::fromHex('#ff000'))
        ->toThrow(InvalidArgumentException::class, 'Invalid hex color format');
});

test('it throws exception for hex with invalid characters', function () {
    expect(fn () => Color::fromHex('#gggggg'))
        ->toThrow(InvalidArgumentException::class, 'Invalid hex color format');
    expect(fn () => Color::fromHex('#zz0000'))
        ->toThrow(InvalidArgumentException::class, 'Invalid hex color format');
});

test('it handles hex with uppercase letters', function () {
    $color = Color::fromHex('#FF0000');

    expect($color->toHex())->toBe('#ff0000');
    expect($color->getRed())->toBe(255);
});

test('it handles hex with mixed case', function () {
    $color = Color::fromHex('#FfAa00');

    expect($color->toHex())->toBe('#ffaa00');
});

test('it handles chained color conversions with precision', function () {
    $original = new Color(123, 45, 67);

    // RGB -> HSL -> RGB
    $hsl = $original->toHsl();
    $fromHsl = Color::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

    expect($fromHsl->getRed())->toBeGreaterThanOrEqual($original->getRed() - 2);
    expect($fromHsl->getRed())->toBeLessThanOrEqual($original->getRed() + 2);

    // RGB -> HSV -> RGB
    $hsv = $original->toHsv();
    $fromHsv = Color::fromHsv($hsv['h'], $hsv['s'], $hsv['v']);

    expect($fromHsv->getRed())->toBeGreaterThanOrEqual($original->getRed() - 2);
    expect($fromHsv->getRed())->toBeLessThanOrEqual($original->getRed() + 2);
});

test('it handles extreme HSL values', function () {
    // Hue at boundary
    $color1 = Color::fromHsl(0, 100, 50);    // Red
    $color2 = Color::fromHsl(360, 100, 50);  // Also red

    expect($color1->toHex())->toBe($color2->toHex());

    // Saturation at 0 (grayscale)
    $gray = Color::fromHsl(180, 0, 50);
    $rgb = $gray->toRgb();
    expect($rgb['r'])->toBe($rgb['g']);
    expect($rgb['g'])->toBe($rgb['b']);

    // Lightness at 0 (black)
    $black = Color::fromHsl(120, 100, 0);
    expect($black->toHex())->toBe('#000000');

    // Lightness at 100 (white)
    $white = Color::fromHsl(240, 100, 100);
    expect($white->toHex())->toBe('#ffffff');
});

test('it handles color manipulation at boundaries', function () {
    $white = new Color(255, 255, 255);
    $black = new Color(0, 0, 0);

    // Lightening white should stay white
    $lightenedWhite = $white->lighten(50);
    expect($lightenedWhite->toHex())->toBe('#ffffff');

    // Darkening black should stay black
    $darkenedBlack = $black->darken(50);
    expect($darkenedBlack->toHex())->toBe('#000000');

    // Saturating already saturated
    $red = new Color(255, 0, 0);
    $saturatedRed = $red->saturate(50);
    expect($saturatedRed->toHex())->toBe('#ff0000');

    // Desaturating gray (already desaturated)
    $gray = new Color(128, 128, 128);
    $desaturatedGray = $gray->desaturate(50);
    expect($desaturatedGray->toHex())->toBe('#808080');
});

test('it handles hue rotation wrap-around', function () {
    $red = new Color(255, 0, 0);

    // Rotate 360 degrees should return to same color
    $rotated360 = $red->rotate(360);
    expect($rotated360->toHex())->toBe($red->toHex());

    // Rotate -360 degrees should return to same color
    $rotatedNeg360 = $red->rotate(-360);
    expect($rotatedNeg360->toHex())->toBe($red->toHex());

    // Large positive rotation
    $rotatedLarge = $red->rotate(720); // 2 full rotations
    expect($rotatedLarge->toHex())->toBe($red->toHex());
});

test('it handles pure primary colors', function () {
    $colors = [
        new Color(255, 0, 0),   // Red
        new Color(0, 255, 0),   // Green
        new Color(0, 0, 255),   // Blue
    ];

    foreach ($colors as $color) {
        // All conversions should work
        expect($color->toHsl())->toBeArray();
        expect($color->toHsv())->toBeArray();
        expect($color->toCmyk())->toBeArray();
        expect($color->toLab())->toBeArray();

        // Hex should be valid
        expect($color->toHex())->toMatch('/^#[0-9a-f]{6}$/');
    }
});

test('it maintains precision across multiple operations', function () {
    $original = new Color(100, 150, 200);

    // Chain multiple operations
    $modified = $original
        ->lighten(10)
        ->saturate(5)
        ->rotate(15)
        ->darken(5);

    // Result should still be valid
    expect($modified)->toBeInstanceOf(Color::class);
    expect($modified->getRed())->toBeBetween(0, 255);
    expect($modified->getGreen())->toBeBetween(0, 255);
    expect($modified->getBlue())->toBeBetween(0, 255);
});
