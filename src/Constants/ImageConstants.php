<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Constants;

/**
 * Image processing and validation constants
 *
 * This class contains constants related to image file handling, validation,
 * and security limits for the color extraction features.
 */
class ImageConstants
{
    /**
     * Maximum allowed image file size in bytes
     *
     * Set to 10 MB to prevent memory exhaustion and denial of service attacks.
     */
    public const MAX_IMAGE_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

    /**
     * Maximum allowed decoded image dimensions
     *
     * The file-size cap above bounds bytes on disk, NOT the size of the decoded
     * bitmap: a small, highly-compressed image (a "decompression bomb") can
     * inflate to billions of pixels and exhaust memory. These limits are checked
     * against the image header (via getimagesize) BEFORE any decoder loads the
     * pixels, capping both each side and the total pixel count.
     */
    public const MAX_IMAGE_WIDTH = 12000;

    public const MAX_IMAGE_HEIGHT = 12000;

    public const MAX_IMAGE_PIXELS = 50_000_000; // ~50 megapixels

    /**
     * Allowed MIME types for image uploads
     *
     * Only these image formats are supported for color extraction.
     */
    public const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/bmp',
        'image/tiff',
    ];

    /**
     * HTTP status code for successful response
     *
     * Used when validating remote image URLs.
     */
    public const HTTP_OK = 200;
}
