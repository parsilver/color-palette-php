<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\Services\ExtensionChecker;
use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

class ImageLoaderFactory
{
    public function __construct(
        private readonly ?ClientInterface $httpClient = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?ExtensionChecker $extensionChecker = null,
        private readonly ?string $preferredDriver = null,
        private readonly ?HttpClientConfig $httpConfig = null
    ) {}

    public function create(): ImageLoader
    {
        $httpConfig = $this->httpConfig ?? new HttpClientConfig;
        $httpClient = $this->httpClient ?? $this->resolveHttpClient($httpConfig);
        $requestFactory = $this->requestFactory ?? $this->resolveRequestFactory($httpClient);
        $extensionChecker = $this->extensionChecker ?? new ExtensionChecker;

        return new ImageLoader(
            $httpClient,
            $requestFactory,
            null, // imageFactory
            $extensionChecker,
            $this->preferredDriver,
            $httpConfig
        );
    }

    /**
     * Resolve a PSR-18 HTTP client to use for downloading remote images.
     *
     * The library does not depend on a concrete HTTP client. When Symfony's
     * HttpClient is installed we build a securely-configured instance (redirects
     * disabled at the transport layer so ImageLoader can follow + re-validate
     * each hop against the SSRF rules); otherwise we discover any PSR-18 client
     * the project provides. Returns null when none is available so that local
     * file loading still works without an HTTP client — ImageLoader raises a
     * clear error only if a URL is actually loaded.
     */
    private function resolveHttpClient(HttpClientConfig $config): ?ClientInterface
    {
        if (class_exists(Psr18Client::class) && class_exists(HttpClient::class)) {
            return new Psr18Client(HttpClient::create([
                'timeout' => $config->getTimeoutSeconds(),
                // Never auto-follow redirects at the transport layer: ImageLoader
                // follows them itself and re-validates each hop against the SSRF
                // rules. The config's maxRedirects is the budget it uses.
                'max_redirects' => 0,
                'verify_peer' => $config->shouldVerifySsl(),
                'verify_host' => $config->shouldVerifySsl(),
                'headers' => [
                    'User-Agent' => $config->getUserAgent(),
                ],
            ]));
        }

        if (class_exists(Psr18ClientDiscovery::class)) {
            try {
                return Psr18ClientDiscovery::find();
            } catch (\Throwable) {
                // No discoverable client — fall through and return null.
            }
        }

        return null;
    }

    /**
     * Resolve a PSR-17 request factory.
     *
     * Many PSR-18 clients (e.g. Symfony's Psr18Client) also implement PSR-17, so
     * the client itself is reused when possible; otherwise a factory is discovered.
     * Returns null when none is available (see resolveHttpClient()).
     */
    private function resolveRequestFactory(?ClientInterface $client): ?RequestFactoryInterface
    {
        if ($client instanceof RequestFactoryInterface) {
            return $client;
        }

        if (class_exists(Psr17FactoryDiscovery::class)) {
            try {
                return Psr17FactoryDiscovery::findRequestFactory();
            } catch (\Throwable) {
                // No discoverable factory — fall through and return null.
            }
        }

        return null;
    }
}
