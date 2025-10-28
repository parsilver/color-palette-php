<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\ShadesStrategy;

describe('ShadesStrategy Basic Functionality', function () {
    test('it can generate a shades palette', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBeGreaterThan(0);
    });

    test('it generates 5 colors by default', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it includes base color as first color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[0])->toBe($baseColor);
    });

    test('it can generate custom count of colors', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(10);
    });

    test('it can generate two colors', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor, ['count' => 2]);

        expect($palette->count())->toBe(2);
        expect($palette->getColors()[0])->toBe($baseColor);
    });

    test('it can generate large palettes', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, ['count' => 20]);

        expect($palette->count())->toBe(20);
    });
});

describe('ShadesStrategy Color Theory', function () {
    test('it generates progressively darker colors', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor, ['count' => 5]);
        $colors = $palette->getColors();

        // Get lightness values
        $lightnessValues = array_map(fn ($color) => $color->toHsl()['l'], $colors);

        // First value should be the base color's lightness
        expect($lightnessValues[0])->toBe($baseColor->toHsl()['l']);

        // Subsequent values should generally decrease (getting darker)
        for ($i = 1; $i < count($lightnessValues); $i++) {
            // Each shade should be equal or darker than the previous
            expect($lightnessValues[$i])->toBeLessThanOrEqual($lightnessValues[$i - 1]);
        }
    });

    test('it maintains hue across all shades', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor, ['count' => 7]);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // All colors should have the same or similar hue (allowing for slight precision loss)
            // At very low lightness, hue may become undefined, so we skip very dark colors
            if ($hsl['l'] > 5) {
                expect(abs($hsl['h'] - $baseHsl['h']))->toBeLessThan(2);
            }
        }
    });

    test('it varies lightness across palette', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor, ['count' => 5]);
        $colors = $palette->getColors();

        $lightnessValues = array_map(fn ($color) => $color->toHsl()['l'], $colors);

        // Check that we have at least 2 different lightness values
        $uniqueLightness = array_unique($lightnessValues);
        expect(count($uniqueLightness))->toBeGreaterThan(1);
    });

    test('it keeps lightness values within valid range', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor, ['count' => 10]);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // Lightness should be between 0 and 100
            expect($hsl['l'])->toBeGreaterThanOrEqual(0);
            expect($hsl['l'])->toBeLessThanOrEqual(100);
        }
    });

    test('it creates depth and hierarchy', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor, ['count' => 6]);
        $colors = $palette->getColors();

        // Get lightness range
        $lightnessValues = array_map(fn ($color) => $color->toHsl()['l'], $colors);
        $minLightness = min($lightnessValues);
        $maxLightness = max($lightnessValues);

        // Should have a good range of lightness
        expect($maxLightness - $minLightness)->toBeGreaterThan(10);
    });
});

describe('ShadesStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette->count())->toBe(5);
    });

    test('it works with green color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette->count())->toBe(5);
    });

    test('it works with blue color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette->count())->toBe(5);
    });

    test('it works with black color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with white color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with gray color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with already dark color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(50, 25, 25); // Very dark red

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with highly saturated color', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(255, 0, 0); // Pure red

        $palette = $strategy->generate($baseColor, ['count' => 7]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(7);
    });
});

describe('ShadesStrategy Options', function () {
    test('it respects count option', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette1 = $strategy->generate($baseColor, ['count' => 3]);
        $palette2 = $strategy->generate($baseColor, ['count' => 8]);

        expect($palette1->count())->toBe(3);
        expect($palette2->count())->toBe(8);
    });

    test('it handles empty options array', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(5); // Should use default
    });

    test('it ignores unknown options', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'count' => 7,
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(7);
    });
});

describe('ShadesStrategy Color Progression', function () {
    test('it generates progressive darkening', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor, ['count' => 5]);
        $colors = $palette->getColors();

        // Get lightness values
        $lightnessValues = array_map(fn ($color) => $color->toHsl()['l'], $colors);

        // First value should be the base color's lightness
        expect($lightnessValues[0])->toBe($baseColor->toHsl()['l']);

        // Colors should get progressively darker
        for ($i = 1; $i < count($lightnessValues); $i++) {
            expect($lightnessValues[$i])->toBeLessThanOrEqual($lightnessValues[$i - 1]);
        }
    });

    test('it maintains color harmony', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor, ['count' => 6]);
        $colors = $palette->getColors();

        // All colors should be valid Color instances
        foreach ($colors as $color) {
            expect($color)->toBeInstanceOf(Color::class);

            // Verify RGB values are in valid range
            expect($color->getRed())->toBeGreaterThanOrEqual(0);
            expect($color->getRed())->toBeLessThanOrEqual(255);
            expect($color->getGreen())->toBeGreaterThanOrEqual(0);
            expect($color->getGreen())->toBeLessThanOrEqual(255);
            expect($color->getBlue())->toBeGreaterThanOrEqual(0);
            expect($color->getBlue())->toBeLessThanOrEqual(255);
        }
    });
});

describe('ShadesStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor, ['count' => 5]);
        $palette2 = $strategy->generate($baseColor, ['count' => 5]);

        // Both palettes should have the same colors
        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it can generate different palettes from different base colors', function () {
        $strategy = new ShadesStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1, ['count' => 5]);
        $palette2 = $strategy->generate($baseColor2, ['count' => 5]);

        // Palettes should be different
        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('ShadesStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new ShadesStrategy;
        $baseColor = new Color(200, 100, 50);

        $palette = $strategy->generate($baseColor, ['count' => 8]);
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
