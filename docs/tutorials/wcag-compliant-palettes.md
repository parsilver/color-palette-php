---
layout: default
title: "WCAG-Compliant Color Palettes"
parent: Tutorials
nav_order: 3
description: "Create accessible, WCAG-compliant color palettes that ensure your designs are usable by everyone"
---

# WCAG-Compliant Color Palettes
{: .no_toc }

Build an accessibility-focused color palette generator that creates WCAG-compliant color schemes ensuring your designs are accessible to all users.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Introduction

In this tutorial, you'll build a **WCAG Accessibility Color Checker** that:

- Validates color contrast ratios against WCAG standards
- Automatically adjusts colors to meet accessibility requirements
- Generates accessible color palettes
- Tests text readability on various backgrounds
- Provides detailed accessibility reports
- Suggests alternative colors for failed combinations

This tool ensures your designs are accessible to users with visual impairments, including color blindness and low vision.

**What you'll learn:**
- Understanding WCAG color contrast requirements
- Calculating relative luminance and contrast ratios
- Algorithmic color adjustment for accessibility
- Building color blindness simulators
- Creating comprehensive accessibility reports
- Best practices for accessible design

---

## Prerequisites

Before starting, ensure you have:

- **PHP 8.0 or higher**
- **Composer** for dependency management
- **Basic understanding** of web accessibility concepts
- **Knowledge** of WCAG 2.1 guidelines (helpful but not required)
- **Understanding** of color theory basics

**Required packages:**
```bash
composer require farzai/color-palette-php
```

**WCAG 2.1 Quick Reference:**
- **Level AA** (minimum): 4.5:1 for normal text, 3:1 for large text
- **Level AAA** (enhanced): 7:1 for normal text, 4.5:1 for large text

---

## Project Structure

```
wcag-color-checker/
├── composer.json
├── public/
│   ├── index.php                 # Main application
│   ├── api/
│   │   ├── check-contrast.php    # Contrast checking endpoint
│   │   ├── generate-palette.php  # Generate accessible palettes
│   │   └── simulate-vision.php   # Color blindness simulation
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css
│   │   └── js/
│   │       └── app.js
│   └── reports/                  # Generated accessibility reports
├── src/
│   ├── ContrastChecker.php       # WCAG contrast calculations
│   ├── AccessibilityAdjuster.php # Automatic color adjustment
│   ├── PaletteValidator.php      # Validate color combinations
│   ├── ColorBlindnessSimulator.php # Simulate color blindness
│   └── ReportGenerator.php       # Generate accessibility reports
└── tests/
    └── ContrastCheckerTest.php   # Unit tests
```

---

## Step 1: Understanding WCAG Contrast Requirements

### 1.1 WCAG Levels Explained

**Level AA (Minimum Standard):**
- Normal text (< 18pt): **4.5:1** contrast ratio
- Large text (≥ 18pt or ≥ 14pt bold): **3:1** contrast ratio
- UI components and graphics: **3:1** contrast ratio

**Level AAA (Enhanced Standard):**
- Normal text: **7:1** contrast ratio
- Large text: **4.5:1** contrast ratio

### 1.2 Relative Luminance Formula

The relative luminance calculation is the foundation of contrast checking:

```
L = 0.2126 * R + 0.7152 * G + 0.0722 * B

Where R, G, and B are adjusted:
  if (channel <= 0.03928)
    channel = channel / 12.92
  else
    channel = ((channel + 0.055) / 1.055) ^ 2.4
```

---

## Step 2: Building the Contrast Checker

### 2.1 Create ContrastChecker Class

Create `src/ContrastChecker.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;

class ContrastChecker
{
    // WCAG 2.1 contrast ratio requirements
    public const WCAG_AA_NORMAL = 4.5;
    public const WCAG_AA_LARGE = 3.0;
    public const WCAG_AAA_NORMAL = 7.0;
    public const WCAG_AAA_LARGE = 4.5;

    /**
     * Calculate contrast ratio between two colors
     *
     * @param Color $foreground Foreground color (text)
     * @param Color $background Background color
     * @return float Contrast ratio (1:1 to 21:1)
     */
    public function calculateContrast(Color $foreground, Color $background): float
    {
        $l1 = $this->getRelativeLuminance($foreground);
        $l2 = $this->getRelativeLuminance($background);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        $contrast = ($lighter + 0.05) / ($darker + 0.05);

        return round($contrast, 2);
    }

    /**
     * Calculate relative luminance of a color
     *
     * @param Color $color
     * @return float Relative luminance (0 to 1)
     */
    public function getRelativeLuminance(Color $color): float
    {
        $rgb = $color->toRgb();

        $r = $this->adjustChannel($rgb['r'] / 255);
        $g = $this->adjustChannel($rgb['g'] / 255);
        $b = $this->adjustChannel($rgb['b'] / 255);

        return (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
    }

    /**
     * Adjust RGB channel value for luminance calculation
     *
     * @param float $channel Channel value (0 to 1)
     * @return float Adjusted channel value
     */
    private function adjustChannel(float $channel): float
    {
        if ($channel <= 0.03928) {
            return $channel / 12.92;
        }

        return pow(($channel + 0.055) / 1.055, 2.4);
    }

    /**
     * Check if contrast meets WCAG AA standard
     *
     * @param float $contrast Contrast ratio
     * @param bool $isLargeText Whether text is large (≥18pt or ≥14pt bold)
     * @return bool True if meets AA standard
     */
    public function meetsWCAG_AA(float $contrast, bool $isLargeText = false): bool
    {
        $required = $isLargeText ? self::WCAG_AA_LARGE : self::WCAG_AA_NORMAL;
        return $contrast >= $required;
    }

    /**
     * Check if contrast meets WCAG AAA standard
     *
     * @param float $contrast Contrast ratio
     * @param bool $isLargeText Whether text is large
     * @return bool True if meets AAA standard
     */
    public function meetsWCAG_AAA(float $contrast, bool $isLargeText = false): bool
    {
        $required = $isLargeText ? self::WCAG_AAA_LARGE : self::WCAG_AAA_NORMAL;
        return $contrast >= $required;
    }

    /**
     * Get detailed contrast check results
     *
     * @param Color $foreground
     * @param Color $background
     * @return array Detailed check results
     */
    public function check(Color $foreground, Color $background): array
    {
        $contrast = $this->calculateContrast($foreground, $background);

        return [
            'contrast' => $contrast,
            'ratio' => sprintf('%.2f:1', $contrast),
            'AA' => [
                'normal' => [
                    'passes' => $this->meetsWCAG_AA($contrast, false),
                    'required' => self::WCAG_AA_NORMAL
                ],
                'large' => [
                    'passes' => $this->meetsWCAG_AA($contrast, true),
                    'required' => self::WCAG_AA_LARGE
                ]
            ],
            'AAA' => [
                'normal' => [
                    'passes' => $this->meetsWCAG_AAA($contrast, false),
                    'required' => self::WCAG_AAA_NORMAL
                ],
                'large' => [
                    'passes' => $this->meetsWCAG_AAA($contrast, true),
                    'required' => self::WCAG_AAA_LARGE
                ]
            ]
        ];
    }

    /**
     * Get WCAG level achieved
     *
     * @param float $contrast Contrast ratio
     * @param bool $isLargeText Whether text is large
     * @return string WCAG level: 'AAA', 'AA', or 'Fail'
     */
    public function getWCAGLevel(float $contrast, bool $isLargeText = false): string
    {
        if ($this->meetsWCAG_AAA($contrast, $isLargeText)) {
            return 'AAA';
        }

        if ($this->meetsWCAG_AA($contrast, $isLargeText)) {
            return 'AA';
        }

        return 'Fail';
    }

    /**
     * Find minimum contrast needed to pass level
     *
     * @param string $level 'AA' or 'AAA'
     * @param bool $isLargeText Whether text is large
     * @return float Minimum contrast ratio needed
     */
    public function getMinimumContrast(string $level, bool $isLargeText = false): float
    {
        return match(strtoupper($level)) {
            'AAA' => $isLargeText ? self::WCAG_AAA_LARGE : self::WCAG_AAA_NORMAL,
            'AA' => $isLargeText ? self::WCAG_AA_LARGE : self::WCAG_AA_NORMAL,
            default => self::WCAG_AA_NORMAL
        };
    }
}
```

---

## Step 3: Building the Accessibility Adjuster

### 3.1 Create AccessibilityAdjuster Class

Create `src/AccessibilityAdjuster.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;

class AccessibilityAdjuster
{
    private ContrastChecker $checker;

    public function __construct()
    {
        $this->checker = new ContrastChecker();
    }

    /**
     * Adjust foreground color to meet contrast requirements
     *
     * @param Color $foreground Original foreground color
     * @param Color $background Background color
     * @param string $level Target WCAG level ('AA' or 'AAA')
     * @param bool $isLargeText Whether text is large
     * @return array Adjusted color and details
     */
    public function adjustForeground(
        Color $foreground,
        Color $background,
        string $level = 'AA',
        bool $isLargeText = false
    ): array {
        $targetContrast = $this->checker->getMinimumContrast($level, $isLargeText);
        $currentContrast = $this->checker->calculateContrast($foreground, $background);

        if ($currentContrast >= $targetContrast) {
            return [
                'adjusted' => false,
                'originalColor' => $foreground,
                'adjustedColor' => $foreground,
                'originalContrast' => $currentContrast,
                'finalContrast' => $currentContrast,
                'passes' => true
            ];
        }

        // Try darkening first, then lightening
        $adjusted = $this->adjustLightness($foreground, $background, $targetContrast, 'darker');

        if (!$adjusted) {
            $adjusted = $this->adjustLightness($foreground, $background, $targetContrast, 'lighter');
        }

        if (!$adjusted) {
            // As last resort, use pure black or white
            $adjusted = $this->useFallbackColor($background, $targetContrast);
        }

        $finalContrast = $this->checker->calculateContrast($adjusted, $background);

        return [
            'adjusted' => true,
            'originalColor' => $foreground,
            'adjustedColor' => $adjusted,
            'originalContrast' => $currentContrast,
            'finalContrast' => $finalContrast,
            'passes' => $finalContrast >= $targetContrast
        ];
    }

    /**
     * Adjust color lightness to achieve target contrast
     *
     * @param Color $color Color to adjust
     * @param Color $background Background color
     * @param float $targetContrast Target contrast ratio
     * @param string $direction 'darker' or 'lighter'
     * @return Color|null Adjusted color or null if target not achievable
     */
    private function adjustLightness(
        Color $color,
        Color $background,
        float $targetContrast,
        string $direction
    ): ?Color {
        $step = 5;
        $maxAdjustment = 100;
        $adjustment = 0;

        while ($adjustment <= $maxAdjustment) {
            $adjusted = $direction === 'darker'
                ? $color->darken($adjustment)
                : $color->lighten($adjustment);

            $contrast = $this->checker->calculateContrast($adjusted, $background);

            if ($contrast >= $targetContrast) {
                return $adjusted;
            }

            $adjustment += $step;
        }

        return null;
    }

    /**
     * Use black or white as fallback
     *
     * @param Color $background Background color
     * @param float $targetContrast Target contrast ratio
     * @return Color Black or white color
     */
    private function useFallbackColor(Color $background, float $targetContrast): Color
    {
        $white = Color::parse('#FFFFFF');
        $black = Color::parse('#000000');

        $whiteContrast = $this->checker->calculateContrast($white, $background);
        $blackContrast = $this->checker->calculateContrast($black, $background);

        // Return the one that meets target, prefer black
        if ($blackContrast >= $targetContrast) {
            return $black;
        }

        return $white;
    }

    /**
     * Adjust background color to meet contrast requirements
     *
     * @param Color $foreground Foreground color (fixed)
     * @param Color $background Background color to adjust
     * @param string $level Target WCAG level
     * @param bool $isLargeText Whether text is large
     * @return array Adjusted background and details
     */
    public function adjustBackground(
        Color $foreground,
        Color $background,
        string $level = 'AA',
        bool $isLargeText = false
    ): array {
        $targetContrast = $this->checker->getMinimumContrast($level, $isLargeText);
        $currentContrast = $this->checker->calculateContrast($foreground, $background);

        if ($currentContrast >= $targetContrast) {
            return [
                'adjusted' => false,
                'originalColor' => $background,
                'adjustedColor' => $background,
                'originalContrast' => $currentContrast,
                'finalContrast' => $currentContrast,
                'passes' => true
            ];
        }

        // Determine if background should be lighter or darker
        $fgLuminance = $this->checker->getRelativeLuminance($foreground);
        $direction = $fgLuminance > 0.5 ? 'darker' : 'lighter';

        $adjusted = $this->adjustLightness($background, $foreground, $targetContrast, $direction);

        if (!$adjusted) {
            $opposite = $direction === 'darker' ? 'lighter' : 'darker';
            $adjusted = $this->adjustLightness($background, $foreground, $targetContrast, $opposite);
        }

        if (!$adjusted) {
            $adjusted = $this->useFallbackColor($foreground, $targetContrast);
        }

        $finalContrast = $this->checker->calculateContrast($foreground, $adjusted);

        return [
            'adjusted' => true,
            'originalColor' => $background,
            'adjustedColor' => $adjusted,
            'originalContrast' => $currentContrast,
            'finalContrast' => $finalContrast,
            'passes' => $finalContrast >= $targetContrast
        ];
    }

    /**
     * Find the closest accessible color
     *
     * @param Color $color Color to adjust
     * @param Color $background Background color
     * @param string $level Target WCAG level
     * @param bool $isLargeText Whether text is large
     * @return Color Closest accessible color
     */
    public function findClosestAccessible(
        Color $color,
        Color $background,
        string $level = 'AA',
        bool $isLargeText = false
    ): Color {
        $result = $this->adjustForeground($color, $background, $level, $isLargeText);
        return $result['adjustedColor'];
    }

    /**
     * Suggest alternative colors that meet requirements
     *
     * @param Color $color Base color
     * @param Color $background Background color
     * @param string $level Target WCAG level
     * @return array Array of alternative colors
     */
    public function suggestAlternatives(
        Color $color,
        Color $background,
        string $level = 'AA'
    ): array {
        $alternatives = [];

        // Try different hue adjustments
        $hueShifts = [-30, -15, 15, 30, 60, -60];

        foreach ($hueShifts as $shift) {
            $hsl = $color->toHsl();
            $newHue = ($hsl['h'] + $shift + 360) % 360;

            $alternative = Color::fromHsl($newHue, $hsl['s'], $hsl['l']);
            $adjusted = $this->adjustForeground($alternative, $background, $level);

            if ($adjusted['passes']) {
                $alternatives[] = [
                    'color' => $adjusted['adjustedColor'],
                    'contrast' => $adjusted['finalContrast'],
                    'hueShift' => $shift
                ];
            }
        }

        // Try saturation adjustments
        $saturationLevels = [80, 60, 40, 100];

        foreach ($saturationLevels as $saturation) {
            $hsl = $color->toHsl();
            $alternative = Color::fromHsl($hsl['h'], $saturation, $hsl['l']);
            $adjusted = $this->adjustForeground($alternative, $background, $level);

            if ($adjusted['passes']) {
                $alternatives[] = [
                    'color' => $adjusted['adjustedColor'],
                    'contrast' => $adjusted['finalContrast'],
                    'saturation' => $saturation
                ];
            }
        }

        // Remove duplicates and sort by contrast
        $alternatives = $this->deduplicateColors($alternatives);
        usort($alternatives, fn($a, $b) => $b['contrast'] <=> $a['contrast']);

        return array_slice($alternatives, 0, 5);
    }

    /**
     * Remove duplicate colors from alternatives
     */
    private function deduplicateColors(array $alternatives): array
    {
        $seen = [];
        $unique = [];

        foreach ($alternatives as $alt) {
            $hex = $alt['color']->toHex();
            if (!in_array($hex, $seen)) {
                $seen[] = $hex;
                $unique[] = $alt;
            }
        }

        return $unique;
    }
}
```

---

## Step 4: Creating the Palette Validator

### 4.1 Create PaletteValidator Class

Create `src/PaletteValidator.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Palette;

class PaletteValidator
{
    private ContrastChecker $checker;
    private AccessibilityAdjuster $adjuster;

    public function __construct()
    {
        $this->checker = new ContrastChecker();
        $this->adjuster = new AccessibilityAdjuster();
    }

    /**
     * Validate entire palette for accessibility
     *
     * @param Palette $palette
     * @param string $level Target WCAG level
     * @return array Validation results
     */
    public function validatePalette(Palette $palette, string $level = 'AA'): array
    {
        $colors = $palette->getColors();
        $results = [
            'passes' => true,
            'level' => $level,
            'combinations' => [],
            'recommendations' => []
        ];

        // Test all color combinations
        for ($i = 0; $i < count($colors); $i++) {
            for ($j = $i + 1; $j < count($colors); $j++) {
                $fg = $colors[$i];
                $bg = $colors[$j];

                $check = $this->checker->check($fg, $bg);
                $passes = $level === 'AAA'
                    ? $check['AAA']['normal']['passes']
                    : $check['AA']['normal']['passes'];

                $results['combinations'][] = [
                    'foreground' => $fg->toHex(),
                    'background' => $bg->toHex(),
                    'contrast' => $check['contrast'],
                    'passes' => $passes,
                    'level' => $this->checker->getWCAGLevel($check['contrast'])
                ];

                if (!$passes) {
                    $results['passes'] = false;

                    // Generate recommendation
                    $adjusted = $this->adjuster->adjustForeground($fg, $bg, $level);
                    $results['recommendations'][] = [
                        'original' => [
                            'foreground' => $fg->toHex(),
                            'background' => $bg->toHex()
                        ],
                        'recommended' => [
                            'foreground' => $adjusted['adjustedColor']->toHex(),
                            'background' => $bg->toHex()
                        ],
                        'improvement' => $adjusted['finalContrast'] - $adjusted['originalContrast']
                    ];
                }
            }
        }

        return $results;
    }

    /**
     * Generate accessible palette from colors
     *
     * @param array $colors Array of Color objects
     * @param string $level Target WCAG level
     * @return array Accessible palette
     */
    public function generateAccessiblePalette(array $colors, string $level = 'AA'): array
    {
        $accessible = [];

        // Always include pure white and black as base
        $white = Color::parse('#FFFFFF');
        $black = Color::parse('#000000');

        $accessible['background-light'] = $white;
        $accessible['background-dark'] = $black;

        // Adjust each color to work on both light and dark backgrounds
        foreach ($colors as $index => $color) {
            $name = 'color-' . ($index + 1);

            // Version for light background
            $onLight = $this->adjuster->adjustForeground($color, $white, $level);
            $accessible[$name . '-on-light'] = $onLight['adjustedColor'];

            // Version for dark background
            $onDark = $this->adjuster->adjustForeground($color, $black, $level);
            $accessible[$name . '-on-dark'] = $onDark['adjustedColor'];
        }

        return $accessible;
    }

    /**
     * Test color against common backgrounds
     *
     * @param Color $color Color to test
     * @param string $level Target WCAG level
     * @return array Test results
     */
    public function testAgainstCommonBackgrounds(Color $color, string $level = 'AA'): array
    {
        $backgrounds = [
            'white' => Color::parse('#FFFFFF'),
            'light-gray' => Color::parse('#F3F4F6'),
            'medium-gray' => Color::parse('#9CA3AF'),
            'dark-gray' => Color::parse('#374151'),
            'black' => Color::parse('#000000')
        ];

        $results = [];

        foreach ($backgrounds as $name => $bg) {
            $check = $this->checker->check($color, $bg);
            $passes = $level === 'AAA'
                ? $check['AAA']['normal']['passes']
                : $check['AA']['normal']['passes'];

            $results[$name] = [
                'background' => $bg->toHex(),
                'contrast' => $check['contrast'],
                'passes' => $passes,
                'level' => $this->checker->getWCAGLevel($check['contrast']),
                'adjusted' => null
            ];

            if (!$passes) {
                $adjusted = $this->adjuster->adjustForeground($color, $bg, $level);
                $results[$name]['adjusted'] = $adjusted['adjustedColor']->toHex();
            }
        }

        return $results;
    }
}
```

---

## Step 5: Building the Color Blindness Simulator

Create `src/ColorBlindnessSimulator.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;

class ColorBlindnessSimulator
{
    /**
     * Simulate protanopia (red-blind)
     */
    public function protanopia(Color $color): Color
    {
        $rgb = $color->toRgb();
        return $this->applyMatrix($rgb, [
            [0.567, 0.433, 0],
            [0.558, 0.442, 0],
            [0, 0.242, 0.758]
        ]);
    }

    /**
     * Simulate deuteranopia (green-blind)
     */
    public function deuteranopia(Color $color): Color
    {
        $rgb = $color->toRgb();
        return $this->applyMatrix($rgb, [
            [0.625, 0.375, 0],
            [0.7, 0.3, 0],
            [0, 0.3, 0.7]
        ]);
    }

    /**
     * Simulate tritanopia (blue-blind)
     */
    public function tritanopia(Color $color): Color
    {
        $rgb = $color->toRgb();
        return $this->applyMatrix($rgb, [
            [0.95, 0.05, 0],
            [0, 0.433, 0.567],
            [0, 0.475, 0.525]
        ]);
    }

    /**
     * Simulate achromatopsia (complete color blindness)
     */
    public function achromatopsia(Color $color): Color
    {
        $rgb = $color->toRgb();
        return $this->applyMatrix($rgb, [
            [0.299, 0.587, 0.114],
            [0.299, 0.587, 0.114],
            [0.299, 0.587, 0.114]
        ]);
    }

    /**
     * Apply transformation matrix to RGB values
     */
    private function applyMatrix(array $rgb, array $matrix): Color
    {
        $r = $rgb['r'] / 255;
        $g = $rgb['g'] / 255;
        $b = $rgb['b'] / 255;

        $newR = ($matrix[0][0] * $r) + ($matrix[0][1] * $g) + ($matrix[0][2] * $b);
        $newG = ($matrix[1][0] * $r) + ($matrix[1][1] * $g) + ($matrix[1][2] * $b);
        $newB = ($matrix[2][0] * $r) + ($matrix[2][1] * $g) + ($matrix[2][2] * $b);

        return Color::fromRgb(
            (int)round(max(0, min(1, $newR)) * 255),
            (int)round(max(0, min(1, $newG)) * 255),
            (int)round(max(0, min(1, $newB)) * 255)
        );
    }

    /**
     * Simulate all types of color blindness
     */
    public function simulateAll(Color $color): array
    {
        return [
            'original' => $color->toHex(),
            'protanopia' => $this->protanopia($color)->toHex(),
            'deuteranopia' => $this->deuteranopia($color)->toHex(),
            'tritanopia' => $this->tritanopia($color)->toHex(),
            'achromatopsia' => $this->achromatopsia($color)->toHex()
        ];
    }
}
```

---

## Step 6: Creating the API Endpoints

### 6.1 Contrast Check API

Create `public/api/check-contrast.php`:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\ContrastChecker;
use App\AccessibilityAdjuster;
use Farzai\ColorPalette\Color;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$foregroundHex = $input['foreground'] ?? null;
$backgroundHex = $input['background'] ?? null;
$level = $input['level'] ?? 'AA';
$isLargeText = (bool)($input['isLargeText'] ?? false);

if (!$foregroundHex || !$backgroundHex) {
    http_response_code(400);
    echo json_encode(['error' => 'Foreground and background colors required']);
    exit;
}

try {
    $foreground = Color::parse($foregroundHex);
    $background = Color::parse($backgroundHex);

    $checker = new ContrastChecker();
    $adjuster = new AccessibilityAdjuster();

    $checkResult = $checker->check($foreground, $background);
    $adjustmentResult = $adjuster->adjustForeground($foreground, $background, $level, $isLargeText);
    $alternatives = $adjuster->suggestAlternatives($foreground, $background, $level);

    echo json_encode([
        'success' => true,
        'check' => $checkResult,
        'adjustment' => [
            'needed' => $adjustmentResult['adjusted'],
            'originalColor' => $adjustmentResult['originalColor']->toHex(),
            'adjustedColor' => $adjustmentResult['adjustedColor']->toHex(),
            'originalContrast' => $adjustmentResult['originalContrast'],
            'finalContrast' => $adjustmentResult['finalContrast']
        ],
        'alternatives' => array_map(function($alt) {
            return [
                'color' => $alt['color']->toHex(),
                'contrast' => $alt['contrast']
            ];
        }, $alternatives)
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### 6.2 Generate Accessible Palette API

Create `public/api/generate-palette.php`:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\PaletteValidator;
use App\AccessibilityAdjuster;
use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Generator\HarmonyGenerator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$baseColor = $input['baseColor'] ?? '#3B82F6';
$harmonyType = $input['harmonyType'] ?? 'complementary';
$level = $input['level'] ?? 'AA';

try {
    $color = Color::parse($baseColor);
    $generator = new HarmonyGenerator();

    // Generate base palette
    $palette = match($harmonyType) {
        'complementary' => $generator->complementary($color),
        'triadic' => $generator->triadic($color),
        'analogous' => $generator->analogous($color),
        'monochromatic' => $generator->monochromatic($color, 5),
        default => $generator->complementary($color)
    };

    $validator = new PaletteValidator();

    // Validate and get accessible versions
    $validation = $validator->validatePalette($palette, $level);
    $accessiblePalette = $validator->generateAccessiblePalette($palette->getColors(), $level);

    echo json_encode([
        'success' => true,
        'originalPalette' => array_map(fn($c) => $c->toHex(), $palette->getColors()),
        'accessiblePalette' => array_map(fn($c) => $c->toHex(), $accessiblePalette),
        'validation' => $validation,
        'level' => $level
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### 6.3 Color Blindness Simulation API

Create `public/api/simulate-vision.php`:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\ColorBlindnessSimulator;
use Farzai\ColorPalette\Color;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$colors = $input['colors'] ?? [];

if (empty($colors)) {
    http_response_code(400);
    echo json_encode(['error' => 'Colors array required']);
    exit;
}

try {
    $simulator = new ColorBlindnessSimulator();
    $results = [];

    foreach ($colors as $hexColor) {
        $color = Color::parse($hexColor);
        $results[$hexColor] = $simulator->simulateAll($color);
    }

    echo json_encode([
        'success' => true,
        'simulations' => $results
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

## Step 7: Building the Frontend (Abbreviated)

Create `public/index.php` with:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WCAG Color Accessibility Checker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>♿ WCAG Color Accessibility Checker</h1>
            <p>Ensure your colors meet WCAG accessibility standards</p>
        </header>

        <main class="main-content">
            <!-- Contrast Checker Section -->
            <section class="checker-section">
                <h2>Contrast Checker</h2>
                <div class="color-inputs">
                    <div class="input-group">
                        <label>Foreground (Text)</label>
                        <input type="color" id="foregroundColor" value="#000000">
                        <input type="text" id="foregroundHex" value="#000000">
                    </div>
                    <div class="input-group">
                        <label>Background</label>
                        <input type="color" id="backgroundColor" value="#FFFFFF">
                        <input type="text" id="backgroundHex" value="#FFFFFF">
                    </div>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox" id="isLargeText">
                        Large Text (≥18pt or ≥14pt bold)
                    </label>
                    <select id="wcagLevel">
                        <option value="AA">WCAG AA</option>
                        <option value="AAA">WCAG AAA</option>
                    </select>
                </div>

                <button id="checkBtn" class="btn btn-primary">Check Contrast</button>

                <!-- Preview -->
                <div class="preview-box" id="previewBox">
                    <div class="preview-sample">
                        <h3>Sample Text</h3>
                        <p>The quick brown fox jumps over the lazy dog</p>
                    </div>
                </div>

                <!-- Results -->
                <div id="results" style="display: none;"></div>
            </section>

            <!-- Palette Generator Section -->
            <section class="generator-section">
                <h2>Accessible Palette Generator</h2>
                <!-- Implementation similar to theme generator -->
            </section>

            <!-- Color Blindness Simulator -->
            <section class="simulator-section">
                <h2>Color Blindness Simulator</h2>
                <!-- Simulation grid -->
            </section>
        </main>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
```

---

## Step 8: Testing

### 8.1 Unit Tests

Create `tests/ContrastCheckerTest.php`:

```php
<?php

use PHPUnit\Framework\TestCase;
use App\ContrastChecker;
use Farzai\ColorPalette\Color;

class ContrastCheckerTest extends TestCase
{
    private ContrastChecker $checker;

    protected function setUp(): void
    {
        $this->checker = new ContrastChecker();
    }

    public function testBlackOnWhiteContrast()
    {
        $black = Color::parse('#000000');
        $white = Color::parse('#FFFFFF');

        $contrast = $this->checker->calculateContrast($black, $white);

        $this->assertEquals(21.0, $contrast);
    }

    public function testWCAGAAPass()
    {
        $foreground = Color::parse('#595959');
        $background = Color::parse('#FFFFFF');

        $contrast = $this->checker->calculateContrast($foreground, $background);

        $this->assertTrue($this->checker->meetsWCAG_AA($contrast));
    }

    public function testWCAGAAFail()
    {
        $foreground = Color::parse('#777777');
        $background = Color::parse('#FFFFFF');

        $contrast = $this->checker->calculateContrast($foreground, $background);

        $this->assertFalse($this->checker->meetsWCAG_AA($contrast));
    }
}
```

Run tests:
```bash
composer require --dev phpunit/phpunit
./vendor/bin/phpunit tests/
```

---

## Troubleshooting

### Contrast Calculation Issues

```php
// Debug relative luminance
$checker = new ContrastChecker();
$color = Color::parse('#3B82F6');
$luminance = $checker->getRelativeLuminance($color);
var_dump($luminance); // Should be between 0 and 1
```

### Color Adjustment Not Working

```php
// Check step-by-step adjustment
$adjuster = new AccessibilityAdjuster();
$result = $adjuster->adjustForeground($fg, $bg, 'AA');
var_dump($result); // Check if adjustment was needed and successful
```

---

## Conclusion

You've built a comprehensive WCAG accessibility checker! This application:

- Validates color contrast against WCAG standards
- Automatically adjusts colors for accessibility
- Simulates color blindness conditions
- Generates accessible color palettes
- Provides detailed accessibility reports

### Best Practices Learned

1. **Always test on both light and dark backgrounds**
2. **Provide multiple contrast levels (AA and AAA)**
3. **Consider large text exceptions**
4. **Test with color blindness simulators**
5. **Offer alternative color suggestions**
6. **Document accessibility decisions**

### Next Steps

1. **Add more features**:
   - PDF accessibility reports
   - Browser extension
   - API for automated testing
   - Integration with design tools

2. **Enhance testing**:
   - Automated CI/CD accessibility checks
   - Batch palette testing
   - Historical tracking

### Related Resources

- [WCAG 2.1 Guidelines](https://www.w3.org/WAI/WCAG21/quickref/)
- [Color Palette PHP Documentation](/color-palette-php/)
- [Building a Theme Generator](/color-palette-php/tutorials/building-a-theme-generator)
- [WebAIM Contrast Checker](https://webaim.org/resources/contrastchecker/)

---

**Tutorial completed!** You now have the tools to create accessible, WCAG-compliant designs for all users.
