<?php

use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImagickColorExtractor;

test('it creates GD extractor when GD is available', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is not available.');
    }

    $factory = new ColorExtractorFactory;
    $extractor = $factory->make();

    expect($extractor)->toBeInstanceOf(GdColorExtractor::class);
});

test('it creates Imagick extractor when Imagick is available', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension is not available.');
    }

    $factory = new ColorExtractorFactory;
    $extractor = $factory->make('imagick');

    expect($extractor)->toBeInstanceOf(ImagickColorExtractor::class);
});

test('it throws exception for invalid driver', function () {
    $factory = new ColorExtractorFactory;

    expect(fn () => $factory->make('invalid'))
        ->toThrow(InvalidArgumentException::class);
});
