<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ColorExtractorInterface;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Images\GdImage;
use Farzai\ColorPalette\Images\ImagickImage;

/**
 * Abstract base class for color extractors
 */
abstract class AbstractColorExtractor implements ColorExtractorInterface
{
    protected const SAMPLE_SIZE = 50; // Number of pixels to sample in each dimension
    protected const MIN_SATURATION = 0.15; // Minimum saturation for color consideration
    protected const MIN_BRIGHTNESS = 0.15; // Minimum brightness for color consideration

    
    /**
     * {@inheritdoc}
     */
    public function extract(ImageInterface $image, int $count = 5): ColorPaletteInterface
    {
        // Validate count
        if ($count < 1) {
            throw new \InvalidArgumentException('Count must be greater than 0');
        }

        try {
            // Extract raw colors
            $colors = $this->extractColors($image);

            // Process and filter colors
            $colors = $this->processColors($colors);

            // If no colors were extracted, return a default palette
            if (empty($colors)) {
                return new ColorPalette([
                    new Color(255, 255, 255), // White
                    new Color(200, 200, 200), // Light gray
                    new Color(150, 150, 150), // Medium gray
                    new Color(100, 100, 100), // Dark gray
                    new Color(50, 50, 50),    // Very dark gray
                ]);
            }

            // Cluster similar colors
            $dominantColors = $this->clusterColors($colors, $count);

            // Create and return color palette
            return new ColorPalette(array_map(
                fn (array $rgb) => new Color(
                    max(0, min(255, $rgb['r'])),
                    max(0, min(255, $rgb['g'])),
                    max(0, min(255, $rgb['b']))
                ),
                $dominantColors
            ));
        } catch (\Throwable $e) {
            // Log the error (you should implement proper logging)
            error_log("Error extracting colors: " . $e->getMessage());
            
            // Return a fallback palette
            return new ColorPalette([
                new Color(255, 255, 255), // White
                new Color(200, 200, 200), // Light gray
                new Color(150, 150, 150), // Medium gray
                new Color(100, 100, 100), // Dark gray
                new Color(50, 50, 50),    // Very dark gray
            ]);
        }
    }

    /**
     * Extract raw colors from the image
     *
     * @param ImageInterface $image
     * @return array<array{r: int, g: int, b: int, count: int}>
     */
    abstract protected function extractColors(ImageInterface $image): array;

    /**
     * Process and filter extracted colors
     *
     * @param array<array{r: int, g: int, b: int, count: int}> $colors
     * @return array<array{r: int, g: int, b: int, count: int}>
     */
    protected function processColors(array $colors): array
    {
        // First, ensure we have valid colors
        if (empty($colors)) {
            return [];
        }

        // Filter colors
        $filteredColors = array_filter($colors, function ($color) {
            // Ensure RGB values are valid
            if (!isset($color['r'], $color['g'], $color['b']) ||
                !is_numeric($color['r']) || !is_numeric($color['g']) || !is_numeric($color['b'])) {
                return false;
            }

            // Convert RGB to HSB
            try {
                $hsb = $this->rgbToHsb($color['r'], $color['g'], $color['b']);
            } catch (\Throwable $e) {
                // If conversion fails, skip this color
                return false;
            }

            // Filter out colors with low saturation or brightness
            return $hsb['s'] >= self::MIN_SATURATION && $hsb['b'] >= self::MIN_BRIGHTNESS;
        });

        // If all colors were filtered out, return the original array
        // to prevent having no colors to work with
        if (empty($filteredColors)) {
            return $colors;
        }

        return array_values($filteredColors);
    }

    /**
     * Cluster similar colors using k-means algorithm
     *
     * @param array<array{r: int, g: int, b: int, count: int}> $colors
     * @param int $k Number of clusters
     * @return array<array{r: int, g: int, b: int}>
     */
    protected function clusterColors(array $colors, int $k): array
    {
        if (empty($colors)) {
            return array_fill(0, $k, ['r' => 0, 'g' => 0, 'b' => 0]);
        }

        // Initialize centroids
        $centroids = $this->initializeCentroids($colors, $k);
        $maxIterations = 100;
        $converged = false;

        while (!$converged && $maxIterations-- > 0) {
            // Assign colors to clusters
            $clusters = array_fill(0, $k, []);
            foreach ($colors as $color) {
                $minDistance = PHP_FLOAT_MAX;
                $closestCluster = 0;

                for ($i = 0; $i < $k; $i++) {
                    $distance = $this->calculateColorDistance(
                        $color,
                        $centroids[$i]
                    );

                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $closestCluster = $i;
                    }
                }

                $clusters[$closestCluster][] = $color;
            }

            // Calculate new centroids
            $newCentroids = [];
            $converged = true;

            for ($i = 0; $i < $k; $i++) {
                if (empty($clusters[$i])) {
                    $newCentroids[$i] = $centroids[$i];
                    continue;
                }

                $sumR = $sumG = $sumB = $totalWeight = 0;
                foreach ($clusters[$i] as $color) {
                    $weight = $color['count'];
                    $sumR += $color['r'] * $weight;
                    $sumG += $color['g'] * $weight;
                    $sumB += $color['b'] * $weight;
                    $totalWeight += $weight;
                }

                $newCentroids[$i] = [
                    'r' => (int) round($sumR / $totalWeight),
                    'g' => (int) round($sumG / $totalWeight),
                    'b' => (int) round($sumB / $totalWeight),
                ];

                // Check convergence
                if ($this->calculateColorDistance($newCentroids[$i], $centroids[$i]) > 1) {
                    $converged = false;
                }
            }

            $centroids = $newCentroids;
        }

        return $centroids;
    }

    /**
     * Initialize k-means centroids using k-means++ algorithm
     *
     * @param array<array{r: int, g: int, b: int, count: int}> $colors
     * @param int $k
     * @return array<array{r: int, g: int, b: int}>
     */
    protected function initializeCentroids(array $colors, int $k): array
    {
        $centroids = [];
        
        // Choose first centroid randomly
        $firstIndex = array_rand($colors);
        $centroids[] = [
            'r' => $colors[$firstIndex]['r'],
            'g' => $colors[$firstIndex]['g'],
            'b' => $colors[$firstIndex]['b'],
        ];

        // Choose remaining centroids
        for ($i = 1; $i < $k; $i++) {
            $distances = [];
            foreach ($colors as $color) {
                $minDistance = PHP_FLOAT_MAX;
                foreach ($centroids as $centroid) {
                    $distance = $this->calculateColorDistance($color, $centroid);
                    $minDistance = min($minDistance, $distance);
                }
                $distances[] = $minDistance;
            }

            // Choose next centroid with probability proportional to distance
            $sum = array_sum($distances);
            $target = mt_rand() / mt_getrandmax() * $sum;
            $currentSum = 0;
            foreach ($colors as $index => $color) {
                $currentSum += $distances[$index];
                if ($currentSum >= $target) {
                    $centroids[] = [
                        'r' => $color['r'],
                        'g' => $color['g'],
                        'b' => $color['b'],
                    ];
                    break;
                }
            }
        }

        return $centroids;
    }

    /**
     * Calculate Euclidean distance between two colors in RGB space
     *
     * @param array{r: int, g: int, b: int} $color1
     * @param array{r: int, g: int, b: int} $color2
     * @return float
     */
    protected function calculateColorDistance(array $color1, array $color2): float
    {
        return sqrt(
            pow($color1['r'] - $color2['r'], 2) +
            pow($color1['g'] - $color2['g'], 2) +
            pow($color1['b'] - $color2['b'], 2)
        );
    }

    /**
     * Convert RGB to HSB color space
     *
     * @param int $r
     * @param int $g
     * @param int $b
     * @return array{h: float, s: float, b: float}
     */
    protected function rgbToHsb(int $r, int $g, int $b): array
    {
        // Normalize RGB values to 0-1 range
        $r = $r / 255;
        $g = $g / 255;
        $b = $b / 255;

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        // Initialize HSB values
        $h = 0;
        $s = ($max === 0) ? 0 : ($delta / $max); // Saturation
        $v = $max; // Brightness

        // Calculate hue only if delta is not zero
        if ($delta !== 0) {
            if ($max === $r) {
                $h = 60 * (($g - $b) / $delta + ($g < $b ? 6 : 0));
            } elseif ($max === $g) {
                $h = 60 * (($b - $r) / $delta + 2);
            } elseif ($max === $b) {
                $h = 60 * (($r - $g) / $delta + 4);
            }
        }

        // Ensure hue is in the range [0, 360]
        $h = max(0, min(360, $h));

        return [
            'h' => $h,
            's' => $s,
            'b' => $v,
        ];
    }

}
