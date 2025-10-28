<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorManipulator;

describe('ColorManipulator Lighten Operations', function () {
    test('it can lighten a color', function () {
        $color = new Color(100, 100, 100);

        $lighter = ColorManipulator::lighten($color, 0.2); // Lighten by 20%

        $originalHsl = $color->toHsl();
        $lighterHsl = $lighter->toHsl();

        expect($lighterHsl['l'])->toBeGreaterThan($originalHsl['l']);
    });

    test('it lightens by specified amount', function () {
        $color = Color::fromHsl(180, 50, 30);

        $lighter = ColorManipulator::lighten($color, 0.2); // Lighten by 20%

        $hsl = $lighter->toHsl();
        // Original lightness: 30, after +20%: 50
        expect($hsl['l'])->toBe(50);
    });

    test('it does not exceed maximum lightness', function () {
        $color = Color::fromHsl(180, 50, 95);

        $lighter = ColorManipulator::lighten($color, 0.2); // Try to go to 115

        $hsl = $lighter->toHsl();
        expect($hsl['l'])->toBe(100); // Capped at 100
    });

    test('it preserves hue when lightening', function () {
        $color = Color::fromHsl(180, 50, 50);

        $lighter = ColorManipulator::lighten($color, 0.2);

        $originalHsl = $color->toHsl();
        $lighterHsl = $lighter->toHsl();

        expect($lighterHsl['h'])->toBe($originalHsl['h']);
    });

    test('it preserves saturation when lightening', function () {
        $color = Color::fromHsl(180, 50, 50);

        $lighter = ColorManipulator::lighten($color, 0.2);

        $originalHsl = $color->toHsl();
        $lighterHsl = $lighter->toHsl();

        expect($lighterHsl['s'])->toBe($originalHsl['s']);
    });

    test('it returns new color instance', function () {
        $color = new Color(100, 100, 100);

        $lighter = ColorManipulator::lighten($color, 0.1);

        expect($lighter)->not->toBe($color);
        expect($lighter)->toBeInstanceOf(Color::class);
    });
});

describe('ColorManipulator Darken Operations', function () {
    test('it can darken a color', function () {
        $color = new Color(100, 100, 100);

        $darker = ColorManipulator::darken($color, 0.2); // Darken by 20%

        $originalHsl = $color->toHsl();
        $darkerHsl = $darker->toHsl();

        expect($darkerHsl['l'])->toBeLessThan($originalHsl['l']);
    });

    test('it darkens by specified amount', function () {
        $color = Color::fromHsl(180, 50, 70);

        $darker = ColorManipulator::darken($color, 0.2); // Darken by 20%

        $hsl = $darker->toHsl();
        // Original lightness: 70, after -20%: 50
        expect($hsl['l'])->toBe(50);
    });

    test('it does not go below minimum lightness', function () {
        $color = Color::fromHsl(180, 50, 5);

        $darker = ColorManipulator::darken($color, 0.2); // Try to go to -15

        $hsl = $darker->toHsl();
        expect($hsl['l'])->toBe(0); // Capped at 0
    });

    test('it preserves hue when darkening', function () {
        $color = Color::fromHsl(180, 50, 50);

        $darker = ColorManipulator::darken($color, 0.2);

        $originalHsl = $color->toHsl();
        $darkerHsl = $darker->toHsl();

        expect($darkerHsl['h'])->toBe($originalHsl['h']);
    });

    test('it preserves saturation when darkening', function () {
        $color = Color::fromHsl(180, 50, 50);

        $darker = ColorManipulator::darken($color, 0.2);

        $originalHsl = $color->toHsl();
        $darkerHsl = $darker->toHsl();

        expect($darkerHsl['s'])->toBe($originalHsl['s']);
    });

    test('it returns new color instance', function () {
        $color = new Color(100, 100, 100);

        $darker = ColorManipulator::darken($color, 0.1);

        expect($darker)->not->toBe($color);
        expect($darker)->toBeInstanceOf(Color::class);
    });
});

describe('ColorManipulator Saturate Operations', function () {
    test('it can increase saturation', function () {
        $color = Color::fromHsl(180, 30, 50);

        $saturated = ColorManipulator::saturate($color, 0.2); // Increase by 20%

        $hsl = $saturated->toHsl();
        // Original saturation: 30, after +20%: 50
        expect($hsl['s'])->toBe(50);
    });

    test('it does not exceed maximum saturation', function () {
        $color = Color::fromHsl(180, 95, 50);

        $saturated = ColorManipulator::saturate($color, 0.2); // Try to go to 115

        $hsl = $saturated->toHsl();
        expect($hsl['s'])->toBe(100); // Capped at 100
    });

    test('it preserves hue when saturating', function () {
        $color = Color::fromHsl(180, 50, 50);

        $saturated = ColorManipulator::saturate($color, 0.2);

        $originalHsl = $color->toHsl();
        $saturatedHsl = $saturated->toHsl();

        expect($saturatedHsl['h'])->toBe($originalHsl['h']);
    });

    test('it preserves lightness when saturating', function () {
        $color = Color::fromHsl(180, 50, 50);

        $saturated = ColorManipulator::saturate($color, 0.2);

        $originalHsl = $color->toHsl();
        $saturatedHsl = $saturated->toHsl();

        expect($saturatedHsl['l'])->toBe($originalHsl['l']);
    });

    test('it makes colors more vibrant', function () {
        $mutedColor = Color::fromHsl(120, 20, 50);

        $vibrantColor = ColorManipulator::saturate($mutedColor, 0.6);

        $hsl = $vibrantColor->toHsl();
        expect($hsl['s'])->toBe(80); // 20 + 60 = 80
    });
});

describe('ColorManipulator Desaturate Operations', function () {
    test('it can decrease saturation', function () {
        $color = Color::fromHsl(180, 70, 50);

        $desaturated = ColorManipulator::desaturate($color, 0.2); // Decrease by 20%

        $hsl = $desaturated->toHsl();
        // Original saturation: 70, after -20%: 50
        expect($hsl['s'])->toBe(50);
    });

    test('it does not go below minimum saturation', function () {
        $color = Color::fromHsl(180, 5, 50);

        $desaturated = ColorManipulator::desaturate($color, 0.2); // Try to go to -15

        $hsl = $desaturated->toHsl();
        expect($hsl['s'])->toBe(0); // Capped at 0
    });

    test('it preserves hue when desaturating', function () {
        $color = Color::fromHsl(180, 50, 50);

        $desaturated = ColorManipulator::desaturate($color, 0.2);

        $originalHsl = $color->toHsl();
        $desaturatedHsl = $desaturated->toHsl();

        expect($desaturatedHsl['h'])->toBe($originalHsl['h']);
    });

    test('it preserves lightness when desaturating', function () {
        $color = Color::fromHsl(180, 50, 50);

        $desaturated = ColorManipulator::desaturate($color, 0.2);

        $originalHsl = $color->toHsl();
        $desaturatedHsl = $desaturated->toHsl();

        expect($desaturatedHsl['l'])->toBe($originalHsl['l']);
    });

    test('it makes colors more muted', function () {
        $vibrantColor = Color::fromHsl(120, 80, 50);

        $mutedColor = ColorManipulator::desaturate($vibrantColor, 0.6);

        $hsl = $mutedColor->toHsl();
        expect($hsl['s'])->toBe(20); // 80 - 60 = 20
    });
});

describe('ColorManipulator Hue Rotation', function () {
    test('it can rotate hue by positive degrees', function () {
        $color = Color::fromHsl(180, 50, 50);

        $rotated = ColorManipulator::rotate($color, 60);

        $hsl = $rotated->toHsl();
        expect($hsl['h'])->toBe(240); // 180 + 60 = 240
    });

    test('it can rotate hue by negative degrees', function () {
        $color = Color::fromHsl(180, 50, 50);

        $rotated = ColorManipulator::rotate($color, -60);

        $hsl = $rotated->toHsl();
        expect($hsl['h'])->toBe(120); // 180 - 60 = 120
    });

    test('it wraps around at 360 degrees', function () {
        $color = Color::fromHsl(350, 50, 50);

        $rotated = ColorManipulator::rotate($color, 30);

        $hsl = $rotated->toHsl();
        expect($hsl['h'])->toBe(20); // (350 + 30) % 360 = 20
    });

    test('it wraps around at 0 degrees', function () {
        $color = Color::fromHsl(10, 50, 50);

        $rotated = ColorManipulator::rotate($color, -30);

        $hsl = $rotated->toHsl();
        expect($hsl['h'])->toBe(340); // (10 - 30 + 360) % 360 = 340
    });

    test('it preserves saturation when rotating', function () {
        $color = Color::fromHsl(180, 50, 50);

        $rotated = ColorManipulator::rotate($color, 90);

        $originalHsl = $color->toHsl();
        $rotatedHsl = $rotated->toHsl();

        expect($rotatedHsl['s'])->toBe($originalHsl['s']);
    });

    test('it preserves lightness when rotating', function () {
        $color = Color::fromHsl(180, 50, 50);

        $rotated = ColorManipulator::rotate($color, 90);

        $originalHsl = $color->toHsl();
        $rotatedHsl = $rotated->toHsl();

        expect($rotatedHsl['l'])->toBe($originalHsl['l']);
    });

    test('it can rotate full circle', function () {
        $color = Color::fromHsl(180, 50, 50);

        $rotated = ColorManipulator::rotate($color, 360);

        $hsl = $rotated->toHsl();
        expect($hsl['h'])->toBe(180); // Back to original
    });
});

describe('ColorManipulator Set Lightness', function () {
    test('it can set lightness to specific value', function () {
        $color = new Color(100, 100, 100);

        $modified = ColorManipulator::withLightness($color, 0.7); // Set to 70%

        $hsl = $modified->toHsl();
        expect($hsl['l'])->toBe(70);
    });

    test('it accepts values from 0 to 1', function () {
        $color = Color::fromHsl(180, 50, 50);

        $min = ColorManipulator::withLightness($color, 0);
        $mid = ColorManipulator::withLightness($color, 0.5);
        $max = ColorManipulator::withLightness($color, 1);

        expect($min->toHsl()['l'])->toBe(0);
        expect($mid->toHsl()['l'])->toBe(50);
        expect($max->toHsl()['l'])->toBe(100);
    });

    test('it preserves hue when setting lightness', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withLightness($color, 0.7);

        $originalHsl = $color->toHsl();
        $modifiedHsl = $modified->toHsl();

        expect($modifiedHsl['h'])->toBe($originalHsl['h']);
    });

    test('it preserves saturation when setting lightness', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withLightness($color, 0.7);

        $originalHsl = $color->toHsl();
        $modifiedHsl = $modified->toHsl();

        expect($modifiedHsl['s'])->toBe($originalHsl['s']);
    });
});

describe('ColorManipulator Set Saturation', function () {
    test('it can set saturation to specific value', function () {
        $color = Color::fromHsl(180, 30, 50);

        $modified = ColorManipulator::withSaturation($color, 0.8); // Set to 80%

        $hsl = $modified->toHsl();
        expect($hsl['s'])->toBe(80);
    });

    test('it accepts values from 0 to 1', function () {
        $color = Color::fromHsl(180, 50, 50);

        $min = ColorManipulator::withSaturation($color, 0);
        $mid = ColorManipulator::withSaturation($color, 0.5);
        $max = ColorManipulator::withSaturation($color, 1);

        expect($min->toHsl()['s'])->toBe(0);
        expect($mid->toHsl()['s'])->toBe(50);
        expect($max->toHsl()['s'])->toBe(100);
    });

    test('it preserves hue when setting saturation', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withSaturation($color, 0.8);

        $originalHsl = $color->toHsl();
        $modifiedHsl = $modified->toHsl();

        expect($modifiedHsl['h'])->toBe($originalHsl['h']);
    });

    test('it preserves lightness when setting saturation', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withSaturation($color, 0.8);

        $originalHsl = $color->toHsl();
        $modifiedHsl = $modified->toHsl();

        expect($modifiedHsl['l'])->toBe($originalHsl['l']);
    });
});

describe('ColorManipulator Set Hue', function () {
    test('it can set hue to specific value', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withHue($color, 240);

        $hsl = $modified->toHsl();
        expect($hsl['h'])->toBe(240);
    });

    test('it accepts values from 0 to 360', function () {
        $color = Color::fromHsl(180, 50, 50);

        $red = ColorManipulator::withHue($color, 0);
        $green = ColorManipulator::withHue($color, 120);
        $blue = ColorManipulator::withHue($color, 240);

        expect($red->toHsl()['h'])->toBe(0);
        expect($green->toHsl()['h'])->toBe(120);
        expect($blue->toHsl()['h'])->toBe(240);
    });

    test('it preserves saturation when setting hue', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withHue($color, 240);

        $originalHsl = $color->toHsl();
        $modifiedHsl = $modified->toHsl();

        expect($modifiedHsl['s'])->toBe($originalHsl['s']);
    });

    test('it preserves lightness when setting hue', function () {
        $color = Color::fromHsl(180, 50, 50);

        $modified = ColorManipulator::withHue($color, 240);

        $originalHsl = $color->toHsl();
        $modifiedHsl = $modified->toHsl();

        expect($modifiedHsl['l'])->toBe($originalHsl['l']);
    });
});

describe('ColorManipulator Color Mixing', function () {
    test('it can mix two colors equally', function () {
        $red = new Color(255, 0, 0);
        $blue = new Color(0, 0, 255);

        $mixed = ColorManipulator::mix($red, $blue, 0.5);

        // 50/50 mix should give purple
        expect($mixed->getRed())->toBeGreaterThan(100);
        expect($mixed->getBlue())->toBeGreaterThan(100);
    });

    test('it respects weight parameter', function () {
        $red = new Color(255, 0, 0);
        $blue = new Color(0, 0, 255);

        $moreRed = ColorManipulator::mix($red, $blue, 0.75);
        $moreBlue = ColorManipulator::mix($red, $blue, 0.25);

        expect($moreRed->getRed())->toBeGreaterThan($moreBlue->getRed());
        expect($moreRed->getBlue())->toBeLessThan($moreBlue->getBlue());
    });

    test('it returns first color when weight is 1', function () {
        $red = new Color(255, 0, 0);
        $blue = new Color(0, 0, 255);

        $mixed = ColorManipulator::mix($red, $blue, 1.0);

        expect($mixed->getRed())->toBe(255);
        expect($mixed->getGreen())->toBe(0);
        expect($mixed->getBlue())->toBe(0);
    });

    test('it returns second color when weight is 0', function () {
        $red = new Color(255, 0, 0);
        $blue = new Color(0, 0, 255);

        $mixed = ColorManipulator::mix($red, $blue, 0.0);

        expect($mixed->getRed())->toBe(0);
        expect($mixed->getGreen())->toBe(0);
        expect($mixed->getBlue())->toBe(255);
    });

    test('it clamps weight to 0-1 range', function () {
        $red = new Color(255, 0, 0);
        $blue = new Color(0, 0, 255);

        $overOne = ColorManipulator::mix($red, $blue, 1.5);
        $underZero = ColorManipulator::mix($red, $blue, -0.5);

        // Should behave like weight = 1.0
        expect($overOne->toHex())->toBe($red->toHex());

        // Should behave like weight = 0.0
        expect($underZero->toHex())->toBe($blue->toHex());
    });

    test('it mixes in RGB color space', function () {
        $black = new Color(0, 0, 0);
        $white = new Color(255, 255, 255);

        $gray = ColorManipulator::mix($black, $white, 0.5);

        // 50/50 mix of black and white should give medium gray
        expect($gray->getRed())->toBeGreaterThan(120);
        expect($gray->getRed())->toBeLessThan(135);
        expect($gray->getGreen())->toBeGreaterThan(120);
        expect($gray->getGreen())->toBeLessThan(135);
        expect($gray->getBlue())->toBeGreaterThan(120);
        expect($gray->getBlue())->toBeLessThan(135);
    });
});

describe('ColorManipulator Color Inversion', function () {
    test('it inverts RGB values', function () {
        $color = new Color(100, 150, 200);

        $inverted = ColorManipulator::invert($color);

        expect($inverted->getRed())->toBe(155); // 255 - 100
        expect($inverted->getGreen())->toBe(105); // 255 - 150
        expect($inverted->getBlue())->toBe(55); // 255 - 200
    });

    test('it inverts black to white', function () {
        $black = new Color(0, 0, 0);

        $inverted = ColorManipulator::invert($black);

        expect($inverted->getRed())->toBe(255);
        expect($inverted->getGreen())->toBe(255);
        expect($inverted->getBlue())->toBe(255);
    });

    test('it inverts white to black', function () {
        $white = new Color(255, 255, 255);

        $inverted = ColorManipulator::invert($white);

        expect($inverted->getRed())->toBe(0);
        expect($inverted->getGreen())->toBe(0);
        expect($inverted->getBlue())->toBe(0);
    });

    test('it inverts pure red to cyan', function () {
        $red = new Color(255, 0, 0);

        $inverted = ColorManipulator::invert($red);

        expect($inverted->getRed())->toBe(0);
        expect($inverted->getGreen())->toBe(255);
        expect($inverted->getBlue())->toBe(255);
    });

    test('it is reversible', function () {
        $color = new Color(123, 45, 67);

        $inverted = ColorManipulator::invert($color);
        $restored = ColorManipulator::invert($inverted);

        expect($restored->toHex())->toBe($color->toHex());
    });
});

describe('ColorManipulator Grayscale Conversion', function () {
    test('it converts to grayscale by removing saturation', function () {
        $color = Color::fromHsl(180, 80, 50);

        $gray = ColorManipulator::grayscale($color);

        $hsl = $gray->toHsl();
        expect($hsl['s'])->toBe(0);
    });

    test('it preserves lightness when converting to grayscale', function () {
        $color = Color::fromHsl(180, 80, 50);

        $gray = ColorManipulator::grayscale($color);

        $originalHsl = $color->toHsl();
        $grayHsl = $gray->toHsl();

        expect($grayHsl['l'])->toBe($originalHsl['l']);
    });

    test('it converts red to gray', function () {
        $red = new Color(255, 0, 0);

        $gray = ColorManipulator::grayscale($red);

        $hsl = $gray->toHsl();
        expect($hsl['s'])->toBe(0);
    });

    test('it converts already gray color correctly', function () {
        $alreadyGray = new Color(128, 128, 128);

        $gray = ColorManipulator::grayscale($alreadyGray);

        // Should remain the same
        expect($gray->getRed())->toBe($alreadyGray->getRed());
        expect($gray->getGreen())->toBe($alreadyGray->getGreen());
        expect($gray->getBlue())->toBe($alreadyGray->getBlue());
    });

    test('it produces valid RGB values', function () {
        $color = new Color(200, 100, 50);

        $gray = ColorManipulator::grayscale($color);

        expect($gray->getRed())->toBeGreaterThanOrEqual(0);
        expect($gray->getRed())->toBeLessThanOrEqual(255);
        expect($gray->getGreen())->toBeGreaterThanOrEqual(0);
        expect($gray->getGreen())->toBeLessThanOrEqual(255);
        expect($gray->getBlue())->toBeGreaterThanOrEqual(0);
        expect($gray->getBlue())->toBeLessThanOrEqual(255);
    });
});

describe('ColorManipulator Edge Cases', function () {
    test('it handles black color manipulation', function () {
        $black = new Color(0, 0, 0);

        $lightened = ColorManipulator::lighten($black, 0.5);
        $saturated = ColorManipulator::saturate($black, 0.5);
        $rotated = ColorManipulator::rotate($black, 180);

        expect($lightened)->toBeInstanceOf(Color::class);
        expect($saturated)->toBeInstanceOf(Color::class);
        expect($rotated)->toBeInstanceOf(Color::class);
    });

    test('it handles white color manipulation', function () {
        $white = new Color(255, 255, 255);

        $darkened = ColorManipulator::darken($white, 0.5);
        $desaturated = ColorManipulator::desaturate($white, 0.5);
        $rotated = ColorManipulator::rotate($white, 180);

        expect($darkened)->toBeInstanceOf(Color::class);
        expect($desaturated)->toBeInstanceOf(Color::class);
        expect($rotated)->toBeInstanceOf(Color::class);
    });

    test('it handles extreme lighten amounts', function () {
        $color = Color::fromHsl(180, 50, 10);

        $result = ColorManipulator::lighten($color, 2.0); // 200%

        $hsl = $result->toHsl();
        expect($hsl['l'])->toBe(100); // Should be capped
    });

    test('it handles extreme darken amounts', function () {
        $color = Color::fromHsl(180, 50, 90);

        $result = ColorManipulator::darken($color, 2.0); // 200%

        $hsl = $result->toHsl();
        expect($hsl['l'])->toBe(0); // Should be capped
    });
});

describe('ColorManipulator Consistency', function () {
    test('it produces consistent results for same inputs', function () {
        $color = new Color(100, 150, 200);

        $result1 = ColorManipulator::lighten($color, 0.2);
        $result2 = ColorManipulator::lighten($color, 0.2);

        expect($result1->toHex())->toBe($result2->toHex());
    });

    test('it returns valid Color instances', function () {
        $color = new Color(100, 150, 200);

        $lightened = ColorManipulator::lighten($color, 0.2);
        $darkened = ColorManipulator::darken($color, 0.2);
        $saturated = ColorManipulator::saturate($color, 0.2);
        $desaturated = ColorManipulator::desaturate($color, 0.2);
        $rotated = ColorManipulator::rotate($color, 60);

        expect($lightened)->toBeInstanceOf(Color::class);
        expect($darkened)->toBeInstanceOf(Color::class);
        expect($saturated)->toBeInstanceOf(Color::class);
        expect($desaturated)->toBeInstanceOf(Color::class);
        expect($rotated)->toBeInstanceOf(Color::class);
    });

    test('it produces valid RGB values after all operations', function () {
        $color = new Color(100, 150, 200);

        $operations = [
            ColorManipulator::lighten($color, 0.2),
            ColorManipulator::darken($color, 0.2),
            ColorManipulator::saturate($color, 0.2),
            ColorManipulator::desaturate($color, 0.2),
            ColorManipulator::rotate($color, 60),
            ColorManipulator::invert($color),
            ColorManipulator::grayscale($color),
        ];

        foreach ($operations as $result) {
            expect($result->getRed())->toBeGreaterThanOrEqual(0);
            expect($result->getRed())->toBeLessThanOrEqual(255);
            expect($result->getGreen())->toBeGreaterThanOrEqual(0);
            expect($result->getGreen())->toBeLessThanOrEqual(255);
            expect($result->getBlue())->toBeGreaterThanOrEqual(0);
            expect($result->getBlue())->toBeLessThanOrEqual(255);
        }
    });
});
