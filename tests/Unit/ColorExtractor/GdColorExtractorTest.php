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

test('it produces idempotent results (same image returns same colors in same order)', function () {
    if (! extension_loaded('gd')) {
        $this->markTestSkipped('GD extension is not available.');
    }

    $loader = (new ImageLoaderFactory)->create();
    $image = $loader->load(__DIR__.'/../../../example/assets/sample.jpg');

    $extractor = new GdColorExtractor;

    // Extract colors multiple times from the same image
    $firstRun = $extractor->extract($image, 5);
    $secondRun = $extractor->extract($image, 5);
    $thirdRun = $extractor->extract($image, 5);

    // Convert to arrays for easier comparison
    $firstColors = $firstRun->toArray();
    $secondColors = $secondRun->toArray();
    $thirdColors = $thirdRun->toArray();

    // All runs should produce identical results
    expect($firstColors)->toBe($secondColors)
        ->and($firstColors)->toBe($thirdColors)
        ->and($secondColors)->toBe($thirdColors);

    // Verify each color in the palette matches across runs
    foreach (range(0, 4) as $index) {
        expect($firstRun[$index]->toHex())
            ->toBe($secondRun[$index]->toHex())
            ->toBe($thirdRun[$index]->toHex());
    }
});
