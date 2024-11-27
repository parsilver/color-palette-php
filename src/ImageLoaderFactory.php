<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

class ImageLoaderFactory
{
    private static ?ImageLoader $instance = null;

    public function create(): ImageLoader
    {
        if (self::$instance === null) {
            $psr17Factory = new Psr17Factory;
            $httpClient = new Psr18Client;

            self::$instance = new ImageLoader(
                $httpClient,
                $psr17Factory,
                $psr17Factory
            );
        }

        return self::$instance;
    }
}
