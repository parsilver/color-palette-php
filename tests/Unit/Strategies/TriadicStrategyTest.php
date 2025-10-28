<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\TriadicStrategy;

describe('TriadicStrategy Basic Functionality', function () {
    test('it can generate a triadic palette', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(3);
    });

    test('it always generates exactly 3 colors', function () {
        $strategy = new TriadicStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(3);
        expect($palette2->count())->toBe(3);
        expect($palette3->count())->toBe(3);
    });

    test('it includes base color as first color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[0])->toBe($baseColor);
    });

    test('it generates triadic colors evenly spaced', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // Second color should be rotated 120 degrees
        $color2Expected = $baseColor->rotate(120);
        expect($colors[1]->toHex())->toBe($color2Expected->toHex());

        // Third color should be rotated 240 degrees
        $color3Expected = $baseColor->rotate(240);
        expect($colors[2]->toHex())->toBe($color3Expected->toHex());
    });
});

describe('TriadicStrategy Color Theory', function () {
    test('it generates colors 120 degrees apart', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $colors[0]->toHsl();
        $color2Hsl = $colors[1]->toHsl();
        $color3Hsl = $colors[2]->toHsl();

        // Color 2 should be +120 degrees from base
        $diff2 = $color2Hsl['h'] - $baseHsl['h'];
        if ($diff2 < 0) {
            $diff2 += 360;
        }
        expect($diff2)->toBeGreaterThanOrEqual(119);
        expect($diff2)->toBeLessThanOrEqual(121);

        // Color 3 should be +240 degrees from base (or -120 from base)
        $diff3 = $color3Hsl['h'] - $baseHsl['h'];
        if ($diff3 < 0) {
            $diff3 += 360;
        }
        expect($diff3)->toBeGreaterThanOrEqual(239);
        expect($diff3)->toBeLessThanOrEqual(241);
    });

    test('it maintains saturation and lightness across colors', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(200, 100, 50);
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // Saturation and lightness should be similar
            expect(abs($hsl['s'] - $baseHsl['s']))->toBeLessThan(2);
            expect(abs($hsl['l'] - $baseHsl['l']))->toBeLessThan(2);
        }
    });

    test('it creates vibrant balanced combinations', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // All three colors should be different
        expect($colors[0]->toHex())->not->toBe($colors[1]->toHex());
        expect($colors[1]->toHex())->not->toBe($colors[2]->toHex());
        expect($colors[0]->toHex())->not->toBe($colors[2]->toHex());
    });

    test('it forms equilateral triangle on color wheel', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $hue1 = $colors[0]->toHsl()['h'];
        $hue2 = $colors[1]->toHsl()['h'];
        $hue3 = $colors[2]->toHsl()['h'];

        // Distance between color 1 and 2 should be ~120 degrees
        $dist12 = abs($hue2 - $hue1);
        if ($dist12 > 180) {
            $dist12 = 360 - $dist12;
        }

        // Distance between color 2 and 3 should be ~120 degrees
        $dist23 = abs($hue3 - $hue2);
        if ($dist23 > 180) {
            $dist23 = 360 - $dist23;
        }

        // Distance between color 3 and 1 should be ~120 degrees
        $dist31 = abs($hue1 - $hue3);
        if ($dist31 > 180) {
            $dist31 = 360 - $dist31;
        }

        // All distances should be approximately 120 degrees
        expect($dist12)->toBeGreaterThanOrEqual(118);
        expect($dist12)->toBeLessThanOrEqual(122);
        expect($dist23)->toBeGreaterThanOrEqual(118);
        expect($dist23)->toBeLessThanOrEqual(122);
        expect($dist31)->toBeGreaterThanOrEqual(118);
        expect($dist31)->toBeLessThanOrEqual(122);
    });
});

describe('TriadicStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with green color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with blue color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with black color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with white color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with gray color', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it handles hue wraparound correctly', function () {
        $strategy = new TriadicStrategy;
        // Color near red (hue near 0/360)
        $baseColor = Color::fromHsl(350, 80, 50);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
        // Should handle wraparound without errors
    });
});

describe('TriadicStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(3);
    });

    test('it ignores count option', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, triadic always returns 3 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(3);
    });

    test('it ignores unknown options', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
        ]);

        expect($palette->count())->toBe(3);
    });
});

describe('TriadicStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new TriadicStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('TriadicStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new TriadicStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            expect($color)->toBeInstanceOf(Color::class);
            expect($color->getRed())->toBeGreaterThanOrEqual(0);
            expect($color->getRed())->toBeLessThanOrEqual(255);
            expect($color->getGreen())->toBeGreaterThanOrEqual(0);
            expect($color->getGreen())->toBeLessThanOrEqual(255);
            expect($color->getBlue())->toBeGreaterThanOrEqual(0);
            expect($color->getBlue())->toBeLessThanOrEqual(255);
        }
    });
});
