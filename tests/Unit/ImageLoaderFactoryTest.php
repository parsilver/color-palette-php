<?php

use Farzai\ColorPalette\ImageLoader;
use Farzai\ColorPalette\ImageLoaderFactory;

test('it can create image loader instance', function () {
    $factory = new ImageLoaderFactory;
    $loader = $factory->create();

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it creates new instance each time (no singleton)', function () {
    $factory = new ImageLoaderFactory;
    $loader1 = $factory->create();
    $loader2 = $factory->create();

    expect($loader1)->not->toBe($loader2);
});

test('it accepts custom http client via constructor', function () {
    $mockClient = Mockery::mock(\Psr\Http\Client\ClientInterface::class);
    $factory = new ImageLoaderFactory($mockClient);
    $loader = $factory->create();

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});
