<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\ComplementaryStrategy;

describe('ComplementaryStrategy Basic Functionality', function () {
    test('it can generate a complementary palette', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(2);
    });

    test('it always generates exactly 2 colors', function () {
        $strategy = new ComplementaryStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(2);
        expect($palette2->count())->toBe(2);
        expect($palette3->count())->toBe(2);
    });

    test('it includes base color as first color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[0])->toBe($baseColor);
    });

    test('it generates complement as second color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red (hue = 0)

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // Second color should be the complement (rotated 180 degrees)
        $complement = $baseColor->rotate(180);
        expect($colors[1]->toHex())->toBe($complement->toHex());
    });
});

describe('ComplementaryStrategy Color Theory', function () {
    test('it generates complement 180 degrees apart on color wheel', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $colors[0]->toHsl();
        $complementHsl = $colors[1]->toHsl();

        // Hue should differ by 180 degrees (accounting for wraparound)
        $hueDiff = abs($baseHsl['h'] - $complementHsl['h']);
        expect($hueDiff)->toBeGreaterThanOrEqual(179);
        expect($hueDiff)->toBeLessThanOrEqual(181);
    });

    test('it generates high contrast colors', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // Complementary colors should be different
        expect($colors[0]->toHex())->not->toBe($colors[1]->toHex());
    });

    test('it maintains saturation and lightness', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(200, 100, 50);
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();
        $complementHsl = $colors[1]->toHsl();

        // Saturation and lightness should be similar (hue rotation doesn't change them)
        // Allow small tolerance for floating point precision
        expect(abs($complementHsl['s'] - $baseHsl['s']))->toBeLessThan(2);
        expect(abs($complementHsl['l'] - $baseHsl['l']))->toBeLessThan(2);
    });
});

describe('ComplementaryStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(2);
        $colors = $palette->getColors();
        // Red's complement should be cyan-ish
        expect($colors[0]->toHex())->toBe('#ff0000');
    });

    test('it works with green color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(2);
        $colors = $palette->getColors();
        // Green's complement should be magenta-ish
        expect($colors[0]->toHex())->toBe('#00ff00');
    });

    test('it works with blue color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(2);
        $colors = $palette->getColors();
        // Blue's complement should be yellow-ish
        expect($colors[0]->toHex())->toBe('#0000ff');
    });

    test('it works with black color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(2);
    });

    test('it works with white color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(2);
    });

    test('it works with gray color', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(2);
    });
});

describe('ComplementaryStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(2);
    });

    test('it ignores count option', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, complementary always returns 2 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(2);
    });

    test('it ignores unknown options', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(2);
    });
});

describe('ComplementaryStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new ComplementaryStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('ComplementaryStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new ComplementaryStrategy;
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
