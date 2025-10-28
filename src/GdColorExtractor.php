<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;
use InvalidArgumentException;

class GdColorExtractor extends AbstractColorExtractor
{
    protected function extractColors(ImageInterface $image): array
    {
        /**
         * Note: This extractor is designed to work with GdImage instances.
         * The type check has been removed to reduce coupling, but proper
         * image/extractor pairing should be ensured by the factory.
         */
        if (! method_exists($image, 'getResource')) {
            throw new InvalidArgumentException(
                'Image must provide getResource() method. '.
                'GdColorExtractor works with GdImage instances.'
            );
        }

        $gdImage = $image->getResource();
        $width = imagesx($gdImage);
        $height = imagesy($gdImage);
        $colorCounts = [];

        // Sample more pixels for better color representation
        $sampleSize = max(1, (int) sqrt($width * $height / 10000)); // Increased sampling

        for ($x = 0; $x < $width; $x += $sampleSize) {
            for ($y = 0; $y < $height; $y += $sampleSize) {
                $rgb = imagecolorat($gdImage, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

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
