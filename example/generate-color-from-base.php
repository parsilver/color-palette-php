<?php

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = isset($_GET['color']) ? $_GET['color'] : '#2196F3';
$baseColor = Color::fromHex($baseColor);
$generator = new PaletteGenerator($baseColor);

// Generate different palettes
$websiteTheme = $generator->websiteTheme();
$complementary = $generator->complementary();
$analogous = $generator->analogous(3);

// Convert colors to hex arrays
$themeColors = $websiteTheme->toHexArray();
$complementaryColors = array_values($complementary->toHexArray());
$analogousColors = array_values($analogous->toHexArray());

// Ensure all theme colors are present
$themeColors = array_merge([
    'primary' => $baseColor->toHex(),
    'secondary' => $complementaryColors[1] ?? $baseColor->lighten(10)->toHex(),
    'accent' => $analogousColors[2] ?? $baseColor->saturate(10)->toHex(),
    'background' => '#FFFFFF',
    'surface' => '#F7FAFC',
    'text' => '#1A202C',
    'text_light' => '#4A5568',
], $themeColors);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Palette Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <style>
        [v-cloak] { display: none; }
        .color-card {
            transition: all 0.3s ease;
        }
        .color-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, <?php echo $themeColors['primary']; ?> 0%, <?php echo $themeColors['secondary']; ?> 100%);
        }
        .example-card {
            transition: all 0.3s ease;
        }
        .example-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -3px rgb(0 0 0 / 0.1);
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div id="app" v-cloak>
        <!-- Header with Gradient -->
        <div class="gradient-bg text-white py-8">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Color Palette Generator</h1>
                        <p class="text-lg opacity-90">Create beautiful color harmonies from a base color</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-4">
                        <button @click="setRandomColor" 
                                :disabled="loading"
                                class="px-6 py-3 bg-white text-purple-700 rounded-lg hover:bg-gray-100 transition-colors font-medium flex items-center disabled:opacity-50">
                            <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ loading ? 'Loading...' : 'Random Color' }}
                        </button>
                        <div class="relative group">
                            <input type="color" 
                                   v-model="baseColor"
                                   @input="generatePalettes"
                                   class="w-12 h-12 rounded-lg cursor-pointer border-2 border-white/20">
                            <div class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-black/80 px-3 py-1 rounded text-sm opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                {{ baseColor }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid gap-8">
                <!-- Color Harmonies -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Base Color -->
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <h2 class="text-xl font-semibold mb-4">Base Color</h2>
                        <div class="color-card rounded-lg overflow-hidden shadow cursor-pointer"
                             @click="copyColor(baseColor)">
                            <div class="aspect-square" :style="{ backgroundColor: baseColor }"></div>
                            <div class="p-3 bg-white">
                                <p class="text-sm text-gray-500 font-mono">{{ baseColor }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Complementary -->
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <h2 class="text-xl font-semibold mb-4">Complementary</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(color, index) in colors.complementary" 
                                 :key="index"
                                 class="color-card rounded-lg overflow-hidden shadow cursor-pointer"
                                 @click="copyColor(color)">
                                <div class="aspect-square" :style="{ backgroundColor: color }"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500 font-mono">{{ color }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Analogous -->
                    <div class="bg-white rounded-xl p-6 shadow-lg">
                        <h2 class="text-xl font-semibold mb-4">Analogous</h2>
                        <div class="grid grid-cols-3 gap-3">
                            <div v-for="(color, index) in colors.analogous" 
                                 :key="index"
                                 class="color-card rounded-lg overflow-hidden shadow cursor-pointer"
                                 @click="copyColor(color)">
                                <div class="aspect-square" :style="{ backgroundColor: color }"></div>
                                <div class="p-2 bg-white">
                                    <p class="text-sm text-gray-500 font-mono">{{ color }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Website Theme -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-6">Website Theme</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div v-for="(color, name) in colors.theme" 
                             :key="name"
                             class="color-card rounded-lg overflow-hidden shadow cursor-pointer"
                             @click="copyColor(color)">
                            <div class="h-24" :style="{ backgroundColor: color }"></div>
                            <div class="p-3 bg-white">
                                <p class="font-medium text-gray-700">{{ formatColorName(name) }}</p>
                                <p class="text-sm text-gray-500 font-mono">{{ color }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Real World Examples -->
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Button Examples -->
                    <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Buttons & CTAs</h3>
                        <div class="space-y-4">
                            <button :style="{ 
                                backgroundColor: colors.theme.primary || '#4A90E2',
                                color: '#FFFFFF'
                            }" 
                                class="w-full px-4 py-2 rounded-lg hover:opacity-90 transition-opacity font-medium">
                                Primary Button
                            </button>
                            <button :style="{ 
                                backgroundColor: colors.theme.secondary || '#2C5282',
                                color: '#FFFFFF'
                            }" 
                                class="w-full px-4 py-2 rounded-lg hover:opacity-90 transition-opacity font-medium">
                                Secondary Button
                            </button>
                            <button :style="{ 
                                border: `2px solid ${colors.theme.primary || '#4A90E2'}`,
                                color: colors.theme.primary || '#4A90E2',
                                backgroundColor: '#FFFFFF'
                            }" 
                                class="w-full px-4 py-2 rounded-lg hover:opacity-90 transition-opacity font-medium">
                                Outlined Button
                            </button>
                        </div>
                    </div>

                    <!-- Card Design -->
                    <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Card Design</h3>
                        <div class="rounded-lg overflow-hidden shadow">
                            <div :style="{ backgroundColor: colors.theme.primary || '#4A90E2' }" class="h-32"></div>
                            <div :style="{ backgroundColor: colors.theme.background || '#FFFFFF' }" class="p-4">
                                <h4 :style="{ color: colors.theme.text || '#1A202C' }" class="font-semibold mb-2">
                                    Card Title
                                </h4>
                                <p :style="{ color: colors.theme.text_light || '#4A5568' }" class="text-sm">
                                    This is a sample card design using your color palette.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Example -->
                    <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900">Navigation</h3>
                        <div :style="{ backgroundColor: colors.theme.primary || '#4A90E2' }" class="rounded-lg p-4">
                            <div class="flex justify-between items-center">
                                <div class="font-semibold text-white">Logo</div>
                                <div class="flex space-x-4 text-white">
                                    <a href="#" class="hover:opacity-80">Home</a>
                                    <a href="#" class="hover:opacity-80">About</a>
                                    <a href="#" class="hover:opacity-80">Contact</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CSS Variables -->
                <div class="bg-white rounded-xl p-6 shadow-lg">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">CSS Variables</h3>
                    <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm font-mono">:root {
    --color-primary: {{ colors.theme.primary || '#4A90E2' }};
    --color-secondary: {{ colors.theme.secondary || '#2C5282' }};
    --color-accent: {{ colors.theme.accent || '#48BB78' }};
    --color-background: {{ colors.theme.background || '#FFFFFF' }};
    --color-surface: {{ colors.theme.surface || '#F7FAFC' }};
    --color-text: {{ colors.theme.text || '#1A202C' }};
    --color-text-light: {{ colors.theme.text_light || '#4A5568' }};
}</pre>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="showToast" 
             class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
            </svg>
            Color copied!
        </div>
    </div>

    <script>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    baseColor: <?php echo json_encode($baseColor->toHex()); ?>,
                    loading: false,
                    showToast: false,
                    colors: {
                        theme: <?php echo json_encode($themeColors); ?>,
                        complementary: <?php echo json_encode($complementaryColors); ?>,
                        analogous: <?php echo json_encode($analogousColors); ?>
                    }
                }
            },
            methods: {
                generatePalettes() {
                    window.location.href = `?color=${encodeURIComponent(this.baseColor)}`;
                },
                setRandomColor() {
                    this.loading = true;
                    const randomColor = '#' + Math.floor(Math.random()*16777215).toString(16).padStart(6, '0');
                    this.baseColor = randomColor;
                    this.generatePalettes();
                },
                async copyColor(color) {
                    try {
                        await navigator.clipboard.writeText(color);
                        this.showToast = true;
                        setTimeout(() => {
                            this.showToast = false;
                        }, 2000);
                    } catch (error) {
                        console.error('Failed to copy:', error);
                    }
                },
                formatColorName(name) {
                    const nameMap = {
                        'primary': 'Primary',
                        'secondary': 'Secondary',
                        'accent': 'Accent',
                        'background': 'Background',
                        'surface': 'Surface',
                        'text': 'Text',
                        'text_light': 'Text Light'
                    };
                    return nameMap[name] || name;
                }
            }
        }).mount('#app')
    </script>
</body>
</html> 