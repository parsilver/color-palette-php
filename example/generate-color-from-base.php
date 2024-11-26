<?php

require __DIR__.'/../vendor/autoload.php';

use Farzai\ColorPalette\Color;
use Farzai\ColorPalette\PaletteGenerator;

$baseColor = isset($_GET['color']) ? $_GET['color'] : '#2196F3';
$baseColor = Color::fromHex($baseColor);
$generator = new PaletteGenerator($baseColor);

// Generate different palettes
$monochromatic = $generator->monochromatic(5);
$complementary = $generator->complementary();
$analogous = $generator->analogous(3);
$triadic = $generator->triadic();
$tetradic = $generator->tetradic();
$splitComplementary = $generator->splitComplementary();
$pastel = $generator->pastel(4);
$vibrant = $generator->vibrant(4);
$websiteTheme = $generator->websiteTheme();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Color Palette Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .color-card {
            transition: transform 0.2s ease-in-out;
        }
        .color-card:hover {
            transform: translateY(-4px);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Color Palette Generator</h1>
            <p class="text-lg text-gray-600 mb-8">Generate beautiful color palettes from a base color</p>
            
            <!-- Color Picker Form -->
            <form method="get" class="flex items-center justify-center gap-4 mb-8">
                <input type="color" name="color" value="<?php echo $baseColor->toHex(); ?>" 
                       class="h-12 w-20 rounded cursor-pointer">
                <button type="submit" 
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Generate Palettes
                </button>
            </form>
        </div>

        <!-- Palette Sections -->
        <div class="grid gap-8">
            <!-- Website Theme -->
            <section class="bg-white rounded-xl p-6 shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Website Theme</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($websiteTheme->toHexArray() as $name => $hex) { ?>
                        <div class="color-card rounded-lg overflow-hidden shadow-md">
                            <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                            <div class="p-3 bg-white">
                                <p class="font-medium text-gray-700 capitalize"><?php echo str_replace('_', ' ', $name); ?></p>
                                <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <!-- Monochromatic -->
            <section class="bg-white rounded-xl p-6 shadow-lg">
                <h2 class="text-2xl font-semibold mb-4">Monochromatic</h2>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <?php foreach ($monochromatic->toHexArray() as $hex) { ?>
                        <div class="color-card rounded-lg overflow-hidden shadow-md">
                            <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                            <div class="p-3 bg-white">
                                <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </section>

            <!-- Color Harmonies -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Complementary -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Complementary</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach ($complementary->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>

                <!-- Analogous -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Analogous</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <?php foreach ($analogous->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>
            </div>

            <!-- More Color Harmonies -->
            <div class="grid md:grid-cols-3 gap-8">
                <!-- Triadic -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Triadic</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <?php foreach ($triadic->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>

                <!-- Split Complementary -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Split Complementary</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <?php foreach ($splitComplementary->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>

                <!-- Tetradic -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Tetradic</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach ($tetradic->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>
            </div>

            <!-- Artistic Palettes -->
            <div class="grid md:grid-cols-2 gap-8">
                <!-- Pastel -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Pastel</h2>
                    <div class="grid grid-cols-4 gap-4">
                        <?php foreach ($pastel->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>

                <!-- Vibrant -->
                <section class="bg-white rounded-xl p-6 shadow-lg">
                    <h2 class="text-2xl font-semibold mb-4">Vibrant</h2>
                    <div class="grid grid-cols-4 gap-4">
                        <?php foreach ($vibrant->toHexArray() as $hex) { ?>
                            <div class="color-card rounded-lg overflow-hidden shadow-md">
                                <div class="h-24" style="background-color: <?php echo $hex; ?>"></div>
                                <div class="p-3 bg-white">
                                    <p class="text-sm text-gray-500"><?php echo $hex; ?></p>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </section>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 text-center text-gray-500">
            <p>Click on any color card to copy the hex code</p>
        </footer>
    </div>

    <!-- JavaScript for copying color codes -->
    <script>
        document.querySelectorAll('.color-card').forEach(card => {
            card.addEventListener('click', () => {
                const hexCode = card.querySelector('.text-gray-500').textContent;
                navigator.clipboard.writeText(hexCode).then(() => {
                    // Show feedback
                    const p = card.querySelector('.text-gray-500');
                    const originalText = p.textContent;
                    p.textContent = 'Copied!';
                    setTimeout(() => {
                        p.textContent = originalText;
                    }, 1000);
                });
            });
        });
    </script>
</body>
</html> 