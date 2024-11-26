<?php

use Farzai\ColorPalette\ImageLoader;
use Farzai\ColorPalette\ImageLoaderFactory;

test('it can create an image loader instance', function () {
    $factory = new ImageLoaderFactory;

    $loader = $factory->create();

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it can create an image loader with custom options', function () {
    $factory = new ImageLoaderFactory;

    $loader = $factory->create([
        'driver' => 'gd',
        'max_width' => 800,
        'max_height' => 600,
    ]);

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it throws exception for invalid driver', function () {
    $factory = new ImageLoaderFactory;

    expect(fn () => $factory->create([
        'driver' => 'invalid-driver',
    ]))->toThrow(InvalidArgumentException::class);
});

test('it can create loader with imagick driver if available', function () {
    if (! extension_loaded('imagick')) {
        $this->markTestSkipped('Imagick extension not available');
    }

    $factory = new ImageLoaderFactory;

    $loader = $factory->create([
        'driver' => 'imagick',
    ]);

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it falls back to GD if imagick is not available', function () {
    $factory = new ImageLoaderFactory;

    $loader = $factory->create([
        'driver' => extension_loaded('imagick') ? 'imagick' : 'gd',
    ]);

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});
