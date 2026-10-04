<?php

use Farzai\ColorPalette\ImageLoaderFactory;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\ImagickColorExtractor;
use Farzai\ColorPalette\Tests\Fixtures\DotGainCmyk;
use Farzai\ColorPalette\Tests\Fixtures\IccProfile;
use Farzai\ColorPalette\Tests\Fixtures\SyntheticPhoto;
use Farzai\ColorPalette\Tests\Fixtures\WideGamutRgb;

test('it can extract colors from image', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $loader = (new ImageLoaderFactory(preferredDriver: 'imagick'))->create();
    $image = $loader->load(__DIR__.'/../../../example/assets/sample.jpg');

    $extractor = new ImagickColorExtractor;
    $colors = $extractor->extract($image, 5);

    expect($colors)->toHaveCount(5);
    expect($colors[0])->toBeObject();
    expect($colors[0]->getRed())->toBeBetween(0, 255);
    expect($colors[0]->getGreen())->toBeBetween(0, 255);
    expect($colors[0]->getBlue())->toBeBetween(0, 255);
    // sample.jpg is solid red; the grayscale fallback would mean extraction failed.
    expect($colors[0]->getRed())->toBeGreaterThan(200)
        ->and($colors[0]->getGreen())->toBeLessThan(50);
});

test('it converts CMYK images to sRGB before reading colours', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $stripes = [[220, 40, 40], [40, 160, 60], [40, 80, 200], [230, 200, 40], [150, 60, 170]];
    $pixels = [];
    for ($y = 0; $y < 60; $y++) {
        for ($x = 0; $x < 60; $x++) {
            array_push($pixels, ...$stripes[intdiv($x, 12)]);
        }
    }

    $srgb = new Imagick;
    $srgb->newImage(60, 60, 'black', 'png');
    $srgb->importImagePixels(0, 0, 60, 60, 'RGB', Imagick::PIXEL_CHAR, $pixels);

    $cmyk = clone $srgb;
    $cmyk->transformImageColorspace(Imagick::COLORSPACE_CMYK);

    $extractor = new ImagickColorExtractor;
    $expected = $extractor->extract(new ImagickImage($srgb), 5);
    $actual = $extractor->extract(new ImagickImage($cmyk), 5);

    // The colourspace round trip may move a channel by a rounding step.
    foreach ($expected->getColors() as $i => $color) {
        expect(abs($actual[$i]->getRed() - $color->getRed()))->toBeLessThanOrEqual(2)
            ->and(abs($actual[$i]->getGreen() - $color->getGreen()))->toBeLessThanOrEqual(2)
            ->and(abs($actual[$i]->getBlue() - $color->getBlue()))->toBeLessThanOrEqual(2);
    }
});

test('it converts images through their embedded ICC profile', function (string $fixture, int $scale) {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }
    if (IccProfile::lacksLcms()) {
        $this->markTestSkipped('ImageMagick was built without lcms, so it cannot apply ICC profiles.');
    }

    // At 3x the image is larger than the size the extractor converts at, so
    // it is shrunk first and the profile has to survive that shrink.
    $bytes = SyntheticPhoto::enlargedBytes($scale);
    $width = SyntheticPhoto::WIDTH * $scale;
    $height = SyntheticPhoto::HEIGHT * $scale;
    $extractor = new ImagickColorExtractor;
    $palette = fn (Imagick $image) => SyntheticPhoto::hexes($extractor->extract(new ImagickImage($image), 5));

    $original = SyntheticPhoto::paletteHex('imagick');
    $tagged = $palette($fixture::imagick($bytes, $width, $height));
    $untagged = $palette($fixture::imagick($bytes, $width, $height, tagged: false));

    expect(SyntheticPhoto::paletteDistance($original, $tagged))->toBeLessThanOrEqual(4.0);
    // The same values read without the profile come back far from the
    // original, so it is the profile that brings them back.
    expect(SyntheticPhoto::paletteDistance($original, $untagged))->toBeGreaterThan(30.0);
})->with([
    'CMYK' => DotGainCmyk::class,
    'wide-gamut RGB' => WideGamutRgb::class,
])->with(['at its own size' => 1, 'larger than the conversion size' => 3]);

test('it converts images through their embedded ICC profile before averaging their pixels', function (string $fixture) {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }
    if (IccProfile::lacksLcms()) {
        $this->markTestSkipped('ImageMagick was built without lcms, so it cannot apply ICC profiles.');
    }

    // A one-pixel black and white checkerboard, which the 50x50 sample turns
    // into flat grey. Averaged in sRGB, as the same image in sRGB would be,
    // that grey is #808080. Averaged before conversion it is not: half-strength
    // black ink on the CMYK press prints as #404040, and half-way values in
    // the RGB profile's 1.8 gamma come out as #929292.
    $bytes = [];
    for ($y = 0; $y < 100; $y++) {
        for ($x = 0; $x < 100; $x++) {
            array_push($bytes, ...array_fill(0, 3, ($x + $y) % 2 * 255));
        }
    }

    $grey = (new ImagickColorExtractor)->extract(new ImagickImage($fixture::imagick($bytes, 100, 100)), 1)[0];

    expect(abs($grey->getRed() - 128))->toBeLessThanOrEqual(2)
        ->and(abs($grey->getGreen() - 128))->toBeLessThanOrEqual(2)
        ->and(abs($grey->getBlue() - 128))->toBeLessThanOrEqual(2);
})->with([
    'CMYK' => DotGainCmyk::class,
    'wide-gamut RGB' => WideGamutRgb::class,
]);

test('it falls back to the CMYK formula when the embedded profile is unusable', function (Closure $profile) {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $bytes = SyntheticPhoto::rgbBytes();
    $tagged = DotGainCmyk::imagick($bytes, SyntheticPhoto::WIDTH, SyntheticPhoto::HEIGHT, tagged: false);
    $tagged->setImageProfile('icc', $profile());
    $untagged = DotGainCmyk::imagick($bytes, SyntheticPhoto::WIDTH, SyntheticPhoto::HEIGHT, tagged: false);

    $extractor = new ImagickColorExtractor;

    // The colours of an image without a profile, not the grayscale fallback
    // extract() returns when conversion throws.
    expect(SyntheticPhoto::hexes($extractor->extract(new ImagickImage($tagged), 5)))
        ->toBe(SyntheticPhoto::hexes($extractor->extract(new ImagickImage($untagged), 5)));
})->with([
    'truncated' => fn () => substr(DotGainCmyk::profile(), 0, 300),
    'not a profile' => fn () => str_repeat('x', 500),
    // ImageMagick applies an RGB profile to CMYK pixels without complaint and
    // returns nonsense. The rendering intent is changed so it does not match
    // the sRGB profile the extractor converts to, which ImageMagick would skip.
    'an RGB profile' => fn () => substr_replace(
        file_get_contents(dirname(__DIR__, 3).'/resources/icc/sRGB2014.icc'), pack('N', 1), 64, 4
    ),
]);

test('it reads RGB images as sRGB when the embedded profile is unusable', function (Closure $profile) {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $tagged = SyntheticPhoto::imagick();
    $tagged->setImageProfile('icc', $profile());

    // The colours of an image without a profile, not the grayscale fallback
    // extract() returns when conversion throws.
    expect(SyntheticPhoto::hexes((new ImagickColorExtractor)->extract(new ImagickImage($tagged), 5)))
        ->toBe(SyntheticPhoto::paletteHex('imagick'));
})->with([
    'truncated' => fn () => substr(WideGamutRgb::profile(), 0, 300),
    'not a profile' => fn () => str_repeat('x', 500),
    // ImageMagick applies a CMYK profile to RGB pixels without complaint and
    // returns nonsense.
    'a CMYK profile' => fn () => DotGainCmyk::profile(),
]);

test('it reads RGB images tagged with the sRGB profile as they are', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    // Larger than the size the extractor converts at, so a needless
    // conversion would also take a different path to the 50x50 sample.
    $bytes = SyntheticPhoto::enlargedBytes(3);
    $width = SyntheticPhoto::WIDTH * 3;
    $height = SyntheticPhoto::HEIGHT * 3;
    $tagged = SyntheticPhoto::imagick($bytes, $width, $height);
    $tagged->setImageProfile('icc', file_get_contents(dirname(__DIR__, 3).'/resources/icc/sRGB2014.icc'));
    $untagged = SyntheticPhoto::imagick($bytes, $width, $height);

    $extractor = new ImagickColorExtractor;

    expect(SyntheticPhoto::hexes($extractor->extract(new ImagickImage($tagged), 5)))
        ->toBe(SyntheticPhoto::hexes($extractor->extract(new ImagickImage($untagged), 5)));
});
