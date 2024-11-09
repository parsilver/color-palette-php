<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Contracts\ImageLoaderInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * Factory for creating ImageLoader instances with default configurations
 */
class ImageLoaderFactory
{
    /**
     * Create a new ImageLoader instance with default settings
     *
     * @param  string  $preferredDriver  The preferred image driver ('gd' or 'imagick')
     * @param  string|null  $tempDir  Custom temporary directory (optional)
     */
    public static function create(
        string $preferredDriver = 'gd',
        ?string $tempDir = null
    ): ImageLoaderInterface {
        return new ImageLoader(
            self::createDefaultHttpClient(),
            self::createDefaultRequestFactory(),
            self::createDefaultStreamFactory(),
            $preferredDriver,
            $tempDir ?? sys_get_temp_dir()
        );
    }

    /**
     * Create a new ImageLoader instance with custom HTTP client
     *
     * @param  string  $preferredDriver  The preferred image driver ('gd' or 'imagick')
     * @param  string|null  $tempDir  Custom temporary directory (optional)
     */
    public static function createWithClient(
        ClientInterface $httpClient,
        string $preferredDriver = 'gd',
        ?string $tempDir = null
    ): ImageLoaderInterface {
        return new ImageLoader(
            $httpClient,
            self::createDefaultRequestFactory(),
            self::createDefaultStreamFactory(),
            $preferredDriver,
            $tempDir ?? sys_get_temp_dir()
        );
    }

    /**
     * Create a fully customized ImageLoader instance
     */
    public static function createCustom(
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        string $preferredDriver = 'gd',
        ?string $tempDir = null
    ): ImageLoaderInterface {
        return new ImageLoader(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $preferredDriver,
            $tempDir ?? sys_get_temp_dir()
        );
    }

    /**
     * Create default HTTP client
     */
    private static function createDefaultHttpClient(): ClientInterface
    {
        return new Client([
            'timeout' => 30,
            'connect_timeout' => 10,
            'verify' => true,
            'http_errors' => false,
            'headers' => [
                'User-Agent' => 'Farzai-ColorPalette/1.0',
            ],
        ]);
    }

    /**
     * Create default request factory
     */
    private static function createDefaultRequestFactory(): RequestFactoryInterface
    {
        return new HttpFactory;
    }

    /**
     * Create default stream factory
     */
    private static function createDefaultStreamFactory(): StreamFactoryInterface
    {
        return new HttpFactory;
    }
}
