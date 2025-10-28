<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Exceptions\InvalidImageException;
use Farzai\ColorPalette\Services\ExtensionChecker;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ImageLoader
{
    private string $preferredDriver;

    private array $tempFiles = [];

    private ExtensionChecker $extensionChecker;

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ?ImageFactory $imageFactory = null,
        ?ExtensionChecker $extensionChecker = null,
        ?string $preferredDriver = null
    ) {
        $this->extensionChecker = $extensionChecker ?? new ExtensionChecker;
        $this->preferredDriver = $preferredDriver ?? $this->extensionChecker->detectPreferredDriver();
    }

    public function load(string $source): ImageInterface
    {
        try {
            if (filter_var($source, FILTER_VALIDATE_URL)) {
                return $this->loadFromUrl($source);
            }

            return $this->loadFromPath($source);
        } catch (\Exception $e) {
            if ($e instanceof InvalidImageException) {
                throw $e;
            }
            throw new InvalidImageException("Failed to load image from source: {$source}", 0, $e);
        }
    }

    public function supports(string $source): bool
    {
        // Check if source is a valid URL
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Check if source is an existing file path
        return file_exists($source);
    }

    private function loadFromPath(string $path): ImageInterface
    {
        if (! file_exists($path)) {
            throw new InvalidImageException("Image file not found: {$path}");
        }

        try {
            $factory = $this->imageFactory ?? new ImageFactory;

            return $factory->createFromPath($path, $this->preferredDriver);
        } catch (\Exception $e) {
            throw new InvalidImageException("Failed to load image from path: {$path}", 0, $e);
        }
    }

    private function loadFromUrl(string $url): ImageInterface
    {
        try {
            $request = $this->requestFactory->createRequest('GET', $url);
            $response = $this->httpClient->sendRequest($request);

            if ($response->getStatusCode() !== 200) {
                throw new InvalidImageException("Failed to download image. Status code: {$response->getStatusCode()}");
            }

            $tempFile = $this->createTempFile();
            file_put_contents($tempFile, $response->getBody()->getContents());

            $factory = $this->imageFactory ?? new ImageFactory;

            return $factory->createFromPath($tempFile, $this->preferredDriver);
        } catch (\Exception $e) {
            if ($e instanceof InvalidImageException) {
                throw $e;
            }
            throw new InvalidImageException("Failed to load image from URL: {$url}", 0, $e);
        }
    }

    private function createTempFile(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');
        $this->tempFiles[] = $tempFile;

        return $tempFile;
    }

    public function __destruct()
    {
        foreach ($this->tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
