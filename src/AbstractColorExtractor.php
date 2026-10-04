<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Constants\AccessibilityConstants;
use Farzai\ColorPalette\Contracts\ColorExtractorInterface;
use Farzai\ColorPalette\Contracts\ColorPaletteInterface;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Random\Engine\Mt19937;
use Random\Randomizer;

/**
 * Abstract base class for color extractors
 */
abstract class AbstractColorExtractor implements ColorExtractorInterface
{
    protected const SAMPLE_SIZE = 50; // Number of pixels to sample in each dimension

    protected const MIN_SATURATION = 0.05; // Reduced from 0.15

    protected const MIN_BRIGHTNESS = 0.05; // Reduced from 0.15

    /**
     * Independent k-means runs per extraction; the lowest-error one is kept
     */
    private const KMEANS_RESTARTS = 10;

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
     * Runs k-means KMEANS_RESTARTS times from different seeded starts and keeps
     * the result with the lowest weighted clustering error. A single run can
     * settle in a poor local optimum, and which one it lands in can flip with a
     * small change to the image (re-encoding, resizing); the best of several
     * runs is far less sensitive to such changes.
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

        // Integer channels throughout; a custom extractor may emit floats.
        $colors = array_map(fn (array $color) => [
            'r' => (int) round($color['r']),
            'g' => (int) round($color['g']),
            'b' => (int) round($color['b']),
            'count' => $color['count'],
        ], array_values($colors));

        // One locally seeded stream feeds every restart: the result is a fixed
        // function of the input, and PHP's global RNG state is left untouched.
        $randomizer = new Randomizer(new Mt19937($this->seed));

        $best = [];
        $bestError = PHP_FLOAT_MAX;
        for ($run = 0; $run < self::KMEANS_RESTARTS; $run++) {
            $centroids = $this->refineCentroids($colors, $this->initializeCentroids($colors, $k, $randomizer));
            $error = $this->clusteringError($colors, $centroids);

            // Strictly lower only: on a tie the earlier run wins, deterministically.
            if ($run === 0 || $error < $bestError) {
                $best = $centroids;
                $bestError = $error;
            }

            // Nothing left to win: a zero error cannot be beaten, and with one
            // cluster every run converges to the same weighted mean.
            if ($bestError <= 0 || $k === 1) {
                break;
            }
        }

        // Sort colors deterministically for consistent ordering across runs
        return $this->sortColors($best);
    }

    /**
     * Run Lloyd iterations from the given starting centroids until they settle
     *
     * @param  list<array{r: int, g: int, b: int, count: int}>  $colors
     * @param  list<array{r: int, g: int, b: int}>  $centroids
     * @return list<array{r: int, g: int, b: int}>
     */
    private function refineCentroids(array $colors, array $centroids): array
    {
        $k = count($centroids);
        $maxIterations = 100;
        $converged = false;

        while (! $converged && $maxIterations-- > 0) {
            // Assign each color to its nearest centroid, accumulating the
            // count-weighted channel sums of every cluster: [r, g, b, weight]
            $sums = array_fill(0, $k, [0, 0, 0, 0]);
            $cr = array_column($centroids, 'r');
            $cg = array_column($centroids, 'g');
            $cb = array_column($centroids, 'b');
            foreach ($colors as $color) {
                // Nearest centroid by squared Euclidean distance, inlined: this
                // is the hot loop of clustering (first centroid wins a tie).
                $r = $color['r'];
                $g = $color['g'];
                $b = $color['b'];
                $i = 0;
                $minDistance = PHP_INT_MAX;
                for ($j = 0; $j < $k; $j++) {
                    $dr = $r - $cr[$j];
                    $dg = $g - $cg[$j];
                    $db = $b - $cb[$j];
                    $distance = $dr * $dr + $dg * $dg + $db * $db;
                    if ($distance < $minDistance) {
                        $minDistance = $distance;
                        $i = $j;
                    }
                }

                $weight = $color['count'];
                $sums[$i][0] += $color['r'] * $weight;
                $sums[$i][1] += $color['g'] * $weight;
                $sums[$i][2] += $color['b'] * $weight;
                $sums[$i][3] += $weight;
            }

            // Calculate new centroids
            $newCentroids = [];
            $converged = true;

            for ($i = 0; $i < $k; $i++) {
                [$sumR, $sumG, $sumB, $totalWeight] = $sums[$i];

                // An empty cluster, or one of zero-weight colors (e.g. a custom
                // extractor emitting count=0), would divide by zero; keep the
                // previous centroid instead.
                if ($totalWeight <= 0) {
                    $newCentroids[$i] = $centroids[$i];

                    continue;
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
     * Index of the centroid closest to a color by squared Euclidean RGB distance
     * (the first one on a tie)
     *
     * @param  array{r: int, g: int, b: int}  $color
     * @param  list<array{r: int, g: int, b: int}>  $centroids
     */
    private function nearestCentroid(array $color, array $centroids): int
    {
        $minDistance = PHP_INT_MAX;
        $closest = 0;

        foreach ($centroids as $i => $centroid) {
            $distance = self::squaredDistance($color, $centroid);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $closest = $i;
            }
        }

        return $closest;
    }

    /**
     * Pixel-count weighted sum of squared distances to the nearest centroid
     *
     * Accumulated as a float so a large total cannot overflow an int on 32-bit
     * builds; the terms are integers, so it stays exact up to 2^53.
     *
     * @param  list<array{r: int, g: int, b: int, count: int}>  $colors
     * @param  list<array{r: int, g: int, b: int}>  $centroids
     */
    private function clusteringError(array $colors, array $centroids): float
    {
        $error = 0.0;
        foreach ($colors as $color) {
            $nearest = $centroids[$this->nearestCentroid($color, $centroids)];
            $error += $color['count'] * self::squaredDistance($color, $nearest);
        }

        return $error;
    }

    /**
     * @param  array{r: int, g: int, b: int}  $color1
     * @param  array{r: int, g: int, b: int}  $color2
     */
    private static function squaredDistance(array $color1, array $color2): int
    {
        $dr = $color1['r'] - $color2['r'];
        $dg = $color1['g'] - $color2['g'];
        $db = $color1['b'] - $color2['b'];

        return $dr * $dr + $dg * $dg + $db * $db;
    }

    /**
     * Choose k starting centroids with weighted k-means++
     *
     * The first centroid is drawn in proportion to pixel count; each further one
     * in proportion to pixel count times the squared distance to the nearest
     * centroid chosen so far. Weighting by count follows the image's color mass
     * instead of the long tail of one-off colors that JPEG noise produces.
     *
     * @param  list<array{r: int, g: int, b: int, count: int}>  $colors
     * @return list<array{r: int, g: int, b: int}>
     */
    protected function initializeCentroids(array $colors, int $k, Randomizer $randomizer): array
    {
        $weights = array_map(fn (array $color) => max(0, $color['count']), $colors);
        if (array_sum($weights) <= 0) {
            // A custom extractor may emit zero counts; treat colors equally.
            $weights = array_fill(0, count($colors), 1);
        }

        $first = $colors[$this->pickWeighted($weights, $randomizer)];
        $centroids = [['r' => $first['r'], 'g' => $first['g'], 'b' => $first['b']]];
        $nearest = array_map(fn (array $color) => self::squaredDistance($color, $centroids[0]), $colors);

        while (count($centroids) < $k) {
            $scores = [];
            foreach ($weights as $i => $weight) {
                $scores[$i] = $weight * $nearest[$i];
            }

            if (array_sum($scores) <= 0) {
                // Every color already coincides with a centroid (fewer distinct
                // colors than k); repeat the first so the palette keeps k entries.
                $centroids[] = $centroids[0];

                continue;
            }

            $next = $colors[$this->pickWeighted($scores, $randomizer)];
            $centroid = ['r' => $next['r'], 'g' => $next['g'], 'b' => $next['b']];
            $centroids[] = $centroid;

            foreach ($colors as $i => $color) {
                $nearest[$i] = min($nearest[$i], self::squaredDistance($color, $centroid));
            }
        }

        return $centroids;
    }

    /**
     * Draw an index with probability proportional to its weight
     *
     * @param  list<int|float>  $weights  Non-negative, with a positive sum
     */
    private function pickWeighted(array $weights, Randomizer $randomizer): int
    {
        // 31 random bits: the same draw on 32- and 64-bit PHP builds, unlike a
        // range up to PHP_INT_MAX.
        $target = $randomizer->getInt(0, 0x7FFFFFFF) / 0x7FFFFFFF * array_sum($weights);

        // The first positive weight whose running sum reaches the target; the
        // last positive one if float rounding leaves the sum just short.
        $cumulative = 0;
        $chosen = 0;
        foreach ($weights as $i => $weight) {
            if ($weight <= 0) {
                continue;
            }

            $chosen = $i;
            $cumulative += $weight;
            if ($cumulative >= $target) {
                break;
            }
        }

        return $chosen;
    }

    /**
     * Calculate Euclidean distance between two colors in RGB space
     *
     * @param  array{r: int, g: int, b: int}  $color1
     * @param  array{r: int, g: int, b: int}  $color2
     */
    protected function calculateColorDistance(array $color1, array $color2): float
    {
        $dr = $color1['r'] - $color2['r'];
        $dg = $color1['g'] - $color2['g'];
        $db = $color1['b'] - $color2['b'];

        return sqrt($dr * $dr + $dg * $dg + $db * $db);
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
