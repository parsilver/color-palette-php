<?php

use Farzai\ColorPalette\ImageLoader;
use Farzai\ColorPalette\ImageLoaderFactory;
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Images\GdImage;

test('can create image loader factory', function () {
    $factory = new ImageLoaderFactory();
    expect($factory)->toBeInstanceOf(ImageLoaderFactory::class);
});

test('can create image loader', function () {
    $loader = ImageLoaderFactory::create();
    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('can load image from path', function () {
    // Create a temporary test image
    $tmpImage = tempnam(sys_get_temp_dir(), 'test_image') . '.png';
    $image = imagecreatetruecolor(100, 100);
    imagepng($image, $tmpImage);
    
    $loader = ImageLoaderFactory::create();
    
    $loadedImage = $loader->load($tmpImage);
    expect($loadedImage)->toBeInstanceOf(GdImage::class);
    
    // Cleanup
    unlink($tmpImage);
    imagedestroy($image);
});

test('throws exception for invalid image path', function () {
    $loader = ImageLoaderFactory::create();
    
    expect(fn() => $loader->load('non_existent_image.jpg'))
        ->toThrow(ImageLoadException::class);
});

test('can create image loader with custom driver', function () {
    $loader = ImageLoaderFactory::create('gd');
    expect($loader)->toBeInstanceOf(ImageLoader::class);
    
    if (extension_loaded('imagick')) {
        $loader = ImageLoaderFactory::create('imagick');
        expect($loader)->toBeInstanceOf(ImageLoader::class);
    }
}); 