<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Services\ExtensionChecker;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpClient\Psr18Client;

class ImageLoaderFactory
{
    public function __construct(
        private readonly ?ClientInterface $httpClient = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
        private readonly ?ExtensionChecker $extensionChecker = null,
        private readonly ?string $preferredDriver = null
    ) {}

    public function create(): ImageLoader
    {
        $httpClient = $this->httpClient ?? new Psr18Client;
        $psr17Factory = $this->requestFactory ?? new Psr17Factory;
        $streamFactory = $this->streamFactory ?? $psr17Factory;
        $extensionChecker = $this->extensionChecker ?? new ExtensionChecker;

        return new ImageLoader(
            $httpClient,
            $psr17Factory,
            $streamFactory,
            null, // imageFactory
            $extensionChecker,
            $this->preferredDriver
        );
    }
}
