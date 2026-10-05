<?php

use Farzai\ColorPalette\ImageLoaderFactory;
use Farzai\ColorPalette\Images\ImagickImage;
use Farzai\ColorPalette\ImagickColorExtractor;
use Farzai\ColorPalette\Tests\Fixtures\SyntheticPhoto;

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

test('it extracts the same palette each time one Imagick is wrapped again', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $imagick = SyntheticPhoto::imagick();
    $extractor = new ImagickColorExtractor;

    // Each wrapper is a temporary, destroyed as soon as extract() returns.
    $first = SyntheticPhoto::hexes($extractor->extract(new ImagickImage($imagick), 5));
    $second = SyntheticPhoto::hexes($extractor->extract(new ImagickImage($imagick), 5));

    // A wrapper that cleared the caller's Imagick on destruct left the second
    // call reading an empty object, which extract() turns into this fallback.
    expect($first)->not->toBe(['#ffffff', '#c7c7c7', '#8f8f8f', '#565656', '#1e1e1e'])
        ->and($second)->toBe($first)
        ->and($imagick->getNumberImages())->toBe(1)
        ->and($imagick->getImageWidth())->toBe(SyntheticPhoto::WIDTH);
});
