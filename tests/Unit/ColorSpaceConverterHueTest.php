<?php

declare(strict_types=1);

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorSpaceConverter;

/**
 * Regression: round($h * 60) can land exactly on 360 (e.g. rgb(255, 0, 1)), but
 * the code only wrapped negative hues. That violated the documented [0, 360) range
 * and made toHsv() emit a hue that fromHsv() then rejected, breaking the round-trip.
 */
describe('ColorSpaceConverter hue normalization', function () {
    // A handful of colors whose raw hue rounds up to exactly 360 degrees.
    $hueWrapColors = [
        [255, 0, 1],
        [255, 0, 2],
        [200, 0, 1],
    ];

    foreach ($hueWrapColors as $rgb) {
        [$r, $g, $b] = $rgb;

        test("toHsl({$r},{$g},{$b}) keeps hue within [0, 360)", function () use ($r, $g, $b) {
            $hsl = ColorSpaceConverter::toHsl(new Color($r, $g, $b));

            expect($hsl['h'])->toBeGreaterThanOrEqual(0);
            expect($hsl['h'])->toBeLessThan(360);
        });

        test("toHsv({$r},{$g},{$b}) keeps hue within [0, 360)", function () use ($r, $g, $b) {
            $hsv = ColorSpaceConverter::toHsv(new Color($r, $g, $b));

            expect($hsv['h'])->toBeGreaterThanOrEqual(0);
            expect($hsv['h'])->toBeLessThan(360);
        });

        test("toHsv({$r},{$g},{$b}) output is a valid fromHsv() input (round-trip does not throw)", function () use ($r, $g, $b) {
            $hsv = ColorSpaceConverter::toHsv(new Color($r, $g, $b));

            $result = ColorSpaceConverter::fromHsv($hsv['h'], $hsv['s'], $hsv['v']);

            expect($result)->toBeInstanceOf(Color::class);
        });
    }
});
