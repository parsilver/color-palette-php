<?php

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ImageLoaderFactory;

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    try {
        $loader = ImageLoaderFactory::create();
        $image = null;
        $tempFile = null;

        if (isset($_FILES['image'])) {
            // Handle uploaded image
            if (! isset($_FILES['image']['tmp_name']) || ! is_uploaded_file($_FILES['image']['tmp_name'])) {
                throw new Exception('No file was uploaded.');
            }

            $sourceFile = $_FILES['image']['tmp_name'];

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($fileInfo, $sourceFile);
            finfo_close($fileInfo);

            if (! in_array($mimeType, $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
            }

            // Copy to a new temporary file with proper extension
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => throw new Exception('Unsupported image type')
            };

            $tempFile = tempnam(sys_get_temp_dir(), 'img_').'.'.$extension;
            if (! copy($sourceFile, $tempFile)) {
                throw new Exception('Failed to process uploaded file.');
            }

            $image = $loader->load($tempFile);
        } elseif (isset($_POST['imageUrl'])) {
            // Handle image URL
            $imageUrl = filter_var($_POST['imageUrl'], FILTER_SANITIZE_URL);
            if (! filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                throw new Exception('Invalid image URL.');
            }

            // Get image content with proper headers
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $imageUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpCode !== 200) {
                throw new Exception('Failed to download image from URL.');
            }

            // Validate content type
            if (! str_starts_with($contentType, 'image/')) {
                throw new Exception('URL does not point to a valid image.');
            }

            // Save to temporary file with proper extension
            $extension = match ($contentType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => throw new Exception('Unsupported image type')
            };

            $tempFile = tempnam(sys_get_temp_dir(), 'img_').'.'.$extension;
            if (file_put_contents($tempFile, $imageData) === false) {
                throw new Exception('Failed to save image data.');
            }

            $image = $loader->load($tempFile);
        } else {
            throw new Exception('No image provided');
        }

        // Extract colors
        $extractor = ColorExtractorFactory::createForImage($image);
        $palette = $extractor->extract($image, 6);

        // Get suggested surface colors
        $surfaceColors = $palette->getSuggestedSurfaceColors();

        // Clean up temporary file
        if ($tempFile && file_exists($tempFile)) {
            unlink($tempFile);
        }

        // Prepare response
        $response = [
            'success' => true,
            'dominantColors' => array_map(fn ($color) => $color->toHex(), $palette->getColors()),
            'surfaceColors' => array_map(fn ($color) => $color->toHex(), $surfaceColors),
        ];

        echo json_encode($response);
        exit;
    } catch (Exception $e) {
        // Clean up temporary file in case of error
        if (isset($tempFile) && $tempFile && file_exists($tempFile)) {
            unlink($tempFile);
        }

        echo json_encode([
            'success' => false,
            'error' => $e->getMessage(),
        ]);
        exit;
    }
}

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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .example-card {
            transition: all 0.3s ease;
        }
        .example-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px -3px rgb(0 0 0 / 0.1);
        }
        .error-shake {
            animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
        }
        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div id="app" v-cloak>
        <!-- Header with Gradient -->
        <div class="gradient-bg text-white py-8">
            <div class="max-w-7xl mx-auto px-4">
                <div class="py-4">
                    <a href="generate-color-from-base.php" class="text-white hover:text-gray-200 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
                        </svg>
                        Try Color Generator from Base Color
                    </a>
                </div>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-4xl font-bold mb-2">Color Palette Generator</h1>
                        <p class="text-lg opacity-90">Extract beautiful color palettes from any image</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-4">
                        <button @click="generateRandomImage" 
                                :disabled="loading"
                                class="px-6 py-3 bg-white text-purple-700 rounded-lg hover:bg-gray-100 transition-colors font-medium flex items-center disabled:opacity-50">
                            <svg v-if="!loading" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else class="animate-spin h-5 w-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ loading ? 'Loading...' : 'Random Image' }}
                        </button>
                        <input type="file" ref="fileInput" @change="handleFileUpload" class="hidden" accept="image/*">
                        <button @click="$refs.fileInput.click()"
                                :disabled="loading"
                                class="px-6 py-3 bg-white/10 text-white rounded-lg hover:bg-white/20 transition-colors font-medium flex items-center disabled:opacity-50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 5a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V7a2 2 0 00-2-2h-1.586a1 1 0 01-.707-.293l-1.121-1.121A2 2 0 0011.172 3H8.828a2 2 0 00-1.414.586L6.293 4.707A1 1 0 015.586 5H4zm6 9a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd" />
                            </svg>
                            Upload Image
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="grid lg:grid-cols-5 gap-8">
                <!-- Left Column: Image Preview -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl p-4 shadow-lg">
                        <!-- Image Preview -->
                        <div v-if="imageUrl" class="relative rounded-lg overflow-hidden">
                            <img :src="imageUrl" alt="Selected Image" class="w-full h-auto">
                            <div class="absolute inset-0 bg-black bg-opacity-0 hover:bg-opacity-10 transition-all">
                                <button @click="clearImage" 
                                        class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Drag & Drop Zone -->
                        <div v-if="!imageUrl && !loading" 
                             class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center"
                             @dragover.prevent="dragover = true"
                             @dragleave.prevent="dragover = false"
                             @drop.prevent="handleDrop"
                             :class="{ 'border-blue-500 bg-blue-50': dragover }">
                            <div class="space-y-4">
                                <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="text-gray-600">Drag and drop your image here</p>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8">
                            <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-purple-500 border-t-transparent"></div>
                            <p class="mt-2 text-gray-600">Extracting colors...</p>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Color Palettes -->
                <div class="lg:col-span-3 space-y-6">
                    <!-- Dominant Colors -->
                    <div v-if="colors.dominant.length" class="bg-white rounded-xl p-6 shadow-lg">
                        <h2 class="text-2xl font-semibold mb-4">Dominant Colors</h2>
                        <div class="grid grid-cols-3 sm:grid-cols-6 gap-4">
                            <div v-for="color in colors.dominant" 
                                 :key="color"
                                 class="color-card rounded-lg overflow-hidden shadow cursor-pointer"
                                 @click="copyColor(color)">
                                <div class="aspect-square" :style="{ backgroundColor: color }"></div>
                                <div class="p-2 bg-white text-center">
                                    <p class="text-sm text-gray-500 font-mono">{{ color }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Surface Colors -->
                    <div v-if="colors.dominant.length" class="bg-white rounded-xl p-6 shadow-lg">
                        <h2 class="text-2xl font-semibold mb-4">Surface Colors</h2>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                            <div v-for="(color, name) in colors.surface" 
                                 :key="name"
                                 class="color-card rounded-lg overflow-hidden shadow cursor-pointer"
                                 @click="copyColor(color)">
                                <div class="aspect-square" :style="{ backgroundColor: color }"></div>
                                <div class="p-3 bg-white">
                                    <p class="font-medium text-gray-700 capitalize">{{ name.replace(/_/g, ' ') }}</p>
                                    <p class="text-sm text-gray-500 font-mono">{{ color }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Real World Examples Section -->
        <div v-if="colors.dominant.length" class="max-w-7xl mx-auto px-4 py-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Real World Examples</h2>
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Button Examples -->
                <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                    <h3 class="text-lg font-semibold mb-4">Buttons & CTAs</h3>
                    <div class="space-y-4">
                        <button :style="{ backgroundColor: colors.dominant[0] }" 
                                class="w-full px-4 py-2 text-white rounded-lg hover:opacity-90 transition-opacity">
                            Primary Button
                        </button>
                        <button :style="{ backgroundColor: colors.surface?.accent || colors.dominant[1] }" 
                                class="w-full px-4 py-2 text-white rounded-lg hover:opacity-90 transition-opacity">
                            Secondary Button
                        </button>
                        <button :style="{ 
                                border: `2px solid ${colors.dominant[0]}`,
                                color: colors.dominant[0]
                            }" 
                                class="w-full px-4 py-2 rounded-lg hover:opacity-90 transition-opacity">
                            Outlined Button
                        </button>
                    </div>
                </div>

                <!-- Card Design -->
                <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                    <h3 class="text-lg font-semibold mb-4">Card Design</h3>
                    <div class="rounded-lg overflow-hidden">
                        <div :style="{ backgroundColor: colors.dominant[0] }" class="h-32"></div>
                        <div :style="{ backgroundColor: colors.surface?.background || colors.dominant[3] }" class="p-4">
                            <h4 :style="{ color: colors.surface?.text || '#1a202c' }" class="font-semibold mb-2">
                                Card Title
                            </h4>
                            <p :style="{ color: colors.surface?.text_light || '#4a5568' }" class="text-sm">
                                This is a sample card design using the extracted color palette.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Navigation Example -->
                <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                    <h3 class="text-lg font-semibold mb-4">Navigation</h3>
                    <div :style="{ backgroundColor: colors.dominant[0] }" class="rounded-lg p-4">
                        <div class="flex justify-between items-center text-white">
                            <div class="font-semibold">Logo</div>
                            <div class="flex space-x-4">
                                <a href="#" class="hover:opacity-80">Home</a>
                                <a href="#" class="hover:opacity-80">About</a>
                                <a href="#" class="hover:opacity-80">Contact</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alert Component -->
                <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                    <h3 class="text-lg font-semibold mb-4">Alerts</h3>
                    <div class="space-y-4">
                        <div :style="{ 
                                backgroundColor: colors.surface?.background || colors.dominant[3],
                                borderLeft: `4px solid ${colors.dominant[0]}`
                            }" 
                             class="p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 mr-2" :style="{ color: colors.dominant[0] }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                                <p :style="{ color: colors.surface?.text || '#1a202c' }">
                                    This is an info alert message
                                </p>
                            </div>
                        </div>
                        <div :style="{ 
                                backgroundColor: `${colors.dominant[0]}22`,
                                borderLeft: `4px solid ${colors.dominant[0]}`
                            }" 
                             class="p-4 rounded-lg">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 mr-2" :style="{ color: colors.dominant[0] }" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                                <p :style="{ color: colors.dominant[0] }">
                                    This is a warning alert message
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Elements -->
                <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                    <h3 class="text-lg font-semibold mb-4">Form Elements</h3>
                    <div class="space-y-4">
                        <div>
                            <label :style="{ color: colors.surface?.text || '#1a202c' }" class="block text-sm font-medium mb-1">
                                Input Field
                            </label>
                            <input type="text" 
                                   placeholder="Enter text..."
                                   :style="{ 
                                       borderColor: colors.dominant[0],
                                       '--tw-ring-color': colors.dominant[0]
                                   }"
                                   class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-opacity-50 outline-none">
                        </div>
                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" 
                                       :style="{ 
                                           '--tw-ring-color': colors.dominant[0],
                                           accentColor: colors.dominant[0]
                                       }"
                                       class="rounded">
                                <span :style="{ color: colors.surface?.text || '#1a202c' }" class="ml-2 text-sm">
                                    Checkbox Example
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Progress Indicators -->
                <div class="bg-white rounded-xl p-6 shadow-lg example-card">
                    <h3 class="text-lg font-semibold mb-4">Progress Indicators</h3>
                    <div class="space-y-4">
                        <!-- Progress Bar -->
                        <div class="space-y-2">
                            <div class="h-2 rounded-full bg-gray-200">
                                <div :style="{ backgroundColor: colors.dominant[0] }" 
                                     class="h-full w-2/3 rounded-full"></div>
                            </div>
                            <div class="h-2 rounded-full bg-gray-200">
                                <div :style="{ backgroundColor: colors.surface?.accent || colors.dominant[1] }" 
                                     class="h-full w-1/3 rounded-full"></div>
                            </div>
                        </div>
                        <!-- Steps -->
                        <div class="flex justify-between">
                            <div v-for="i in 4" :key="i" class="flex items-center">
                                <div :style="{ 
                                        backgroundColor: i <= 2 ? colors.dominant[0] : 'transparent',
                                        borderColor: colors.dominant[0]
                                    }" 
                                     class="w-4 h-4 rounded-full border-2"></div>
                                <div v-if="i < 4" :style="{ backgroundColor: i < 2 ? colors.dominant[0] : '#e2e8f0' }" 
                                     class="h-1 w-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CSS Code Example -->
            <div class="mt-8 bg-white rounded-xl p-6 shadow-lg">
                <h3 class="text-lg font-semibold mb-4">CSS Variables Example</h3>
                <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto">
:root {
    --color-primary: {{ colors.dominant[0] }};
    --color-secondary: {{ colors.surface?.accent || colors.dominant[1] }};
    --color-background: {{ colors.surface?.background || colors.dominant[3] }};
    --color-surface: {{ colors.surface?.surface || colors.dominant[4] }};
    --color-text: {{ colors.surface?.text || '#1a202c' }};
    --color-text-light: {{ colors.surface?.text_light || '#4a5568' }};
}</pre>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="showToast" 
             class="fixed bottom-4 right-4 bg-gray-800 text-white px-4 py-2 rounded-lg shadow-lg">
            Color copied to clipboard!
        </div>

        <!-- Error Alert -->
        <div v-if="error" 
             class="fixed top-4 left-1/2 transform -translate-x-1/2 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg error-shake">
            <div class="flex items-center space-x-2">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ error }}</span>
                <button @click="error = null" class="ml-4 hover:text-gray-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <script>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    imageUrl: null,
                    loading: false,
                    dragover: false,
                    showToast: false,
                    error: null,
                    colors: {
                        dominant: [],
                        surface: {}
                    }
                }
            },
            mounted() {
                // Generate random image on load
                this.generateRandomImage()
            },
            methods: {
                async handleFileUpload(event) {
                    const file = event.target.files[0]
                    if (!file) return

                    // Validate file type
                    if (!file.type.match(/^image\/(jpeg|png|gif)$/)) {
                        this.showError('Please upload a valid image file (JPG, PNG, or GIF).')
                        return
                    }

                    // Validate file size (max 10MB)
                    if (file.size > 10 * 1024 * 1024) {
                        this.showError('File size should be less than 10MB.')
                        return
                    }

                    await this.processImage(file)
                },
                async handleDrop(event) {
                    this.dragover = false
                    const file = event.dataTransfer.files[0]
                    
                    if (!file || !file.type.startsWith('image/')) {
                        this.showError('Please drop a valid image file.')
                        return
                    }

                    await this.processImage(file)
                },
                async processImage(file) {
                    this.loading = true
                    this.error = null
                    
                    try {
                        // Create object URL for preview
                        if (this.imageUrl) {
                            URL.revokeObjectURL(this.imageUrl)
                        }
                        this.imageUrl = URL.createObjectURL(file)

                        const formData = new FormData()
                        formData.append('image', file)

                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            body: formData
                        })
                        
                        const data = await response.json()
                        if (!data.success) {
                            throw new Error(data.error || 'Failed to process image.')
                        }

                        this.colors.dominant = data.dominantColors
                        this.colors.surface = data.surfaceColors
                    } catch (error) {
                        this.showError(error.message)
                        this.clearImage()
                    } finally {
                        this.loading = false
                    }
                },
                async generateRandomImage() {
                    this.loading = true
                    this.error = null

                    try {
                        const randomImageUrl = `https://picsum.photos/800/600?random=${Date.now()}`
                        this.imageUrl = randomImageUrl

                        const response = await fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `imageUrl=${encodeURIComponent(randomImageUrl)}`
                        })

                        const data = await response.json()
                        if (!data.success) {
                            throw new Error(data.error || 'Failed to generate random image.')
                        }

                        this.colors.dominant = data.dominantColors
                        this.colors.surface = data.surfaceColors
                    } catch (error) {
                        this.showError(error.message)
                        this.clearImage()
                    } finally {
                        this.loading = false
                    }
                },
                clearImage() {
                    if (this.imageUrl && this.imageUrl.startsWith('blob:')) {
                        URL.revokeObjectURL(this.imageUrl)
                    }
                    this.imageUrl = null
                    this.colors.dominant = []
                    this.colors.surface = {}
                },
                async copyColor(color) {
                    try {
                        await navigator.clipboard.writeText(color)
                        this.showToast = true
                        setTimeout(() => {
                            this.showToast = false
                        }, 2000)
                    } catch (error) {
                        this.showError('Failed to copy color to clipboard.')
                    }
                },
                showError(message) {
                    this.error = message
                    setTimeout(() => {
                        this.error = null
                    }, 5000)
                }
            }
        }).mount('#app')
    </script>
</body>
</html>