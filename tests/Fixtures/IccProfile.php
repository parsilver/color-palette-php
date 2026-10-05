<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Tests\Fixtures;

/**
 * Builds the ICC v2 profiles the colour-managed test images are tagged with,
 * and the sRGB colour maths they are separated with.
 */
final class IccProfile
{
    /** The D50 illuminant of the ICC profile connection space. */
    public const D50 = [0.9642, 1.0, 0.8249];

    /** sRGB primaries adapted to the D50 profile connection space (Bradford). */
    private const SRGB_TO_XYZ_D50 = [
        [0.4360747, 0.3850649, 0.1430804],
        [0.2225045, 0.7168786, 0.0606169],
        [0.0139322, 0.0971045, 0.7141733],
    ];

    /**
     * An ICC v2.1 profile with a D50 white point and the given tags, after
     * the description, copyright and white point tags every profile carries.
     *
     * @param  string  $class  Profile class signature, such as 'scnr' or 'mntr'
     * @param  string  $colorSpace  Data colour space signature, such as 'CMYK' or 'RGB '
     * @param  string  $pcs  Profile connection space signature, 'Lab ' or 'XYZ '
     * @param  array<string, string>  $tags  Tag data keyed by tag signature
     */
    public static function build(string $class, string $colorSpace, string $pcs, string $description, array $tags): string
    {
        $tags = [
            'desc' => 'desc'.pack('N2', 0, strlen($description) + 1).$description."\0"
                .pack('N2', 0, 0).pack('nC', 0, 0).str_repeat("\0", 67),
            'cprt' => 'text'.pack('N', 0)."No copyright, use freely\0",
            'wtpt' => self::xyz(self::D50),
            ...$tags,
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
            .$class.$colorSpace.$pcs
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
     * An XYZType tag holding one XYZ value.
     *
     * @param  array{float, float, float}  $xyz
     */
    public static function xyz(array $xyz): string
    {
        return 'XYZ '.pack('N', 0).self::xyzNumber($xyz);
    }

    /**
     * CIE XYZ (D50) of an sRGB colour whose channels are given in 0..1.
     *
     * @param  array{float, float, float}  $rgb
     * @return array{float, float, float}
     */
    public static function srgbToXyz(array $rgb): array
    {
        $linear = array_map(
            fn (float $v) => $v <= 0.04045 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4,
            $rgb
        );

        return array_map(
            fn (array $row) => $row[0] * $linear[0] + $row[1] * $linear[1] + $row[2] * $linear[2],
            self::SRGB_TO_XYZ_D50
        );
    }

    /**
     * Whether ImageMagick reports being built without lcms, which it needs to
     * apply ICC profiles. Without it the extractor reads the pixels as they
     * are, or converts CMYK by formula.
     */
    public static function lacksLcms(): bool
    {
        $delegates = \Imagick::getConfigureOptions('DELEGATES')['DELEGATES'] ?? null;

        return $delegates !== null && ! in_array('lcms', explode(' ', $delegates), true);
    }

    /**
     * @param  array{float, float, float}  $xyz
     */
    private static function xyzNumber(array $xyz): string
    {
        return pack('N3', ...array_map(fn (float $v) => (int) round($v * 0x10000), $xyz));
    }
}
