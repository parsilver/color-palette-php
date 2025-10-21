---
layout: default
title: Performance Optimization
description: Strategies for optimizing color processing, caching, memory management, and best practices for high-performance color manipulation
keywords: performance, optimization, caching, memory management, benchmarking, best practices
permalink: /concepts/performance/
---

# Performance Optimization

Color processing can be computationally expensive, especially when working with large images, complex transformations, or generating multiple palette variations. This guide covers strategies for optimizing color operations in production environments.

## Table of Contents
{:.no_toc}

* TOC
{:toc}

## Performance Fundamentals

### Why Performance Matters

**User Experience:**
- Page load times affect bounce rates (53% of mobile users abandon pages that take >3s)
- Interactive delays >100ms are perceived as sluggish
- Color processing can block rendering in web applications

**Server Resources:**
- Image processing is CPU-intensive
- Memory consumption can spike with large images
- Concurrent requests can overwhelm servers

**Cost:**
- Higher server costs for processing
- Increased bandwidth for unoptimized images
- Cloud computing costs (AWS Lambda, serverless functions)

### Performance Targets

**General Guidelines:**
- Single color operation: <1ms
- Palette generation (5-10 colors): <50ms
- Image analysis (1000x1000px): <200ms
- Batch operations: <1s for 100 items

**Real-World Scenarios:**
```php
// Good performance
$color = Color::fromHex('#3498db');
$hsl = $color->toHsl();  // ~0.05ms

// Acceptable performance
$palette = Palette::fromImage('photo.jpg', 8);  // ~150ms

// Needs optimization
foreach ($thousandImages as $image) {
    $palette = Palette::fromImage($image, 10);  // ~150s total
}
```

---

## Color Conversion Optimization

### Conversion Cost Analysis

Different color space conversions have different computational costs:

```
Computational Cost (relative):
RGB → HEX:  1x   (simple integer to string)
HEX → RGB:  1x   (simple string parsing)
RGB → HSL:  3x   (trigonometry, conditional logic)
RGB → HSV:  3x   (similar to HSL)
RGB → CMYK: 2x   (arithmetic operations)
RGB → LAB:  10x  (gamma correction + matrix math + nonlinear transforms)
```

### Memoization for Color Conversions

```php
class OptimizedColor {
    private array $rgb;
    private ?array $hsl = null;
    private ?array $lab = null;
    private ?string $hex = null;

    public function __construct(int $r, int $g, int $b) {
        $this->rgb = ['r' => $r, 'g' => $g, 'b' => $b];
    }

    // Cache computed values
    public function toHsl(): array {
        if ($this->hsl === null) {
            $this->hsl = $this->computeHsl();
        }
        return $this->hsl;
    }

    public function toLab(): array {
        if ($this->lab === null) {
            $this->lab = $this->computeLab();
        }
        return $this->lab;
    }

    public function toHex(): string {
        if ($this->hex === null) {
            $this->hex = sprintf(
                '#%02x%02x%02x',
                $this->rgb['r'],
                $this->rgb['g'],
                $this->rgb['b']
            );
        }
        return $this->hex;
    }

    // Private computation methods
    private function computeHsl(): array {
        // HSL conversion logic
        // (see color-spaces.md for full implementation)
    }

    private function computeLab(): array {
        // LAB conversion logic (expensive)
        // (see color-spaces.md for full implementation)
    }
}
```

### Batch Conversions

```php
// ❌ SLOW: Convert one at a time
foreach ($colors as $color) {
    $hsl = $color->toHsl();
    $lab = $color->toLab();
    // Process...
}

// ✅ FAST: Batch conversions with optimized algorithms
class ColorConverter {
    // Reuse lookup tables and pre-computed values
    private static array $gammaLut = [];

    public static function batchToLab(array $colors): array {
        // Initialize lookup table once
        if (empty(self::$gammaLut)) {
            self::initGammaLut();
        }

        return array_map(function($color) {
            return $color->toLab();  // Uses cached LUT
        }, $colors);
    }

    private static function initGammaLut(): void {
        for ($i = 0; $i <= 255; $i++) {
            $normalized = $i / 255.0;
            self::$gammaLut[$i] = $normalized <= 0.04045
                ? $normalized / 12.92
                : pow(($normalized + 0.055) / 1.055, 2.4);
        }
    }
}
```

### Lazy Evaluation

```php
// Defer expensive operations until needed
class LazyPalette {
    private array $colors;
    private ?array $sortedByLuminance = null;
    private ?array $colorBlindSafe = null;

    public function __construct(array $colors) {
        $this->colors = $colors;
    }

    // Only compute when requested
    public function sortedByLuminance(): array {
        if ($this->sortedByLuminance === null) {
            $this->sortedByLuminance = $this->computeSortedByLuminance();
        }
        return $this->sortedByLuminance;
    }

    // Only compute when requested
    public function isColorBlindSafe(): bool {
        if ($this->colorBlindSafe === null) {
            $this->colorBlindSafe = $this->checkColorBlindSafety();
        }
        return $this->colorBlindSafe;
    }

    private function computeSortedByLuminance(): array {
        // Expensive: Calculate luminance for each color
        $withLuminance = array_map(function($color) {
            return [
                'color' => $color,
                'luminance' => $this->calculateLuminance($color),
            ];
        }, $this->colors);

        usort($withLuminance, fn($a, $b) => $a['luminance'] <=> $b['luminance']);

        return array_column($withLuminance, 'color');
    }

    private function checkColorBlindSafety(): bool {
        // Expensive: Simulate color blindness for all combinations
        // Only run if explicitly requested
    }
}
```

---

## Image Processing Optimization

### Image Resizing Before Analysis

```php
// ❌ SLOW: Analyze full-resolution image
$largePalette = Palette::fromImage('photo_4000x3000.jpg', 10);  // ~2000ms

// ✅ FAST: Resize before analysis
class OptimizedPaletteExtractor {
    private const MAX_DIMENSION = 400;

    public static function extract(string $imagePath, int $colors = 10): Palette {
        $image = imagecreatefromjpeg($imagePath);
        $resized = self::resizeForAnalysis($image);

        $palette = Palette::fromGdImage($resized, $colors);

        imagedestroy($image);
        imagedestroy($resized);

        return $palette;  // ~100ms (20x faster)
    }

    private static function resizeForAnalysis($image) {
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate new dimensions maintaining aspect ratio
        if ($width > $height) {
            $newWidth = self::MAX_DIMENSION;
            $newHeight = (int) ($height * (self::MAX_DIMENSION / $width));
        } else {
            $newHeight = self::MAX_DIMENSION;
            $newWidth = (int) ($width * (self::MAX_DIMENSION / $height));
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled(
            $resized, $image,
            0, 0, 0, 0,
            $newWidth, $newHeight,
            $width, $height
        );

        return $resized;
    }
}
```

### Pixel Sampling Strategies

```php
// Sample pixels instead of analyzing every pixel
class SamplingExtractor {
    // ❌ SLOW: Analyze all pixels
    public static function analyzeAll($image): array {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $pixels[] = [
                    'r' => ($rgb >> 16) & 0xFF,
                    'g' => ($rgb >> 8) & 0xFF,
                    'b' => $rgb & 0xFF,
                ];
            }
        }

        return $pixels;  // Millions of pixels
    }

    // ✅ FAST: Systematic sampling
    public static function sampleGrid($image, int $sampleRate = 10): array {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        // Sample every Nth pixel
        for ($y = 0; $y < $height; $y += $sampleRate) {
            for ($x = 0; $x < $width; $x += $sampleRate) {
                $rgb = imagecolorat($image, $x, $y);
                $pixels[] = [
                    'r' => ($rgb >> 16) & 0xFF,
                    'g' => ($rgb >> 8) & 0xFF,
                    'b' => $rgb & 0xFF,
                ];
            }
        }

        return $pixels;  // 100x fewer pixels with minimal quality loss
    }

    // ✅ FAST: Random sampling
    public static function sampleRandom($image, int $sampleCount = 1000): array {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        for ($i = 0; $i < $sampleCount; $i++) {
            $x = mt_rand(0, $width - 1);
            $y = mt_rand(0, $height - 1);

            $rgb = imagecolorat($image, $x, $y);
            $pixels[] = [
                'r' => ($rgb >> 16) & 0xFF,
                'g' => ($rgb >> 8) & 0xFF,
                'b' => $rgb & 0xFF,
            ];
        }

        return $pixels;
    }
}
```

### Color Quantization Algorithms

```php
// Choose efficient quantization algorithms
class QuantizationComparison {
    // ❌ SLOW: K-means (iterative, many distance calculations)
    // O(n * k * i) where n=pixels, k=colors, i=iterations
    public static function kMeans($pixels, $k): array {
        // ~500ms for 10,000 pixels, 8 colors, 10 iterations
    }

    // ✅ FASTER: Median cut (divide and conquer)
    // O(n * log(k)) where n=pixels, k=colors
    public static function medianCut($pixels, $k): array {
        // ~50ms for 10,000 pixels, 8 colors
    }

    // ✅ FASTEST: Octree (tree-based quantization)
    // O(n) where n=pixels
    public static function octree($pixels, $k): array {
        // ~20ms for 10,000 pixels, 8 colors
    }
}

// Octree implementation (fastest for real-time use)
class OctreeQuantizer {
    private const MAX_DEPTH = 8;

    private OctreeNode $root;
    private array $reducibleNodes = [];
    private int $leafCount = 0;

    public function addPixels(array $pixels): void {
        foreach ($pixels as $pixel) {
            $this->addColor($pixel['r'], $pixel['g'], $pixel['b']);
        }
    }

    public function getPalette(int $colorCount): array {
        // Reduce tree to desired color count
        while ($this->leafCount > $colorCount) {
            $this->reduceTree();
        }

        return $this->collectColors();
    }

    // Tree operations are O(log n) per pixel
    private function addColor(int $r, int $g, int $b): void {
        // Implementation details...
    }

    private function reduceTree(): void {
        // Merge least significant nodes
    }
}
```

---

## Caching Strategies

### In-Memory Caching

```php
class ColorCache {
    private static array $cache = [];
    private static int $hits = 0;
    private static int $misses = 0;

    public static function get(string $key, callable $generator) {
        if (isset(self::$cache[$key])) {
            self::$hits++;
            return self::$cache[$key];
        }

        self::$misses++;
        $value = $generator();
        self::$cache[$key] = $value;

        return $value;
    }

    public static function stats(): array {
        $total = self::$hits + self::$misses;
        return [
            'hits' => self::$hits,
            'misses' => self::$misses,
            'hit_rate' => $total > 0 ? self::$hits / $total : 0,
        ];
    }
}

// Usage
$palette = ColorCache::get('image_123_palette_8', function() {
    return Palette::fromImage('uploads/image_123.jpg', 8);
});
```

### File-Based Caching

```php
class PaletteCache {
    private string $cacheDir;

    public function __construct(string $cacheDir = '/tmp/palette_cache') {
        $this->cacheDir = $cacheDir;

        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }

    public function get(string $imagePath, int $colorCount): ?Palette {
        $cacheKey = $this->getCacheKey($imagePath, $colorCount);
        $cachePath = $this->getCachePath($cacheKey);

        if (file_exists($cachePath)) {
            // Check if cache is still valid
            if (filemtime($cachePath) > filemtime($imagePath)) {
                return unserialize(file_get_contents($cachePath));
            }
        }

        return null;
    }

    public function set(string $imagePath, int $colorCount, Palette $palette): void {
        $cacheKey = $this->getCacheKey($imagePath, $colorCount);
        $cachePath = $this->getCachePath($cacheKey);

        file_put_contents($cachePath, serialize($palette));
    }

    private function getCacheKey(string $imagePath, int $colorCount): string {
        return md5($imagePath . '_' . $colorCount);
    }

    private function getCachePath(string $key): string {
        return $this->cacheDir . '/' . $key . '.cache';
    }

    // Cleanup old cache files
    public function cleanup(int $maxAge = 86400): void {
        $files = glob($this->cacheDir . '/*.cache');
        $now = time();

        foreach ($files as $file) {
            if ($now - filemtime($file) > $maxAge) {
                unlink($file);
            }
        }
    }
}

// Usage
$cache = new PaletteCache();

$palette = $cache->get('uploads/photo.jpg', 8);

if ($palette === null) {
    $palette = Palette::fromImage('uploads/photo.jpg', 8);
    $cache->set('uploads/photo.jpg', 8, $palette);
}
```

### Redis/Memcached Caching

```php
class RedisPaletteCache {
    private Redis $redis;
    private int $ttl = 3600;  // 1 hour

    public function __construct(string $host = '127.0.0.1', int $port = 6379) {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }

    public function get(string $imagePath, int $colorCount): ?Palette {
        $key = $this->buildKey($imagePath, $colorCount);
        $data = $this->redis->get($key);

        if ($data === false) {
            return null;
        }

        return unserialize($data);
    }

    public function set(string $imagePath, int $colorCount, Palette $palette): void {
        $key = $this->buildKey($imagePath, $colorCount);
        $this->redis->setex($key, $this->ttl, serialize($palette));
    }

    private function buildKey(string $imagePath, int $colorCount): string {
        return 'palette:' . md5($imagePath) . ':' . $colorCount;
    }

    // Batch operations for better performance
    public function mget(array $requests): array {
        $keys = array_map(
            fn($req) => $this->buildKey($req['path'], $req['count']),
            $requests
        );

        $results = $this->redis->mget($keys);

        return array_map(
            fn($data) => $data !== false ? unserialize($data) : null,
            $results
        );
    }
}
```

### HTTP Cache Headers

```php
// Set appropriate cache headers for color API responses
class ColorApiResponse {
    public static function sendWithCache(Palette $palette, int $maxAge = 3600): void {
        // Generate ETag based on palette content
        $etag = md5(serialize($palette));

        // Check if client has cached version
        $clientEtag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? null;

        if ($clientEtag === $etag) {
            http_response_code(304);  // Not Modified
            exit;
        }

        // Set cache headers
        header("Cache-Control: public, max-age={$maxAge}");
        header("ETag: {$etag}");
        header('Content-Type: application/json');

        echo json_encode($palette->toArray());
    }
}

// Usage in API endpoint
$palette = Palette::fromImage('photo.jpg', 8);
ColorApiResponse::sendWithCache($palette, 3600);
```

---

## Memory Management

### Memory-Efficient Image Processing

```php
class MemoryEfficientProcessor {
    // ❌ BAD: Load entire image into memory
    public static function processBad(array $imagePaths): array {
        $images = [];

        foreach ($imagePaths as $path) {
            $images[] = imagecreatefromjpeg($path);  // Each image stays in memory
        }

        $palettes = array_map(
            fn($img) => Palette::fromGdImage($img, 8),
            $images
        );

        // Memory usage: All images + all palettes

        foreach ($images as $img) {
            imagedestroy($img);
        }

        return $palettes;
    }

    // ✅ GOOD: Process one at a time
    public static function processGood(array $imagePaths): array {
        $palettes = [];

        foreach ($imagePaths as $path) {
            $image = imagecreatefromjpeg($path);
            $palette = Palette::fromGdImage($image, 8);
            imagedestroy($image);  // Free memory immediately

            $palettes[] = $palette;
        }

        // Memory usage: Peak = 1 image + current palettes

        return $palettes;
    }

    // ✅ BEST: Generator pattern for streaming
    public static function processStream(array $imagePaths): Generator {
        foreach ($imagePaths as $path) {
            $image = imagecreatefromjpeg($path);
            $palette = Palette::fromGdImage($image, 8);
            imagedestroy($image);

            yield $path => $palette;  // Process one at a time
        }

        // Memory usage: Peak = 1 image at a time
    }
}

// Usage with generator
foreach (MemoryEfficientProcessor::processStream($paths) as $path => $palette) {
    // Process palette immediately
    saveToDatabase($path, $palette);
}
```

### Monitoring Memory Usage

```php
class MemoryMonitor {
    private static array $checkpoints = [];

    public static function checkpoint(string $label): void {
        self::$checkpoints[$label] = [
            'memory' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'time' => microtime(true),
        ];
    }

    public static function report(): array {
        $report = [];
        $previous = null;

        foreach (self::$checkpoints as $label => $data) {
            $report[$label] = [
                'memory' => self::formatBytes($data['memory']),
                'peak' => self::formatBytes($data['peak']),
            ];

            if ($previous !== null) {
                $report[$label]['delta'] = self::formatBytes(
                    $data['memory'] - $previous['memory']
                );
                $report[$label]['time'] = round(
                    ($data['time'] - $previous['time']) * 1000, 2
                ) . 'ms';
            }

            $previous = $data;
        }

        return $report;
    }

    private static function formatBytes(int $bytes): string {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}

// Usage
MemoryMonitor::checkpoint('start');

$image = imagecreatefromjpeg('large_photo.jpg');
MemoryMonitor::checkpoint('image_loaded');

$palette = Palette::fromGdImage($image, 10);
MemoryMonitor::checkpoint('palette_extracted');

imagedestroy($image);
MemoryMonitor::checkpoint('image_freed');

print_r(MemoryMonitor::report());
/* Output:
[
    'start' => ['memory' => '2.00 MB', 'peak' => '2.00 MB'],
    'image_loaded' => ['memory' => '45.00 MB', 'peak' => '45.00 MB', 'delta' => '+43.00 MB', 'time' => '120.50ms'],
    'palette_extracted' => ['memory' => '45.50 MB', 'peak' => '47.00 MB', 'delta' => '+0.50 MB', 'time' => '85.20ms'],
    'image_freed' => ['memory' => '2.50 MB', 'peak' => '47.00 MB', 'delta' => '-43.00 MB', 'time' => '0.10ms'],
]
*/
```

### Resource Limits

```php
// Set appropriate memory and time limits
class ResourceManager {
    public static function configure(array $options = []): void {
        $defaults = [
            'memory_limit' => '256M',
            'max_execution_time' => 60,
            'max_input_time' => 60,
        ];

        $config = array_merge($defaults, $options);

        ini_set('memory_limit', $config['memory_limit']);
        set_time_limit($config['max_execution_time']);
        ini_set('max_input_time', $config['max_input_time']);
    }

    // Calculate required memory for image
    public static function estimateImageMemory(string $imagePath): int {
        $info = getimagesize($imagePath);
        $width = $info[0];
        $height = $info[1];
        $channels = $info['channels'] ?? 3;
        $bits = $info['bits'] ?? 8;

        // Memory = width × height × channels × (bits/8) × overhead
        $baseMemory = $width * $height * $channels * ($bits / 8);
        $overhead = 1.5;  // GD library overhead

        return (int) ($baseMemory * $overhead);
    }

    public static function canProcessImage(string $imagePath): bool {
        $required = self::estimateImageMemory($imagePath);
        $available = self::getAvailableMemory();

        return $required < $available * 0.8;  // Leave 20% buffer
    }

    private static function getAvailableMemory(): int {
        $limit = ini_get('memory_limit');

        if ($limit === '-1') {
            return PHP_INT_MAX;
        }

        $value = (int) $limit;
        $unit = strtoupper(substr($limit, -1));

        $multipliers = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
        ];

        $limitBytes = $value * ($multipliers[$unit] ?? 1);
        $currentUsage = memory_get_usage(true);

        return $limitBytes - $currentUsage;
    }
}

// Usage
ResourceManager::configure([
    'memory_limit' => '512M',
    'max_execution_time' => 120,
]);

if (!ResourceManager::canProcessImage('huge_photo.jpg')) {
    throw new Exception('Image too large to process safely');
}
```

---

## Asynchronous Processing

### Queue-Based Processing

```php
// Process color extraction asynchronously
class PaletteQueue {
    private array $queue = [];

    public function enqueue(string $imagePath, int $colorCount = 8): string {
        $jobId = uniqid('job_', true);

        $this->queue[$jobId] = [
            'id' => $jobId,
            'image' => $imagePath,
            'colors' => $colorCount,
            'status' => 'pending',
            'created_at' => time(),
        ];

        return $jobId;
    }

    public function process(): void {
        foreach ($this->queue as $jobId => &$job) {
            if ($job['status'] !== 'pending') {
                continue;
            }

            $job['status'] = 'processing';

            try {
                $palette = Palette::fromImage($job['image'], $job['colors']);

                $job['status'] = 'completed';
                $job['result'] = $palette;
                $job['completed_at'] = time();
            } catch (Exception $e) {
                $job['status'] = 'failed';
                $job['error'] = $e->getMessage();
            }
        }
    }

    public function getStatus(string $jobId): ?array {
        return $this->queue[$jobId] ?? null;
    }
}

// Usage in web application
$queue = new PaletteQueue();

// User uploads image
$jobId = $queue->enqueue($_FILES['image']['tmp_name']);

// Return immediately
header('Content-Type: application/json');
echo json_encode([
    'job_id' => $jobId,
    'status_url' => "/api/palette/status/{$jobId}",
]);

// Background worker processes queue
$queue->process();
```

### Parallel Processing

```php
// Process multiple images in parallel (requires ext-parallel or similar)
class ParallelProcessor {
    public static function extractPalettes(array $imagePaths, int $colorCount = 8): array {
        if (!extension_loaded('parallel')) {
            // Fallback to sequential processing
            return self::extractSequential($imagePaths, $colorCount);
        }

        $runtime = new \parallel\Runtime();
        $futures = [];

        foreach ($imagePaths as $path) {
            $futures[$path] = $runtime->run(function($path, $count) {
                return Palette::fromImage($path, $count);
            }, [$path, $colorCount]);
        }

        $results = [];
        foreach ($futures as $path => $future) {
            $results[$path] = $future->value();
        }

        return $results;
    }

    private static function extractSequential(array $imagePaths, int $colorCount): array {
        $results = [];

        foreach ($imagePaths as $path) {
            $results[$path] = Palette::fromImage($path, $colorCount);
        }

        return $results;
    }
}
```

---

## Benchmarking

### Microbenchmarking

```php
class Benchmark {
    private array $results = [];

    public function run(string $name, callable $function, int $iterations = 100): void {
        // Warmup
        for ($i = 0; $i < 10; $i++) {
            $function();
        }

        // Measure
        $times = [];
        for ($i = 0; $i < $iterations; $i++) {
            $start = microtime(true);
            $function();
            $times[] = (microtime(true) - $start) * 1000;  // ms
        }

        $this->results[$name] = [
            'min' => min($times),
            'max' => max($times),
            'avg' => array_sum($times) / count($times),
            'median' => $this->median($times),
            'iterations' => $iterations,
        ];
    }

    public function compare(array $tests, int $iterations = 100): void {
        foreach ($tests as $name => $function) {
            $this->run($name, $function, $iterations);
        }

        $this->printResults();
    }

    private function median(array $values): float {
        sort($values);
        $count = count($values);
        $middle = floor($count / 2);

        if ($count % 2 === 0) {
            return ($values[$middle - 1] + $values[$middle]) / 2;
        }

        return $values[$middle];
    }

    private function printResults(): void {
        echo "\nBenchmark Results:\n";
        echo str_repeat('=', 80) . "\n";

        foreach ($this->results as $name => $stats) {
            printf("%-40s %8.3fms (avg) %8.3fms (median)\n",
                $name,
                $stats['avg'],
                $stats['median']
            );
        }

        echo str_repeat('=', 80) . "\n";

        // Show relative performance
        $baseline = reset($this->results);
        foreach ($this->results as $name => $stats) {
            $ratio = $stats['avg'] / $baseline['avg'];
            printf("%-40s %.2fx\n", $name, $ratio);
        }
    }
}

// Usage
$bench = new Benchmark();

$color = Color::fromHex('#3498db');

$bench->compare([
    'RGB to HEX' => fn() => $color->toHex(),
    'RGB to HSL' => fn() => $color->toHsl(),
    'RGB to CMYK' => fn() => $color->toCmyk(),
    'RGB to LAB' => fn() => $color->toLab(),
], 1000);

/* Output:
Benchmark Results:
================================================================================
RGB to HEX                                  0.008ms (avg)    0.007ms (median)
RGB to HSL                                  0.025ms (avg)    0.023ms (median)
RGB to CMYK                                 0.018ms (avg)    0.017ms (median)
RGB to LAB                                  0.095ms (avg)    0.092ms (median)
================================================================================
RGB to HEX                               1.00x
RGB to HSL                               3.13x
RGB to CMYK                              2.25x
RGB to LAB                              11.88x
*/
```

### Real-World Performance Testing

```php
class PerformanceProfiler {
    private array $timings = [];
    private array $memorySnapshots = [];

    public function start(string $label): void {
        $this->timings[$label] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
        ];
    }

    public function end(string $label): void {
        if (!isset($this->timings[$label])) {
            throw new Exception("No timing started for: {$label}");
        }

        $this->timings[$label]['end'] = microtime(true);
        $this->timings[$label]['memory_end'] = memory_get_usage(true);
        $this->timings[$label]['duration'] =
            ($this->timings[$label]['end'] - $this->timings[$label]['start']) * 1000;
        $this->timings[$label]['memory_used'] =
            $this->timings[$label]['memory_end'] - $this->timings[$label]['memory_start'];
    }

    public function report(): array {
        $report = [];

        foreach ($this->timings as $label => $data) {
            if (!isset($data['end'])) {
                continue;  // Not finished
            }

            $report[$label] = [
                'duration_ms' => round($data['duration'], 2),
                'memory_mb' => round($data['memory_used'] / 1024 / 1024, 2),
            ];
        }

        return $report;
    }
}

// Usage in real application
$profiler = new PerformanceProfiler();

$profiler->start('load_image');
$image = imagecreatefromjpeg('uploads/photo.jpg');
$profiler->end('load_image');

$profiler->start('resize');
$resized = imagescale($image, 400, 400);
$profiler->end('resize');

$profiler->start('extract_palette');
$palette = Palette::fromGdImage($resized, 10);
$profiler->end('extract_palette');

$profiler->start('generate_schemes');
$complementary = $palette->complementary();
$analogous = $palette->analogous();
$triadic = $palette->triadic();
$profiler->end('generate_schemes');

print_r($profiler->report());
/* Output:
[
    'load_image' => ['duration_ms' => 120.45, 'memory_mb' => 42.50],
    'resize' => ['duration_ms' => 35.20, 'memory_mb' => 5.25],
    'extract_palette' => ['duration_ms' => 85.60, 'memory_mb' => 0.15],
    'generate_schemes' => ['duration_ms' => 2.30, 'memory_mb' => 0.05],
]
*/
```

---

## Best Practices Summary

### DO:

✅ **Cache aggressively**
```php
$cached = $cache->remember($key, fn() => expensiveOperation());
```

✅ **Resize images before processing**
```php
$resized = resizeToMaxDimension($image, 400);
$palette = Palette::fromGdImage($resized, 10);
```

✅ **Use lazy evaluation**
```php
$palette->lazyLoad();  // Don't compute until needed
```

✅ **Sample pixels, don't analyze all**
```php
$samples = sampleEveryNthPixel($image, 10);  // 100x faster
```

✅ **Free resources immediately**
```php
imagedestroy($image);  // Don't wait for GC
```

✅ **Use appropriate algorithms**
```php
$palette = octreeQuantize($pixels, 8);  // O(n) instead of O(n²)
```

✅ **Profile and benchmark**
```php
$profiler->measure('operation', fn() => doSomething());
```

### DON'T:

❌ **Don't process full-resolution images**
```php
// 4000×3000 image = 36 million operations
```

❌ **Don't repeatedly convert color spaces**
```php
// Cache conversions instead
```

❌ **Don't keep images in memory**
```php
// Process and free immediately
```

❌ **Don't ignore memory limits**
```php
// Check available memory before loading
```

❌ **Don't synchronously process in requests**
```php
// Use queues for expensive operations
```

---

## Related Guides

- [Color Spaces](./color-spaces.md) - Understanding conversion costs
- [Extracting Colors from Images](/guides/extracting-colors/) - Practical implementation
- [Generating Color Schemes](/guides/color-schemes/) - Efficient palette generation
- [Color Manipulation](/guides/color-manipulation/) - Optimization techniques

---

## Further Reading

### Articles
- [High Performance Images](https://web.dev/fast/) - Google Web Fundamentals
- [PHP Performance Tips](https://www.php.net/manual/en/features.gc.performance-considerations.php) - Official PHP docs
- [Image Optimization](https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/image-optimization)

### Tools
- **[Blackfire.io](https://blackfire.io/)** - PHP profiler
- **[XDebug](https://xdebug.org/)** - PHP debugging and profiling
- **[Apache Bench](https://httpd.apache.org/docs/current/programs/ab.html)** - HTTP benchmarking
- **[k6](https://k6.io/)** - Load testing tool

### Libraries
- **[Intervention Image](http://image.intervention.io/)** - Optimized image processing
- **[Imagine](https://imagine.readthedocs.io/)** - Image manipulation library
- **[phpredis](https://github.com/phpredis/phpredis)** - Redis extension for caching

---

## Summary

Performance optimization for color processing requires:

1. **Efficient Algorithms**
   - Choose appropriate color quantization methods
   - Use octree or median cut over k-means
   - Sample pixels instead of analyzing all

2. **Caching Strategies**
   - In-memory caching for repeated operations
   - File/Redis caching for palette extraction
   - HTTP caching for API responses

3. **Memory Management**
   - Free resources immediately
   - Process images one at a time
   - Monitor memory usage
   - Set appropriate limits

4. **Optimization Techniques**
   - Resize images before analysis
   - Memoize expensive conversions
   - Use lazy evaluation
   - Batch operations when possible

5. **Asynchronous Processing**
   - Queue expensive operations
   - Return immediately to users
   - Process in background workers

**Key Principle**: Measure first, optimize second. Profile your specific use case to identify actual bottlenecks before applying optimizations.
