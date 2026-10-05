<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Tests\Fixtures;

/**
 * An RGB image tagged with a wide-gamut ICC profile, the way a ProPhoto RGB
 * export from a photo editor is. ImageMagick reports it as sRGB like any
 * other RGB image.
 *
 * The profile has the ProPhoto (ROMM RGB) primaries and a plain 1.8 gamma.
 * Pixels are encoded for it here, in PHP, so the only way to read the original
 * colours back is through the embedded profile. Read as sRGB, every colour
 * comes out duller and lighter: the synthetic photo's palette lands about 38
 * RGB units away.
 *
 * The profile is generated rather than committed for the same reason as
 * DotGainCmyk's: it keeps the fixture independent of any one vendor's profile.
 */
final class WideGamutRgb
{
    /**
     * ROMM RGB primaries in the D50 profile connection space: the columns are
     * the XYZ of full red, green and blue.
     */
    private const RGB_TO_XYZ_D50 = [
        [0.7976749, 0.1351917, 0.0313534],
        [0.2880402, 0.7118741, 0.0000857],
        [0.0, 0.0, 0.8252100],
    ];

    private const GAMMA = 1.8;

    /**
     * Encode row-major sRGB triplets for the profile and return them as an
     * RGB image, tagged with the profile unless $tagged is false.
     *
     * @param  list<int>  $rgbBytes  Row-major RGB triplets, length width * height * 3
     */
    public static function imagick(array $rgbBytes, int $width, int $height, bool $tagged = true): \Imagick
    {
        $xyzToRgb = self::xyzToRgb();
        $encoded = [];
        foreach (array_chunk($rgbBytes, 3) as $rgb) {
            $xyz = IccProfile::srgbToXyz(array_map(fn (int $v) => $v / 255, $rgb));
            foreach ($xyzToRgb as $row) {
                $linear = $row[0] * $xyz[0] + $row[1] * $xyz[1] + $row[2] * $xyz[2];
                // Every sRGB colour is inside this gamut, so clamping only
                // trims rounding error.
                $encoded[] = (int) round(255 * max(0, min(1, $linear)) ** (1 / self::GAMMA));
            }
        }

        $image = new \Imagick;
        $image->newImage($width, $height, 'black', 'png');
        $image->importImagePixels(0, 0, $width, $height, 'RGB', \Imagick::PIXEL_CHAR, $encoded);
        if ($tagged) {
            $image->setImageProfile('icc', self::profile());
        }

        return $image;
    }

    /**
     * An ICC v2 matrix/TRC display profile: RGB in, XYZ out, through the
     * primaries and one gamma curve shared by the three channels.
     */
    public static function profile(): string
    {
        $column = fn (int $channel) => array_column(self::RGB_TO_XYZ_D50, $channel);
        // curveType with one entry: a gamma, as u8Fixed8Number.
        $gamma = 'curv'.pack('N2', 0, 1).pack('n', (int) round(self::GAMMA * 256));

        return IccProfile::build('mntr', 'RGB ', 'XYZ ', 'Wide gamut RGB test display', [
            'rXYZ' => IccProfile::xyz($column(0)),
            'gXYZ' => IccProfile::xyz($column(1)),
            'bXYZ' => IccProfile::xyz($column(2)),
            'rTRC' => $gamma,
            'gTRC' => $gamma,
            'bTRC' => $gamma,
        ]);
    }

    /**
     * The inverse of RGB_TO_XYZ_D50. Its rows are the cross products of the
     * matrix's columns, divided by the determinant.
     *
     * @return list<array{float, float, float}>
     */
    private static function xyzToRgb(): array
    {
        [$r, $g, $b] = array_map(fn (int $channel) => array_column(self::RGB_TO_XYZ_D50, $channel), [0, 1, 2]);
        $cross = fn (array $u, array $v) => [
            $u[1] * $v[2] - $u[2] * $v[1],
            $u[2] * $v[0] - $u[0] * $v[2],
            $u[0] * $v[1] - $u[1] * $v[0],
        ];
        $rows = [$cross($g, $b), $cross($b, $r), $cross($r, $g)];
        $determinant = $r[0] * $rows[0][0] + $r[1] * $rows[0][1] + $r[2] * $rows[0][2];

        return array_map(fn (array $row) => array_map(fn (float $v) => $v / $determinant, $row), $rows);
    }
}
