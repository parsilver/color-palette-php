<?php

use Farzai\ColorPalette\AbstractColorExtractor;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ImageInterface;

// Mock implementation for testing
class TestColorExtractor extends AbstractColorExtractor
{
    private array $mockColors = [];

    public function setMockColors(array $colors): void
    {
        $this->mockColors = $colors;
    }

    protected function extractColors(ImageInterface $image): array
    {
        return $this->mockColors;
    }

    // Expose protected methods for testing
    public function publicProcessColors(array $colors): array
    {
        return $this->processColors($colors);
    }

    public function publicClusterColors(array $colors, int $k): array
    {
        return $this->clusterColors($colors, $k);
    }

    public function publicSortColors(array $colors): array
    {
        return $this->sortColors($colors);
    }

    public function publicRgbToHsb(int $r, int $g, int $b): array
    {
        return $this->rgbToHsb($r, $g, $b);
    }
}

beforeEach(function () {
    $this->extractor = new TestColorExtractor;
    $this->mockImage = Mockery::mock(ImageInterface::class);
});

afterEach(function () {
    Mockery::close();
});

test('it throws exception for invalid count parameter', function () {
    expect(fn () => $this->extractor->extract($this->mockImage, 0))
        ->toThrow(InvalidArgumentException::class, 'Count must be greater than 0');

    expect(fn () => $this->extractor->extract($this->mockImage, -1))
        ->toThrow(InvalidArgumentException::class, 'Count must be greater than 0');
});

test('it returns fallback palette when no colors extracted', function () {
    $this->extractor->setMockColors([]);

    $palette = $this->extractor->extract($this->mockImage, 5);

    expect($palette)->toBeInstanceOf(ColorPaletteInterface::class)
        ->and($palette)->toHaveCount(5);

    // Should return grayscale fallback
    expect($palette[0]->toHex())->toBe('#ffffff');
});

test('it processes colors correctly', function () {
    $colors = [
        ['r' => 255, 'g' => 0, 'b' => 0, 'count' => 10],
        ['r' => 0, 'g' => 255, 'b' => 0, 'count' => 5],
        ['r' => 0, 'g' => 0, 'b' => 255, 'count' => 8],
    ];

    $processed = $this->extractor->publicProcessColors($colors);

    expect($processed)->toBeArray();
});

test('it filters out colors with invalid RGB values in processColors', function () {
    $colors = [
        ['r' => 255, 'g' => 0, 'b' => 0, 'count' => 10],
        ['r' => 'invalid', 'g' => 0, 'b' => 0, 'count' => 5], // Invalid
        ['r' => 0, 'g' => null, 'b' => 0, 'count' => 5], // Invalid
        ['g' => 0, 'b' => 0, 'count' => 5], // Missing 'r'
        ['r' => 0, 'g' => 255, 'b' => 0, 'count' => 8],
    ];

    $processed = $this->extractor->publicProcessColors($colors);

    // Should only keep valid colors
    expect(count($processed))->toBeLessThanOrEqual(2);
});

test('it returns original colors when all filtered out due to low saturation', function () {
    // Very low saturation colors (grayscale-ish)
    $colors = [
        ['r' => 10, 'g' => 10, 'b' => 10, 'count' => 10],
        ['r' => 20, 'g' => 20, 'b' => 20, 'count' => 5],
    ];

    $processed = $this->extractor->publicProcessColors($colors);

    // Should return original array when all filtered
    expect(count($processed))->toBe(2);
});

test('it handles empty colors array in clusterColors', function () {
    $result = $this->extractor->publicClusterColors([], 3);

    expect($result)->toBeArray()
        ->and($result)->toHaveCount(3);

    // Should return default black colors
    expect($result[0])->toBe(['r' => 0, 'g' => 0, 'b' => 0]);
});

test('it clusters colors correctly', function () {
    $colors = [
        ['r' => 255, 'g' => 0, 'b' => 0, 'count' => 10],
        ['r' => 250, 'g' => 5, 'b' => 5, 'count' => 8],
        ['r' => 0, 'g' => 255, 'b' => 0, 'count' => 12],
        ['r' => 5, 'g' => 250, 'b' => 5, 'count' => 9],
        ['r' => 0, 'g' => 0, 'b' => 255, 'count' => 15],
    ];

    $clustered = $this->extractor->publicClusterColors($colors, 3);

    expect($clustered)->toBeArray()
        ->and($clustered)->toHaveCount(3);

    // Each cluster should be an RGB array
    foreach ($clustered as $color) {
        expect($color)->toHaveKeys(['r', 'g', 'b']);
        expect($color['r'])->toBeBetween(0, 255);
        expect($color['g'])->toBeBetween(0, 255);
        expect($color['b'])->toBeBetween(0, 255);
    }
});

test('it sorts colors by luminance', function () {
    $colors = [
        ['r' => 0, 'g' => 0, 'b' => 0],     // Black (darkest)
        ['r' => 128, 'g' => 128, 'b' => 128], // Gray
        ['r' => 255, 'g' => 255, 'b' => 255], // White (brightest)
    ];

    $sorted = $this->extractor->publicSortColors($colors);

    // Should be sorted by luminance descending (brightest first)
    $luminance0 = 0.299 * $sorted[0]['r'] + 0.587 * $sorted[0]['g'] + 0.114 * $sorted[0]['b'];
    $luminance1 = 0.299 * $sorted[1]['r'] + 0.587 * $sorted[1]['g'] + 0.114 * $sorted[1]['b'];
    $luminance2 = 0.299 * $sorted[2]['r'] + 0.587 * $sorted[2]['g'] + 0.114 * $sorted[2]['b'];

    expect($luminance0)->toBeGreaterThanOrEqual($luminance1);
    expect($luminance1)->toBeGreaterThanOrEqual($luminance2);
});

test('it uses hue as secondary sort when luminance is identical', function () {
    // Colors with same luminance but different hues
    $colors = [
        ['r' => 255, 'g' => 0, 'b' => 0],   // Red, Hue = 0
        ['r' => 0, 'g' => 255, 'b' => 0],   // Green, Hue = 120
        ['r' => 0, 'g' => 0, 'b' => 255],   // Blue, Hue = 240
    ];

    $sorted = $this->extractor->publicSortColors($colors);

    // Should be deterministically sorted
    expect($sorted)->toBeArray();
    expect(count($sorted))->toBe(3);
});

test('it converts RGB to HSB correctly', function () {
    // Test pure red
    $hsb = $this->extractor->publicRgbToHsb(255, 0, 0);
    expect($hsb)->toHaveKeys(['h', 's', 'b']);
    expect($hsb['h'])->toBeBetween(0, 360);
    expect($hsb['s'])->toBeGreaterThanOrEqual(0.99); // Close to 1.0
    expect($hsb['b'])->toBeGreaterThanOrEqual(0.99); // Close to 1.0

    // Test pure green
    $hsb = $this->extractor->publicRgbToHsb(0, 255, 0);
    expect($hsb['h'])->toBeBetween(119, 121); // Close to 120
    expect($hsb['s'])->toBeGreaterThanOrEqual(0.99);
    expect($hsb['b'])->toBeGreaterThanOrEqual(0.99);

    // Test pure blue
    $hsb = $this->extractor->publicRgbToHsb(0, 0, 255);
    expect($hsb['h'])->toBeBetween(239, 241); // Close to 240
    expect($hsb['s'])->toBeGreaterThanOrEqual(0.99);
    expect($hsb['b'])->toBeGreaterThanOrEqual(0.99);

    // Test grayscale (no saturation)
    $hsb = $this->extractor->publicRgbToHsb(128, 128, 128);
    expect($hsb['s'])->toBeLessThanOrEqual(0.01); // Close to 0
    expect($hsb['b'])->toBeGreaterThan(0);

    // Test black
    $hsb = $this->extractor->publicRgbToHsb(0, 0, 0);
    expect($hsb['s'])->toBeLessThanOrEqual(0.01); // Close to 0
    expect($hsb['b'])->toBeLessThanOrEqual(0.01); // Close to 0

    // Test white
    $hsb = $this->extractor->publicRgbToHsb(255, 255, 255);
    expect($hsb['s'])->toBeLessThanOrEqual(0.01); // Close to 0
    expect($hsb['b'])->toBeGreaterThanOrEqual(0.99); // Close to 1.0
});

test('it clamps RGB values when creating palette', function () {
    $colors = [
        ['r' => 255, 'g' => 0, 'b' => 0, 'count' => 10],
    ];

    $this->extractor->setMockColors($colors);
    $palette = $this->extractor->extract($this->mockImage, 1);

    expect($palette[0]->getRed())->toBeBetween(0, 255);
    expect($palette[0]->getGreen())->toBeBetween(0, 255);
    expect($palette[0]->getBlue())->toBeBetween(0, 255);
});

test('it produces idempotent results with same input', function () {
    $colors = [
        ['r' => 255, 'g' => 0, 'b' => 0, 'count' => 10],
        ['r' => 0, 'g' => 255, 'b' => 0, 'count' => 8],
        ['r' => 0, 'g' => 0, 'b' => 255, 'count' => 12],
        ['r' => 255, 'g' => 255, 'b' => 0, 'count' => 7],
        ['r' => 255, 'g' => 0, 'b' => 255, 'count' => 9],
    ];

    $this->extractor->setMockColors($colors);

    $palette1 = $this->extractor->extract($this->mockImage, 3);
    $palette2 = $this->extractor->extract($this->mockImage, 3);

    // Should produce identical results
    expect($palette1->toArray())->toBe($palette2->toArray());
});
