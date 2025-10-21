---
layout: default
title: "Image Color Analysis Tool"
parent: Tutorials
nav_order: 2
description: "Build an image color analysis tool that extracts dominant colors and generates design recommendations"
---

# Image Color Analysis Tool
{: .no_toc }

Create a professional image color analysis tool that extracts dominant colors from images and generates palette recommendations for design projects.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Introduction

In this tutorial, you'll build an **Image Color Analysis Tool** that:

- Extracts dominant colors from uploaded images
- Analyzes color distribution and frequency
- Generates complementary palettes based on extracted colors
- Provides design recommendations
- Exports color schemes for use in design tools
- Creates mood boards with color swatches

This tool is perfect for designers, developers, and anyone who needs to create color palettes from images.

**What you'll learn:**
- Image processing with PHP GD library
- Color quantization algorithms
- Dominant color extraction techniques
- K-means clustering for color analysis
- Building file upload systems
- Creating visual color analytics

---

## Prerequisites

Before starting, ensure you have:

- **PHP 8.0 or higher** with GD extension enabled
- **Composer** for dependency management
- **Basic knowledge** of PHP, HTML, CSS, and JavaScript
- **Understanding** of image processing concepts (helpful but not required)
- **Web server** with file upload capabilities

**Required PHP extensions:**
```bash
# Check if GD is installed
php -m | grep gd

# If not installed (Ubuntu/Debian)
sudo apt-get install php-gd

# If not installed (macOS with Homebrew)
brew install php
```

**Required packages:**
```bash
composer require farzai/color-palette-php
composer require intervention/image
```

---

## Project Structure

```
image-color-analyzer/
├── composer.json
├── public/
│   ├── index.php              # Main application
│   ├── api/
│   │   ├── upload.php         # Image upload handler
│   │   ├── analyze.php        # Color analysis endpoint
│   │   └── generate-palette.php # Palette generation
│   ├── uploads/               # Uploaded images directory
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css      # Application styles
│   │   └── js/
│   │       └── app.js         # Frontend logic
│   └── cache/                 # Analysis cache
├── src/
│   ├── ImageAnalyzer.php      # Core image analysis
│   ├── ColorExtractor.php     # Color extraction logic
│   ├── PaletteGenerator.php   # Palette generation
│   └── DesignRecommender.php  # Design recommendations
└── examples/
    └── sample-images/         # Sample images for testing
```

---

## Step 1: Project Setup

### 1.1 Initialize the Project

Create your project structure:

```bash
mkdir image-color-analyzer
cd image-color-analyzer
composer init
```

### 1.2 Configure Composer

Update `composer.json`:

```json
{
    "name": "yourname/image-color-analyzer",
    "description": "Extract and analyze colors from images",
    "type": "project",
    "require": {
        "php": ">=8.0",
        "ext-gd": "*",
        "farzai/color-palette-php": "^1.0",
        "intervention/image": "^2.7"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

### 1.3 Install Dependencies

```bash
composer install
```

### 1.4 Create Directory Structure

```bash
mkdir -p public/{api,uploads,assets/{css,js},cache}
mkdir -p src examples/sample-images
chmod 755 public/uploads public/cache
```

---

## Step 2: Building the Color Extractor

### 2.1 Create ColorExtractor Class

Create `src/ColorExtractor.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;

class ColorExtractor
{
    private const DEFAULT_COLORS = 5;
    private const SAMPLE_SIZE = 10000; // Max pixels to analyze

    /**
     * Extract dominant colors from an image
     *
     * @param string $imagePath Path to image file
     * @param int $colorCount Number of colors to extract
     * @return array Array of Color objects with frequency
     */
    public function extractDominantColors(string $imagePath, int $colorCount = self::DEFAULT_COLORS): array
    {
        if (!file_exists($imagePath)) {
            throw new \InvalidArgumentException("Image file not found: {$imagePath}");
        }

        $image = $this->loadImage($imagePath);
        if (!$image) {
            throw new \RuntimeException("Failed to load image");
        }

        $colors = $this->sampleColors($image, self::SAMPLE_SIZE);
        $dominantColors = $this->kMeansClustering($colors, $colorCount);

        imagedestroy($image);

        return $dominantColors;
    }

    /**
     * Load image and create GD resource
     */
    private function loadImage(string $path)
    {
        $imageInfo = getimagesize($path);

        return match($imageInfo['mime']) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/gif' => imagecreatefromgif($path),
            'image/webp' => imagecreatefromwebp($path),
            default => false
        };
    }

    /**
     * Sample colors from image
     */
    private function sampleColors($image, int $maxSamples): array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $totalPixels = $width * $height;

        // Calculate step size for sampling
        $step = max(1, (int)sqrt($totalPixels / $maxSamples));

        $colors = [];

        for ($y = 0; $y < $height; $y += $step) {
            for ($x = 0; $x < $width; $x += $step) {
                $rgb = imagecolorat($image, $x, $y);
                $colors[] = [
                    'r' => ($rgb >> 16) & 0xFF,
                    'g' => ($rgb >> 8) & 0xFF,
                    'b' => $rgb & 0xFF
                ];
            }
        }

        return $colors;
    }

    /**
     * K-means clustering algorithm for color quantization
     */
    private function kMeansClustering(array $colors, int $k, int $maxIterations = 20): array
    {
        // Initialize random centroids
        $centroids = $this->initializeCentroids($colors, $k);

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            // Assign colors to nearest centroid
            $clusters = array_fill(0, $k, []);

            foreach ($colors as $color) {
                $nearestCentroid = $this->findNearestCentroid($color, $centroids);
                $clusters[$nearestCentroid][] = $color;
            }

            // Update centroids
            $newCentroids = [];
            foreach ($clusters as $cluster) {
                if (empty($cluster)) {
                    $newCentroids[] = $centroids[array_rand($centroids)];
                } else {
                    $newCentroids[] = $this->calculateCentroid($cluster);
                }
            }

            // Check convergence
            if ($this->centroidsEqual($centroids, $newCentroids)) {
                break;
            }

            $centroids = $newCentroids;
        }

        // Calculate frequency and convert to Color objects
        $result = [];
        $totalColors = count($colors);

        foreach ($clusters as $index => $cluster) {
            if (empty($cluster)) continue;

            $centroid = $centroids[$index];
            $frequency = count($cluster) / $totalColors;

            $result[] = [
                'color' => Color::fromRgb(
                    (int)round($centroid['r']),
                    (int)round($centroid['g']),
                    (int)round($centroid['b'])
                ),
                'frequency' => $frequency,
                'percentage' => round($frequency * 100, 2)
            ];
        }

        // Sort by frequency
        usort($result, fn($a, $b) => $b['frequency'] <=> $a['frequency']);

        return $result;
    }

    /**
     * Initialize random centroids from color samples
     */
    private function initializeCentroids(array $colors, int $k): array
    {
        $centroids = [];
        $indices = array_rand($colors, min($k, count($colors)));

        if (!is_array($indices)) {
            $indices = [$indices];
        }

        foreach ($indices as $index) {
            $centroids[] = $colors[$index];
        }

        return $centroids;
    }

    /**
     * Find nearest centroid for a color
     */
    private function findNearestCentroid(array $color, array $centroids): int
    {
        $minDistance = PHP_FLOAT_MAX;
        $nearest = 0;

        foreach ($centroids as $index => $centroid) {
            $distance = $this->colorDistance($color, $centroid);
            if ($distance < $minDistance) {
                $minDistance = $distance;
                $nearest = $index;
            }
        }

        return $nearest;
    }

    /**
     * Calculate Euclidean distance between two colors
     */
    private function colorDistance(array $color1, array $color2): float
    {
        return sqrt(
            pow($color1['r'] - $color2['r'], 2) +
            pow($color1['g'] - $color2['g'], 2) +
            pow($color1['b'] - $color2['b'], 2)
        );
    }

    /**
     * Calculate average color (centroid) of a cluster
     */
    private function calculateCentroid(array $cluster): array
    {
        $count = count($cluster);
        $sum = ['r' => 0, 'g' => 0, 'b' => 0];

        foreach ($cluster as $color) {
            $sum['r'] += $color['r'];
            $sum['g'] += $color['g'];
            $sum['b'] += $color['b'];
        }

        return [
            'r' => $sum['r'] / $count,
            'g' => $sum['g'] / $count,
            'b' => $sum['b'] / $count
        ];
    }

    /**
     * Check if centroids have converged
     */
    private function centroidsEqual(array $centroids1, array $centroids2, float $tolerance = 1.0): bool
    {
        foreach ($centroids1 as $index => $centroid1) {
            if ($this->colorDistance($centroid1, $centroids2[$index]) > $tolerance) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get color histogram (color distribution)
     */
    public function getColorHistogram(string $imagePath, int $buckets = 16): array
    {
        $image = $this->loadImage($imagePath);
        $width = imagesx($image);
        $height = imagesy($image);

        $histogram = [
            'red' => array_fill(0, $buckets, 0),
            'green' => array_fill(0, $buckets, 0),
            'blue' => array_fill(0, $buckets, 0)
        ];

        $bucketSize = 256 / $buckets;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                $histogram['red'][(int)($r / $bucketSize)]++;
                $histogram['green'][(int)($g / $bucketSize)]++;
                $histogram['blue'][(int)($b / $bucketSize)]++;
            }
        }

        imagedestroy($image);

        return $histogram;
    }
}
```

### 2.2 Create ImageAnalyzer Class

Create `src/ImageAnalyzer.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;

class ImageAnalyzer
{
    private ColorExtractor $colorExtractor;

    public function __construct()
    {
        $this->colorExtractor = new ColorExtractor();
    }

    /**
     * Perform complete image color analysis
     */
    public function analyze(string $imagePath, array $options = []): array
    {
        $colorCount = $options['colorCount'] ?? 5;
        $includeHistogram = $options['includeHistogram'] ?? false;

        $dominantColors = $this->colorExtractor->extractDominantColors($imagePath, $colorCount);

        $analysis = [
            'dominantColors' => $this->formatDominantColors($dominantColors),
            'statistics' => $this->calculateStatistics($dominantColors),
            'mood' => $this->analyzeMood($dominantColors),
            'temperature' => $this->analyzeTemperature($dominantColors)
        ];

        if ($includeHistogram) {
            $analysis['histogram'] = $this->colorExtractor->getColorHistogram($imagePath);
        }

        return $analysis;
    }

    /**
     * Format dominant colors for output
     */
    private function formatDominantColors(array $dominantColors): array
    {
        $formatted = [];

        foreach ($dominantColors as $colorData) {
            $color = $colorData['color'];
            $formatted[] = [
                'hex' => $color->toHex(),
                'rgb' => $color->toRgb(),
                'hsl' => $color->toHsl(),
                'percentage' => $colorData['percentage'],
                'name' => $this->getColorName($color)
            ];
        }

        return $formatted;
    }

    /**
     * Calculate color statistics
     */
    private function calculateStatistics(array $dominantColors): array
    {
        $hues = [];
        $saturations = [];
        $lightnesses = [];

        foreach ($dominantColors as $colorData) {
            $hsl = $colorData['color']->toHsl();
            $hues[] = $hsl['h'];
            $saturations[] = $hsl['s'];
            $lightnesses[] = $hsl['l'];
        }

        return [
            'averageHue' => array_sum($hues) / count($hues),
            'averageSaturation' => array_sum($saturations) / count($saturations),
            'averageLightness' => array_sum($lightnesses) / count($lightnesses),
            'colorDiversity' => $this->calculateColorDiversity($dominantColors)
        ];
    }

    /**
     * Analyze color mood/emotion
     */
    private function analyzeMood(array $dominantColors): array
    {
        $moods = [];
        $totalWeight = 0;

        foreach ($dominantColors as $colorData) {
            $hsl = $colorData['color']->toHsl();
            $weight = $colorData['frequency'];
            $totalWeight += $weight;

            $colorMood = $this->getColorMood($hsl);
            foreach ($colorMood as $mood => $score) {
                if (!isset($moods[$mood])) {
                    $moods[$mood] = 0;
                }
                $moods[$mood] += $score * $weight;
            }
        }

        // Normalize scores
        foreach ($moods as $mood => $score) {
            $moods[$mood] = round($score / $totalWeight * 100, 2);
        }

        arsort($moods);

        return [
            'primary' => array_key_first($moods),
            'scores' => $moods
        ];
    }

    /**
     * Analyze color temperature
     */
    private function analyzeTemperature(array $dominantColors): string
    {
        $warmth = 0;

        foreach ($dominantColors as $colorData) {
            $hsl = $colorData['color']->toHsl();
            $hue = $hsl['h'];
            $weight = $colorData['frequency'];

            // Warm colors: red, orange, yellow (0-60 and 300-360)
            if ($hue < 60 || $hue > 300) {
                $warmth += $weight;
            }
            // Cool colors: blue, green (120-300)
            elseif ($hue > 120 && $hue < 300) {
                $warmth -= $weight;
            }
        }

        if ($warmth > 0.2) return 'warm';
        if ($warmth < -0.2) return 'cool';
        return 'neutral';
    }

    /**
     * Get basic color name
     */
    private function getColorName(Color $color): string
    {
        $hsl = $color->toHsl();
        $hue = $hsl['h'];
        $saturation = $hsl['s'];
        $lightness = $hsl['l'];

        // Check for achromatic colors
        if ($saturation < 10) {
            if ($lightness > 90) return 'white';
            if ($lightness < 10) return 'black';
            return 'gray';
        }

        // Determine color name based on hue
        return match(true) {
            $hue < 15 || $hue >= 345 => 'red',
            $hue < 45 => 'orange',
            $hue < 75 => 'yellow',
            $hue < 165 => 'green',
            $hue < 195 => 'cyan',
            $hue < 255 => 'blue',
            $hue < 285 => 'purple',
            default => 'pink'
        };
    }

    /**
     * Get mood associations for a color
     */
    private function getColorMood(array $hsl): array
    {
        $hue = $hsl['h'];
        $saturation = $hsl['s'];
        $lightness = $hsl['l'];

        $moods = [];

        // Red family (0-15, 345-360)
        if ($hue < 15 || $hue >= 345) {
            $moods = ['energetic' => 0.8, 'passionate' => 0.9, 'bold' => 0.7];
        }
        // Orange (15-45)
        elseif ($hue < 45) {
            $moods = ['cheerful' => 0.8, 'energetic' => 0.7, 'warm' => 0.9];
        }
        // Yellow (45-75)
        elseif ($hue < 75) {
            $moods = ['cheerful' => 0.9, 'optimistic' => 0.8, 'warm' => 0.6];
        }
        // Green (75-165)
        elseif ($hue < 165) {
            $moods = ['calm' => 0.8, 'natural' => 0.9, 'balanced' => 0.7];
        }
        // Blue (165-255)
        elseif ($hue < 255) {
            $moods = ['calm' => 0.9, 'professional' => 0.8, 'cool' => 0.9];
        }
        // Purple (255-285)
        elseif ($hue < 285) {
            $moods = ['creative' => 0.9, 'mysterious' => 0.7, 'luxurious' => 0.8];
        }
        // Pink/Magenta (285-345)
        else {
            $moods = ['romantic' => 0.9, 'playful' => 0.7, 'energetic' => 0.6];
        }

        // Adjust based on saturation
        if ($saturation < 30) {
            $moods['subtle'] = 0.8;
            $moods['professional'] = 0.7;
        }

        // Adjust based on lightness
        if ($lightness > 70) {
            $moods['light'] = 0.8;
            $moods['airy'] = 0.7;
        } elseif ($lightness < 30) {
            $moods['dramatic'] = 0.8;
            $moods['mysterious'] = 0.7;
        }

        return $moods;
    }

    /**
     * Calculate color diversity score
     */
    private function calculateColorDiversity(array $dominantColors): float
    {
        if (count($dominantColors) < 2) {
            return 0.0;
        }

        $totalDistance = 0;
        $comparisons = 0;

        for ($i = 0; $i < count($dominantColors); $i++) {
            for ($j = $i + 1; $j < count($dominantColors); $j++) {
                $hsl1 = $dominantColors[$i]['color']->toHsl();
                $hsl2 = $dominantColors[$j]['color']->toHsl();

                // Calculate hue distance (circular)
                $hueDiff = abs($hsl1['h'] - $hsl2['h']);
                if ($hueDiff > 180) {
                    $hueDiff = 360 - $hueDiff;
                }

                $totalDistance += $hueDiff / 180; // Normalize to 0-1
                $comparisons++;
            }
        }

        return round($totalDistance / $comparisons * 100, 2);
    }
}
```

---

## Step 3: Creating the Palette Generator

Create `src/PaletteGenerator.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Generator\HarmonyGenerator;

class PaletteGenerator
{
    private HarmonyGenerator $harmonyGenerator;

    public function __construct()
    {
        $this->harmonyGenerator = new HarmonyGenerator();
    }

    /**
     * Generate palettes from extracted colors
     */
    public function generateFromImage(array $dominantColors): array
    {
        $palettes = [];

        // Use the most dominant color as base
        $baseColor = $dominantColors[0]['color'];

        $palettes['complementary'] = $this->generateComplementary($baseColor);
        $palettes['analogous'] = $this->generateAnalogous($baseColor);
        $palettes['triadic'] = $this->generateTriadic($baseColor);
        $palettes['monochromatic'] = $this->generateMonochromatic($baseColor);
        $palettes['extracted'] = $this->formatExtractedPalette($dominantColors);

        return $palettes;
    }

    private function generateComplementary(Color $baseColor): array
    {
        $palette = $this->harmonyGenerator->complementary($baseColor);
        return $this->formatPalette($palette, 'Complementary');
    }

    private function generateAnalogous(Color $baseColor): array
    {
        $palette = $this->harmonyGenerator->analogous($baseColor);
        return $this->formatPalette($palette, 'Analogous');
    }

    private function generateTriadic(Color $baseColor): array
    {
        $palette = $this->harmonyGenerator->triadic($baseColor);
        return $this->formatPalette($palette, 'Triadic');
    }

    private function generateMonochromatic(Color $baseColor): array
    {
        $palette = $this->harmonyGenerator->monochromatic($baseColor, 5);
        return $this->formatPalette($palette, 'Monochromatic');
    }

    private function formatExtractedPalette(array $dominantColors): array
    {
        return [
            'name' => 'Extracted from Image',
            'colors' => array_map(fn($c) => $c['color']->toHex(), $dominantColors),
            'description' => 'Colors extracted directly from the image'
        ];
    }

    private function formatPalette($palette, string $name): array
    {
        return [
            'name' => $name,
            'colors' => array_map(fn($c) => $c->toHex(), $palette->getColors()),
            'description' => "Generated using {$name} harmony"
        ];
    }
}
```

---

## Step 4: Building the Upload and Analysis APIs

### 4.1 Image Upload Handler

Create `public/api/upload.php`:

```php
<?php

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$uploadDir = __DIR__ . '/../uploads/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 10 * 1024 * 1024; // 10MB

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No image uploaded']);
    exit;
}

$file = $_FILES['image'];

// Validate file type
if (!in_array($file['type'], $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Only JPEG, PNG, GIF, and WebP are allowed.']);
    exit;
}

// Validate file size
if ($file['size'] > $maxSize) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size is 10MB.']);
    exit;
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid('img_', true) . '.' . $extension;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save uploaded file']);
    exit;
}

echo json_encode([
    'success' => true,
    'filename' => $filename,
    'path' => '/uploads/' . $filename,
    'size' => $file['size']
]);
```

### 4.2 Analysis Endpoint

Create `public/api/analyze.php`:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\ImageAnalyzer;
use App\PaletteGenerator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$filename = $input['filename'] ?? null;

if (!$filename) {
    http_response_code(400);
    echo json_encode(['error' => 'Filename required']);
    exit;
}

$imagePath = __DIR__ . '/../uploads/' . basename($filename);

if (!file_exists($imagePath)) {
    http_response_code(404);
    echo json_encode(['error' => 'Image not found']);
    exit;
}

try {
    $analyzer = new ImageAnalyzer();
    $paletteGenerator = new PaletteGenerator();

    $colorCount = (int)($input['colorCount'] ?? 5);
    $includeHistogram = (bool)($input['includeHistogram'] ?? false);

    $analysis = $analyzer->analyze($imagePath, [
        'colorCount' => $colorCount,
        'includeHistogram' => $includeHistogram
    ]);

    // Generate palettes
    $dominantColors = array_map(function($colorData) {
        return [
            'color' => \Farzai\ColorPalette\Color::parse($colorData['hex']),
            'frequency' => $colorData['percentage'] / 100
        ];
    }, $analysis['dominantColors']);

    $palettes = $paletteGenerator->generateFromImage($dominantColors);

    echo json_encode([
        'success' => true,
        'analysis' => $analysis,
        'palettes' => $palettes,
        'timestamp' => time()
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

---

## Step 5: Building the Frontend Interface

### 5.1 Main Application Page

Create `public/index.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Color Analyzer - Extract Colors from Images</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🎨 Image Color Analyzer</h1>
            <p>Extract dominant colors and generate palettes from your images</p>
        </header>

        <main class="main-content">
            <!-- Upload Section -->
            <section class="upload-section">
                <div class="upload-area" id="uploadArea">
                    <svg class="upload-icon" viewBox="0 0 24 24" width="64" height="64">
                        <path fill="currentColor" d="M9 16h6v-6h4l-7-7-7 7h4v6zm-4 2h14v2H5v-2z"/>
                    </svg>
                    <p class="upload-text">Drag and drop an image here</p>
                    <p class="upload-subtext">or</p>
                    <button class="btn btn-primary" id="selectFileBtn">Select Image</button>
                    <input type="file" id="fileInput" accept="image/*" hidden>
                    <p class="upload-hint">Supports JPEG, PNG, GIF, WebP (max 10MB)</p>
                </div>

                <div class="image-preview" id="imagePreview" style="display: none;">
                    <img id="previewImage" alt="Uploaded image">
                    <button class="btn-close" id="removeImageBtn">&times;</button>
                </div>
            </section>

            <!-- Analysis Options -->
            <section class="options-section" id="optionsSection" style="display: none;">
                <h2>Analysis Options</h2>
                <div class="options-grid">
                    <div class="option-group">
                        <label>Number of Colors</label>
                        <input type="range" id="colorCount" min="3" max="10" value="5">
                        <span id="colorCountValue">5</span>
                    </div>
                    <div class="option-group">
                        <label>
                            <input type="checkbox" id="includeHistogram">
                            Include Color Histogram
                        </label>
                    </div>
                </div>
                <button class="btn btn-primary" id="analyzeBtn">Analyze Colors</button>
            </section>

            <!-- Results Section -->
            <section class="results-section" id="resultsSection" style="display: none;">
                <!-- Dominant Colors -->
                <div class="result-card">
                    <h2>Dominant Colors</h2>
                    <div id="dominantColors" class="color-grid"></div>
                </div>

                <!-- Statistics -->
                <div class="result-card">
                    <h2>Color Statistics</h2>
                    <div id="statistics" class="stats-grid"></div>
                </div>

                <!-- Mood Analysis -->
                <div class="result-card">
                    <h2>Mood & Atmosphere</h2>
                    <div id="moodAnalysis"></div>
                </div>

                <!-- Generated Palettes -->
                <div class="result-card">
                    <h2>Generated Palettes</h2>
                    <div id="palettes" class="palettes-container"></div>
                </div>
            </section>
        </main>
    </div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay" style="display: none;">
        <div class="spinner"></div>
        <p>Analyzing image...</p>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
```

### 5.2 Application Styles

Create `public/assets/css/style.css`:

```css
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary: #6366F1;
    --primary-dark: #4F46E5;
    --success: #10B981;
    --danger: #EF4444;
    --background: #F9FAFB;
    --surface: #FFFFFF;
    --text: #1F2937;
    --text-secondary: #6B7280;
    --border: #E5E7EB;
    --shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    --shadow-lg: 0 10px 40px rgba(0, 0, 0, 0.1);
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: var(--background);
    color: var(--text);
    line-height: 1.6;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}

.header {
    text-align: center;
    margin-bottom: 3rem;
}

.header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: var(--primary);
}

.main-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

/* Upload Section */
.upload-section {
    background: var(--surface);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow);
}

.upload-area {
    border: 3px dashed var(--border);
    border-radius: 12px;
    padding: 3rem;
    text-align: center;
    transition: all 0.3s ease;
    cursor: pointer;
}

.upload-area:hover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.05);
}

.upload-area.dragover {
    border-color: var(--primary);
    background: rgba(99, 102, 241, 0.1);
}

.upload-icon {
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.upload-text {
    font-size: 1.25rem;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

.upload-subtext {
    color: var(--text-secondary);
    margin: 1rem 0;
}

.upload-hint {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 1rem;
}

.image-preview {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    max-height: 500px;
}

.image-preview img {
    width: 100%;
    height: auto;
    display: block;
}

.btn-close {
    position: absolute;
    top: 1rem;
    right: 1rem;
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 1.5rem;
    border-radius: 50%;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-close:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}

/* Options Section */
.options-section {
    background: var(--surface);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow);
}

.options-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin: 1.5rem 0;
}

.option-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 0.5rem;
}

input[type="range"] {
    width: calc(100% - 50px);
    margin-right: 10px;
}

#colorCountValue {
    display: inline-block;
    width: 40px;
    text-align: center;
    font-weight: 600;
    background: var(--background);
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
}

.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

/* Results Section */
.results-section {
    display: grid;
    gap: 2rem;
}

.result-card {
    background: var(--surface);
    border-radius: 12px;
    padding: 2rem;
    box-shadow: var(--shadow);
}

.result-card h2 {
    margin-bottom: 1.5rem;
    color: var(--text);
}

.color-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1rem;
}

.color-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
    transition: transform 0.2s ease;
}

.color-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.color-swatch {
    height: 120px;
    cursor: pointer;
}

.color-info {
    padding: 1rem;
}

.color-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.color-hex {
    font-family: 'Courier New', monospace;
    color: var(--text-secondary);
    font-size: 0.875rem;
}

.color-percentage {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-top: 0.5rem;
}

/* Statistics */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.stat-item {
    padding: 1rem;
    background: var(--background);
    border-radius: 8px;
}

.stat-label {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary);
}

/* Mood Analysis */
#moodAnalysis {
    display: grid;
    gap: 1rem;
}

.mood-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.mood-label {
    flex: 0 0 150px;
    font-weight: 600;
}

.mood-bar {
    flex: 1;
    height: 24px;
    background: var(--background);
    border-radius: 12px;
    overflow: hidden;
}

.mood-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--primary-dark));
    transition: width 0.5s ease;
}

.mood-score {
    font-weight: 600;
    color: var(--primary);
}

/* Palettes */
.palettes-container {
    display: grid;
    gap: 1.5rem;
}

.palette-card {
    border: 1px solid var(--border);
    border-radius: 8px;
    overflow: hidden;
}

.palette-header {
    padding: 1rem;
    background: var(--background);
}

.palette-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.palette-description {
    font-size: 0.875rem;
    color: var(--text-secondary);
}

.palette-colors {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(80px, 1fr));
}

.palette-color {
    height: 80px;
    cursor: pointer;
    position: relative;
    transition: transform 0.2s ease;
}

.palette-color:hover {
    transform: scale(1.05);
    z-index: 1;
}

.palette-color-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.25rem;
    font-size: 0.75rem;
    text-align: center;
    font-family: 'Courier New', monospace;
}

/* Loading Overlay */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.spinner {
    width: 60px;
    height: 60px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.loading-overlay p {
    color: white;
    margin-top: 1rem;
    font-size: 1.125rem;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }

    .header h1 {
        font-size: 2rem;
    }

    .upload-area {
        padding: 2rem 1rem;
    }
}
```

### 5.3 JavaScript Application

Create `public/assets/js/app.js`:

```javascript
class ImageColorAnalyzer {
    constructor() {
        this.currentFile = null;
        this.currentFilename = null;
        this.init();
    }

    init() {
        this.cacheElements();
        this.attachEventListeners();
    }

    cacheElements() {
        this.uploadArea = document.getElementById('uploadArea');
        this.fileInput = document.getElementById('fileInput');
        this.selectFileBtn = document.getElementById('selectFileBtn');
        this.imagePreview = document.getElementById('imagePreview');
        this.previewImage = document.getElementById('previewImage');
        this.removeImageBtn = document.getElementById('removeImageBtn');
        this.optionsSection = document.getElementById('optionsSection');
        this.colorCount = document.getElementById('colorCount');
        this.colorCountValue = document.getElementById('colorCountValue');
        this.analyzeBtn = document.getElementById('analyzeBtn');
        this.resultsSection = document.getElementById('resultsSection');
        this.loadingOverlay = document.getElementById('loadingOverlay');
    }

    attachEventListeners() {
        // File selection
        this.selectFileBtn.addEventListener('click', () => this.fileInput.click());
        this.fileInput.addEventListener('change', (e) => this.handleFileSelect(e));

        // Drag and drop
        this.uploadArea.addEventListener('dragover', (e) => this.handleDragOver(e));
        this.uploadArea.addEventListener('dragleave', (e) => this.handleDragLeave(e));
        this.uploadArea.addEventListener('drop', (e) => this.handleDrop(e));

        // Remove image
        this.removeImageBtn.addEventListener('click', () => this.removeImage());

        // Color count slider
        this.colorCount.addEventListener('input', (e) => {
            this.colorCountValue.textContent = e.target.value;
        });

        // Analyze button
        this.analyzeBtn.addEventListener('click', () => this.analyzeImage());
    }

    handleDragOver(e) {
        e.preventDefault();
        this.uploadArea.classList.add('dragover');
    }

    handleDragLeave(e) {
        e.preventDefault();
        this.uploadArea.classList.remove('dragover');
    }

    handleDrop(e) {
        e.preventDefault();
        this.uploadArea.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    }

    handleFileSelect(e) {
        const files = e.target.files;
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    }

    processFile(file) {
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        this.currentFile = file;
        this.showImagePreview(file);
        this.uploadImage(file);
    }

    showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            this.previewImage.src = e.target.result;
            this.uploadArea.style.display = 'none';
            this.imagePreview.style.display = 'block';
            this.optionsSection.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    async uploadImage(file) {
        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch('/api/upload.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.currentFilename = data.filename;
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Upload error:', error);
            alert('Failed to upload image: ' + error.message);
        }
    }

    removeImage() {
        this.currentFile = null;
        this.currentFilename = null;
        this.previewImage.src = '';
        this.uploadArea.style.display = 'block';
        this.imagePreview.style.display = 'none';
        this.optionsSection.style.display = 'none';
        this.resultsSection.style.display = 'none';
        this.fileInput.value = '';
    }

    async analyzeImage() {
        if (!this.currentFilename) {
            alert('Please upload an image first');
            return;
        }

        this.loadingOverlay.style.display = 'flex';

        try {
            const response = await fetch('/api/analyze.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    filename: this.currentFilename,
                    colorCount: parseInt(this.colorCount.value),
                    includeHistogram: document.getElementById('includeHistogram').checked
                })
            });

            const data = await response.json();

            if (data.success) {
                this.displayResults(data);
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Analysis error:', error);
            alert('Failed to analyze image: ' + error.message);
        } finally {
            this.loadingOverlay.style.display = 'none';
        }
    }

    displayResults(data) {
        this.resultsSection.style.display = 'block';
        this.displayDominantColors(data.analysis.dominantColors);
        this.displayStatistics(data.analysis.statistics);
        this.displayMoodAnalysis(data.analysis.mood);
        this.displayPalettes(data.palettes);

        // Scroll to results
        this.resultsSection.scrollIntoView({ behavior: 'smooth' });
    }

    displayDominantColors(colors) {
        const container = document.getElementById('dominantColors');
        container.innerHTML = colors.map(color => `
            <div class="color-card" data-hex="${color.hex}">
                <div class="color-swatch" style="background-color: ${color.hex}"></div>
                <div class="color-info">
                    <div class="color-name">${color.name}</div>
                    <div class="color-hex">${color.hex}</div>
                    <div class="color-percentage">${color.percentage}%</div>
                </div>
            </div>
        `).join('');

        // Add click-to-copy
        container.querySelectorAll('.color-card').forEach(card => {
            card.addEventListener('click', () => {
                navigator.clipboard.writeText(card.dataset.hex);
                this.showToast(`Copied ${card.dataset.hex}`);
            });
        });
    }

    displayStatistics(stats) {
        const container = document.getElementById('statistics');
        container.innerHTML = `
            <div class="stat-item">
                <div class="stat-label">Average Hue</div>
                <div class="stat-value">${Math.round(stats.averageHue)}°</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Average Saturation</div>
                <div class="stat-value">${Math.round(stats.averageSaturation)}%</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Average Lightness</div>
                <div class="stat-value">${Math.round(stats.averageLightness)}%</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Color Diversity</div>
                <div class="stat-value">${stats.colorDiversity}%</div>
            </div>
        `;
    }

    displayMoodAnalysis(mood) {
        const container = document.getElementById('moodAnalysis');
        container.innerHTML = Object.entries(mood.scores)
            .slice(0, 5)
            .map(([name, score]) => `
                <div class="mood-item">
                    <div class="mood-label">${this.capitalize(name)}</div>
                    <div class="mood-bar">
                        <div class="mood-fill" style="width: ${score}%"></div>
                    </div>
                    <div class="mood-score">${Math.round(score)}%</div>
                </div>
            `).join('');
    }

    displayPalettes(palettes) {
        const container = document.getElementById('palettes');
        container.innerHTML = Object.values(palettes).map(palette => `
            <div class="palette-card">
                <div class="palette-header">
                    <div class="palette-name">${palette.name}</div>
                    <div class="palette-description">${palette.description}</div>
                </div>
                <div class="palette-colors">
                    ${palette.colors.map(color => `
                        <div class="palette-color" style="background-color: ${color}" data-hex="${color}">
                            <div class="palette-color-label">${color}</div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `).join('');

        // Add click-to-copy
        container.querySelectorAll('.palette-color').forEach(el => {
            el.addEventListener('click', () => {
                navigator.clipboard.writeText(el.dataset.hex);
                this.showToast(`Copied ${el.dataset.hex}`);
            });
        });
    }

    capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    showToast(message) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.cssText = `
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background: #1F2937;
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            z-index: 10000;
        `;

        document.body.appendChild(toast);

        setTimeout(() => toast.remove(), 2000);
    }
}

// Initialize app
document.addEventListener('DOMContentLoaded', () => {
    new ImageColorAnalyzer();
});
```

---

## Step 6: Testing the Application

### 6.1 Start the Server

```bash
cd public
php -S localhost:8000
```

### 6.2 Test Scenarios

1. **Upload a vibrant image** (sunset, colorful art)
   - Verify dominant colors are extracted correctly
   - Check mood analysis matches visual impression

2. **Upload a monochrome image**
   - Ensure analyzer handles low color diversity
   - Verify statistics reflect monochromatic nature

3. **Test large images**
   - Upload a 10MB image
   - Verify processing completes without timeout

4. **Test different formats**
   - Try JPEG, PNG, GIF, WebP
   - Verify all formats work correctly

---

## Troubleshooting

### GD Extension Not Found

```bash
# Ubuntu/Debian
sudo apt-get install php-gd
sudo service apache2 restart

# macOS
brew install php
brew services restart php
```

### Upload Directory Not Writable

```bash
chmod 755 public/uploads
chown www-data:www-data public/uploads  # Linux
```

### Memory Limit Errors

Update `php.ini`:
```ini
memory_limit = 256M
upload_max_filesize = 10M
post_max_size = 10M
```

### Slow Analysis

Reduce sample size in `ColorExtractor.php`:
```php
private const SAMPLE_SIZE = 5000; // Reduce from 10000
```

---

## Conclusion

You've built a professional image color analysis tool! This application demonstrates:

- Advanced color extraction using K-means clustering
- Image processing with PHP GD library
- Real-time analysis and visualization
- Palette generation from extracted colors
- Mood and statistical analysis

### Next Steps

1. **Add more features**:
   - Batch image processing
   - Color trend analysis
   - Export to design tools (Figma, Sketch)
   - Social media sharing

2. **Enhance analysis**:
   - Machine learning color classification
   - Emotion detection
   - Brand color matching

3. **Performance improvements**:
   - Image caching
   - Background processing
   - Progressive results

### Related Resources

- [Color Palette PHP Documentation](/color-palette-php/)
- [Building a Theme Generator](/color-palette-php/tutorials/building-a-theme-generator)
- [WCAG Compliance Tutorial](/color-palette-php/tutorials/wcag-compliant-palettes)

---

**Tutorial completed!** You now have a powerful tool for extracting and analyzing colors from images.
