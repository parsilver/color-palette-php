<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;

class GdColorExtractor extends AbstractColorExtractor
{
    protected function extractColors(ImageInterface $image): array
    {
        // ImageInterface guarantees getResource(); a GdImage yields the \GdImage
        // handle the GD functions below operate on. A mismatched image type (e.g.
        // an ImagickImage) surfaces as a TypeError, which extract() turns into the
        // grayscale fallback.
        $gdImage = $image->getResource();
        $width = imagesx($gdImage);
        $height = imagesy($gdImage);
        $colorCounts = [];

        // Palette-based images (GIF, PNG-8, 8-bit grayscale PNG) return a palette
        // index from imagecolorat(), not a packed RGB value. Resolve indexes
        // through the palette, read once; a malformed file can reference an
        // index the palette does not define, and those pixels are skipped.
        $palette = null;
        if (! imageistruecolor($gdImage)) {
            $palette = [];
            for ($index = 0, $total = imagecolorstotal($gdImage); $index < $total; $index++) {
                $entry = imagecolorsforindex($gdImage, $index);
                $palette[$index] = [$entry['red'], $entry['green'], $entry['blue']];
            }
        }

        // Sample more pixels for better color representation
        $sampleSize = max(1, (int) sqrt($width * $height / 10000)); // Increased sampling

        for ($x = 0; $x < $width; $x += $sampleSize) {
            for ($y = 0; $y < $height; $y += $sampleSize) {
                $pixel = imagecolorat($gdImage, $x, $y);
                if ($palette === null) {
                    $r = ($pixel >> 16) & 0xFF;
                    $g = ($pixel >> 8) & 0xFF;
                    $b = $pixel & 0xFF;
                } elseif (isset($palette[$pixel])) {
                    [$r, $g, $b] = $palette[$pixel];
                } else {
                    continue;
                }

                // Skip pure black and white
                if (($r === 0 && $g === 0 && $b === 0) || ($r === 255 && $g === 255 && $b === 255)) {
                    continue;
                }

                $key = sprintf('%d-%d-%d', $r, $g, $b);

                if (! isset($colorCounts[$key])) {
                    $colorCounts[$key] = [
                        'r' => $r,
                        'g' => $g,
                        'b' => $b,
                        'count' => 0,
                    ];
                }
                $colorCounts[$key]['count']++;
            }
        }

        // Sort by frequency
        uasort($colorCounts, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_values($colorCounts);
    }
}
