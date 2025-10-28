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
     * Allowed MIME types for image uploads
     *
     * Only these image formats are supported for color extraction.
     */
    public const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
    ];

    /**
     * HTTP status code for successful response
     *
     * Used when validating remote image URLs.
     */
    public const HTTP_OK = 200;
}
