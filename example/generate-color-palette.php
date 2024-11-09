<?php

require __DIR__ . '/../vendor/autoload.php';

use Farzai\ColorPalette\ImageLoaderFactory;
use Farzai\ColorPalette\ColorExtractorFactory;
use Farzai\ColorPalette\ThemeGenerator;

// Create image loader
$loader = ImageLoaderFactory::create();

// Load image
$image = $loader->load(
    $imageUrl = 'https://picsum.photos/800/600'
);

// Extract colors
$extractor = ColorExtractorFactory::createForImage($image);
$palette = $extractor->extract($image, 5); // Extract 5 dominant colors

$generator = new ThemeGenerator();
$theme = $generator->generate($palette);

$colors = $theme->toArray();
// Array(
//     'primary' => '#000000',
//     'secondary' => '#000000',
//     'accent' => '#000000',
//     'background' => '#000000',
//     'surface' => '#000000',
//     'on_primary' => '#000000',
//     'on_secondary' => '#000000',
// )
?>

<!DOCTYPE html>
<html>
<body>
    <div style="margin: auto; max-width: 1280px;">
        <div style="display: flex; flex-wrap: wrap; gap: 10px; width: 400px; overflow: hidden; border-radius: 10px;">
            <div style="position: relative; width: 100%; height: 200px;">
                <img src="<?php echo $imageUrl; ?>" alt="Image" style="width: 100%; height: 100%; object-fit: cover;">

                <div style="position: absolute; bottom: 0; left: 0; right: 0; background-color: <?php echo $colors['primary']; ?>; color: <?php echo $colors['on_primary']; ?>; padding: 10px;">
                    <strong>Primary</strong>
                    <br>
                    <?php echo $colors['primary']; ?>
                </div>

                <div style="position: absolute; bottom: 0; left: 0; right: 0; background-color: <?php echo $colors['secondary']; ?>; color: <?php echo $colors['on_secondary']; ?>; padding: 10px;">
                    <strong>Secondary</strong>
                    <br>
                    <?php echo $colors['secondary']; ?>
                </div>
            </div>
        </div>

        <div style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; width: 400px; overflow: hidden; border-radius: 10px;">
            <?php foreach ($colors as $name => $color): ?>
                <div style="
                    height: 100px; 
                    width: 100px; 
                    background-color: <?php echo $color; ?>; 
                    border-radius: 10px; 
                    display: flex; 
                    flex-direction: column; 
                    align-items: center; 
                    justify-content: center; 
                    color: <?php echo $colors['on_primary']; ?>;
                ">
                    <span style="font-size: 12px; font-weight: bold;">
                        <?php echo $name; ?>
                    </span>

                    <span style="font-size: 12px; font-weight: bold;">
                        <?php echo $color; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>