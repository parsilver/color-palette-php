<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Images\GdImage;

/**
 * GD implementation of ColorExtractor
 */
class GdColorExtractor extends AbstractColorExtractor
{
    /**
     * {@inheritdoc}
     */
    protected function extractColors(ImageInterface $image): array
    {
        if (! ($image instanceof GdImage)) {
            throw new \InvalidArgumentException('GdColorExtractor requires GdImage instance');
        }

        $resource = $image->getResource();
        $width = $image->getWidth();
        $height = $image->getHeight();

        $colors = [];
        $stepX = max(1, (int) ($width / self::SAMPLE_SIZE));
        $stepY = max(1, (int) ($height / self::SAMPLE_SIZE));

        for ($y = 0; $y < $height; $y += $stepY) {
            for ($x = 0; $x < $width; $x += $stepX) {
                $rgb = imagecolorat($resource, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $key = "{$r},{$g},{$b}";
                if (! isset($colors[$key])) {
                    $colors[$key] = ['r' => $r, 'g' => $g, 'b' => $b, 'count' => 0];
                }
                $colors[$key]['count']++;
            }
        }

        return array_values($colors);
    }
}
