<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteFactory;
use Farzai\ColorPalette\ThemeFactory;

// Create a base color (e.g., a nice blue)
$baseColor = isset($_GET['color']) ? Color::fromHex($_GET['color']) : Color::fromHex('#4A90E2');

// Create palette factory and generate palettes
$paletteFactory = new PaletteFactory($baseColor);
$palettes = $paletteFactory->createPredefinedPalettes();

// Create theme factory and generate themes
$themeFactory = new ThemeFactory;
$themes = $themeFactory->createFromPalettes($palettes);

// Icons for palette types
$icons = [
    'Vibrant' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
    'Pastel' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>',
    'Golden Ratio' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
    'Gradient' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>',
    'Neutral' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
    'Complementary' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>',
    'Analogous' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>',
    'Triadic' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z M12 8v8 M8 12h8"/></svg>',
    'Autumn' => '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>',
];

// Add icons to palettes
foreach ($palettes as $name => &$data) {
    $data['icon'] = $icons[$name] ?? '';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Palette Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 fixed top-0 left-0 right-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <h1 class="text-2xl font-bold text-gray-900">Color Palette Generator</h1>
                    <span class="text-sm text-gray-500">Create beautiful color combinations</span>
                </div>
                <div class="flex items-center space-x-6">
                    <form class="flex items-center space-x-3" method="get">
                        <label for="color" class="text-sm font-medium text-gray-700">Base Color:</label>
                        <div class="relative flex items-center space-x-2">
                            <input 
                                type="color" 
                                id="colorPicker" 
                                value="<?php echo $baseColor->toHex(); ?>"
                                class="sr-only peer"
                                onchange="updateColor(this.value)"
                            >
                            <div 
                                class="w-10 h-10 rounded-lg shadow-md cursor-pointer border-2 border-white ring-2 ring-gray-200 transition-transform hover:scale-105"
                                style="background-color: <?php echo $baseColor->toHex(); ?>"
                                onclick="document.getElementById('colorPicker').click()"
                            ></div>
                            <input 
                                type="text" 
                                name="color" 
                                id="colorInput"
                                value="<?php echo $baseColor->toHex(); ?>"
                                class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                                pattern="^#[0-9A-Fa-f]{6}$"
                                title="Please enter a valid hex color (e.g., #FF0000)"
                            >
                            <button 
                                type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 hover:scale-105"
                            >
                                Generate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-24 pb-12">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <?php foreach ($palettes as $name => $data) {
                $colors = $data['palette']->getColors();
                $theme = $themes[$name];
                $themeColors = $theme->toArray();
                ?>
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-all duration-200">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-start space-x-3">
                                <div class="p-2 bg-gray-100 rounded-lg">
                                    <?php echo $data['icon']; ?>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-gray-900"><?php echo $name; ?></h2>
                                    <p class="text-sm text-gray-600 mt-1"><?php echo $data['description']; ?></p>
                                </div>
                            </div>
                            <button 
                                onclick="copyPalette('<?php echo implode(', ', array_map(fn ($c) => $c->toHex(), $colors)); ?>')"
                                class="text-sm px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 flex items-center space-x-1"
                                title="Copy all colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                                </svg>
                                <span>Copy All</span>
                            </button>
                        </div>

                        <!-- Color Swatches -->
                        <div class="grid gap-3 mb-6">
                            <?php foreach ($colors as $index => $color) {
                                $textColor = $color->isLight() ? '#000000' : '#FFFFFF';
                                $luminance = $color->getLuminance();
                                $borderColor = $luminance > 0.95 ? '#E5E7EB' : 'transparent';
                                ?>
                                <div 
                                    class="group relative flex items-center rounded-xl overflow-hidden transition-all duration-200 hover:scale-[1.02] hover:shadow-md"
                                    style="border: 1px solid <?php echo $borderColor; ?>"
                                >
                                    <div 
                                        class="w-full h-16 flex items-center justify-between px-4 cursor-pointer"
                                        style="background-color: <?php echo $color->toHex(); ?>; color: <?php echo $textColor; ?>"
                                        onclick="copyToClipboard('<?php echo $color->toHex(); ?>')"
                                    >
                                        <div class="flex items-center space-x-3">
                                            <span class="font-mono font-medium"><?php echo $color->toHex(); ?></span>
                                            <span class="text-sm opacity-75">RGB(<?php echo implode(', ', array_values($color->toRgb())); ?>)</span>
                                        </div>
                                        <span class="opacity-0 group-hover:opacity-100 transition-opacity text-sm flex items-center space-x-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Click to copy</span>
                                        </span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Theme Preview -->
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                                <span>Theme Preview</span>
                            </h3>
                            <div class="rounded-xl overflow-hidden shadow-sm"
                                style="background-color: <?php echo $themeColors['background']; ?>">
                                <div class="p-4">
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        <?php foreach ($themeColors as $role => $hex) {
                                            $roleColor = Color::fromHex($hex);
                                            $textColor = $roleColor->isLight() ? '#000000' : '#FFFFFF';
                                            ?>
                                            <div 
                                                class="px-3 py-2 rounded-lg text-sm cursor-pointer transition-all duration-200 hover:scale-105"
                                                style="background-color: <?php echo $hex; ?>; color: <?php echo $textColor; ?>"
                                                onclick="copyToClipboard('<?php echo $hex; ?>')"
                                            >
                                                <?php echo ucfirst($role); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <div 
                                        class="rounded-lg p-4 transition-all duration-200 hover:shadow-md"
                                        style="background-color: <?php echo $themeColors['surface']; ?>; color: <?php echo $themeColors['primary']; ?>"
                                    >
                                        <h4 class="font-semibold mb-2">Sample Content</h4>
                                        <p style="color: <?php echo $themeColors['secondary']; ?>">
                                            This is how your theme might look in a real application.
                                            <span style="color: <?php echo $themeColors['accent']; ?>">
                                                With different color roles.
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </main>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed bottom-4 right-4 space-y-2"></div>

    <script>
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

            // Trigger animation
            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
            });

            // Remove toast after delay
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

        function updateColor(value) {
            document.getElementById('colorInput').value = value;
            document.querySelector('[style*="background-color"]').style.backgroundColor = value;
        }

        // Sync color input with color picker
        document.getElementById('colorInput').addEventListener('input', function(e) {
            const value = e.target.value;
            if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
                document.getElementById('colorPicker').value = value;
                document.querySelector('[style*="background-color"]').style.backgroundColor = value;
            }
        });
    </script>
</body>
</html> 