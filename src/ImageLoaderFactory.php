<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\Services\ExtensionChecker;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class ImageLoaderFactory
{
    public function __construct(
        private readonly ?ClientInterface $httpClient = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly ?ExtensionChecker $extensionChecker = null,
        private readonly ?string $preferredDriver = null,
        private readonly ?HttpClientConfig $httpConfig = null
    ) {}

    public function create(): ImageLoader
    {
        $httpConfig = $this->httpConfig ?? new HttpClientConfig;
        $httpClient = $this->httpClient ?? $this->createDefaultHttpClient($httpConfig);
        $psr17Factory = $this->requestFactory ?? new Psr17Factory;

        // Psr17Factory implements both RequestFactoryInterface and StreamFactoryInterface
        $streamFactory = $this->streamFactory ?? ($psr17Factory instanceof StreamFactoryInterface ? $psr17Factory : new Psr17Factory);
        $extensionChecker = $this->extensionChecker ?? new ExtensionChecker;

        return new ImageLoader(
            $httpClient,
            $psr17Factory,
            $streamFactory,
            null, // imageFactory
            $extensionChecker,
            $this->preferredDriver,
            $httpConfig
        );
    }

    /**
     * Create a properly configured Symfony HttpClient
     */
    private function createDefaultHttpClient(HttpClientConfig $config): ClientInterface
    {
        $symfonyClient = HttpClient::create([
            'timeout' => $config->getTimeoutSeconds(),
            'max_redirects' => $config->getMaxRedirects(),
            'verify_peer' => $config->shouldVerifySsl(),
            'verify_host' => $config->shouldVerifySsl(),
            'headers' => [
                'User-Agent' => $config->getUserAgent(),
            ],
        ]);

        return new Psr18Client($symfonyClient);
    }
}
