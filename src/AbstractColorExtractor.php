<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\AccessibilityConstants;
use Farzai\ColorPalette\Contracts\ColorExtractorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Abstract base class for color extractors
 */
abstract class AbstractColorExtractor implements ColorExtractorInterface
{
    protected const SAMPLE_SIZE = 50; // Number of pixels to sample in each dimension

    protected const MIN_SATURATION = 0.05; // Reduced from 0.15

    protected const MIN_BRIGHTNESS = 0.05; // Reduced from 0.15

    /**
     * Seed for deterministic random number generation
     * Using a fixed seed ensures idempotent color extraction
     */
    protected int $seed = 42;

    protected LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger;
    }

    /**
     * Extract dominant colors from an image
     *
     * Extracts the specified number of dominant colors from an image using k-means clustering.
     * If extraction fails or no colors pass the filtering criteria, returns a fallback
     * grayscale palette with the requested number of colors.
     *
     * @param  ImageInterface  $image  The image to extract colors from
     * @param  int  $count  Number of colors to extract (default: 5, minimum: 1)
     * @return ColorPaletteInterface A palette containing exactly $count colors
     *
     * @throws \InvalidArgumentException If count is less than 1
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
                return $this->createDefaultGrayscalePalette($count);
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
            // Log the error using PSR-3 logger
            $this->logger->error('Error extracting colors from image', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a fallback palette
            return $this->createDefaultGrayscalePalette($count);
        }
    }

    /**
     * Create a default grayscale palette for fallback purposes
     *
     * Generates a palette of grayscale colors evenly distributed from white to dark gray.
     * This method is called when color extraction fails or returns no usable colors.
     *
     * @param  int  $count  Number of colors to generate (must be >= 1)
     * @return ColorPalette A palette containing the specified number of grayscale colors
     *
     * @throws \InvalidArgumentException If count is less than 1
     */
    private function createDefaultGrayscalePalette(int $count): ColorPalette
    {
        if ($count < 1) {
            throw new \InvalidArgumentException('Count must be at least 1');
        }

        // Define the grayscale range: from white (255) to dark gray (30)
        $maxValue = 255;
        $minValue = 30;

        $colors = [];

        if ($count === 1) {
            // For a single color, return a medium gray
            $grayValue = (int) round(($maxValue + $minValue) / 2);
            $colors[] = new Color($grayValue, $grayValue, $grayValue);
        } else {
            // For multiple colors, distribute evenly across the grayscale spectrum
            $step = ($maxValue - $minValue) / ($count - 1);

            for ($i = 0; $i < $count; $i++) {
                $grayValue = (int) round($maxValue - ($step * $i));
                // Ensure value stays within valid range
                $grayValue = max($minValue, min($maxValue, $grayValue));
                $colors[] = new Color($grayValue, $grayValue, $grayValue);
            }
        }

        return new ColorPalette($colors);
    }

    /**
     * Extract raw colors from the image
     *
     * @return array<array{r: int, g: int, b: int, count: int}>
     */
    abstract protected function extractColors(ImageInterface $image): array;

    /**
     * Process and filter extracted colors
     *
     * @param  array<array{r: int, g: int, b: int, count: int}>  $colors
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
            // Check if color has required RGB keys
            // @phpstan-ignore-next-line - Validation needed for defensive programming even if type suggests keys always exist
            if (! is_array($color) || ! array_key_exists('r', $color) || ! array_key_exists('g', $color) || ! array_key_exists('b', $color)) {
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
     * @param  array<array{r: int, g: int, b: int, count: int}>  $colors
     * @param  int  $k  Number of clusters
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

        while (! $converged && $maxIterations-- > 0) {
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

        // Sort colors deterministically for consistent ordering across runs
        return $this->sortColors($centroids);
    }

    /**
     * Initialize k-means centroids using k-means++ algorithm
     * Uses seeded random number generation for deterministic results
     *
     * @param  array<array{r: int, g: int, b: int, count: int}>  $colors
     * @return array<array{r: int, g: int, b: int}>
     */
    protected function initializeCentroids(array $colors, int $k): array
    {
        $centroids = [];

        // Seed the random number generator for deterministic results
        mt_srand($this->seed);

        // Choose first centroid using seeded random
        $colorKeys = array_keys($colors);
        $firstIndex = $colorKeys[mt_rand(0, count($colorKeys) - 1)];
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

            // Choose next centroid with probability proportional to distance (seeded)
            $sum = array_sum($distances);
            $target = (mt_rand() / mt_getrandmax()) * $sum;
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
     * @param  array{r: int, g: int, b: int}  $color1
     * @param  array{r: int, g: int, b: int}  $color2
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
     * Sort colors deterministically for consistent ordering
     * Colors are sorted by brightness (luminance) in descending order
     *
     * @param  array<array{r: int, g: int, b: int}>  $colors
     * @return array<array{r: int, g: int, b: int}>
     */
    protected function sortColors(array $colors): array
    {
        usort($colors, function ($a, $b) {
            // Calculate perceived brightness using standard luminance formula
            // This provides consistent ordering based on how bright humans perceive colors
            $luminanceA = (AccessibilityConstants::BRIGHTNESS_RED_COEFFICIENT * $a['r'] +
                          AccessibilityConstants::BRIGHTNESS_GREEN_COEFFICIENT * $a['g'] +
                          AccessibilityConstants::BRIGHTNESS_BLUE_COEFFICIENT * $a['b']) /
                          AccessibilityConstants::BRIGHTNESS_DIVISOR;
            $luminanceB = (AccessibilityConstants::BRIGHTNESS_RED_COEFFICIENT * $b['r'] +
                          AccessibilityConstants::BRIGHTNESS_GREEN_COEFFICIENT * $b['g'] +
                          AccessibilityConstants::BRIGHTNESS_BLUE_COEFFICIENT * $b['b']) /
                          AccessibilityConstants::BRIGHTNESS_DIVISOR;

            // Sort by luminance descending (brightest first)
            $diff = $luminanceB - $luminanceA;

            // If luminance is nearly identical, use hue as secondary sort
            if (abs($diff) < 0.01) {
                $hsbA = $this->rgbToHsb($a['r'], $a['g'], $a['b']);
                $hsbB = $this->rgbToHsb($b['r'], $b['g'], $b['b']);

                return $hsbA['h'] <=> $hsbB['h'];
            }

            return $diff <=> 0;
        });

        return $colors;
    }

    /**
     * Convert RGB to HSB color space
     *
     * @return array{h: float, s: float, b: float}
     */
    protected function rgbToHsb(int $r, int $g, int $b): array
    {
        return ColorSpaceConverter::rgbToHsb($r, $g, $b);
    }
}
