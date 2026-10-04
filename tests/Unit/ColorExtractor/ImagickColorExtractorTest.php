<?php

use Farzai\ColorPalette\ImageLoaderFactory;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\ImagickColorExtractor;

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

describe('ImagickColorExtractor - Transparency Handling', function () {
    test('it ignores the colour under transparent pixels', function (string $format, int $backgroundAlpha) {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        // Five opaque stripes on a transparent background that hides a
        // saturated green. Stripes and background meet on even pixels, so the
        // 2:1 resize to the 50x50 sample never blends one into another. An
        // 8-bit alpha of 1 is still fully transparent on GD's 0..127 scale,
        // which stores it as 127 - (1 >> 1) = 127.
        $stripes = [[220, 40, 40], [40, 80, 200], [230, 200, 40], [150, 60, 170], [240, 140, 60]];
        $pixels = [];
        $stripesOnly = [];
        for ($y = 0; $y < 100; $y++) {
            for ($x = 0; $x < 100; $x++) {
                $inside = $x >= 20 && $x < 80 && $y >= 20 && $y < 80;
                array_push($pixels, ...($inside ? [...$stripes[intdiv($x - 20, 12)], 255] : [0, 200, 0, $backgroundAlpha]));
                array_push($stripesOnly, ...$stripes[intdiv($x, 20)]);
            }
        }

        $canvas = new Imagick;
        $canvas->newImage(100, 100, 'black', $format);
        $canvas->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $canvas->importImagePixels(0, 0, 100, 100, 'RGBA', Imagick::PIXEL_CHAR, $pixels);
        $decoded = new Imagick;
        $decoded->readImageBlob($canvas->getImageBlob());

        $reference = new Imagick;
        $reference->newImage(100, 100, 'black', 'png');
        $reference->importImagePixels(0, 0, 100, 100, 'RGB', Imagick::PIXEL_CHAR, $stripesOnly);

        $extractor = new ImagickColorExtractor;

        expect($extractor->extract(new ImagickImage($decoded), 5)->toArray())
            ->toBe($extractor->extract(new ImagickImage($reference), 5)->toArray());
    })->with([
        'PNG, fully transparent' => ['png', 0],
        'PNG, alpha 1 of 255' => ['png', 1],
        'GIF, transparent index' => ['gif', 0],
    ]);

    test('it falls back to the grayscale palette when every pixel is fully transparent', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        $imagick = new Imagick;
        $imagick->newImage(50, 50, 'black', 'png');
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $imagick->importImagePixels(0, 0, 50, 50, 'RGBA', Imagick::PIXEL_CHAR, array_merge(...array_fill(0, 2500, [0, 200, 0, 0])));

        expect((new ImagickColorExtractor)->extract(new ImagickImage($imagick), 5)->toArray())
            ->toBe(['#ffffff', '#c7c7c7', '#8f8f8f', '#565656', '#1e1e1e']);
    });

    test('it weights partially transparent pixels by their opacity', function () {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('Imagick extension is not available.');
        }

        // Left half opaque red, right half blue at an 8-bit alpha of 84, which
        // is how a PNG stores GD's alpha 85: each blue pixel is 42/127 opaque on
        // both drivers. A single swatch is then the opacity-weighted mean the
        // GD driver returns, #af2855, not the plain mean #822882.
        $pixels = [];
        for ($y = 0; $y < 100; $y++) {
            for ($x = 0; $x < 100; $x++) {
                array_push($pixels, ...($x < 50 ? [220, 40, 40, 255] : [40, 40, 220, 84]));
            }
        }

        $imagick = new Imagick;
        $imagick->newImage(100, 100, 'black', 'png');
        $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_SET);
        $imagick->importImagePixels(0, 0, 100, 100, 'RGBA', Imagick::PIXEL_CHAR, $pixels);

        expect((new ImagickColorExtractor)->extract(new ImagickImage($imagick), 1)->toArray())->toBe(['#af2855']);
    });
});
