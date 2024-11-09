<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Contracts\ImageLoaderInterface;
use Farzai\ColorPalette\Exceptions\ImageLoadException;
use Farzai\ColorPalette\Images\ImageFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

class ImageLoader implements ImageLoaderInterface
{
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    private const URL_PATTERN = '/^https?:\/\//i';

    private const MAX_DOWNLOAD_SIZE = 10485760; // 10MB

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly string $preferredDriver = 'gd',
        private readonly string $tempDir = '/tmp'
    ) {
        //
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
            $request = $this->requestFactory->createRequest('GET', $url);
            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() >= 400) {
                throw new ImageLoadException(
                    "Failed to download image. Status code: {$response->getStatusCode()}"
                );
            }

            // If redirected, follow the redirect
            if ($response->getStatusCode() === 302) {
                $url = $response->getHeaderLine('Location');

                return $this->loadFromUrl($url);
            }

            if ($response->getBody()->getSize() > self::MAX_DOWNLOAD_SIZE) {
                throw new ImageLoadException('Image size exceeds maximum allowed size');
            }

            // Create temporary file
            $tempFile = $this->createTempFile();
            $stream = $this->streamFactory->createStreamFromFile($tempFile, 'w');
            $stream->write($response->getBody()->getContents());

            return ImageFactory::createFromPath($tempFile, $this->preferredDriver);
        } finally {
            // Cleanup temporary file if it exists
            if (isset($tempFile) && file_exists($tempFile)) {
                unlink($tempFile);
            }
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

        return ImageFactory::createFromPath($path, $this->preferredDriver);
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
