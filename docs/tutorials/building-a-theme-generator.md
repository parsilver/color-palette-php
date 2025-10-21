---
layout: default
title: "Building a Theme Generator Web App"
parent: Tutorials
nav_order: 1
description: "Build a complete theme generator web application with real-time color palette generation and CSS export functionality"
---

# Building a Theme Generator Web App
{: .no_toc }

Learn how to build a professional theme generator web application that generates harmonious color palettes and exports them as CSS variables.
{: .fs-6 .fw-300 }

## Table of contents
{: .no_toc .text-delta }

1. TOC
{:toc}

---

## Introduction

In this tutorial, you'll build a complete **Theme Generator Web Application** that allows users to:

- Generate color palettes from a base color
- Choose different color harmony types (complementary, triadic, analogous, etc.)
- Preview the theme in real-time
- Export themes as CSS custom properties
- Save and load theme presets
- Apply themes to a sample UI

By the end of this tutorial, you'll have a fully functional web app that demonstrates the power of the Color Palette PHP library.

**What you'll learn:**
- Working with color harmonies and palette generation
- Real-time color manipulation and preview
- CSS custom properties for theming
- Building an interactive UI with PHP backend
- Exporting and importing theme configurations

---

## Prerequisites

Before starting this tutorial, ensure you have:

- **PHP 8.0 or higher** installed
- **Composer** for dependency management
- **Basic knowledge** of PHP, HTML, CSS, and JavaScript
- **A web server** (Apache, Nginx, or PHP's built-in server)
- **Text editor or IDE** (VS Code, PhpStorm, etc.)

**Required packages:**
```bash
composer require farzai/color-palette-php
```

---

## Project Structure

Our theme generator will have the following file structure:

```
theme-generator/
├── composer.json
├── public/
│   ├── index.php          # Main application entry
│   ├── api/
│   │   ├── generate.php   # Palette generation API
│   │   └── export.php     # Export functionality
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css  # Application styles
│   │   └── js/
│   │       └── app.js     # Frontend JavaScript
│   └── presets/
│       └── themes.json    # Saved theme presets
├── src/
│   ├── ThemeGenerator.php # Core theme generation logic
│   └── CSSExporter.php    # CSS export functionality
└── views/
    ├── layout.php         # Main layout template
    └── components/
        ├── palette-preview.php
        ├── color-picker.php
        └── export-modal.php
```

---

## Step 1: Setting Up the Project

### 1.1 Initialize Composer Project

Create a new directory and initialize Composer:

```bash
mkdir theme-generator
cd theme-generator
composer init
```

### 1.2 Install Dependencies

Update your `composer.json`:

```json
{
    "name": "yourname/theme-generator",
    "description": "A professional theme generator web application",
    "type": "project",
    "require": {
        "php": ">=8.0",
        "farzai/color-palette-php": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    }
}
```

Install dependencies:

```bash
composer install
```

---

## Step 2: Building the Core Theme Generator

### 2.1 Create ThemeGenerator Class

Create `src/ThemeGenerator.php`:

```php
<?php

namespace App;

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\Palette;
use Farzai\ColorPalette\Generator\HarmonyGenerator;

class ThemeGenerator
{
    private HarmonyGenerator $harmonyGenerator;

    public function __construct()
    {
        $this->harmonyGenerator = new HarmonyGenerator();
    }

    /**
     * Generate a complete theme from a base color
     *
     * @param string $baseColor Hex color code
     * @param string $harmonyType Type of harmony (complementary, triadic, etc.)
     * @param int $shades Number of shades to generate
     * @return array Theme data including palette and variations
     */
    public function generateTheme(
        string $baseColor,
        string $harmonyType = 'complementary',
        int $shades = 5
    ): array {
        $color = Color::parse($baseColor);

        // Generate the base palette using harmony
        $basePalette = match($harmonyType) {
            'complementary' => $this->harmonyGenerator->complementary($color),
            'triadic' => $this->harmonyGenerator->triadic($color),
            'tetradic' => $this->harmonyGenerator->tetradic($color),
            'analogous' => $this->harmonyGenerator->analogous($color),
            'split-complementary' => $this->harmonyGenerator->splitComplementary($color),
            'monochromatic' => $this->harmonyGenerator->monochromatic($color, $shades),
            default => $this->harmonyGenerator->complementary($color),
        };

        // Generate variations for each color
        $theme = [
            'name' => ucfirst($harmonyType) . ' Theme',
            'baseColor' => $baseColor,
            'harmonyType' => $harmonyType,
            'timestamp' => time(),
            'palette' => [],
            'variations' => []
        ];

        foreach ($basePalette->getColors() as $index => $paletteColor) {
            $colorName = $this->generateColorName($index);

            // Store base color
            $theme['palette'][$colorName] = [
                'hex' => $paletteColor->toHex(),
                'rgb' => $paletteColor->toRgb(),
                'hsl' => $paletteColor->toHsl()
            ];

            // Generate shades (lighter and darker variations)
            $theme['variations'][$colorName] = $this->generateShades($paletteColor, $shades);
        }

        // Add neutral colors (grays)
        $theme['neutrals'] = $this->generateNeutrals(10);

        return $theme;
    }

    /**
     * Generate lighter and darker shades of a color
     */
    private function generateShades(Color $color, int $count): array
    {
        $shades = [];
        $step = 100 / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $lightness = $i * $step;

            if ($lightness < 50) {
                // Darker shades
                $shade = $color->darken((50 - $lightness) * 2);
                $shades['dark-' . $i] = $shade->toHex();
            } else {
                // Lighter shades
                $shade = $color->lighten(($lightness - 50) * 2);
                $shades['light-' . ($i - ($count / 2))] = $shade->toHex();
            }
        }

        return $shades;
    }

    /**
     * Generate neutral gray colors
     */
    private function generateNeutrals(int $count): array
    {
        $neutrals = [];
        $step = 100 / ($count - 1);

        for ($i = 0; $i < $count; $i++) {
            $lightness = $i * $step;
            $gray = Color::parse('#808080')->lighten($lightness - 50);
            $neutrals['gray-' . ($i * 100)] = $gray->toHex();
        }

        return $neutrals;
    }

    /**
     * Generate semantic color names
     */
    private function generateColorName(int $index): string
    {
        $names = ['primary', 'secondary', 'accent', 'tertiary', 'quaternary'];
        return $names[$index] ?? 'color-' . ($index + 1);
    }

    /**
     * Load a saved theme preset
     */
    public function loadPreset(string $name): ?array
    {
        $file = __DIR__ . '/../public/presets/themes.json';

        if (!file_exists($file)) {
            return null;
        }

        $presets = json_decode(file_get_contents($file), true);
        return $presets[$name] ?? null;
    }

    /**
     * Save a theme as a preset
     */
    public function savePreset(string $name, array $theme): bool
    {
        $file = __DIR__ . '/../public/presets/themes.json';
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $presets = [];
        if (file_exists($file)) {
            $presets = json_decode(file_get_contents($file), true) ?? [];
        }

        $presets[$name] = $theme;

        return file_put_contents($file, json_encode($presets, JSON_PRETTY_PRINT)) !== false;
    }
}
```

### 2.2 Create CSS Exporter

Create `src/CSSExporter.php`:

```php
<?php

namespace App;

class CSSExporter
{
    /**
     * Export theme as CSS custom properties
     */
    public function exportAsCSS(array $theme): string
    {
        $css = ":root {\n";
        $css .= "  /* Generated by Theme Generator */\n";
        $css .= "  /* Base Colors */\n";

        // Export base palette colors
        foreach ($theme['palette'] as $name => $color) {
            $css .= "  --color-{$name}: {$color['hex']};\n";
            $css .= "  --color-{$name}-rgb: {$this->hexToRgbValues($color['hex'])};\n";
        }

        $css .= "\n  /* Color Variations */\n";

        // Export variations
        foreach ($theme['variations'] as $colorName => $shades) {
            foreach ($shades as $shadeName => $hex) {
                $css .= "  --color-{$colorName}-{$shadeName}: {$hex};\n";
            }
        }

        $css .= "\n  /* Neutral Colors */\n";

        // Export neutrals
        foreach ($theme['neutrals'] as $name => $hex) {
            $css .= "  --color-{$name}: {$hex};\n";
        }

        $css .= "}\n\n";

        // Add utility classes
        $css .= $this->generateUtilityClasses($theme);

        return $css;
    }

    /**
     * Export theme as SCSS variables
     */
    public function exportAsSCSS(array $theme): string
    {
        $scss = "// Generated by Theme Generator\n";
        $scss .= "// Base Colors\n";

        foreach ($theme['palette'] as $name => $color) {
            $scss .= "\${$name}: {$color['hex']};\n";
        }

        $scss .= "\n// Color Variations\n";

        foreach ($theme['variations'] as $colorName => $shades) {
            foreach ($shades as $shadeName => $hex) {
                $scss .= "\${$colorName}-{$shadeName}: {$hex};\n";
            }
        }

        $scss .= "\n// Neutral Colors\n";

        foreach ($theme['neutrals'] as $name => $hex) {
            $scss .= "\${$name}: {$hex};\n";
        }

        return $scss;
    }

    /**
     * Export theme as JSON
     */
    public function exportAsJSON(array $theme): string
    {
        return json_encode($theme, JSON_PRETTY_PRINT);
    }

    /**
     * Convert hex to RGB values (without 'rgb()' wrapper)
     */
    private function hexToRgbValues(string $hex): string
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }

    /**
     * Generate utility CSS classes
     */
    private function generateUtilityClasses(array $theme): string
    {
        $css = "/* Utility Classes */\n";

        foreach ($theme['palette'] as $name => $color) {
            $css .= ".bg-{$name} { background-color: var(--color-{$name}); }\n";
            $css .= ".text-{$name} { color: var(--color-{$name}); }\n";
            $css .= ".border-{$name} { border-color: var(--color-{$name}); }\n\n";
        }

        return $css;
    }
}
```

---

## Step 3: Creating the API Endpoints

### 3.1 Palette Generation API

Create `public/api/generate.php`:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\ThemeGenerator;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$baseColor = $input['baseColor'] ?? '#3B82F6';
$harmonyType = $input['harmonyType'] ?? 'complementary';
$shades = (int)($input['shades'] ?? 5);

try {
    $generator = new ThemeGenerator();
    $theme = $generator->generateTheme($baseColor, $harmonyType, $shades);

    echo json_encode([
        'success' => true,
        'theme' => $theme
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

### 3.2 Export API

Create `public/api/export.php`:

```php
<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\CSSExporter;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$theme = $input['theme'] ?? null;
$format = $input['format'] ?? 'css';

if (!$theme) {
    http_response_code(400);
    echo json_encode(['error' => 'Theme data required']);
    exit;
}

try {
    $exporter = new CSSExporter();

    $output = match($format) {
        'css' => $exporter->exportAsCSS($theme),
        'scss' => $exporter->exportAsSCSS($theme),
        'json' => $exporter->exportAsJSON($theme),
        default => $exporter->exportAsCSS($theme)
    };

    $contentType = match($format) {
        'json' => 'application/json',
        default => 'text/plain'
    };

    header("Content-Type: {$contentType}");
    echo $output;
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

---

## Step 4: Building the Frontend

### 4.1 Main Application Page

Create `public/index.php`:

```php
<?php require_once __DIR__ . '/../vendor/autoload.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Generator - Professional Color Palette Tool</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1>🎨 Theme Generator</h1>
            <p>Create beautiful, harmonious color palettes for your projects</p>
        </header>

        <main class="main-content">
            <!-- Controls Panel -->
            <section class="controls-panel">
                <div class="control-group">
                    <label for="baseColor">Base Color</label>
                    <div class="color-input-wrapper">
                        <input type="color" id="baseColor" value="#3B82F6">
                        <input type="text" id="baseColorHex" value="#3B82F6" pattern="^#[0-9A-Fa-f]{6}$">
                    </div>
                </div>

                <div class="control-group">
                    <label for="harmonyType">Harmony Type</label>
                    <select id="harmonyType">
                        <option value="complementary">Complementary</option>
                        <option value="triadic">Triadic</option>
                        <option value="tetradic">Tetradic</option>
                        <option value="analogous">Analogous</option>
                        <option value="split-complementary">Split Complementary</option>
                        <option value="monochromatic">Monochromatic</option>
                    </select>
                </div>

                <div class="control-group">
                    <label for="shades">Shade Variations</label>
                    <input type="range" id="shades" min="3" max="9" value="5" step="2">
                    <span id="shadesValue">5</span>
                </div>

                <button id="generateBtn" class="btn btn-primary">Generate Theme</button>
                <button id="exportBtn" class="btn btn-secondary">Export Theme</button>
            </section>

            <!-- Palette Preview -->
            <section class="palette-preview" id="palettePreview">
                <div class="loading">Click "Generate Theme" to start</div>
            </section>

            <!-- Theme Preview -->
            <section class="theme-preview">
                <h2>Theme Preview</h2>
                <div class="preview-card">
                    <div class="card-header">
                        <h3>Sample UI Component</h3>
                        <button class="preview-btn btn-primary">Primary Button</button>
                    </div>
                    <div class="card-body">
                        <p>This is how your theme looks in action.</p>
                        <button class="preview-btn btn-secondary">Secondary Button</button>
                        <button class="preview-btn btn-accent">Accent Button</button>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Export Theme</h2>
            <div class="export-options">
                <button class="export-format" data-format="css">CSS</button>
                <button class="export-format" data-format="scss">SCSS</button>
                <button class="export-format" data-format="json">JSON</button>
            </div>
            <pre id="exportOutput"></pre>
            <button id="copyBtn" class="btn btn-primary">Copy to Clipboard</button>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
```

### 4.2 Application Styles

Create `public/assets/css/style.css`:

```css
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

:root {
    --primary: #3B82F6;
    --secondary: #8B5CF6;
    --accent: #10B981;
    --background: #F9FAFB;
    --surface: #FFFFFF;
    --text: #1F2937;
    --text-secondary: #6B7280;
    --border: #E5E7EB;
}

body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: var(--background);
    color: var(--text);
    line-height: 1.6;
}

.container {
    max-width: 1400px;
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

.header p {
    font-size: 1.1rem;
    color: var(--text-secondary);
}

.main-content {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    align-items: start;
}

@media (max-width: 968px) {
    .main-content {
        grid-template-columns: 1fr;
    }
}

/* Controls Panel */
.controls-panel {
    background: var(--surface);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    position: sticky;
    top: 2rem;
}

.control-group {
    margin-bottom: 1.5rem;
}

.control-group label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 600;
    color: var(--text);
}

.color-input-wrapper {
    display: flex;
    gap: 0.5rem;
}

input[type="color"] {
    width: 60px;
    height: 44px;
    border: 2px solid var(--border);
    border-radius: 8px;
    cursor: pointer;
}

input[type="text"] {
    flex: 1;
    padding: 0.75rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    font-family: 'Courier New', monospace;
}

select {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid var(--border);
    border-radius: 8px;
    font-size: 1rem;
    background: white;
    cursor: pointer;
}

input[type="range"] {
    width: calc(100% - 40px);
    margin-right: 10px;
}

#shadesValue {
    display: inline-block;
    width: 30px;
    text-align: center;
    font-weight: 600;
}

.btn {
    width: 100%;
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
    margin-bottom: 0.5rem;
}

.btn-primary:hover {
    background: #2563EB;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
}

.btn-secondary {
    background: var(--surface);
    color: var(--text);
    border: 2px solid var(--border);
}

.btn-secondary:hover {
    background: var(--background);
}

/* Palette Preview */
.palette-preview {
    background: var(--surface);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    min-height: 400px;
}

.loading {
    text-align: center;
    color: var(--text-secondary);
    padding: 3rem;
}

.palette-grid {
    display: grid;
    gap: 1.5rem;
}

.color-group {
    margin-bottom: 2rem;
}

.color-group h3 {
    margin-bottom: 1rem;
    color: var(--text);
    font-size: 1.25rem;
}

.color-swatches {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
    gap: 1rem;
}

.color-swatch {
    aspect-ratio: 1;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: 0.75rem;
    cursor: pointer;
    transition: transform 0.2s ease;
    position: relative;
    overflow: hidden;
}

.color-swatch:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
}

.color-info {
    background: rgba(255, 255, 255, 0.9);
    padding: 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
}

.color-name {
    font-weight: 600;
    margin-bottom: 0.25rem;
}

.color-value {
    font-family: 'Courier New', monospace;
    color: var(--text-secondary);
}

/* Theme Preview */
.theme-preview {
    background: var(--surface);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    grid-column: 1 / -1;
}

.theme-preview h2 {
    margin-bottom: 1.5rem;
}

.preview-card {
    background: var(--background);
    border-radius: 8px;
    overflow: hidden;
    border: 1px solid var(--border);
}

.card-header {
    background: var(--primary);
    color: white;
    padding: 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-body {
    padding: 1.5rem;
}

.preview-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    margin-right: 0.5rem;
}

.preview-btn.btn-primary {
    background: var(--primary);
    color: white;
}

.preview-btn.btn-secondary {
    background: var(--secondary);
    color: white;
}

.preview-btn.btn-accent {
    background: var(--accent);
    color: white;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal.show {
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: var(--surface);
    padding: 2rem;
    border-radius: 12px;
    max-width: 800px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    position: relative;
}

.close {
    position: absolute;
    right: 1rem;
    top: 1rem;
    font-size: 2rem;
    cursor: pointer;
    color: var(--text-secondary);
}

.close:hover {
    color: var(--text);
}

.export-options {
    display: flex;
    gap: 0.5rem;
    margin: 1.5rem 0;
}

.export-format {
    padding: 0.5rem 1rem;
    border: 2px solid var(--border);
    background: white;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
}

.export-format.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
}

#exportOutput {
    background: var(--background);
    padding: 1rem;
    border-radius: 8px;
    overflow-x: auto;
    max-height: 400px;
    margin-bottom: 1rem;
    font-family: 'Courier New', monospace;
    font-size: 0.875rem;
}
```

### 4.3 JavaScript Application Logic

Create `public/assets/js/app.js`:

```javascript
class ThemeGeneratorApp {
    constructor() {
        this.currentTheme = null;
        this.initializeElements();
        this.attachEventListeners();
    }

    initializeElements() {
        this.baseColor = document.getElementById('baseColor');
        this.baseColorHex = document.getElementById('baseColorHex');
        this.harmonyType = document.getElementById('harmonyType');
        this.shades = document.getElementById('shades');
        this.shadesValue = document.getElementById('shadesValue');
        this.generateBtn = document.getElementById('generateBtn');
        this.exportBtn = document.getElementById('exportBtn');
        this.palettePreview = document.getElementById('palettePreview');
        this.exportModal = document.getElementById('exportModal');
        this.exportOutput = document.getElementById('exportOutput');
        this.copyBtn = document.getElementById('copyBtn');
    }

    attachEventListeners() {
        // Color input sync
        this.baseColor.addEventListener('input', (e) => {
            this.baseColorHex.value = e.target.value.toUpperCase();
        });

        this.baseColorHex.addEventListener('input', (e) => {
            if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
                this.baseColor.value = e.target.value;
            }
        });

        // Shades slider
        this.shades.addEventListener('input', (e) => {
            this.shadesValue.textContent = e.target.value;
        });

        // Generate button
        this.generateBtn.addEventListener('click', () => this.generateTheme());

        // Export button
        this.exportBtn.addEventListener('click', () => this.showExportModal());

        // Modal close
        document.querySelector('.close').addEventListener('click', () => {
            this.exportModal.classList.remove('show');
        });

        // Export format buttons
        document.querySelectorAll('.export-format').forEach(btn => {
            btn.addEventListener('click', (e) => {
                document.querySelectorAll('.export-format').forEach(b =>
                    b.classList.remove('active')
                );
                e.target.classList.add('active');
                this.exportTheme(e.target.dataset.format);
            });
        });

        // Copy button
        this.copyBtn.addEventListener('click', () => this.copyToClipboard());

        // Generate initial theme
        this.generateTheme();
    }

    async generateTheme() {
        this.generateBtn.disabled = true;
        this.generateBtn.textContent = 'Generating...';
        this.palettePreview.innerHTML = '<div class="loading">Generating theme...</div>';

        try {
            const response = await fetch('/api/generate.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    baseColor: this.baseColorHex.value,
                    harmonyType: this.harmonyType.value,
                    shades: parseInt(this.shades.value)
                })
            });

            const data = await response.json();

            if (data.success) {
                this.currentTheme = data.theme;
                this.renderPalette(data.theme);
                this.applyThemePreview(data.theme);
            } else {
                throw new Error(data.error);
            }
        } catch (error) {
            console.error('Error generating theme:', error);
            this.palettePreview.innerHTML =
                `<div class="loading" style="color: red;">Error: ${error.message}</div>`;
        } finally {
            this.generateBtn.disabled = false;
            this.generateBtn.textContent = 'Generate Theme';
        }
    }

    renderPalette(theme) {
        let html = '<div class="palette-grid">';

        // Render base palette
        html += '<div class="color-group">';
        html += '<h3>Base Colors</h3>';
        html += '<div class="color-swatches">';

        for (const [name, color] of Object.entries(theme.palette)) {
            html += this.createColorSwatch(name, color.hex, name);
        }

        html += '</div></div>';

        // Render variations
        html += '<div class="color-group">';
        html += '<h3>Color Variations</h3>';

        for (const [colorName, shades] of Object.entries(theme.variations)) {
            html += `<h4 style="margin-top: 1rem; font-size: 1rem; color: #6B7280;">${colorName}</h4>`;
            html += '<div class="color-swatches">';

            for (const [shadeName, hex] of Object.entries(shades)) {
                html += this.createColorSwatch(`${colorName}-${shadeName}`, hex, shadeName);
            }

            html += '</div>';
        }

        html += '</div>';

        // Render neutrals
        html += '<div class="color-group">';
        html += '<h3>Neutral Colors</h3>';
        html += '<div class="color-swatches">';

        for (const [name, hex] of Object.entries(theme.neutrals)) {
            html += this.createColorSwatch(name, hex, name);
        }

        html += '</div></div>';

        html += '</div>';
        this.palettePreview.innerHTML = html;

        // Add click-to-copy functionality
        document.querySelectorAll('.color-swatch').forEach(swatch => {
            swatch.addEventListener('click', () => {
                const hex = swatch.dataset.hex;
                navigator.clipboard.writeText(hex);
                this.showToast(`Copied ${hex} to clipboard!`);
            });
        });
    }

    createColorSwatch(name, hex, displayName) {
        const brightness = this.getBrightness(hex);
        const textColor = brightness > 128 ? '#000000' : '#FFFFFF';

        return `
            <div class="color-swatch" style="background-color: ${hex}" data-hex="${hex}">
                <div class="color-info">
                    <div class="color-name" style="color: #1F2937">${displayName}</div>
                    <div class="color-value">${hex}</div>
                </div>
            </div>
        `;
    }

    getBrightness(hex) {
        const rgb = parseInt(hex.slice(1), 16);
        const r = (rgb >> 16) & 0xff;
        const g = (rgb >>  8) & 0xff;
        const b = (rgb >>  0) & 0xff;
        return (r * 299 + g * 587 + b * 114) / 1000;
    }

    applyThemePreview(theme) {
        const root = document.documentElement;

        // Apply base colors
        if (theme.palette.primary) {
            root.style.setProperty('--primary', theme.palette.primary.hex);
        }
        if (theme.palette.secondary) {
            root.style.setProperty('--secondary', theme.palette.secondary.hex);
        }
        if (theme.palette.accent) {
            root.style.setProperty('--accent', theme.palette.accent.hex);
        }
    }

    showExportModal() {
        if (!this.currentTheme) {
            this.showToast('Please generate a theme first!');
            return;
        }

        this.exportModal.classList.add('show');
        document.querySelector('.export-format').click();
    }

    async exportTheme(format) {
        try {
            const response = await fetch('/api/export.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    theme: this.currentTheme,
                    format: format
                })
            });

            const output = await response.text();
            this.exportOutput.textContent = output;
        } catch (error) {
            console.error('Error exporting theme:', error);
            this.exportOutput.textContent = `Error: ${error.message}`;
        }
    }

    copyToClipboard() {
        const text = this.exportOutput.textContent;
        navigator.clipboard.writeText(text).then(() => {
            this.showToast('Copied to clipboard!');
        });
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
            animation: slideIn 0.3s ease;
        `;

        document.body.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'slideOut 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, 2000);
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new ThemeGeneratorApp();
});

// Add animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);
```

---

## Step 5: Testing Your Theme Generator

### 5.1 Start the Development Server

```bash
cd public
php -S localhost:8000
```

### 5.2 Test Different Harmony Types

Open your browser to `http://localhost:8000` and test:

1. **Complementary**: Select complementary harmony with base color #3B82F6
2. **Triadic**: Try triadic with base color #EF4444
3. **Analogous**: Test analogous with base color #10B981
4. **Monochromatic**: Generate monochromatic theme

### 5.3 Test Export Functionality

1. Generate a theme
2. Click "Export Theme"
3. Try exporting as CSS, SCSS, and JSON
4. Click "Copy to Clipboard" and paste into a file
5. Verify the exported CSS works in a test HTML file

### 5.4 Test Color Copying

1. Click any color swatch
2. Verify the hex code is copied to clipboard
3. Check the toast notification appears

---

## Step 6: Enhancing the Application

### 6.1 Add Preset Management

Add to `public/assets/js/app.js`:

```javascript
savePreset() {
    const name = prompt('Enter preset name:');
    if (!name) return;

    fetch('/api/save-preset.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            name: name,
            theme: this.currentTheme
        })
    }).then(() => {
        this.showToast('Preset saved!');
        this.loadPresets();
    });
}

loadPresets() {
    fetch('/api/list-presets.php')
        .then(r => r.json())
        .then(presets => {
            // Render preset list
            this.renderPresets(presets);
        });
}
```

### 6.2 Add Accessibility Checker

Enhance `src/ThemeGenerator.php`:

```php
public function checkAccessibility(array $theme): array
{
    $results = [];

    foreach ($theme['palette'] as $name => $color) {
        $bgColor = Color::parse($color['hex']);
        $whiteContrast = $this->calculateContrast($bgColor, Color::parse('#FFFFFF'));
        $blackContrast = $this->calculateContrast($bgColor, Color::parse('#000000'));

        $results[$name] = [
            'color' => $color['hex'],
            'whiteContrast' => $whiteContrast,
            'blackContrast' => $blackContrast,
            'wcagAA' => $whiteContrast >= 4.5 || $blackContrast >= 4.5,
            'wcagAAA' => $whiteContrast >= 7 || $blackContrast >= 7
        ];
    }

    return $results;
}

private function calculateContrast(Color $color1, Color $color2): float
{
    $l1 = $this->getRelativeLuminance($color1);
    $l2 = $this->getRelativeLuminance($color2);

    $lighter = max($l1, $l2);
    $darker = min($l1, $l2);

    return ($lighter + 0.05) / ($darker + 0.05);
}

private function getRelativeLuminance(Color $color): float
{
    $rgb = $color->toRgb();

    $r = $this->adjustChannel($rgb['r'] / 255);
    $g = $this->adjustChannel($rgb['g'] / 255);
    $b = $this->adjustChannel($rgb['b'] / 255);

    return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
}

private function adjustChannel(float $channel): float
{
    return $channel <= 0.03928
        ? $channel / 12.92
        : pow(($channel + 0.055) / 1.055, 2.4);
}
```

---

## Troubleshooting

### Issue: "Class not found" errors

**Solution**: Run `composer dump-autoload` to regenerate autoload files.

```bash
composer dump-autoload
```

### Issue: API endpoints returning 404

**Solution**: Ensure your web server is configured to route API requests correctly. For PHP's built-in server, make sure you're running it from the `public` directory.

### Issue: Colors not displaying correctly

**Solution**: Check browser console for JavaScript errors. Ensure the API is returning valid color data.

```javascript
// Add debugging
console.log('Theme data:', data.theme);
```

### Issue: Export modal not showing

**Solution**: Check that the modal CSS classes are being applied. Verify JavaScript console for errors.

```javascript
// Debug modal
console.log('Modal element:', this.exportModal);
console.log('Modal classes:', this.exportModal.classList);
```

### Issue: Presets directory not writable

**Solution**: Set proper permissions on the presets directory.

```bash
chmod 755 public/presets
```

---

## Conclusion

Congratulations! You've built a complete theme generator web application with:

- Real-time color palette generation using multiple harmony types
- Interactive color swatches with click-to-copy functionality
- Live theme preview on sample UI components
- Export functionality for CSS, SCSS, and JSON formats
- A professional, responsive user interface

### Next Steps

1. **Add more features**:
   - Theme presets management
   - Color accessibility checker
   - Theme history/undo functionality
   - Social sharing of themes

2. **Enhance the UI**:
   - Add animations and transitions
   - Implement dark mode
   - Create mobile-responsive design improvements

3. **Add persistence**:
   - Save themes to database
   - User authentication
   - Public theme gallery

### Related Resources

- [Color Palette PHP Documentation](/color-palette-php/)
- [API Reference](/color-palette-php/api/)
- [Color Harmony Guide](/color-palette-php/guides/color-harmony)
- [Accessibility Guide](/color-palette-php/guides/accessibility)

### Share Your Creation

Built something cool with this tutorial? Share it with the community!

---

**Tutorial completed!** You now have a fully functional theme generator that can serve as the foundation for more advanced color tools and applications.
