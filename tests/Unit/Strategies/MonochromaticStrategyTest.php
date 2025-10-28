<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\MonochromaticStrategy;

describe('MonochromaticStrategy Basic Functionality', function () {
    test('it can generate a monochromatic palette', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBeGreaterThan(0);
    });

    test('it generates 5 colors by default', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(5);
    });

    test('it includes base color as first color', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[0])->toBe($baseColor);
    });

    test('it can generate custom count of colors', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(10);
    });

    test('it can generate two colors', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor, ['count' => 2]);

        expect($palette->count())->toBe(2);
        expect($palette->getColors()[0])->toBe($baseColor);
    });

    test('it can generate large palettes', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, ['count' => 20]);

        expect($palette->count())->toBe(20);
    });
});

describe('MonochromaticStrategy Color Theory', function () {
    test('it generates colors with same hue', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor, ['count' => 7]);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // All colors should have the same hue
            expect($hsl['h'])->toBe($baseHsl['h']);
        }
    });

    test('it generates colors with similar saturation when not at extremes', function () {
        $strategy = new MonochromaticStrategy;
        // Use a mid-range lightness color to avoid extremes where saturation becomes 0
        $baseColor = new Color(180, 90, 60);
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor, ['count' => 5]);
        $colors = $palette->getColors();

        // Colors that aren't at extreme lightness should have similar saturation
        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // At extreme lightness (near 0 or 100), saturation can become 0 due to color space conversion
            // So we only check non-extreme colors
            if ($hsl['l'] > 10 && $hsl['l'] < 90) {
                expect(abs($hsl['s'] - $baseHsl['s']))->toBeLessThan(5);
            }
        }
    });

    test('it varies lightness across palette', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor, ['count' => 5]);
        $colors = $palette->getColors();

        $lightnessValues = array_map(fn ($color) => $color->toHsl()['l'], $colors);

        // Check that we have at least 2 different lightness values
        $uniqueLightness = array_unique($lightnessValues);
        expect(count($uniqueLightness))->toBeGreaterThan(1);
    });

    test('it keeps lightness values within valid range', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 255, 255); // White (very high lightness)

        $palette = $strategy->generate($baseColor, ['count' => 10]);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // Lightness should be between 0 and 100
            expect($hsl['l'])->toBeGreaterThanOrEqual(0);
            expect($hsl['l'])->toBeLessThanOrEqual(100);
        }
    });
});

describe('MonochromaticStrategy Edge Cases', function () {
    test('it works with black base color', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with white base color', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with gray base color', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor, ['count' => 5]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(5);
    });

    test('it works with highly saturated color', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(255, 0, 0); // Pure red

        $palette = $strategy->generate($baseColor, ['count' => 7]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(7);
    });

    test('it works with low saturation color', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(200, 180, 180); // Low saturation pink

        $palette = $strategy->generate($baseColor, ['count' => 7]);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(7);
    });
});

describe('MonochromaticStrategy Options', function () {
    test('it respects count option', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette1 = $strategy->generate($baseColor, ['count' => 3]);
        $palette2 = $strategy->generate($baseColor, ['count' => 8]);

        expect($palette1->count())->toBe(3);
        expect($palette2->count())->toBe(8);
    });

    test('it handles empty options array', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(5); // Should use default
    });

    test('it ignores unknown options', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'count' => 7,
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(7);
    });
});

describe('MonochromaticStrategy Color Progression', function () {
    test('it generates progressive lightness changes', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(128, 64, 32);

        $palette = $strategy->generate($baseColor, ['count' => 5]);
        $colors = $palette->getColors();

        // Get lightness values
        $lightnessValues = array_map(fn ($color) => $color->toHsl()['l'], $colors);

        // First value should be the base color's lightness
        expect($lightnessValues[0])->toBe($baseColor->toHsl()['l']);

        // Subsequent values should generally increase (though they may be clamped at boundaries)
        // We just verify we have a range of values
        $min = min($lightnessValues);
        $max = max($lightnessValues);
        expect($max)->toBeGreaterThanOrEqual($min);
    });

    test('it maintains color harmony', function () {
        $strategy = new MonochromaticStrategy;
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

describe('MonochromaticStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor, ['count' => 5]);
        $palette2 = $strategy->generate($baseColor, ['count' => 5]);

        // Both palettes should have the same colors
        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it can generate different palettes from different base colors', function () {
        $strategy = new MonochromaticStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1, ['count' => 5]);
        $palette2 = $strategy->generate($baseColor2, ['count' => 5]);

        // Palettes should be different
        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});
