<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Color;

// Enable CORS for local development
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Get the action from query parameter
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'extract':
            handleExtract();
            break;

        case 'generate':
            handleGenerate();
            break;

        case 'manipulate':
            handleManipulate();
            break;

        case 'contrast':
            handleContrast();
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

/**
 * Get human-readable error message for PHP upload errors
 */
function getUploadErrorMessage($errorCode)
{
    switch ($errorCode) {
        case UPLOAD_ERR_INI_SIZE:
            return 'File is too large. Maximum size allowed: ' . ini_get('upload_max_filesize');
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds the maximum size specified in the form';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded. Please try again';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder on server';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'A PHP extension stopped the file upload';
        default:
            return 'Unknown upload error occurred';
    }
}

/**
 * Extract colors from uploaded image or URL
 */
function handleExtract()
{
    $count = (int)($_POST['count'] ?? 5);
    $count = max(3, min(15, $count)); // Limit between 3 and 15

    $imagePath = null;
    $tempFile = null;

    try {
        // Check if it's a URL (from picsum.photos)
        if (!empty($_POST['url'])) {
            $url = $_POST['url'];

            // Download the image to a temporary file
            $imageData = @file_get_contents($url);
            if ($imageData === false) {
                throw new Exception('Failed to download image from URL');
            }

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'color_palette_');
            file_put_contents($tempFile, $imageData);
            $imagePath = $tempFile;

        } elseif (isset($_FILES['image'])) {
            // Handle uploaded file
            $file = $_FILES['image'];

            // Validate file upload
            if ($file['error'] !== UPLOAD_ERR_OK) {
                throw new Exception(getUploadErrorMessage($file['error']));
            }

            // Validate file size (max 10MB)
            if ($file['size'] > 10 * 1024 * 1024) {
                throw new Exception('File size exceeds 10MB limit');
            }

            // Validate file type
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                throw new Exception('Invalid file type. Allowed: JPEG, PNG, GIF, WebP');
            }

            $imagePath = $file['tmp_name'];
        } else {
            throw new Exception('No image provided');
        }

        // Extract colors from image
        $palette = ColorPalette::fromImage($imagePath, $count);

        // Get color information
        $colors = [];
        foreach ($palette->getColors() as $color) {
            $colors[] = [
                'hex' => $color->toHex(),
                'rgb' => $color->toRgb(),
                'hsl' => $color->toHsl(),
                'brightness' => $color->getBrightness(),
                'isLight' => $color->isLight(),
                'isDark' => $color->isDark()
            ];
        }

        // Get suggested theme colors
        $theme = $palette->getSuggestedSurfaceColors();
        $themeColors = [];
        foreach ($theme as $key => $color) {
            $themeColors[$key] = [
                'hex' => $color->toHex(),
                'rgb' => $color->toRgb()
            ];
        }

        echo json_encode([
            'success' => true,
            'colors' => $colors,
            'theme' => $themeColors,
            'count' => count($colors)
        ]);

    } finally {
        // Clean up temporary file if created
        if ($tempFile && file_exists($tempFile)) {
            @unlink($tempFile);
        }
    }
}

/**
 * Generate palette from base color using different schemes
 */
function handleGenerate()
{
    $input = json_decode(file_get_contents('php://input'), true);

    $baseColor = $input['color'] ?? '';
    $scheme = $input['scheme'] ?? 'monochromatic';
    $count = (int)($input['count'] ?? 5);

    if (empty($baseColor)) {
        throw new Exception('Base color is required');
    }

    // Create color from hex
    $color = Color::fromHex($baseColor);

    // Set options based on scheme
    $options = [];
    if (in_array($scheme, ['monochromatic', 'shades', 'tints'])) {
        $options['count'] = $count;
    }

    // Generate palette
    $palette = ColorPalette::fromColor($color, $scheme, $options);

    // Get color information
    $colors = [];
    foreach ($palette->getColors() as $paletteColor) {
        $colors[] = [
            'hex' => $paletteColor->toHex(),
            'rgb' => $paletteColor->toRgb(),
            'hsl' => $paletteColor->toHsl(),
            'brightness' => $paletteColor->getBrightness(),
            'isLight' => $paletteColor->isLight()
        ];
    }

    echo json_encode([
        'success' => true,
        'scheme' => $scheme,
        'colors' => $colors
    ]);
}

/**
 * Manipulate a color (lighten, darken, saturate, etc.)
 */
function handleManipulate()
{
    $input = json_decode(file_get_contents('php://input'), true);

    $baseColor = $input['color'] ?? '';
    $operation = $input['operation'] ?? '';
    $amount = (float)($input['amount'] ?? 0);

    if (empty($baseColor)) {
        throw new Exception('Base color is required');
    }

    if (empty($operation)) {
        throw new Exception('Operation is required');
    }

    // Create color from hex
    $color = Color::fromHex($baseColor);

    // Apply manipulation
    switch ($operation) {
        case 'lighten':
            $result = $color->lighten($amount);
            break;

        case 'darken':
            $result = $color->darken($amount);
            break;

        case 'saturate':
            $result = $color->saturate($amount);
            break;

        case 'desaturate':
            $result = $color->desaturate($amount);
            break;

        case 'rotate':
            $result = $color->rotate($amount);
            break;

        default:
            throw new Exception('Invalid operation');
    }

    echo json_encode([
        'success' => true,
        'original' => [
            'hex' => $color->toHex(),
            'rgb' => $color->toRgb(),
            'hsl' => $color->toHsl()
        ],
        'result' => [
            'hex' => $result->toHex(),
            'rgb' => $result->toRgb(),
            'hsl' => $result->toHsl(),
            'brightness' => $result->getBrightness(),
            'isLight' => $result->isLight()
        ]
    ]);
}

/**
 * Check contrast ratio between two colors (WCAG accessibility)
 */
function handleContrast()
{
    $input = json_decode(file_get_contents('php://input'), true);

    $backgroundColor = $input['background'] ?? '';
    $textColor = $input['text'] ?? '';

    if (empty($backgroundColor) || empty($textColor)) {
        throw new Exception('Both background and text colors are required');
    }

    // Create colors from hex
    $bgColor = Color::fromHex($backgroundColor);
    $txtColor = Color::fromHex($textColor);

    // Calculate contrast ratio
    $contrastRatio = $bgColor->getContrastRatio($txtColor);

    // Check WCAG compliance
    $wcagAA = $contrastRatio >= 4.5;
    $wcagAAA = $contrastRatio >= 7.0;
    $wcagAALarge = $contrastRatio >= 3.0;
    $wcagAAALarge = $contrastRatio >= 4.5;

    // Get suggested text color if contrast is poor
    $palette = ColorPalette::builder()
        ->addColor($bgColor)
        ->build();

    $suggestedTextColor = $palette->getSuggestedTextColor($bgColor);

    echo json_encode([
        'success' => true,
        'contrastRatio' => round($contrastRatio, 2),
        'background' => [
            'hex' => $bgColor->toHex(),
            'luminance' => round($bgColor->getLuminance(), 3),
            'isLight' => $bgColor->isLight()
        ],
        'text' => [
            'hex' => $txtColor->toHex(),
            'luminance' => round($txtColor->getLuminance(), 3),
            'isLight' => $txtColor->isLight()
        ],
        'wcag' => [
            'aa' => [
                'normal' => $wcagAA,
                'large' => $wcagAALarge
            ],
            'aaa' => [
                'normal' => $wcagAAA,
                'large' => $wcagAAALarge
            ]
        ],
        'suggestedTextColor' => [
            'hex' => $suggestedTextColor->toHex(),
            'rgb' => $suggestedTextColor->toRgb()
        ]
    ]);
}
