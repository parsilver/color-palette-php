<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Tests\Fixtures;

/**
 * A CMYK image tagged with an ICC profile that the plain CMYK formula gets
 * wrong, the way the press profile in a Photoshop CMYK export does.
 *
 * The profile describes a press whose ink dots spread: every colour comes out
 * as the plain formula's (1 - C)(1 - K) value squared, so a 50% tint prints as
 * dark as 75%. Pixels are separated for that press here, in PHP, so the only
 * way to read the original colours back is through the embedded profile.
 * ImageMagick's formula conversion, which ignores the profile, makes every
 * colour lighter: a channel of 64 comes back as 128.
 *
 * The profile is generated rather than committed because freely licensed
 * CMYK profiles are tens of kilobytes or more, and generating it keeps the
 * fixture independent of any one press standard.
 */
final class DotGainCmyk
{
    /** CLUT grid points per ink. */
    private const GRID = 11;

    /** sRGB primaries adapted to the D50 profile connection space (Bradford). */
    private const SRGB_TO_XYZ_D50 = [
        [0.4360747, 0.3850649, 0.1430804],
        [0.2225045, 0.7168786, 0.0606169],
        [0.0139322, 0.0971045, 0.7141733],
    ];

    private const D50 = [0.9642, 1.0, 0.8249];

    /**
     * Separate row-major RGB triplets for the press and return them as a CMYK
     * image, tagged with the press profile unless $tagged is false.
     *
     * @param  list<int>  $rgbBytes  Row-major RGB triplets, length width * height * 3
     */
    public static function imagick(array $rgbBytes, int $width, int $height, bool $tagged = true): \Imagick
    {
        $cmyk = [];
        foreach (array_chunk($rgbBytes, 3) as $rgb) {
            // Undo the dot gain, then separate with full grey replacement:
            // K carries the darkness common to all three channels.
            [$r, $g, $b] = array_map(fn (int $v) => sqrt($v / 255), $rgb);
            $k = 1 - max($r, $g, $b);
            $white = 1 - $k;
            foreach ([$r, $g, $b] as $channel) {
                $cmyk[] = $white > 0 ? (int) round(255 * ($white - $channel) / $white) : 0;
            }
            $cmyk[] = (int) round(255 * $k);
        }

        $image = new \Imagick;
        $image->newImage($width, $height, 'white', 'tiff');
        $image->setImageColorspace(\Imagick::COLORSPACE_CMYK);
        $image->importImagePixels(0, 0, $width, $height, 'CMYK', \Imagick::PIXEL_CHAR, $cmyk);
        if ($tagged) {
            $image->setImageProfile('icc', self::profile());
        }

        return $image;
    }

    /**
     * Whether ImageMagick reports being built without lcms, which it needs to
     * apply ICC profiles. Without it the extractor converts by formula.
     */
    public static function lacksLcms(): bool
    {
        $delegates = \Imagick::getConfigureOptions('DELEGATES')['DELEGATES'] ?? null;

        return $delegates !== null && ! in_array('lcms', explode(' ', $delegates), true);
    }

    /**
     * An ICC v2 input profile for the press: CMYK in, CIELAB out, through one
     * lut16 (A2B0) table.
     */
    public static function profile(): string
    {
        $clut = '';
        $steps = range(0, self::GRID - 1);
        foreach ($steps as $c) {
            foreach ($steps as $m) {
                foreach ($steps as $y) {
                    foreach ($steps as $k) {
                        $white = 1 - $k / (self::GRID - 1);
                        [$l, $a, $b] = self::lab([
                            ((1 - $c / (self::GRID - 1)) * $white) ** 2,
                            ((1 - $m / (self::GRID - 1)) * $white) ** 2,
                            ((1 - $y / (self::GRID - 1)) * $white) ** 2,
                        ]);
                        // ICC v2 16-bit Lab: L 0..100 -> 0..0xFF00, a/b + 128 in units of 1/256.
                        $clut .= pack('n3',
                            (int) round(max(0, min(100, $l)) * 0xFF00 / 100),
                            (int) round(max(0, min(0xFFFF, ($a + 128) * 256))),
                            (int) round(max(0, min(0xFFFF, ($b + 128) * 256))),
                        );
                    }
                }
            }
        }

        $identityMatrix = pack('N9', 0x10000, 0, 0, 0, 0x10000, 0, 0, 0, 0x10000);
        $a2b0 = 'mft2'.pack('N', 0).pack('C4', 4, 3, self::GRID, 0).$identityMatrix
            .pack('n2', 2, 2)
            .str_repeat(pack('n2', 0, 0xFFFF), 4)
            .$clut
            .str_repeat(pack('n2', 0, 0xFFFF), 3);

        $description = 'Dot gain CMYK test press';
        $tags = [
            'desc' => 'desc'.pack('N2', 0, strlen($description) + 1).$description."\0"
                .pack('N2', 0, 0).pack('nC', 0, 0).str_repeat("\0", 67),
            'cprt' => 'text'.pack('N', 0)."No copyright, use freely\0",
            'wtpt' => 'XYZ '.pack('N', 0).self::xyzNumber(self::D50),
            'A2B0' => $a2b0,
        ];

        $offset = 128 + 4 + 12 * count($tags);
        $table = pack('N', count($tags));
        $data = '';
        foreach ($tags as $signature => $tag) {
            $tag = str_pad($tag, (int) ceil(strlen($tag) / 4) * 4, "\0");
            $table .= $signature.pack('N2', $offset + strlen($data), strlen($tag));
            $data .= $tag;
        }

        $header = pack('N', 128 + strlen($table) + strlen($data))
            ."\0\0\0\0"            // preferred CMM
            .pack('N', 0x02100000) // version 2.1
            .'scnr'.'CMYK'.'Lab '
            .pack('n6', 2026, 1, 1, 0, 0, 0)
            .'acsp'
            .str_repeat("\0", 4)   // platform
            .pack('N', 0)          // flags
            .str_repeat("\0", 8)   // manufacturer, model
            .str_repeat("\0", 8)   // attributes
            .pack('N', 0)          // perceptual intent
            .self::xyzNumber(self::D50)
            .str_repeat("\0", 4)   // creator
            .str_repeat("\0", 44); // profile ID and reserved

        return $header.$table.$data;
    }

    /**
     * CIELAB (D50) of an sRGB colour whose channels are given in 0..1.
     *
     * @param  array{float, float, float}  $rgb
     * @return array{float, float, float}
     */
    private static function lab(array $rgb): array
    {
        $linear = array_map(
            fn (float $v) => $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4,
            $rgb
        );

        $f = [];
        foreach (self::SRGB_TO_XYZ_D50 as $i => $row) {
            $t = ($row[0] * $linear[0] + $row[1] * $linear[1] + $row[2] * $linear[2]) / self::D50[$i];
            $f[] = $t > 216 / 24389 ? $t ** (1 / 3) : (24389 / 27 * $t + 16) / 116;
        }

        return [116 * $f[1] - 16, 500 * ($f[0] - $f[1]), 200 * ($f[1] - $f[2])];
    }

    /**
     * @param  array{float, float, float}  $xyz
     */
    private static function xyzNumber(array $xyz): string
    {
        return pack('N3', ...array_map(fn (float $v) => (int) round($v * 0x10000), $xyz));
    }
}
