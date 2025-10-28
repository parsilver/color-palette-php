<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorSpaceConverter;

describe('ColorSpaceConverter RGB to HSL', function () {
    test('it converts pure red to HSL', function () {
        $red = new Color(255, 0, 0);

        $hsl = ColorSpaceConverter::toHsl($red);

        expect($hsl['h'])->toBe(0);
        expect($hsl['s'])->toBe(100);
        expect($hsl['l'])->toBe(50);
    });

    test('it converts pure green to HSL', function () {
        $green = new Color(0, 255, 0);

        $hsl = ColorSpaceConverter::toHsl($green);

        expect($hsl['h'])->toBe(120);
        expect($hsl['s'])->toBe(100);
        expect($hsl['l'])->toBe(50);
    });

    test('it converts pure blue to HSL', function () {
        $blue = new Color(0, 0, 255);

        $hsl = ColorSpaceConverter::toHsl($blue);

        expect($hsl['h'])->toBe(240);
        expect($hsl['s'])->toBe(100);
        expect($hsl['l'])->toBe(50);
    });

    test('it converts black to HSL', function () {
        $black = new Color(0, 0, 0);

        $hsl = ColorSpaceConverter::toHsl($black);

        expect($hsl['h'])->toBe(0);
        expect($hsl['s'])->toBe(0);
        expect($hsl['l'])->toBe(0);
    });

    test('it converts white to HSL', function () {
        $white = new Color(255, 255, 255);

        $hsl = ColorSpaceConverter::toHsl($white);

        expect($hsl['h'])->toBe(0);
        expect($hsl['s'])->toBe(0);
        expect($hsl['l'])->toBe(100);
    });

    test('it converts gray to HSL', function () {
        $gray = new Color(128, 128, 128);

        $hsl = ColorSpaceConverter::toHsl($gray);

        expect($hsl['h'])->toBe(0);
        expect($hsl['s'])->toBe(0);
        expect($hsl['l'])->toBeGreaterThan(49);
        expect($hsl['l'])->toBeLessThan(51);
    });

    test('it returns integer values', function () {
        $color = new Color(100, 150, 200);

        $hsl = ColorSpaceConverter::toHsl($color);

        expect($hsl['h'])->toBeInt();
        expect($hsl['s'])->toBeInt();
        expect($hsl['l'])->toBeInt();
    });
});

describe('ColorSpaceConverter HSL to RGB', function () {
    test('it converts HSL red to RGB', function () {
        $color = ColorSpaceConverter::fromHsl(0, 100, 50);

        expect($color->getRed())->toBe(255);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts HSL green to RGB', function () {
        $color = ColorSpaceConverter::fromHsl(120, 100, 50);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(255);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts HSL blue to RGB', function () {
        $color = ColorSpaceConverter::fromHsl(240, 100, 50);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(255);
    });

    test('it converts HSL black to RGB', function () {
        $color = ColorSpaceConverter::fromHsl(0, 0, 0);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts HSL white to RGB', function () {
        $color = ColorSpaceConverter::fromHsl(0, 0, 100);

        expect($color->getRed())->toBe(255);
        expect($color->getGreen())->toBe(255);
        expect($color->getBlue())->toBe(255);
    });

    test('it converts HSL gray to RGB', function () {
        $color = ColorSpaceConverter::fromHsl(0, 0, 50);

        // Should be approximately 128,128,128
        expect($color->getRed())->toBeGreaterThan(125);
        expect($color->getRed())->toBeLessThan(130);
        expect($color->getGreen())->toBeGreaterThan(125);
        expect($color->getGreen())->toBeLessThan(130);
        expect($color->getBlue())->toBeGreaterThan(125);
        expect($color->getBlue())->toBeLessThan(130);
    });

    test('it returns Color instance', function () {
        $color = ColorSpaceConverter::fromHsl(180, 50, 50);

        expect($color)->toBeInstanceOf(Color::class);
    });
});

describe('ColorSpaceConverter HSL Round Trip', function () {
    test('it maintains color through RGB->HSL->RGB conversion', function () {
        $original = new Color(100, 150, 200);

        $hsl = ColorSpaceConverter::toHsl($original);
        $converted = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

        // Allow small rounding differences
        expect(abs($original->getRed() - $converted->getRed()))->toBeLessThanOrEqual(2);
        expect(abs($original->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(2);
        expect(abs($original->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(2);
    });

    test('it maintains primary colors through round trip', function () {
        $colors = [
            new Color(255, 0, 0),
            new Color(0, 255, 0),
            new Color(0, 0, 255),
        ];

        foreach ($colors as $original) {
            $hsl = ColorSpaceConverter::toHsl($original);
            $converted = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

            expect($converted->toHex())->toBe($original->toHex());
        }
    });
});

describe('ColorSpaceConverter RGB to HSV', function () {
    test('it converts pure red to HSV', function () {
        $red = new Color(255, 0, 0);

        $hsv = ColorSpaceConverter::toHsv($red);

        expect($hsv['h'])->toBe(0);
        expect($hsv['s'])->toBe(100);
        expect($hsv['v'])->toBe(100);
    });

    test('it converts pure green to HSV', function () {
        $green = new Color(0, 255, 0);

        $hsv = ColorSpaceConverter::toHsv($green);

        expect($hsv['h'])->toBe(120);
        expect($hsv['s'])->toBe(100);
        expect($hsv['v'])->toBe(100);
    });

    test('it converts pure blue to HSV', function () {
        $blue = new Color(0, 0, 255);

        $hsv = ColorSpaceConverter::toHsv($blue);

        expect($hsv['h'])->toBe(240);
        expect($hsv['s'])->toBe(100);
        expect($hsv['v'])->toBe(100);
    });

    test('it converts black to HSV', function () {
        $black = new Color(0, 0, 0);

        $hsv = ColorSpaceConverter::toHsv($black);

        expect($hsv['h'])->toBe(0);
        expect($hsv['s'])->toBe(0);
        expect($hsv['v'])->toBe(0);
    });

    test('it converts white to HSV', function () {
        $white = new Color(255, 255, 255);

        $hsv = ColorSpaceConverter::toHsv($white);

        expect($hsv['h'])->toBe(0);
        expect($hsv['s'])->toBe(0);
        expect($hsv['v'])->toBe(100);
    });

    test('it returns integer values', function () {
        $color = new Color(100, 150, 200);

        $hsv = ColorSpaceConverter::toHsv($color);

        expect($hsv['h'])->toBeInt();
        expect($hsv['s'])->toBeInt();
        expect($hsv['v'])->toBeInt();
    });
});

describe('ColorSpaceConverter HSV to RGB', function () {
    test('it converts HSV red to RGB', function () {
        $color = ColorSpaceConverter::fromHsv(0, 100, 100);

        expect($color->getRed())->toBe(255);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts HSV green to RGB', function () {
        $color = ColorSpaceConverter::fromHsv(120, 100, 100);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(255);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts HSV blue to RGB', function () {
        $color = ColorSpaceConverter::fromHsv(240, 100, 100);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(255);
    });

    test('it validates hue range', function () {
        expect(fn () => ColorSpaceConverter::fromHsv(-1, 50, 50))
            ->toThrow(InvalidArgumentException::class, 'Hue must be between 0 and 360');

        expect(fn () => ColorSpaceConverter::fromHsv(361, 50, 50))
            ->toThrow(InvalidArgumentException::class, 'Hue must be between 0 and 360');
    });

    test('it validates saturation range', function () {
        expect(fn () => ColorSpaceConverter::fromHsv(180, -1, 50))
            ->toThrow(InvalidArgumentException::class, 'Saturation must be between 0 and 100');

        expect(fn () => ColorSpaceConverter::fromHsv(180, 101, 50))
            ->toThrow(InvalidArgumentException::class, 'Saturation must be between 0 and 100');
    });

    test('it validates value range', function () {
        expect(fn () => ColorSpaceConverter::fromHsv(180, 50, -1))
            ->toThrow(InvalidArgumentException::class, 'Value must be between 0 and 100');

        expect(fn () => ColorSpaceConverter::fromHsv(180, 50, 101))
            ->toThrow(InvalidArgumentException::class, 'Value must be between 0 and 100');
    });
});

describe('ColorSpaceConverter RGB to CMYK', function () {
    test('it converts pure red to CMYK', function () {
        $red = new Color(255, 0, 0);

        $cmyk = ColorSpaceConverter::toCmyk($red);

        expect($cmyk['c'])->toBe(0);
        expect($cmyk['m'])->toBe(100);
        expect($cmyk['y'])->toBe(100);
        expect($cmyk['k'])->toBe(0);
    });

    test('it converts pure green to CMYK', function () {
        $green = new Color(0, 255, 0);

        $cmyk = ColorSpaceConverter::toCmyk($green);

        expect($cmyk['c'])->toBe(100);
        expect($cmyk['m'])->toBe(0);
        expect($cmyk['y'])->toBe(100);
        expect($cmyk['k'])->toBe(0);
    });

    test('it converts pure blue to CMYK', function () {
        $blue = new Color(0, 0, 255);

        $cmyk = ColorSpaceConverter::toCmyk($blue);

        expect($cmyk['c'])->toBe(100);
        expect($cmyk['m'])->toBe(100);
        expect($cmyk['y'])->toBe(0);
        expect($cmyk['k'])->toBe(0);
    });

    test('it converts black to CMYK', function () {
        $black = new Color(0, 0, 0);

        $cmyk = ColorSpaceConverter::toCmyk($black);

        expect($cmyk['c'])->toBe(0);
        expect($cmyk['m'])->toBe(0);
        expect($cmyk['y'])->toBe(0);
        expect($cmyk['k'])->toBe(100);
    });

    test('it converts white to CMYK', function () {
        $white = new Color(255, 255, 255);

        $cmyk = ColorSpaceConverter::toCmyk($white);

        expect($cmyk['c'])->toBe(0);
        expect($cmyk['m'])->toBe(0);
        expect($cmyk['y'])->toBe(0);
        expect($cmyk['k'])->toBe(0);
    });

    test('it returns integer values', function () {
        $color = new Color(100, 150, 200);

        $cmyk = ColorSpaceConverter::toCmyk($color);

        expect($cmyk['c'])->toBeInt();
        expect($cmyk['m'])->toBeInt();
        expect($cmyk['y'])->toBeInt();
        expect($cmyk['k'])->toBeInt();
    });
});

describe('ColorSpaceConverter CMYK to RGB', function () {
    test('it converts CMYK red to RGB', function () {
        $color = ColorSpaceConverter::fromCmyk(0, 100, 100, 0);

        expect($color->getRed())->toBe(255);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts CMYK black to RGB', function () {
        $color = ColorSpaceConverter::fromCmyk(0, 0, 0, 100);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(0);
    });

    test('it converts CMYK white to RGB', function () {
        $color = ColorSpaceConverter::fromCmyk(0, 0, 0, 0);

        expect($color->getRed())->toBe(255);
        expect($color->getGreen())->toBe(255);
        expect($color->getBlue())->toBe(255);
    });

    test('it validates CMYK values range', function () {
        expect(fn () => ColorSpaceConverter::fromCmyk(-1, 50, 50, 50))
            ->toThrow(InvalidArgumentException::class, 'CMYK values must be between 0 and 100');

        expect(fn () => ColorSpaceConverter::fromCmyk(50, 101, 50, 50))
            ->toThrow(InvalidArgumentException::class, 'CMYK values must be between 0 and 100');
    });
});

describe('ColorSpaceConverter RGB to LAB', function () {
    test('it converts white to LAB', function () {
        $white = new Color(255, 255, 255);

        $lab = ColorSpaceConverter::toLab($white);

        expect($lab['l'])->toBeGreaterThan(95);
        expect($lab['l'])->toBeLessThanOrEqual(100);
        expect($lab['a'])->toBeGreaterThan(-5);
        expect($lab['a'])->toBeLessThan(5);
        expect($lab['b'])->toBeGreaterThan(-5);
        expect($lab['b'])->toBeLessThan(5);
    });

    test('it converts black to LAB', function () {
        $black = new Color(0, 0, 0);

        $lab = ColorSpaceConverter::toLab($black);

        expect($lab['l'])->toBe(0);
        expect($lab['a'])->toBe(0);
        expect($lab['b'])->toBe(0);
    });

    test('it converts pure red to LAB', function () {
        $red = new Color(255, 0, 0);

        $lab = ColorSpaceConverter::toLab($red);

        // Red in LAB has positive 'a' (red-green axis)
        expect($lab['l'])->toBeGreaterThan(40);
        expect($lab['l'])->toBeLessThan(60);
        expect($lab['a'])->toBeGreaterThan(60);
        expect($lab['b'])->toBeGreaterThan(40);
    });

    test('it converts pure green to LAB', function () {
        $green = new Color(0, 255, 0);

        $lab = ColorSpaceConverter::toLab($green);

        // Green in LAB has negative 'a' (red-green axis)
        expect($lab['l'])->toBeGreaterThan(80);
        expect($lab['a'])->toBeLessThan(-60);
    });

    test('it converts pure blue to LAB', function () {
        $blue = new Color(0, 0, 255);

        $lab = ColorSpaceConverter::toLab($blue);

        // Blue in LAB has negative 'b' (blue-yellow axis)
        expect($lab['l'])->toBeGreaterThan(20);
        expect($lab['l'])->toBeLessThan(40);
        expect($lab['b'])->toBeLessThan(-50);
    });

    test('it returns integer values', function () {
        $color = new Color(100, 150, 200);

        $lab = ColorSpaceConverter::toLab($color);

        expect($lab['l'])->toBeInt();
        expect($lab['a'])->toBeInt();
        expect($lab['b'])->toBeInt();
    });
});

describe('ColorSpaceConverter LAB to RGB', function () {
    test('it converts LAB white to RGB', function () {
        $color = ColorSpaceConverter::fromLab(100, 0, 0);

        expect($color->getRed())->toBe(255);
        expect($color->getGreen())->toBe(255);
        expect($color->getBlue())->toBe(255);
    });

    test('it converts LAB black to RGB', function () {
        $color = ColorSpaceConverter::fromLab(0, 0, 0);

        expect($color->getRed())->toBe(0);
        expect($color->getGreen())->toBe(0);
        expect($color->getBlue())->toBe(0);
    });

    test('it validates lightness range', function () {
        expect(fn () => ColorSpaceConverter::fromLab(-1, 0, 0))
            ->toThrow(InvalidArgumentException::class, 'Lightness must be between 0 and 100');

        expect(fn () => ColorSpaceConverter::fromLab(101, 0, 0))
            ->toThrow(InvalidArgumentException::class, 'Lightness must be between 0 and 100');
    });

    test('it validates a component range', function () {
        expect(fn () => ColorSpaceConverter::fromLab(50, -129, 0))
            ->toThrow(InvalidArgumentException::class, 'A value must be between -128 and 127');

        expect(fn () => ColorSpaceConverter::fromLab(50, 128, 0))
            ->toThrow(InvalidArgumentException::class, 'A value must be between -128 and 127');
    });

    test('it validates b component range', function () {
        expect(fn () => ColorSpaceConverter::fromLab(50, 0, -129))
            ->toThrow(InvalidArgumentException::class, 'B value must be between -128 and 127');

        expect(fn () => ColorSpaceConverter::fromLab(50, 0, 128))
            ->toThrow(InvalidArgumentException::class, 'B value must be between -128 and 127');
    });

    test('it clamps RGB values to valid range', function () {
        // LAB values that might produce out-of-gamut RGB
        $color = ColorSpaceConverter::fromLab(50, 100, 50);

        expect($color->getRed())->toBeGreaterThanOrEqual(0);
        expect($color->getRed())->toBeLessThanOrEqual(255);
        expect($color->getGreen())->toBeGreaterThanOrEqual(0);
        expect($color->getGreen())->toBeLessThanOrEqual(255);
        expect($color->getBlue())->toBeGreaterThanOrEqual(0);
        expect($color->getBlue())->toBeLessThanOrEqual(255);
    });
});

describe('ColorSpaceConverter LAB Round Trip', function () {
    test('it maintains color through RGB->LAB->RGB conversion', function () {
        $original = new Color(100, 150, 200);

        $lab = ColorSpaceConverter::toLab($original);
        $converted = ColorSpaceConverter::fromLab($lab['l'], $lab['a'], $lab['b']);

        // LAB conversion may have larger rounding differences due to complexity
        expect(abs($original->getRed() - $converted->getRed()))->toBeLessThanOrEqual(3);
        expect(abs($original->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(3);
        expect(abs($original->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(3);
    });

    test('it maintains black and white through LAB round trip', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        $blackLab = ColorSpaceConverter::toLab($black);
        $blackConverted = ColorSpaceConverter::fromLab($blackLab['l'], $blackLab['a'], $blackLab['b']);

        $whiteLab = ColorSpaceConverter::toLab($white);
        $whiteConverted = ColorSpaceConverter::fromLab($whiteLab['l'], $whiteLab['a'], $whiteLab['b']);

        expect($blackConverted->toHex())->toBe($black->toHex());
        expect($whiteConverted->toHex())->toBe($white->toHex());
    });
});

describe('ColorSpaceConverter RGB to HSB', function () {
    test('it converts RGB to HSB', function () {
        $color = new Color(255, 0, 0);

        $hsb = ColorSpaceConverter::rgbToHsb(255, 0, 0);

        expect($hsb['h'])->toBe(0);
        expect($hsb['s'])->toBe(1);
        expect($hsb['b'])->toBe(1);
    });

    test('it returns numeric values', function () {
        $hsb = ColorSpaceConverter::rgbToHsb(100, 150, 200);

        expect($hsb['h'])->toBeNumeric();
        expect($hsb['s'])->toBeNumeric();
        expect($hsb['b'])->toBeNumeric();
    });

    test('it converts black to HSB', function () {
        $hsb = ColorSpaceConverter::rgbToHsb(0, 0, 0);

        expect($hsb['h'])->toBe(0);
        expect($hsb['s'])->toBe(0);
        expect($hsb['b'])->toBe(0);
    });

    test('it converts white to HSB', function () {
        $hsb = ColorSpaceConverter::rgbToHsb(255, 255, 255);

        expect($hsb['h'])->toBe(0);
        expect($hsb['s'])->toBe(0);
        expect($hsb['b'])->toBe(1);
    });
});

describe('ColorSpaceConverter Edge Cases', function () {
    test('it handles achromatic colors in HSL conversion', function () {
        $colors = [
            new Color(0, 0, 0),     // Black
            new Color(128, 128, 128), // Gray
            new Color(255, 255, 255), // White
        ];

        foreach ($colors as $color) {
            $hsl = ColorSpaceConverter::toHsl($color);

            expect($hsl['h'])->toBe(0);
            expect($hsl['s'])->toBe(0);
        }
    });

    test('it handles achromatic colors in HSV conversion', function () {
        $colors = [
            new Color(0, 0, 0),     // Black
            new Color(128, 128, 128), // Gray
            new Color(255, 255, 255), // White
        ];

        foreach ($colors as $color) {
            $hsv = ColorSpaceConverter::toHsv($color);

            expect($hsv['h'])->toBe(0);
            expect($hsv['s'])->toBe(0);
        }
    });

    test('it handles very dark colors', function () {
        $darkColor = new Color(1, 2, 3);

        $hsl = ColorSpaceConverter::toHsl($darkColor);
        $converted = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

        expect($converted)->toBeInstanceOf(Color::class);
    });

    test('it handles very bright colors', function () {
        $brightColor = new Color(254, 253, 252);

        $hsl = ColorSpaceConverter::toHsl($brightColor);
        $converted = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

        expect($converted)->toBeInstanceOf(Color::class);
    });
});

describe('ColorSpaceConverter Consistency', function () {
    test('it produces consistent HSL conversions', function () {
        $color = new Color(123, 45, 67);

        $hsl1 = ColorSpaceConverter::toHsl($color);
        $hsl2 = ColorSpaceConverter::toHsl($color);

        expect($hsl1)->toBe($hsl2);
    });

    test('it produces consistent HSV conversions', function () {
        $color = new Color(123, 45, 67);

        $hsv1 = ColorSpaceConverter::toHsv($color);
        $hsv2 = ColorSpaceConverter::toHsv($color);

        expect($hsv1)->toBe($hsv2);
    });

    test('it produces consistent CMYK conversions', function () {
        $color = new Color(123, 45, 67);

        $cmyk1 = ColorSpaceConverter::toCmyk($color);
        $cmyk2 = ColorSpaceConverter::toCmyk($color);

        expect($cmyk1)->toBe($cmyk2);
    });

    test('it produces consistent LAB conversions', function () {
        $color = new Color(123, 45, 67);

        $lab1 = ColorSpaceConverter::toLab($color);
        $lab2 = ColorSpaceConverter::toLab($color);

        expect($lab1)->toBe($lab2);
    });
});

describe('ColorSpaceConverter Known Color Values', function () {
    test('it converts standard web colors correctly', function () {
        // Test a few standard web colors
        $cyan = new Color(0, 255, 255);
        $magenta = new Color(255, 0, 255);
        $yellow = new Color(255, 255, 0);

        $cyanHsl = ColorSpaceConverter::toHsl($cyan);
        $magentaHsl = ColorSpaceConverter::toHsl($magenta);
        $yellowHsl = ColorSpaceConverter::toHsl($yellow);

        expect($cyanHsl['h'])->toBe(180);
        expect($magentaHsl['h'])->toBe(300);
        expect($yellowHsl['h'])->toBe(60);
    });

    test('it maintains color relationships in HSL', function () {
        $red = new Color(255, 0, 0);
        $green = new Color(0, 255, 0);

        $redHsl = ColorSpaceConverter::toHsl($red);
        $greenHsl = ColorSpaceConverter::toHsl($green);

        // Red and green should be 120 degrees apart
        $hueDiff = abs($greenHsl['h'] - $redHsl['h']);
        expect($hueDiff)->toBe(120);
    });
});

describe('ColorSpaceConverter Precision', function () {
    test('it handles floating point precision in conversions', function () {
        $color = new Color(127, 127, 127);

        $hsl = ColorSpaceConverter::toHsl($color);
        $converted = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

        // Should be very close to original
        expect(abs($color->getRed() - $converted->getRed()))->toBeLessThanOrEqual(1);
        expect(abs($color->getGreen() - $converted->getGreen()))->toBeLessThanOrEqual(1);
        expect(abs($color->getBlue() - $converted->getBlue()))->toBeLessThanOrEqual(1);
    });

    test('it maintains precision with multiple conversions', function () {
        $original = new Color(100, 150, 200);

        $hsl = ColorSpaceConverter::toHsl($original);
        $step1 = ColorSpaceConverter::fromHsl($hsl['h'], $hsl['s'], $hsl['l']);

        $hsl2 = ColorSpaceConverter::toHsl($step1);
        $step2 = ColorSpaceConverter::fromHsl($hsl2['h'], $hsl2['s'], $hsl2['l']);

        // After two round trips, should still be very close
        expect(abs($original->getRed() - $step2->getRed()))->toBeLessThanOrEqual(2);
        expect(abs($original->getGreen() - $step2->getGreen()))->toBeLessThanOrEqual(2);
        expect(abs($original->getBlue() - $step2->getBlue()))->toBeLessThanOrEqual(2);
    });
});
