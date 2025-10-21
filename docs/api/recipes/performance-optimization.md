---
layout: default
title: Recipe - Performance Optimization
description: Copy-paste solutions for optimizing color extraction and manipulation performance
---

# Recipe: Performance Optimization

Optimize color extraction and manipulation for better performance in production applications.

## Table of Contents

- [Choosing the Right Extractor](#choosing-the-right-extractor)
- [Caching Strategies](#caching-strategies)
- [Memory Management](#memory-management)
- [Batch Processing](#batch-processing)
- [Database Optimization](#database-optimization)
- [Complete Examples](#complete-examples)

---

## Choosing the Right Extractor

### GD vs Imagick Performance Comparison

```php
use Farzai\ColorPalette\ImageFactory;
use Farzai\ColorPalette\ColorExtractorFactory;

function benchmarkExtractors($imagePath, $iterations = 10): array
{
    $results = [];

    foreach (['gd', 'imagick'] as $type) {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make($type);

        for ($i = 0; $i < $iterations; $i++) {
            $image = ImageFactory::createFromPath($imagePath);
            $palette = $extractor->extract($image, 5);
        }

        $results[$type] = [
            'time' => round((microtime(true) - $startTime) * 1000, 2), // ms
            'memory' => round((memory_get_usage(true) - $startMemory) / 1024 / 1024, 2), // MB
            'avg_time_per_extract' => round(((microtime(true) - $startTime) / $iterations) * 1000, 2), // ms
        ];
    }

    return $results;
}

// Usage
$results = benchmarkExtractors('photo.jpg', 10);
print_r($results);
```

**Expected Output:**
```
Array (
    [gd] => Array (
        [time] => 245.67 ms
        [memory] => 4.25 MB
        [avg_time_per_extract] => 24.57 ms
    )
    [imagick] => Array (
        [time] => 389.23 ms
        [memory] => 8.50 MB
        [avg_time_per_extract] => 38.92 ms
    )
)
```

**Recommendation:** Use GD for better performance (2-3x faster, less memory).

---

### Optimized Extraction Settings

```php
function extractOptimized($imagePath, $colorCount = 5): array
{
    $image = ImageFactory::createFromPath($imagePath);

    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd'); // Use GD for speed

    // Optimized settings
    $palette = $extractor->extract($image, $colorCount, [
        'sample_size' => 1000,    // Don't analyze all pixels
        'quality' => 5,            // Lower quality = faster (1-10)
        'min_difference' => 20,    // Reduce processing of similar colors
    ]);

    return array_map(fn($c) => $c->toHex(), $palette->getColors());
}

// Usage
$colors = extractOptimized('large-image.jpg', 5);
print_r($colors);
```

---

## Caching Strategies

### Simple File-Based Cache

```php
class FileColorCache
{
    private $cacheDir;

    public function __construct(string $cacheDir = '/tmp/color-cache')
    {
        $this->cacheDir = $cacheDir;
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }
    }

    public function get(string $key): ?array
    {
        $file = $this->getCacheFile($key);

        if (!file_exists($file)) {
            return null;
        }

        // Check if cache is expired (1 hour)
        if (time() - filemtime($file) > 3600) {
            unlink($file);
            return null;
        }

        $data = file_get_contents($file);
        return json_decode($data, true);
    }

    public function set(string $key, array $data): void
    {
        $file = $this->getCacheFile($key);
        file_put_contents($file, json_encode($data));
    }

    private function getCacheFile(string $key): string
    {
        return $this->cacheDir . '/' . md5($key) . '.json';
    }

    private function getCacheKey(string $imagePath, int $count, array $options): string
    {
        return $imagePath . '_' . $count . '_' . md5(serialize($options));
    }
}

// Usage
class CachedColorExtractor
{
    private $cache;
    private $extractor;

    public function __construct()
    {
        $this->cache = new FileColorCache();
        $extractorFactory = new ColorExtractorFactory();
        $this->extractor = $extractorFactory->make('gd');
    }

    public function extract(string $imagePath, int $count = 5, array $options = []): array
    {
        $cacheKey = $imagePath . '_' . $count . '_' . md5(serialize($options));

        // Try cache first
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Extract colors
        $image = ImageFactory::createFromPath($imagePath);
        $palette = $this->extractor->extract($image, $count, $options);
        $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

        // Cache result
        $this->cache->set($cacheKey, $colors);

        return $colors;
    }
}

// Usage
$cachedExtractor = new CachedColorExtractor();

// First call: extracts from image
$colors1 = $cachedExtractor->extract('photo.jpg', 5); // ~25ms

// Second call: returns from cache
$colors2 = $cachedExtractor->extract('photo.jpg', 5); // ~0.5ms
```

---

### Redis Cache Implementation

```php
class RedisColorCache
{
    private $redis;
    private $ttl = 3600; // 1 hour

    public function __construct(string $host = '127.0.0.1', int $port = 6379)
    {
        $this->redis = new Redis();
        $this->redis->connect($host, $port);
    }

    public function get(string $key): ?array
    {
        $data = $this->redis->get("colors:$key");

        if ($data === false) {
            return null;
        }

        return json_decode($data, true);
    }

    public function set(string $key, array $data): void
    {
        $this->redis->setex("colors:$key", $this->ttl, json_encode($data));
    }

    public function clear(): void
    {
        $keys = $this->redis->keys('colors:*');
        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }
}

// Usage with Redis
class RedisCachedExtractor
{
    private $cache;
    private $extractor;

    public function __construct(RedisColorCache $cache)
    {
        $this->cache = $cache;
        $extractorFactory = new ColorExtractorFactory();
        $this->extractor = $extractorFactory->make('gd');
    }

    public function extract(string $imagePath, int $count = 5): array
    {
        $cacheKey = md5_file($imagePath) . "_$count";

        // Try cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        // Extract
        $image = ImageFactory::createFromPath($imagePath);
        $palette = $this->extractor->extract($image, $count, [
            'sample_size' => 1000,
        ]);

        $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

        // Cache
        $this->cache->set($cacheKey, $colors);

        return $colors;
    }
}
```

---

### PSR-6 Cache Implementation

```php
use Psr\Cache\CacheItemPoolInterface;

class PSR6ColorExtractor
{
    private $cache;
    private $extractor;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
        $extractorFactory = new ColorExtractorFactory();
        $this->extractor = $extractorFactory->make('gd');
    }

    public function extract(string $imagePath, int $count = 5): array
    {
        $cacheKey = 'colors_' . md5_file($imagePath) . '_' . $count;
        $cacheItem = $this->cache->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        // Extract colors
        $image = ImageFactory::createFromPath($imagePath);
        $palette = $this->extractor->extract($image, $count, [
            'sample_size' => 1000,
        ]);

        $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

        // Cache for 1 hour
        $cacheItem->set($colors);
        $cacheItem->expiresAfter(3600);
        $this->cache->save($cacheItem);

        return $colors;
    }
}
```

---

## Memory Management

### Memory-Efficient Image Processing

```php
function extractWithLowMemory($imagePath, $colorCount = 5): array
{
    // Set memory limit
    $originalLimit = ini_get('memory_limit');
    ini_set('memory_limit', '128M');

    try {
        // Use GD (more memory efficient)
        $extractorFactory = new ColorExtractorFactory();
        $extractor = $extractorFactory->make('gd');

        $image = ImageFactory::createFromPath($imagePath);

        // Use minimal sample size
        $palette = $extractor->extract($image, $colorCount, [
            'sample_size' => 500,  // Very small sample
            'quality' => 3,         // Lower quality
        ]);

        $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

        // Free memory
        unset($image, $palette, $extractor);
        gc_collect_cycles();

        return $colors;

    } finally {
        // Restore original limit
        ini_set('memory_limit', $originalLimit);
    }
}

// Usage
$colors = extractWithLowMemory('huge-image.jpg', 5);
```

---

### Process Large Images in Chunks

```php
function extractFromLargeImage($imagePath, $colorCount = 5): array
{
    // Resize large images before processing
    $maxDimension = 800;

    $imageInfo = getimagesize($imagePath);
    $width = $imageInfo[0];
    $height = $imageInfo[1];

    // Check if resize is needed
    if ($width > $maxDimension || $height > $maxDimension) {
        // Calculate new dimensions
        if ($width > $height) {
            $newWidth = $maxDimension;
            $newHeight = (int)($height * ($maxDimension / $width));
        } else {
            $newHeight = $maxDimension;
            $newWidth = (int)($width * ($maxDimension / $height));
        }

        // Create resized image
        $sourceImage = imagecreatefromjpeg($imagePath);
        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($resizedImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'color_');
        imagejpeg($resizedImage, $tempFile, 85);

        // Clean up
        imagedestroy($sourceImage);
        imagedestroy($resizedImage);

        $imagePath = $tempFile;
    }

    // Extract colors
    $image = ImageFactory::createFromPath($imagePath);
    $extractorFactory = new ColorExtractorFactory();
    $extractor = $extractorFactory->make('gd');
    $palette = $extractor->extract($image, $colorCount);

    $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

    // Cleanup temp file
    if (isset($tempFile)) {
        unlink($tempFile);
    }

    return $colors;
}
```

---

### Monitor Memory Usage

```php
class MemoryMonitor
{
    private $checkpoints = [];

    public function checkpoint(string $label): void
    {
        $this->checkpoints[$label] = [
            'memory' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'time' => microtime(true),
        ];
    }

    public function report(): array
    {
        $report = [];
        $previous = null;

        foreach ($this->checkpoints as $label => $data) {
            $report[$label] = [
                'memory_mb' => round($data['memory'] / 1024 / 1024, 2),
                'peak_mb' => round($data['peak'] / 1024 / 1024, 2),
            ];

            if ($previous) {
                $report[$label]['delta_mb'] = round(
                    ($data['memory'] - $previous['memory']) / 1024 / 1024,
                    2
                );
                $report[$label]['time_ms'] = round(
                    ($data['time'] - $previous['time']) * 1000,
                    2
                );
            }

            $previous = $data;
        }

        return $report;
    }
}

// Usage
$monitor = new MemoryMonitor();

$monitor->checkpoint('start');

$image = ImageFactory::createFromPath('photo.jpg');
$monitor->checkpoint('image_loaded');

$extractorFactory = new ColorExtractorFactory();
$extractor = $extractorFactory->make('gd');
$palette = $extractor->extract($image, 5);
$monitor->checkpoint('colors_extracted');

unset($image, $palette, $extractor);
gc_collect_cycles();
$monitor->checkpoint('cleanup');

print_r($monitor->report());
```

**Expected Output:**
```
Array (
    [start] => Array (
        [memory_mb] => 2.00
        [peak_mb] => 2.00
    )
    [image_loaded] => Array (
        [memory_mb] => 6.25
        [peak_mb] => 6.25
        [delta_mb] => 4.25
        [time_ms] => 12.34
    )
    [colors_extracted] => Array (
        [memory_mb] => 8.50
        [peak_mb] => 8.50
        [delta_mb] => 2.25
        [time_ms] => 23.45
    )
    [cleanup] => Array (
        [memory_mb] => 2.50
        [peak_mb] => 8.50
        [delta_mb] => -6.00
        [time_ms] => 1.23
    )
)
```

---

## Batch Processing

### Process Multiple Images Efficiently

```php
class BatchColorProcessor
{
    private $extractor;
    private $cache;

    public function __construct(?CacheInterface $cache = null)
    {
        $extractorFactory = new ColorExtractorFactory();
        $this->extractor = $extractorFactory->make('gd');
        $this->cache = $cache;
    }

    public function processBatch(array $imagePaths, int $colorsPerImage = 5): array
    {
        $results = [];
        $processed = 0;
        $total = count($imagePaths);

        foreach ($imagePaths as $imagePath) {
            $cacheKey = md5_file($imagePath) . "_$colorsPerImage";

            // Try cache
            if ($this->cache && ($cached = $this->cache->get($cacheKey))) {
                $results[$imagePath] = $cached;
                $processed++;
                continue;
            }

            try {
                // Extract colors
                $image = ImageFactory::createFromPath($imagePath);
                $palette = $this->extractor->extract($image, $colorsPerImage, [
                    'sample_size' => 1000,
                ]);

                $colors = array_map(fn($c) => $c->toHex(), $palette->getColors());

                $results[$imagePath] = [
                    'success' => true,
                    'colors' => $colors,
                ];

                // Cache
                if ($this->cache) {
                    $this->cache->set($cacheKey, $results[$imagePath]);
                }

                // Free memory
                unset($image, $palette);
                if ($processed % 10 === 0) {
                    gc_collect_cycles();
                }

            } catch (\Exception $e) {
                $results[$imagePath] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                ];
            }

            $processed++;

            // Progress callback
            if ($processed % 10 === 0) {
                echo "Processed $processed/$total images\n";
            }
        }

        return $results;
    }
}

// Usage
$processor = new BatchColorProcessor($cache);
$images = glob('photos/*.jpg');
$results = $processor->processBatch($images, 5);

echo "Successfully processed: " . count(array_filter($results, fn($r) => $r['success'])) . "\n";
```

---

### Parallel Processing with Multiple Workers

```php
class ParallelColorProcessor
{
    private $workerCount;

    public function __construct(int $workerCount = 4)
    {
        $this->workerCount = $workerCount;
    }

    public function processInParallel(array $imagePaths, int $colorsPerImage = 5): array
    {
        $chunks = array_chunk($imagePaths, ceil(count($imagePaths) / $this->workerCount));
        $results = [];

        // Process each chunk (in real implementation, use actual parallel processing)
        foreach ($chunks as $chunkIndex => $chunk) {
            echo "Worker $chunkIndex processing " . count($chunk) . " images\n";

            foreach ($chunk as $imagePath) {
                try {
                    $colors = extractOptimized($imagePath, $colorsPerImage);
                    $results[$imagePath] = [
                        'success' => true,
                        'colors' => $colors,
                        'worker' => $chunkIndex,
                    ];
                } catch (\Exception $e) {
                    $results[$imagePath] = [
                        'success' => false,
                        'error' => $e->getMessage(),
                        'worker' => $chunkIndex,
                    ];
                }
            }
        }

        return $results;
    }
}

// Usage
$processor = new ParallelColorProcessor(4);
$images = glob('photos/*.jpg');
$results = $processor->processInParallel($images, 5);
```

---

## Database Optimization

### Store Extracted Colors in Database

```php
// Migration
/*
CREATE TABLE image_colors (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255) NOT NULL,
    image_hash VARCHAR(32) NOT NULL,
    color_count INT NOT NULL,
    colors JSON NOT NULL,
    extracted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_image_hash (image_hash),
    INDEX idx_extracted_at (extracted_at)
);
*/

class DatabaseColorCache
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function get(string $imagePath, int $colorCount): ?array
    {
        $hash = md5_file($imagePath);

        $stmt = $this->pdo->prepare("
            SELECT colors
            FROM image_colors
            WHERE image_hash = ? AND color_count = ?
            AND extracted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
            LIMIT 1
        ");

        $stmt->execute([$hash, $colorCount]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return json_decode($result['colors'], true);
        }

        return null;
    }

    public function set(string $imagePath, int $colorCount, array $colors): void
    {
        $hash = md5_file($imagePath);

        $stmt = $this->pdo->prepare("
            INSERT INTO image_colors (image_path, image_hash, color_count, colors)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                colors = VALUES(colors),
                extracted_at = CURRENT_TIMESTAMP
        ");

        $stmt->execute([
            $imagePath,
            $hash,
            $colorCount,
            json_encode($colors),
        ]);
    }

    public function cleanup(int $daysOld = 7): int
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM image_colors
            WHERE extracted_at < DATE_SUB(NOW(), INTERVAL ? DAY)
        ");

        $stmt->execute([$daysOld]);

        return $stmt->rowCount();
    }
}

// Usage
$pdo = new PDO('mysql:host=localhost;dbname=myapp', 'user', 'pass');
$cache = new DatabaseColorCache($pdo);

// Try cache
$colors = $cache->get('photo.jpg', 5);

if ($colors === null) {
    // Extract
    $colors = extractOptimized('photo.jpg', 5);

    // Store
    $cache->set('photo.jpg', 5, $colors);
}
```

---

## Complete Examples

### Example 1: Production-Ready Color Extraction Service

```php
class ColorExtractionService
{
    private $extractor;
    private $cache;
    private $monitor;

    public function __construct(
        CacheInterface $cache,
        ?MemoryMonitor $monitor = null
    ) {
        $extractorFactory = new ColorExtractorFactory();
        $this->extractor = $extractorFactory->make('gd');
        $this->cache = $cache;
        $this->monitor = $monitor;
    }

    public function extract(
        string $imagePath,
        int $colorCount = 5,
        array $options = []
    ): array {
        // Monitor
        if ($this->monitor) {
            $this->monitor->checkpoint('start');
        }

        // Validate
        if (!file_exists($imagePath)) {
            throw new \InvalidArgumentException("Image not found: $imagePath");
        }

        // Cache key
        $cacheKey = $this->getCacheKey($imagePath, $colorCount, $options);

        // Try cache
        $cached = $this->cache->get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        if ($this->monitor) {
            $this->monitor->checkpoint('cache_checked');
        }

        // Optimize options
        $options = array_merge([
            'sample_size' => 1000,
            'quality' => 5,
            'min_difference' => 20,
        ], $options);

        // Extract
        $image = ImageFactory::createFromPath($imagePath);

        if ($this->monitor) {
            $this->monitor->checkpoint('image_loaded');
        }

        $palette = $this->extractor->extract($image, $colorCount, $options);

        if ($this->monitor) {
            $this->monitor->checkpoint('colors_extracted');
        }

        // Format result
        $result = [
            'colors' => array_map(fn($c) => [
                'hex' => $c->toHex(),
                'rgb' => $c->toRgb(),
            ], $palette->getColors()),
            'dominant' => $palette->getDominantColor()->toHex(),
            'metadata' => [
                'image' => basename($imagePath),
                'extracted_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Cache
        $this->cache->set($cacheKey, $result, 3600);

        // Cleanup
        unset($image, $palette);
        gc_collect_cycles();

        if ($this->monitor) {
            $this->monitor->checkpoint('cleanup');
        }

        return $result;
    }

    private function getCacheKey(string $imagePath, int $count, array $options): string
    {
        return 'colors_' . md5_file($imagePath) . '_' . $count . '_' . md5(serialize($options));
    }

    public function getPerformanceReport(): ?array
    {
        return $this->monitor ? $this->monitor->report() : null;
    }
}

// Usage
$cache = new FileColorCache();
$monitor = new MemoryMonitor();
$service = new ColorExtractionService($cache, $monitor);

$result = $service->extract('photo.jpg', 5);
print_r($result);

echo "\nPerformance Report:\n";
print_r($service->getPerformanceReport());
```

---

### Example 2: High-Performance API Endpoint

```php
// POST /api/extract-colors-fast
// Body: { "image_url": "https://example.com/image.jpg", "count": 5 }

class FastColorExtractionController
{
    private $service;
    private $rateLimiter;

    public function __construct(ColorExtractionService $service, RateLimiter $rateLimiter)
    {
        $this->service = $service;
        $this->rateLimiter = $rateLimiter;
    }

    public function handle($request)
    {
        $startTime = microtime(true);

        try {
            // Rate limiting
            $clientId = $request->ip();
            if (!$this->rateLimiter->attempt($clientId, 10, 60)) { // 10 requests per minute
                return response()->json([
                    'error' => 'Rate limit exceeded'
                ], 429);
            }

            $imageUrl = $request->input('image_url');
            $count = min($request->input('count', 5), 10); // Max 10 colors

            // Download image
            $tempFile = $this->downloadImage($imageUrl);

            // Extract colors
            $result = $this->service->extract($tempFile, $count);

            // Cleanup
            unlink($tempFile);

            // Add performance metrics
            $result['performance'] = [
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
                'cached' => isset($result['_cached']) ? $result['_cached'] : false,
            ];

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'processing_time_ms' => round((microtime(true) - $startTime) * 1000, 2),
            ], 400);
        }
    }

    private function downloadImage(string $url): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'color_');

        $ch = curl_init($url);
        $fp = fopen($tempFile, 'wb');

        curl_setopt_array($ch, [
            CURLOPT_FILE => $fp,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        fclose($fp);
        curl_close($ch);

        if (!$result || $httpCode !== 200) {
            unlink($tempFile);
            throw new \Exception('Failed to download image');
        }

        return $tempFile;
    }
}
```

---

## Related Recipes

- [Extracting Dominant Colors](extracting-dominant-colors) - Basic extraction techniques
- [Creating Color Schemes](creating-color-schemes) - Generate schemes efficiently
- [Checking Accessibility](checking-accessibility) - Fast accessibility checks

---

## See Also

- [ColorExtractor Reference](../reference/color-extractor)
- [ImageLoader Reference](../reference/image-loader)
- [PHP Performance Best Practices](https://www.php.net/manual/en/features.gc.performance-considerations.php)
