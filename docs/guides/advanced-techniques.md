---
layout: default
title: Advanced Techniques - Color Palette PHP
description: Advanced optimization techniques, performance tips, and extending Color Palette PHP functionality
keywords: advanced techniques, optimization, performance, caching, batch processing, custom extensions
---

# Advanced Techniques

Master advanced optimization techniques, performance tuning, and extension patterns for Color Palette PHP. This guide covers everything from caching strategies to building custom functionality.

<div class="quick-links">
  <a href="#performance">Performance</a> •
  <a href="#caching">Caching</a> •
  <a href="#batch-processing">Batch Processing</a> •
  <a href="#custom-extensions">Custom Extensions</a> •
  <a href="#integration">Integration</a>
</div>

## Performance Optimization

### Image Size Optimization

Reduce processing time by optimizing image sizes:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class OptimizedColorExtractor {
    private int $maxWidth = 800;
    private int $maxHeight = 600;

    public function extract(string $imagePath, int $colorCount = 5): array {
        // Check image dimensions
        $imageInfo = getimagesize($imagePath);
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // If image is too large, use temporary resized version
        if ($width > $this->maxWidth || $height > $this->maxHeight) {
            $imagePath = $this->createResizedCopy($imagePath);
            $shouldDelete = true;
        }

        try {
            $image = ImageFactory::createFromPath($imagePath);
            $extractorFactory = new ColorExtractorFactory();
            $extractor = $extractorFactory->make('gd');
            $palette = $extractor->extract($image, $colorCount);

            $colors = $palette->toArray();
        } finally {
            // Clean up temporary file if created
            if (isset($shouldDelete) && $shouldDelete) {
                @unlink($imagePath);
            }
        }

        return $colors;
    }

    private function createResizedCopy(string $originalPath): string {
        $imageInfo = getimagesize($originalPath);
        $width = $imageInfo[0];
        $height = $imageInfo[1];

        // Calculate new dimensions
        $ratio = min($this->maxWidth / $width, $this->maxHeight / $height);
        $newWidth = (int)($width * $ratio);
        $newHeight = (int)($height * $ratio);

        // Create resized image
        $original = imagecreatefromstring(file_get_contents($originalPath));
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resized, $original, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save to temporary file
        $tempPath = sys_get_temp_dir() . '/' . uniqid('color_') . '.jpg';
        imagejpeg($resized, $tempPath, 85);

        // Cleanup
        imagedestroy($original);
        imagedestroy($resized);

        return $tempPath;
    }
}

// Usage
$extractor = new OptimizedColorExtractor();
$colors = $extractor->extract('large-photo.jpg', 5);

echo "Extracted colors: " . implode(', ', $colors) . "\n";
```

### Memory Management

Handle memory efficiently for large operations:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class MemoryEfficientProcessor {
    private int $memoryLimit;
    private string $originalLimit;

    public function __construct(int $memoryLimitMB = 512) {
        $this->memoryLimit = $memoryLimitMB;
    }

    public function processImage(string $imagePath, callable $callback): mixed {
        // Save original memory limit
        $this->originalLimit = ini_get('memory_limit');

        // Increase memory limit
        ini_set('memory_limit', $this->memoryLimit . 'M');

        try {
            // Process image
            $result = $callback($imagePath);

            // Force garbage collection
            gc_collect_cycles();

            return $result;
        } finally {
            // Restore original limit
            ini_set('memory_limit', $this->originalLimit);
        }
    }

    public function getMemoryUsage(): array {
        return [
            'current' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
            'limit' => ini_get('memory_limit'),
        ];
    }
}

// Usage
$processor = new MemoryEfficientProcessor(512);

$colors = $processor->processImage('huge-image.jpg', function($path) {
    $image = ImageFactory::createFromPath($path);
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');
    $palette = $extractor->extract($image, 5);
    return $palette->toArray();
});

echo "Colors: " . implode(', ', $colors) . "\n";
print_r($processor->getMemoryUsage());
```

### Backend Selection Strategy

Automatically choose the best backend:

```php
<?php

use Farzai\ColorPalette\ColorExtractorFactory;

class SmartBackendSelector {
    public function selectBackend(string $imagePath): string {
        $fileSize = filesize($imagePath);
        $fileSizeMB = $fileSize / 1024 / 1024;

        // Check available extensions
        $hasGd = extension_loaded('gd');
        $hasImagick = extension_loaded('imagick');

        // Large files: prefer Imagick if available
        if ($fileSizeMB > 2 && $hasImagick) {
            return 'imagick';
        }

        // Medium files: GD is sufficient
        if ($fileSizeMB <= 5 && $hasGd) {
            return 'gd';
        }

        // Very large files: require Imagick
        if ($fileSizeMB > 5) {
            if (!$hasImagick) {
                throw new \RuntimeException(
                    'ImageMagick is required for files larger than 5MB'
                );
            }
            return 'imagick';
        }

        // Default to whatever is available
        return $hasGd ? 'gd' : 'imagick';
    }

    public function getRecommendation(string $imagePath): array {
        $backend = $this->selectBackend($imagePath);
        $fileSize = filesize($imagePath);

        return [
            'backend' => $backend,
            'file_size_mb' => round($fileSize / 1024 / 1024, 2),
            'reason' => $this->getReasonForBackend($backend, $fileSize),
        ];
    }

    private function getReasonForBackend(string $backend, int $fileSize): string {
        $sizeMB = $fileSize / 1024 / 1024;

        if ($backend === 'imagick' && $sizeMB > 2) {
            return 'ImageMagick selected for better performance with large images';
        }

        if ($backend === 'gd') {
            return 'GD selected for optimal performance with standard images';
        }

        return 'Selected based on availability';
    }
}

// Usage
$selector = new SmartBackendSelector();
$recommendation = $selector->getRecommendation('photo.jpg');

echo "Recommended backend: {$recommendation['backend']}\n";
echo "File size: {$recommendation['file_size_mb']} MB\n";
echo "Reason: {$recommendation['reason']}\n";
```

## Caching Strategies

### File-Based Cache

Implement simple file-based caching:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class ColorPaletteCache {
    private string $cacheDir;
    private int $ttl;

    public function __construct(string $cacheDir = null, int $ttl = 3600) {
        $this->cacheDir = $cacheDir ?? sys_get_temp_dir() . '/color-cache';
        $this->ttl = $ttl;

        // Create cache directory if it doesn't exist
        if (!is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0755, true);
        }
    }

    public function get(string $imagePath, int $colorCount): ?array {
        $cacheKey = $this->getCacheKey($imagePath, $colorCount);
        $cacheFile = $this->getCacheFile($cacheKey);

        // Check if cache exists and is not expired
        if (file_exists($cacheFile)) {
            $cacheAge = time() - filemtime($cacheFile);

            if ($cacheAge < $this->ttl) {
                $data = json_decode(file_get_contents($cacheFile), true);
                return $data['colors'] ?? null;
            }

            // Cache expired, delete it
            @unlink($cacheFile);
        }

        return null;
    }

    public function set(string $imagePath, int $colorCount, array $colors): void {
        $cacheKey = $this->getCacheKey($imagePath, $colorCount);
        $cacheFile = $this->getCacheFile($cacheKey);

        $data = [
            'colors' => $colors,
            'created_at' => time(),
            'image' => basename($imagePath),
        ];

        file_put_contents($cacheFile, json_encode($data));
    }

    public function extractWithCache(string $imagePath, int $colorCount = 5): array {
        // Try to get from cache
        $cached = $this->get($imagePath, $colorCount);
        if ($cached !== null) {
            return $cached;
        }

        // Extract colors
        $image = ImageFactory::createFromPath($imagePath);
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, $colorCount);
        $colors = $palette->toArray();

        // Store in cache
        $this->set($imagePath, $colorCount, $colors);

        return $colors;
    }

    private function getCacheKey(string $imagePath, int $colorCount): string {
        return md5($imagePath . $colorCount . filemtime($imagePath));
    }

    private function getCacheFile(string $key): string {
        return $this->cacheDir . '/' . $key . '.json';
    }

    public function clear(): int {
        $count = 0;
        $files = glob($this->cacheDir . '/*.json');

        foreach ($files as $file) {
            if (unlink($file)) {
                $count++;
            }
        }

        return $count;
    }

    public function clearExpired(): int {
        $count = 0;
        $files = glob($this->cacheDir . '/*.json');
        $now = time();

        foreach ($files as $file) {
            if (($now - filemtime($file)) >= $this->ttl) {
                if (unlink($file)) {
                    $count++;
                }
            }
        }

        return $count;
    }
}

// Usage
$cache = new ColorPaletteCache('/tmp/color-cache', 3600); // 1 hour TTL

// First call - extracts and caches
$colors1 = $cache->extractWithCache('photo.jpg', 5);
echo "First call (extracted): " . implode(', ', $colors1) . "\n";

// Second call - returns from cache (much faster)
$colors2 = $cache->extractWithCache('photo.jpg', 5);
echo "Second call (cached): " . implode(', ', $colors2) . "\n";

// Clear expired cache entries
$cleared = $cache->clearExpired();
echo "Cleared {$cleared} expired cache entries\n";
```

### Redis Cache

Implement Redis-based caching for distributed systems:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class RedisPaletteCache {
    private \Redis $redis;
    private int $ttl;
    private string $prefix;

    public function __construct(string $host = '127.0.0.1', int $port = 6379, int $ttl = 3600) {
        $this->redis = new \Redis();
        $this->redis->connect($host, $port);
        $this->ttl = $ttl;
        $this->prefix = 'color_palette:';
    }

    public function get(string $imagePath, int $colorCount): ?array {
        $key = $this->getKey($imagePath, $colorCount);
        $data = $this->redis->get($key);

        if ($data === false) {
            return null;
        }

        return json_decode($data, true);
    }

    public function set(string $imagePath, int $colorCount, array $colors): void {
        $key = $this->getKey($imagePath, $colorCount);
        $data = json_encode($colors);

        $this->redis->setex($key, $this->ttl, $data);
    }

    public function extractWithCache(string $imagePath, int $colorCount = 5): array {
        // Try cache first
        $cached = $this->get($imagePath, $colorCount);
        if ($cached !== null) {
            return $cached;
        }

        // Extract colors
        $image = ImageFactory::createFromPath($imagePath);
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, $colorCount);
        $colors = $palette->toArray();

        // Cache result
        $this->set($imagePath, $colorCount, $colors);

        return $colors;
    }

    private function getKey(string $imagePath, int $colorCount): string {
        $hash = md5($imagePath . $colorCount . filemtime($imagePath));
        return $this->prefix . $hash;
    }

    public function clear(): bool {
        $keys = $this->redis->keys($this->prefix . '*');
        if (empty($keys)) {
            return true;
        }

        return $this->redis->del($keys) > 0;
    }
}

// Usage (requires Redis extension and server)
// $cache = new RedisPaletteCache('localhost', 6379, 3600);
// $colors = $cache->extractWithCache('photo.jpg', 5);
```

## Batch Processing

### Parallel Processing

Process multiple images efficiently:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class BatchColorProcessor {
    private ColorExtractorFactory $extractorFactory;
    private ?ColorPaletteCache $cache;

    public function __construct(?ColorPaletteCache $cache = null) {
        $this->extractorFactory = new ColorExtractorFactory();
        $this->cache = $cache;
    }

    public function processBatch(array $imagePaths, int $colorCount = 5): array {
        $results = [];
        $startTime = microtime(true);

        foreach ($imagePaths as $imagePath) {
            try {
                // Use cache if available
                if ($this->cache) {
                    $colors = $this->cache->extractWithCache($imagePath, $colorCount);
                } else {
                    $image = ImageFactory::createFromPath($imagePath);
                    $extractor = $this->extractorFactory->make('gd');
                    $palette = $extractor->extract($image, $colorCount);
                    $colors = $palette->toArray();
                }

                $results[$imagePath] = [
                    'success' => true,
                    'colors' => $colors,
                    'count' => count($colors),
                ];

            } catch (\Exception $e) {
                $results[$imagePath] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }
        }

        $duration = microtime(true) - $startTime;

        return [
            'results' => $results,
            'summary' => [
                'total' => count($imagePaths),
                'successful' => count(array_filter($results, fn($r) => $r['success'])),
                'failed' => count(array_filter($results, fn($r) => !$r['success'])),
                'duration' => round($duration, 2) . 's',
                'avg_per_image' => round($duration / count($imagePaths), 2) . 's',
            ],
        ];
    }

    public function processDirectory(string $directory, int $colorCount = 5): array {
        $imagePaths = glob($directory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
        return $this->processBatch($imagePaths, $colorCount);
    }
}

// Usage
$cache = new ColorPaletteCache();
$processor = new BatchColorProcessor($cache);

// Process multiple images
$images = [
    'photo1.jpg',
    'photo2.jpg',
    'photo3.jpg',
];

$result = $processor->processBatch($images, 5);

echo "Batch Processing Results:\n";
echo "Total: {$result['summary']['total']}\n";
echo "Successful: {$result['summary']['successful']}\n";
echo "Failed: {$result['summary']['failed']}\n";
echo "Duration: {$result['summary']['duration']}\n";
echo "Avg per image: {$result['summary']['avg_per_image']}\n\n";

foreach ($result['results'] as $path => $data) {
    if ($data['success']) {
        echo basename($path) . ": " . implode(', ', $data['colors']) . "\n";
    }
}
```

### Database Integration

Store extracted colors in database:

```php
<?php

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

class ColorPaletteDatabase {
    private \PDO $pdo;

    public function __construct(string $dsn, string $username = '', string $password = '') {
        $this->pdo = new \PDO($dsn, $username, $password);
        $this->createTable();
    }

    private function createTable(): void {
        $sql = "CREATE TABLE IF NOT EXISTS color_palettes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            image_path TEXT NOT NULL,
            image_hash TEXT NOT NULL,
            color_count INTEGER NOT NULL,
            colors TEXT NOT NULL,
            extracted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(image_hash, color_count)
        )";

        $this->pdo->exec($sql);
    }

    public function get(string $imagePath, int $colorCount): ?array {
        $hash = $this->getImageHash($imagePath);

        $stmt = $this->pdo->prepare(
            "SELECT colors FROM color_palettes
             WHERE image_hash = ? AND color_count = ?"
        );

        $stmt->execute([$hash, $colorCount]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return json_decode($result['colors'], true);
        }

        return null;
    }

    public function save(string $imagePath, int $colorCount, array $colors): void {
        $hash = $this->getImageHash($imagePath);

        $stmt = $this->pdo->prepare(
            "INSERT OR REPLACE INTO color_palettes
             (image_path, image_hash, color_count, colors)
             VALUES (?, ?, ?, ?)"
        );

        $stmt->execute([
            $imagePath,
            $hash,
            $colorCount,
            json_encode($colors)
        ]);
    }

    public function extractAndSave(string $imagePath, int $colorCount = 5): array {
        // Check database first
        $cached = $this->get($imagePath, $colorCount);
        if ($cached !== null) {
            return $cached;
        }

        // Extract colors
        $image = ImageFactory::createFromPath($imagePath);
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');
        $palette = $extractor->extract($image, $colorCount);
        $colors = $palette->toArray();

        // Save to database
        $this->save($imagePath, $colorCount, $colors);

        return $colors;
    }

    private function getImageHash(string $imagePath): string {
        return md5_file($imagePath);
    }

    public function search(string $hexColor, float $tolerance = 10): array {
        $stmt = $this->pdo->query("SELECT * FROM color_palettes");
        $results = [];

        $searchColor = \Farzai\ColorPalette\Color::fromHex($hexColor);

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $colors = json_decode($row['colors'], true);

            foreach ($colors as $color) {
                $paletteColor = \Farzai\ColorPalette\Color::fromHex($color);

                // Simple color distance check
                $distance = $this->calculateColorDistance($searchColor, $paletteColor);

                if ($distance <= $tolerance) {
                    $results[] = [
                        'image_path' => $row['image_path'],
                        'matching_color' => $color,
                        'distance' => round($distance, 2),
                    ];
                    break;
                }
            }
        }

        return $results;
    }

    private function calculateColorDistance($color1, $color2): float {
        $rgb1 = $color1->toRgb();
        $rgb2 = $color2->toRgb();

        $rDiff = $rgb1['r'] - $rgb2['r'];
        $gDiff = $rgb1['g'] - $rgb2['g'];
        $bDiff = $rgb1['b'] - $rgb2['b'];

        return sqrt($rDiff * $rDiff + $gDiff * $gDiff + $bDiff * $bDiff);
    }
}

// Usage
$db = new ColorPaletteDatabase('sqlite:colors.db');

// Extract and save
$colors = $db->extractAndSave('photo.jpg', 5);
echo "Extracted: " . implode(', ', $colors) . "\n";

// Search for similar images
$similar = $db->search('#3498db', 20);
echo "\nImages with similar colors:\n";
foreach ($similar as $match) {
    echo "- {$match['image_path']} (distance: {$match['distance']})\n";
}
```

## Custom Extensions

### Custom Color Extractor

Create a custom extraction algorithm:

```php
<?php

namespace App\CustomExtractors;

use Farzai\ColorPalette\Contracts\ColorExtractorInterface;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Color;

class CustomColorExtractor implements ColorExtractorInterface {
    public function extract(ImageInterface $image, int $count = 5): ColorPalette {
        // Your custom extraction logic here
        $colors = $this->customExtractionAlgorithm($image, $count);

        return new ColorPalette($colors);
    }

    private function customExtractionAlgorithm(ImageInterface $image, int $count): array {
        // Example: Sample colors from specific regions
        $colors = [];

        // Implement your custom algorithm
        // This is a simple example that samples from image corners

        $positions = [
            ['x' => 0.25, 'y' => 0.25],      // Top-left
            ['x' => 0.75, 'y' => 0.25],      // Top-right
            ['x' => 0.5, 'y' => 0.5],        // Center
            ['x' => 0.25, 'y' => 0.75],      // Bottom-left
            ['x' => 0.75, 'y' => 0.75],      // Bottom-right
        ];

        foreach (array_slice($positions, 0, $count) as $pos) {
            // Sample color from position
            // Note: This is pseudocode - adapt to your image interface
            $color = new Color(rand(0, 255), rand(0, 255), rand(0, 255));
            $colors[] = $color;
        }

        return $colors;
    }
}
```

### Custom Color Analyzer

Build custom color analysis tools:

```php
<?php

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

class AdvancedColorAnalyzer {
    public function analyzeHarmony(ColorPalette $palette): array {
        $colors = $palette->getColors();
        $hues = array_map(fn($c) => $c->toHsl()['h'], $colors);

        return [
            'is_monochromatic' => $this->isMonochromatic($hues),
            'is_analogous' => $this->isAnalogous($hues),
            'is_complementary' => $this->isComplementary($hues),
            'is_triadic' => $this->isTriadic($hues),
            'harmony_score' => $this->calculateHarmonyScore($hues),
        ];
    }

    private function isMonochromatic(array $hues): bool {
        if (count($hues) < 2) return true;

        $variance = $this->calculateVariance($hues);
        return $variance < 15; // Hues within 15 degrees
    }

    private function isAnalogous(array $hues): bool {
        if (count($hues) < 2) return false;

        sort($hues);
        $maxDiff = max(array_map(
            fn($i) => abs($hues[$i] - $hues[$i + 1]),
            range(0, count($hues) - 2)
        ));

        return $maxDiff <= 60;
    }

    private function isComplementary(array $hues): bool {
        if (count($hues) !== 2) return false;

        $diff = abs($hues[0] - $hues[1]);
        return abs($diff - 180) < 30;
    }

    private function isTriadic(array $hues): bool {
        if (count($hues) !== 3) return false;

        sort($hues);
        $diffs = [
            abs($hues[1] - $hues[0]),
            abs($hues[2] - $hues[1]),
            abs(($hues[0] + 360) - $hues[2]),
        ];

        // Check if roughly 120 degrees apart
        return array_reduce($diffs, fn($carry, $d) => $carry && abs($d - 120) < 30, true);
    }

    private function calculateHarmonyScore(array $hues): float {
        // Simple harmony score based on color spacing
        if (count($hues) < 2) return 1.0;

        $variance = $this->calculateVariance($hues);

        // Lower variance = more harmonious (monochromatic)
        // Higher variance = less harmonious (unless complementary/triadic)

        if ($this->isMonochromatic($hues)) return 0.9;
        if ($this->isComplementary($hues)) return 0.85;
        if ($this->isTriadic($hues)) return 0.8;
        if ($this->isAnalogous($hues)) return 0.75;

        return max(0, 1 - ($variance / 360));
    }

    private function calculateVariance(array $values): float {
        $mean = array_sum($values) / count($values);
        $squaredDiffs = array_map(fn($v) => ($v - $mean) ** 2, $values);
        return sqrt(array_sum($squaredDiffs) / count($values));
    }
}

// Usage
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\ColorPalette;

$colors = [
    new Color(52, 152, 219),
    new Color(41, 128, 185),
    new Color(30, 104, 151),
];

$palette = new ColorPalette($colors);
$analyzer = new AdvancedColorAnalyzer();
$analysis = $analyzer->analyzeHarmony($palette);

echo "Color Harmony Analysis:\n";
echo "Monochromatic: " . ($analysis['is_monochromatic'] ? 'Yes' : 'No') . "\n";
echo "Analogous: " . ($analysis['is_analogous'] ? 'Yes' : 'No') . "\n";
echo "Harmony Score: " . round($analysis['harmony_score'], 2) . "\n";
```

## Integration Patterns

### Laravel Integration

```php
<?php

namespace App\Services;

use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Illuminate\Support\Facades\Cache;

class ColorPaletteService {
    private ColorExtractorFactory $extractorFactory;

    public function __construct() {
        $this->extractorFactory = new ColorExtractorFactory();
    }

    public function extractFromUpload($uploadedFile, int $colorCount = 5): array {
        $path = $uploadedFile->getRealPath();

        return Cache::remember(
            'palette:' . md5_file($path) . ':' . $colorCount,
            3600,
            function() use ($path, $colorCount) {
                $image = ImageFactory::createFromPath($path);
                $extractor = $this->extractorFactory->make(config('colors.backend', 'gd'));
                $palette = $extractor->extract($image, $colorCount);

                return $palette->toArray();
            }
        );
    }
}
```

### API Endpoint

```php
<?php

// In your controller
public function extractColors(Request $request) {
    $request->validate([
        'image' => 'required|image|max:5120',
        'count' => 'integer|min:1|max:20',
    ]);

    $colorCount = $request->input('count', 5);
    $service = new ColorPaletteService();

    $colors = $service->extractFromUpload(
        $request->file('image'),
        $colorCount
    );

    return response()->json([
        'success' => true,
        'colors' => $colors,
        'count' => count($colors),
    ]);
}
```

## Next Steps

- **[Basic Usage Guide](basic-usage)** - Review fundamentals
- **[Color Extraction Guide](color-extraction)** - Master extraction
- **[API Reference](../api/)** - Complete API documentation
- **[Examples](../examples/)** - Browse code examples

## Best Practices Summary

1. **Always cache** extracted palettes for production
2. **Resize large images** before processing
3. **Choose the right backend** based on file size
4. **Handle errors gracefully** with try-catch blocks
5. **Monitor memory usage** for large batches
6. **Use batch processing** for multiple images
7. **Index colors in database** for searchability
8. **Test with real images** from your use case
