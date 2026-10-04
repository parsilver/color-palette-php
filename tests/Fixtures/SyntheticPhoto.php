<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Tests\Fixtures;

use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\ImagickColorExtractor;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * A deterministic, photo-like test image for extraction-determinism tests.
 *
 * Issue #13 could not be caught with example/assets/sample.jpg: that file is a
 * single flat colour (#fe0000), so every k-means initialisation collapses to the
 * same five identical centroids and any amount of randomness is invisible.
 *
 * This fixture is built from overlapping soft colour blobs plus noise, so the
 * colour distribution is continuous and k-means has several local optima: a
 * different centroid initialisation produces a visibly different palette, the
 * same way the reporter's photograph did.
 *
 * It is generated in memory with integer arithmetic and a locally seeded
 * Mt19937, with no image codec involved, so the pixels are bit-identical on
 * every platform and PHP version the library supports.
 */
final class SyntheticPhoto
{
    public const WIDTH = 96;

    public const HEIGHT = 96;

    /**
     * Row-major RGB triplets, length WIDTH * HEIGHT * 3.
     *
     * @return list<int>
     */
    public static function rgbBytes(): array
    {
        $rng = new Randomizer(new Mt19937(1313));

        // Soft blobs: [x, y, r, g, b]. Muted, photo-like tones with no
        // well-separated clusters, so clustering is genuinely ambiguous.
        $blobs = [];
        for ($i = 0; $i < 9; $i++) {
            $blobs[] = [
                $rng->getInt(0, self::WIDTH - 1),
                $rng->getInt(0, self::HEIGHT - 1),
                $rng->getInt(20, 235),
                $rng->getInt(20, 235),
                $rng->getInt(20, 235),
            ];
        }

        $bytes = [];
        for ($y = 0; $y < self::HEIGHT; $y++) {
            for ($x = 0; $x < self::WIDTH; $x++) {
                $wSum = $r = $g = $b = 0;
                foreach ($blobs as [$bx, $by, $br, $bg, $bb]) {
                    $d2 = ($x - $bx) ** 2 + ($y - $by) ** 2;
                    $w = intdiv(1_000_000, 40 + $d2);
                    $wSum += $w;
                    $r += $w * $br;
                    $g += $w * $bg;
                    $b += $w * $bb;
                }
                $noise = $rng->getInt(-10, 10);
                foreach ([$r, $g, $b] as $c) {
                    // Quantise to multiples of 4 to keep the distinct-colour
                    // count (and therefore test runtime) modest.
                    $v = max(1, min(254, intdiv($c, $wSum) + $noise));
                    $bytes[] = $v & ~3 | 1;
                }
            }
        }

        return $bytes;
    }

    /**
     * The same pixels mirrored left to right: an identical colour histogram,
     * met in a different scan order.
     *
     * @return list<int>
     */
    public static function mirroredBytes(): array
    {
        $rows = array_chunk(self::rgbBytes(), self::WIDTH * 3);

        return array_merge(...array_map(
            fn (array $row) => array_merge(...array_reverse(array_chunk($row, 3))),
            $rows
        ));
    }

    /**
     * The same pixels with every channel nudged by -1, 0 or +1 in a fixed
     * pattern: an invisible change of the kind re-encoding or resampling makes.
     *
     * @return list<int>
     */
    public static function nudgedBytes(): array
    {
        $bytes = self::rgbBytes();
        foreach ($bytes as $i => $value) {
            $pixel = intdiv($i, 3);
            $delta = ((($pixel % self::WIDTH) * 7 + intdiv($pixel, self::WIDTH) * 13) % 3) - 1;
            $bytes[$i] = max(0, min(255, $value + $delta));
        }

        return $bytes;
    }

    /**
     * @param  list<int>|null  $bytes  Row-major RGB triplets; defaults to rgbBytes()
     */
    public static function gd(?array $bytes = null): \GdImage
    {
        $bytes ??= self::rgbBytes();
        $img = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
        $i = 0;
        for ($y = 0; $y < self::HEIGHT; $y++) {
            for ($x = 0; $x < self::WIDTH; $x++) {
                imagesetpixel($img, $x, $y, ($bytes[$i] << 16) | ($bytes[$i + 1] << 8) | $bytes[$i + 2]);
                $i += 3;
            }
        }

        return $img;
    }

    /**
     * @param  list<int>|null  $bytes  Row-major RGB triplets; defaults to rgbBytes()
     */
    public static function imagick(?array $bytes = null): \Imagick
    {
        $im = new \Imagick;
        $im->newImage(self::WIDTH, self::HEIGHT, 'black', 'png');
        $im->importImagePixels(0, 0, self::WIDTH, self::HEIGHT, 'RGB', \Imagick::PIXEL_CHAR, $bytes ?? self::rgbBytes());

        return $im;
    }

    /**
     * Extract a palette from this fixture with a FRESH extractor and image,
     * the way a caller handling separate requests would.
     *
     * @param  'gd'|'imagick'  $driver
     * @param  list<int>|null  $bytes  Row-major RGB triplets; defaults to rgbBytes()
     * @return list<string> hex colours in palette order
     */
    public static function paletteHex(string $driver, int $count = 5, ?array $bytes = null): array
    {
        if ($driver === 'gd') {
            $image = new GdImage(self::gd($bytes));
            $extractor = new GdColorExtractor;
        } else {
            $image = new ImagickImage(self::imagick($bytes));
            $extractor = new ImagickColorExtractor;
        }

        return array_map(
            fn ($color) => $color->toHex(),
            $extractor->extract($image, $count)->getColors()
        );
    }

    /**
     * Largest RGB distance between paired colours of two equal-size palettes,
     * pairing them so that this largest distance is as small as possible.
     *
     * @param  list<string>  $a  hex colours
     * @param  list<string>  $b  hex colours
     */
    public static function paletteDistance(array $a, array $b): float
    {
        $rgb = fn (string $hex) => sscanf($hex, '#%02x%02x%02x');
        $a = array_map($rgb, $a);
        $b = array_map($rgb, $b);

        $best = INF;
        foreach (self::permutations(array_keys($b)) as $order) {
            $worst = 0.0;
            foreach ($a as $i => [$r, $g, $bl]) {
                [$r2, $g2, $b2] = $b[$order[$i]];
                $worst = max($worst, sqrt(($r - $r2) ** 2 + ($g - $g2) ** 2 + ($bl - $b2) ** 2));
            }
            $best = min($best, $worst);
        }

        return $best;
    }

    /**
     * @param  list<int>  $items
     * @return list<list<int>>
     */
    private static function permutations(array $items): array
    {
        if (count($items) <= 1) {
            return [$items];
        }

        $result = [];
        foreach ($items as $i => $item) {
            $rest = $items;
            unset($rest[$i]);
            foreach (self::permutations(array_values($rest)) as $tail) {
                $result[] = [$item, ...$tail];
            }
        }

        return $result;
    }
}
