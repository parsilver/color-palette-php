<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\AnalogousStrategy;

describe('AnalogousStrategy Basic Functionality', function () {
    test('it can generate an analogous palette', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(3);
    });

    test('it always generates exactly 3 colors', function () {
        $strategy = new AnalogousStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(3);
        expect($palette2->count())->toBe(3);
        expect($palette3->count())->toBe(3);
    });

    test('it includes base color as middle color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[1])->toBe($baseColor);
    });

    test('it generates adjacent colors on color wheel', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // First color should be rotated -30 degrees
        $color1Expected = $baseColor->rotate(-30);
        expect($colors[0]->toHex())->toBe($color1Expected->toHex());

        // Third color should be rotated +30 degrees
        $color3Expected = $baseColor->rotate(30);
        expect($colors[2]->toHex())->toBe($color3Expected->toHex());
    });
});

describe('AnalogousStrategy Color Theory', function () {
    test('it generates colors 30 degrees apart', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $colors[1]->toHsl();
        $color1Hsl = $colors[0]->toHsl();
        $color3Hsl = $colors[2]->toHsl();

        // Color 1 should be -30 degrees from base
        $diff1 = $baseHsl['h'] - $color1Hsl['h'];
        // Handle wraparound
        if ($diff1 < 0) {
            $diff1 += 360;
        }
        expect($diff1)->toBeGreaterThanOrEqual(29);
        expect($diff1)->toBeLessThanOrEqual(31);

        // Color 3 should be +30 degrees from base
        $diff3 = $color3Hsl['h'] - $baseHsl['h'];
        // Handle wraparound
        if ($diff3 < 0) {
            $diff3 += 360;
        }
        expect($diff3)->toBeGreaterThanOrEqual(29);
        expect($diff3)->toBeLessThanOrEqual(31);
    });

    test('it maintains saturation and lightness across colors', function () {
        $strategy = new AnalogousStrategy;
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

    test('it creates harmonious color combinations', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // All three colors should be different
        expect($colors[0]->toHex())->not->toBe($colors[1]->toHex());
        expect($colors[1]->toHex())->not->toBe($colors[2]->toHex());
        expect($colors[0]->toHex())->not->toBe($colors[2]->toHex());
    });
});

describe('AnalogousStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with green color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with blue color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with black color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with white color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it works with gray color', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
    });

    test('it handles hue wraparound correctly', function () {
        $strategy = new AnalogousStrategy;
        // Color near red (hue near 0/360)
        $baseColor = Color::fromHsl(5, 80, 50);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(3);
        // Should handle wraparound without errors
    });
});

describe('AnalogousStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(3);
    });

    test('it ignores count option', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, analogous always returns 3 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(3);
    });

    test('it ignores unknown options', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
        ]);

        expect($palette->count())->toBe(3);
    });
});

describe('AnalogousStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new AnalogousStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new AnalogousStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('AnalogousStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new AnalogousStrategy;
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
