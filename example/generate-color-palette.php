<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;
use Farzai\ColorPalette\ThemeGenerator;
use Farzai\ColorPalette\GdColorExtractor;
use Farzai\ColorPalette\ImageLoader;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\PaletteFactory;
use Farzai\ColorPalette\ThemeFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;

// Create HTTP client and factories for ImageLoader
$httpClient = new Client();
$httpFactory = new HttpFactory();

// Create image loader with dependencies
$imageLoader = new ImageLoader(
    $httpClient,
    $httpFactory,
    $httpFactory,
    'gd',
    sys_get_temp_dir()
);

// If this is an AJAX request to process an image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $uploadedImage = null;
        $imageUrl = null;
        
        // Handle file upload
        if (isset($_FILES['image'])) {
            $file = $_FILES['image'];
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception(match($file['error']) {
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    default => 'Unknown upload error'
                });
            }

            // Verify file type
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new Exception("Invalid image file");
            }
            
            $mimeType = $imageInfo['mime'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            
            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception("Please upload a valid image file (JPEG, PNG, or GIF)");
            }
            
            // Create a temporary copy with proper extension
            $extension = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                default => throw new Exception("Unsupported image type")
            };
            
            $tempFile = tempnam(sys_get_temp_dir(), 'palette_');
            if ($tempFile === false) {
                throw new Exception("Failed to create temporary file");
            }
            
            // Add proper extension
            $uploadedImage = $tempFile . '.' . $extension;
            rename($tempFile, $uploadedImage);
            
            // Copy uploaded file to temporary file
            if (!copy($file['tmp_name'], $uploadedImage)) {
                throw new Exception("Failed to process uploaded file");
            }
        } elseif (isset($_POST['random'])) {
            // Handle random image request
            $imageUrl = 'https://picsum.photos/800/600?random=' . time();
        } else {
            throw new Exception("No image provided");
        }

        // Load and process image
        $image = $imageLoader->load($uploadedImage ?? $imageUrl);
        
        // Extract colors from image using factory
        $extractor = ColorExtractorFactory::createForImage($image);
        $extractedPalette = $extractor->extract($image, 5);
        
        // Get the dominant color
        $dominantColor = $extractedPalette->getColors()[0];
        
        // Create palette factory and generate palettes
        $paletteFactory = new PaletteFactory($dominantColor);
        $palettes = $paletteFactory->createPredefinedPalettes();
        
        // Create theme factory and generate themes
        $themeFactory = new ThemeFactory();
        $themes = $themeFactory->createFromPalettes($palettes);

        // Prepare response data
        $response = [
            'success' => true,
            'dominantColor' => $dominantColor->toHex(),
            'palettes' => [],
            'themes' => []
        ];

        foreach ($palettes as $name => $data) {
            $response['palettes'][$name] = [
                'description' => $data['description'],
                'colors' => array_map(fn($color) => [
                    'hex' => $color->toHex(),
                    'rgb' => $color->toRgb(),
                    'isLight' => $color->isLight()
                ], $data['palette']->getColors())
            ];

            $theme = $themes[$name];
            $response['themes'][$name] = [
                'background' => $theme->getBackgroundColor()->toHex(),
                'surface' => $theme->getSurfaceColor()->toHex(),
                'primary' => $theme->getPrimaryColor()->toHex(),
                'secondary' => $theme->getSecondaryColor()->toHex(),
                'accent' => $theme->getAccentColor()->toHex()
            ];
        }

        echo json_encode($response);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } finally {
        // Clean up temporary files
        if (isset($uploadedImage) && file_exists($uploadedImage)) {
            unlink($uploadedImage);
        }
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Color Palette Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen font-sans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center space-x-4">
                <h1 class="text-2xl font-bold text-gray-900">Image Color Palette Generator</h1>
                <span class="text-sm text-gray-500">Extract beautiful color palettes from images</span>
            </div>
            <div class="flex gap-4">
                <div class="relative">
                    <input 
                        type="file" 
                        id="imageInput" 
                        accept="image/jpeg,image/png,image/gif"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                    >
                    <button 
                        type="button"
                        class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center gap-2 hover:scale-105"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Upload Image
                    </button>
                </div>
                <button 
                    id="randomButton"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center gap-2 hover:scale-105"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Random Image
                </button>
            </div>
        </div>

        <div id="errorContainer" class="hidden mb-6 bg-red-50 text-red-600 p-4 rounded-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span id="errorMessage"></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Source Image -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-sm p-6 sticky top-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Source Image
                    </h2>
                    <div class="relative w-full aspect-[4/3] rounded-xl overflow-hidden shadow-md mb-4">
                        <img 
                            id="sourceImage"
                            src="https://picsum.photos/800/600?random=1"
                            alt="Source Image" 
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <div id="dominantColorContainer" class="hidden flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">Dominant Color:</span>
                        <div class="flex items-center gap-2">
                            <div id="dominantColorSwatch" class="w-6 h-6 rounded shadow-sm"></div>
                            <code id="dominantColorHex" class="text-sm"></code>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Generated Palettes -->
            <div id="palettesContainer" class="lg:col-span-2">
                <!-- Palettes will be dynamically inserted here -->
            </div>
        </div>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 space-y-2"></div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center gap-4">
            <div class="animate-spin rounded-full h-8 w-8 border-4 border-blue-500 border-t-transparent"></div>
            <span class="text-gray-700 font-medium">Processing image...</span>
        </div>
    </div>

    <script>
        // Icons for palette types
        const paletteIcons = {
            'Vibrant': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
            'Pastel': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>',
            'Golden Ratio': '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>'
        };

        function showToast(message, type = 'success') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-gray-900';
            
            toast.className = `${bgColor} text-white px-4 py-2 rounded-lg shadow-lg transform transition-all duration-300 opacity-0 translate-y-2 flex items-center space-x-2`;
            
            const icon = document.createElement('span');
            icon.innerHTML = type === 'success' 
                ? '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
                : '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
            
            const text = document.createElement('span');
            text.textContent = message;
            
            toast.appendChild(icon);
            toast.appendChild(text);
            container.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
            });

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    container.removeChild(toast);
                }, 300);
            }, 3000);
        }

        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Color copied: ' + text);
            }).catch(() => {
                showToast('Failed to copy color', 'error');
            });
        }

        function copyPalette(colors) {
            navigator.clipboard.writeText(colors).then(() => {
                showToast('All colors copied to clipboard');
            }).catch(() => {
                showToast('Failed to copy colors', 'error');
            });
        }

        function showError(message) {
            const container = document.getElementById('errorContainer');
            const messageElement = document.getElementById('errorMessage');
            messageElement.textContent = message;
            container.classList.remove('hidden');
        }

        function hideError() {
            const container = document.getElementById('errorContainer');
            container.classList.add('hidden');
        }

        function showLoading() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingOverlay').classList.add('hidden');
        }

        function updateUI(data) {
            // Update dominant color
            const dominantColorContainer = document.getElementById('dominantColorContainer');
            const dominantColorSwatch = document.getElementById('dominantColorSwatch');
            const dominantColorHex = document.getElementById('dominantColorHex');
            
            dominantColorContainer.classList.remove('hidden');
            dominantColorSwatch.style.backgroundColor = data.dominantColor;
            dominantColorHex.textContent = data.dominantColor;

            // Update palettes
            const palettesContainer = document.getElementById('palettesContainer');
            palettesContainer.innerHTML = '';

            Object.entries(data.palettes).forEach(([name, palette]) => {
                const colors = palette.colors;
                const theme = data.themes[name];

                const paletteHtml = `
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all duration-200">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-6">
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-gray-100 rounded-lg">
                                        ${paletteIcons[name]}
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900">${name}</h3>
                                        <p class="text-sm text-gray-600">${palette.description}</p>
                                    </div>
                                </div>
                                <button 
                                    onclick="copyPalette('${colors.map(c => c.hex).join(', ')}')"
                                    class="text-sm px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-all duration-200 flex items-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                    </svg>
                                    Copy Colors
                                </button>
                            </div>

                            <!-- Color Swatches -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
                                ${colors.map(color => `
                                    <div 
                                        class="group relative flex items-center rounded-xl overflow-hidden transition-all duration-200 hover:scale-[1.02] hover:shadow-md cursor-pointer"
                                        onclick="copyToClipboard('${color.hex}')"
                                        style="background-color: ${color.hex}; color: ${color.isLight ? '#000000' : '#FFFFFF'}"
                                    >
                                        <div class="w-full h-16 flex items-center justify-between px-4">
                                            <div class="flex items-center space-x-3">
                                                <span class="font-mono font-medium">${color.hex}</span>
                                                <span class="text-sm opacity-75">RGB(${Object.values(color.rgb).join(', ')})</span>
                                            </div>
                                            <span class="opacity-0 group-hover:opacity-100 transition-opacity text-sm flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                                </svg>
                                                Copy
                                            </span>
                                        </div>
                                    </div>
                                `).join('')}
                            </div>

                            <!-- Theme Preview -->
                            <div class="mt-6 pt-6 border-t border-gray-100">
                                <h4 class="text-sm font-semibold text-gray-900 mb-3 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                    </svg>
                                    Theme Preview
                                </h4>
                                <div class="rounded-xl overflow-hidden shadow-sm"
                                    style="background-color: ${theme.background}">
                                    <div class="p-4">
                                        <div class="rounded-lg p-4 transition-all duration-200 hover:shadow-md"
                                            style="background-color: ${theme.surface}">
                                            <h5 class="font-semibold mb-2" style="color: ${theme.primary}">
                                                Sample Content
                                            </h5>
                                            <p style="color: ${theme.secondary}">
                                                This is how your theme might look in a real application.
                                                <span style="color: ${theme.accent}">
                                                    With different color roles.
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                palettesContainer.insertAdjacentHTML('beforeend', paletteHtml);
            });
        }

        async function processImage(formData) {
            try {
                showLoading();
                hideError();

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.error);
                }

                updateUI(data);
            } catch (error) {
                showError(error.message);
            } finally {
                hideLoading();
            }
        }

        // Handle file upload
        document.getElementById('imageInput').addEventListener('change', async (event) => {
            const file = event.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('image', file);

            // Update source image preview
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('sourceImage').src = e.target.result;
            };
            reader.readAsDataURL(file);

            await processImage(formData);
        });

        // Handle random image
        document.getElementById('randomButton').addEventListener('click', async () => {
            const formData = new FormData();
            formData.append('random', '1');

            // Update source image with loading state
            const sourceImage = document.getElementById('sourceImage');
            sourceImage.src = 'https://picsum.photos/800/600?random=' + Date.now();

            await processImage(formData);
        });

        // Load initial random image
        document.getElementById('randomButton').click();
    </script>
</body>
</html>