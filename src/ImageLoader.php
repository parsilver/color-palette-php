<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Contracts\ImageLoaderInterface;
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Images\ImageFactory;
use RuntimeException;

class ImageLoader implements ImageLoaderInterface
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private const URL_PATTERN = '/^https?:\/\//i';

    private const MAX_DOWNLOAD_SIZE = 10485760; // 10MB

    private string $driver;

    private string $tempDir;

    private ?int $maxWidth;

    private ?int $maxHeight;

    public function __construct(
        string $driver = 'gd',
        ?string $tempDir = null,
        ?int $maxWidth = null,
        ?int $maxHeight = null
    ) {
        $this->driver = $driver;
        $this->tempDir = $tempDir ?? sys_get_temp_dir();
        $this->maxWidth = $maxWidth;
        $this->maxHeight = $maxHeight;
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $source): ImageInterface
    {
        try {
            if ($this->isUrl($source)) {
                return $this->loadFromUrl($source);
            }

            return $this->loadFromPath($source);
        } catch (\Throwable $e) {
            throw new ImageLoadException(
                "Failed to load image from source: {$source}",
                previous: $e
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $source): bool
    {
        // Check if it's a URL or a file path with valid extension
        return $this->isUrl($source) || $this->hasValidExtension($source);
    }

    /**
     * Load image from a URL
     *
     * @throws ImageLoadException
     */
    private function loadFromUrl(string $url): ImageInterface
    {
        try {
            $tempFile = $this->createTempFile();
            $contents = file_get_contents($url);

            if ($contents === false) {
                throw new ImageLoadException('Failed to download image from URL');
            }

            file_put_contents($tempFile, $contents);

            return ImageFactory::createFromPath($tempFile, $this->driver);
        } catch (Exception $e) {
            throw new ImageLoadException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Load image from a file path
     *
     * @throws ImageLoadException
     */
    private function loadFromPath(string $path): ImageInterface
    {
        if (! file_exists($path)) {
            throw new ImageLoadException("Image file not found: {$path}");
        }

        if (! $this->hasValidExtension($path)) {
            throw new ImageLoadException("Unsupported image type: {$path}");
        }

        return ImageFactory::createFromPath($path, $this->driver);
    }

    /**
     * Check if the source is a URL
     */
    private function isUrl(string $source): bool
    {
        return (bool) preg_match(self::URL_PATTERN, $source);
    }

    /**
     * Check if the source has a valid image extension
     */
    private function hasValidExtension(string $source): bool
    {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        return in_array($extension, self::ALLOWED_EXTENSIONS, true);
    }

    /**
     * Create a temporary file
     *
     * @throws RuntimeException
     */
    private function createTempFile(): string
    {
        // Ensure temp directory exists and is writable
        if (! is_dir($this->tempDir)) {
            if (! mkdir($this->tempDir, 0777, true)) {
                throw new RuntimeException("Failed to create temporary directory: {$this->tempDir}");
            }
        }

        if (! is_writable($this->tempDir)) {
            throw new RuntimeException("Temporary directory is not writable: {$this->tempDir}");
        }

        $tempFile = tempnam($this->tempDir, 'img_');
        if ($tempFile === false) {
            throw new RuntimeException("Failed to create temporary file in: {$this->tempDir}");
        }

        return $tempFile;
    }
}
