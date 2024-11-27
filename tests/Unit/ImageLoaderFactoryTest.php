<?php

use Farzai\ColorPalette\ImageLoader;
use Farzai\ColorPalette\ImageLoaderFactory;

test('it can create image loader instance', function () {
    $factory = new ImageLoaderFactory;
    $loader = $factory->create();

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it returns singleton instance', function () {
    $factory = new ImageLoaderFactory;
    $loader1 = $factory->create();
    $loader2 = $factory->create();

    expect($loader1)->toBe($loader2);
});
