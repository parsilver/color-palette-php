<?php

/*
 * Regression tests for issue #13 ("Idempotence not observed").
 *
 * The idempotence tests that shipped with the original fix extracted three
 * times from example/assets/sample.jpg, which is a single flat colour. Every
 * k-means initialisation collapses to the same result on that image, so those
 * tests passed even with the 1.0.0 unseeded-random code restored. These tests
 * use a photo-like fixture on which k-means is genuinely sensitive to its
 * initialisation, and check the properties the fix promised:
 *
 *   1. the palette is a fixed function of the image (golden snapshot, GD);
 *   2. it does not depend on the extractor instance, the caller's global RNG
 *      state, or which PHP process computes it;
 *   3. it is ordered brightest-first, so equal colour sets always come back in
 *      the same order;
 *   4. it is stable: the same colours met in another order, or nudged by an
 *      invisible +/-1, give the same palette (the reporter also saw an
 *      upscaled copy of the photo come back with different colours).
 */

use Farzai\ColorPalette\Constants\AccessibilityConstants as A;
use Farzai\ColorPalette\Tests\Fixtures\SyntheticPhoto;

describe('extraction determinism (issue #13)', function () {
    test('GD returns the recorded palette for a photo-like image', function () {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('The gd extension is not available.');
        }

        // Recorded from the current clustering implementation (best of 10 seeded
        // weighted k-means++ runs). If an intentional algorithm change moves
        // this, update it in the same commit and say why.
        $golden = ['#b2bc72', '#8fbaa4', '#6eac5f', '#589c8c', '#898290'];

        foreach (range(1, 3) as $_) {
            expect(SyntheticPhoto::paletteHex('gd'))->toBe($golden);
        }
    });

    test('palette does not depend on the extractor instance or the global RNG', function (string $driver) {
        if (! extension_loaded($driver)) {
            $this->markTestSkipped("The {$driver} extension is not available.");
        }

        $palettes = [];
        foreach (range(1, 6) as $seed) {
            // A caller that seeds or consumes the global generator must not
            // change the result (1.0.0 drew its k-means seeds from mt_rand()).
            mt_srand($seed);
            mt_rand();
            $palettes[] = implode(',', SyntheticPhoto::paletteHex($driver));
        }
        mt_srand(random_int(0, PHP_INT_MAX));

        expect(array_values(array_unique($palettes)))->toHaveCount(1);
    })->with(['gd', 'imagick']);

    test('palette is identical across separate PHP processes', function (string $driver) {
        if (! extension_loaded($driver)) {
            $this->markTestSkipped("The {$driver} extension is not available.");
        }

        // Issue #13 was observed across separate requests, i.e. separate
        // processes. In-process repetition cannot see per-process randomness.
        $worker = dirname(__DIR__, 2).'/Fixtures/determinism_worker.php';
        $expected = SyntheticPhoto::paletteHex($driver);

        foreach (range(1, 3) as $_) {
            $proc = proc_open(
                [PHP_BINARY, '-d', 'memory_limit=-1', '-d', 'display_errors=stderr', $worker, $driver],
                [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
                $pipes
            );
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            expect(proc_close($proc))->toBe(0, $stderr)
                ->and(json_decode($stdout, true))->toBe($expected);
        }
    })->with(['gd', 'imagick']);

    test('palette is ordered brightest first', function (string $driver) {
        if (! extension_loaded($driver)) {
            $this->markTestSkipped("The {$driver} extension is not available.");
        }

        $luminances = array_map(function (string $hex) {
            [$r, $g, $b] = sscanf($hex, '#%02x%02x%02x');

            return (A::BRIGHTNESS_RED_COEFFICIENT * $r
                + A::BRIGHTNESS_GREEN_COEFFICIENT * $g
                + A::BRIGHTNESS_BLUE_COEFFICIENT * $b) / A::BRIGHTNESS_DIVISOR;
        }, SyntheticPhoto::paletteHex($driver));

        // sortColors() falls back to hue for luminances within 0.01 of each
        // other, so allow that tolerance between neighbours.
        for ($i = 1; $i < count($luminances); $i++) {
            expect($luminances[$i - 1] + 0.01)->toBeGreaterThanOrEqual($luminances[$i]);
        }
    })->with(['gd', 'imagick']);

    test('a mirrored copy gives the same palette', function (string $driver) {
        if (! extension_loaded($driver)) {
            $this->markTestSkipped("The {$driver} extension is not available.");
        }

        // Same colour histogram, different scan order: only the order in which
        // the colours reach k-means changes.
        $distance = SyntheticPhoto::paletteDistance(
            SyntheticPhoto::paletteHex($driver),
            SyntheticPhoto::paletteHex($driver, 5, SyntheticPhoto::mirroredBytes())
        );

        expect($distance)->toBeLessThanOrEqual(4.0);
    })->with(['gd', 'imagick']);

    test('an invisibly nudged copy gives the same palette', function (string $driver) {
        if (! extension_loaded($driver)) {
            $this->markTestSkipped("The {$driver} extension is not available.");
        }

        $distance = SyntheticPhoto::paletteDistance(
            SyntheticPhoto::paletteHex($driver),
            SyntheticPhoto::paletteHex($driver, 5, SyntheticPhoto::nudgedBytes())
        );

        expect($distance)->toBeLessThanOrEqual(4.0);
    })->with(['gd', 'imagick']);
});
