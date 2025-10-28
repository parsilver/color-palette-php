<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Strategies\TetradicStrategy;

describe('TetradicStrategy Basic Functionality', function () {
    test('it can generate a tetradic palette', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette)->toBeInstanceOf(ColorPalette::class);
        expect($palette->count())->toBe(4);
    });

    test('it always generates exactly 4 colors', function () {
        $strategy = new TetradicStrategy;

        $palette1 = $strategy->generate(new Color(255, 0, 0));
        $palette2 = $strategy->generate(new Color(0, 255, 0));
        $palette3 = $strategy->generate(new Color(128, 128, 128));

        expect($palette1->count())->toBe(4);
        expect($palette2->count())->toBe(4);
        expect($palette3->count())->toBe(4);
    });

    test('it includes base color as first color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        expect($colors[0])->toBe($baseColor);
    });

    test('it generates four colors at 90 degree intervals', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 0, 0);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // Second color should be rotated 90 degrees
        $color2Expected = $baseColor->rotate(90);
        expect($colors[1]->toHex())->toBe($color2Expected->toHex());

        // Third color should be rotated 180 degrees
        $color3Expected = $baseColor->rotate(180);
        expect($colors[2]->toHex())->toBe($color3Expected->toHex());

        // Fourth color should be rotated 270 degrees
        $color4Expected = $baseColor->rotate(270);
        expect($colors[3]->toHex())->toBe($color4Expected->toHex());
    });
});

describe('TetradicStrategy Color Theory', function () {
    test('it generates colors 90 degrees apart on color wheel', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 0, 0); // Red: hue = 0

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $baseHsl = $colors[0]->toHsl();
        $color2Hsl = $colors[1]->toHsl();
        $color3Hsl = $colors[2]->toHsl();
        $color4Hsl = $colors[3]->toHsl();

        // Color 2 should be +90 degrees from base
        $diff2 = $color2Hsl['h'] - $baseHsl['h'];
        if ($diff2 < 0) {
            $diff2 += 360;
        }
        expect($diff2)->toBeGreaterThanOrEqual(89);
        expect($diff2)->toBeLessThanOrEqual(91);

        // Color 3 should be +180 degrees from base
        $diff3 = abs($baseHsl['h'] - $color3Hsl['h']);
        expect($diff3)->toBeGreaterThanOrEqual(179);
        expect($diff3)->toBeLessThanOrEqual(181);

        // Color 4 should be +270 degrees from base (or -90)
        $diff4 = $baseHsl['h'] - $color4Hsl['h'];
        if ($diff4 < 0) {
            $diff4 += 360;
        }
        expect($diff4)->toBeGreaterThanOrEqual(89);
        expect($diff4)->toBeLessThanOrEqual(91);
    });

    test('it creates two complementary pairs', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        $hsl1 = $colors[0]->toHsl();
        $hsl2 = $colors[1]->toHsl();
        $hsl3 = $colors[2]->toHsl();
        $hsl4 = $colors[3]->toHsl();

        // Colors 1 and 3 should be complementary (180° apart)
        $diff13 = abs($hsl1['h'] - $hsl3['h']);
        expect($diff13)->toBeGreaterThanOrEqual(179);
        expect($diff13)->toBeLessThanOrEqual(181);

        // Colors 2 and 4 should be complementary (180° apart)
        $diff24 = abs($hsl2['h'] - $hsl4['h']);
        expect($diff24)->toBeGreaterThanOrEqual(179);
        expect($diff24)->toBeLessThanOrEqual(181);
    });

    test('it maintains saturation and lightness across colors', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(200, 100, 50);
        $baseHsl = $baseColor->toHsl();

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        foreach ($colors as $color) {
            $hsl = $color->toHsl();
            // Saturation and lightness should be similar (hue rotation only)
            expect(abs($hsl['s'] - $baseHsl['s']))->toBeLessThan(2);
            expect(abs($hsl['l'] - $baseHsl['l']))->toBeLessThan(2);
        }
    });

    test('it creates rich color variety', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette = $strategy->generate($baseColor);
        $colors = $palette->getColors();

        // All four colors should be different
        $hexValues = array_map(fn ($color) => $color->toHex(), $colors);
        $uniqueHexValues = array_unique($hexValues);
        expect(count($uniqueHexValues))->toBe(4);
    });
});

describe('TetradicStrategy Edge Cases', function () {
    test('it works with red color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 0, 0); // Red

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
        expect($palette->getColors()[0]->toHex())->toBe('#ff0000');
    });

    test('it works with green color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(0, 255, 0); // Green

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
        expect($palette->getColors()[0]->toHex())->toBe('#00ff00');
    });

    test('it works with blue color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(0, 0, 255); // Blue

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
        expect($palette->getColors()[0]->toHex())->toBe('#0000ff');
    });

    test('it works with black color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(0, 0, 0); // Black

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
    });

    test('it works with white color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(255, 255, 255); // White

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
    });

    test('it works with gray color', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(128, 128, 128); // Gray

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
    });

    test('it handles hue wraparound correctly', function () {
        $strategy = new TetradicStrategy;
        // Color near red (hue near 0/360)
        $baseColor = Color::fromHsl(5, 80, 50);

        $palette = $strategy->generate($baseColor);

        expect($palette->count())->toBe(4);
        // Should handle wraparound without errors
    });
});

describe('TetradicStrategy Options', function () {
    test('it handles empty options array', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, []);

        expect($palette->count())->toBe(4);
    });

    test('it ignores count option', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(100, 150, 200);

        // Even if count is specified, tetradic always returns 4 colors
        $palette = $strategy->generate($baseColor, ['count' => 10]);

        expect($palette->count())->toBe(4);
    });

    test('it ignores unknown options', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(100, 150, 200);

        $palette = $strategy->generate($baseColor, [
            'unknown_option' => 'some_value',
            'another_option' => 123,
        ]);

        expect($palette->count())->toBe(4);
    });
});

describe('TetradicStrategy Multiple Invocations', function () {
    test('it produces consistent results for same inputs', function () {
        $strategy = new TetradicStrategy;
        $baseColor = new Color(180, 100, 150);

        $palette1 = $strategy->generate($baseColor);
        $palette2 = $strategy->generate($baseColor);

        expect($palette1->toArray())->toBe($palette2->toArray());
    });

    test('it generates different palettes for different base colors', function () {
        $strategy = new TetradicStrategy;
        $baseColor1 = new Color(255, 0, 0); // Red
        $baseColor2 = new Color(0, 255, 0); // Green

        $palette1 = $strategy->generate($baseColor1);
        $palette2 = $strategy->generate($baseColor2);

        expect($palette1->toArray())->not->toBe($palette2->toArray());
    });
});

describe('TetradicStrategy Color Validation', function () {
    test('it generates valid RGB colors', function () {
        $strategy = new TetradicStrategy;
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
