<?php

use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImageLoaderFactory;

test('it can extract colors from image', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is not available.');
    }

    $loader = (new ImageLoaderFactory)->create();
    $image = $loader->load(__DIR__.'/../../../example/assets/sample.jpg');

    $extractor = new GdColorExtractor;
    $colors = $extractor->extract($image, 5);

    expect($colors)->toHaveCount(5);
    expect($colors[0])->toBeObject();
    expect($colors[0]->getRed())->toBeBetween(0, 255);
    expect($colors[0]->getGreen())->toBeBetween(0, 255);
    expect($colors[0]->getBlue())->toBeBetween(0, 255);
});
