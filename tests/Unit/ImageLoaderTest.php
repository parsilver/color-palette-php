<?php

use Farzai\ColorPalette\Exceptions\InvalidImageException;
use Farzai\ColorPalette\ImageLoader;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

beforeEach(function () {
    if (! file_exists(__DIR__.'/../../example/assets/sample.jpg')) {
        // Create a sample image for testing
        $image = imagecreatetruecolor(100, 100);
        $red = imagecolorallocate($image, 255, 0, 0);
        imagefill($image, 0, 0, $red);
        imagejpeg($image, __DIR__.'/../../example/assets/sample.jpg');
        imagedestroy($image);
    }
});

test('it can load image from path', function () {
    /** @var ClientInterface $httpClient */
    $httpClient = Mockery::mock(ClientInterface::class);
    /** @var RequestFactoryInterface $requestFactory */
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    /** @var StreamFactoryInterface $streamFactory */
    $streamFactory = Mockery::mock(StreamFactoryInterface::class);

    $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);
    $image = $loader->load(__DIR__.'/../../example/assets/sample.jpg');

    expect($image)->toBeObject();
    expect($image->getWidth())->toBeGreaterThan(0);
    expect($image->getHeight())->toBeGreaterThan(0);
});

test('it can load image from url', function () {
    /** @var ClientInterface $httpClient */
    $httpClient = Mockery::mock(ClientInterface::class);
    /** @var RequestFactoryInterface $requestFactory */
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    /** @var StreamFactoryInterface $streamFactory */
    $streamFactory = Mockery::mock(StreamFactoryInterface::class);
    /** @var RequestInterface $request */
    $request = Mockery::mock(RequestInterface::class);
    /** @var ResponseInterface $response */
    $response = Mockery::mock(ResponseInterface::class);
    /** @var StreamInterface $stream */
    $stream = Mockery::mock(StreamInterface::class);

    $requestFactory->shouldReceive('createRequest')
        ->with('GET', 'https://example.com/image.jpg')
        ->andReturn($request);

    $httpClient->shouldReceive('sendRequest')
        ->with($request)
        ->andReturn($response);

    $response->shouldReceive('getStatusCode')
        ->andReturn(200);

    $response->shouldReceive('getBody')
        ->andReturn($stream);

    $stream->shouldReceive('getContents')
        ->andReturn(file_get_contents(__DIR__.'/../../example/assets/sample.jpg'));

    $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);
    $image = $loader->load('https://example.com/image.jpg');

    expect($image)->toBeObject();
    expect($image->getWidth())->toBeGreaterThan(0);
    expect($image->getHeight())->toBeGreaterThan(0);
});

test('it throws exception when loading invalid image path', function () {
    /** @var ClientInterface $httpClient */
    $httpClient = Mockery::mock(ClientInterface::class);
    /** @var RequestFactoryInterface $requestFactory */
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    /** @var StreamFactoryInterface $streamFactory */
    $streamFactory = Mockery::mock(StreamFactoryInterface::class);

    $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

    expect(fn () => $loader->load('invalid/path/to/image.jpg'))
        ->toThrow(InvalidImageException::class, 'Image file not found: invalid/path/to/image.jpg');
});

test('it throws exception when loading invalid image url', function () {
    /** @var ClientInterface $httpClient */
    $httpClient = Mockery::mock(ClientInterface::class);
    /** @var RequestFactoryInterface $requestFactory */
    $requestFactory = Mockery::mock(RequestFactoryInterface::class);
    /** @var StreamFactoryInterface $streamFactory */
    $streamFactory = Mockery::mock(StreamFactoryInterface::class);
    /** @var RequestInterface $request */
    $request = Mockery::mock(RequestInterface::class);
    /** @var ResponseInterface $response */
    $response = Mockery::mock(ResponseInterface::class);

    $requestFactory->shouldReceive('createRequest')
        ->with('GET', 'https://invalid-url/image.jpg')
        ->andReturn($request);

    $httpClient->shouldReceive('sendRequest')
        ->with($request)
        ->andReturn($response);

    $response->shouldReceive('getStatusCode')
        ->andReturn(404);

    $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

    expect(fn () => $loader->load('https://invalid-url/image.jpg'))
        ->toThrow(InvalidImageException::class, 'Failed to download image. Status code: 404');
});

describe('ImageLoader supports() method', function () {
    test('it supports valid URL', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect($loader->supports('https://example.com/image.jpg'))->toBeTrue();
        expect($loader->supports('http://example.com/image.png'))->toBeTrue();
    });

    test('it supports existing file path', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect($loader->supports(__DIR__.'/../../example/assets/sample.jpg'))->toBeTrue();
    });

    test('it does not support non-existent file path', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect($loader->supports('/non/existent/path.jpg'))->toBeFalse();
    });

    test('it does not support invalid source', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect($loader->supports('not-a-valid-source'))->toBeFalse();
    });
});

describe('ImageLoader HTTP error handling', function () {
    test('it throws exception for 500 server error', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        /** @var RequestInterface $request */
        $request = Mockery::mock(RequestInterface::class);
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')
            ->with('GET', 'https://example.com/error.jpg')
            ->andReturn($request);

        $httpClient->shouldReceive('sendRequest')
            ->with($request)
            ->andReturn($response);

        $response->shouldReceive('getStatusCode')
            ->andReturn(500);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('https://example.com/error.jpg'))
            ->toThrow(InvalidImageException::class, 'Failed to download image. Status code: 500');
    });

    test('it throws exception for 403 forbidden', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        /** @var RequestInterface $request */
        $request = Mockery::mock(RequestInterface::class);
        /** @var ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')
            ->with('GET', 'https://example.com/forbidden.jpg')
            ->andReturn($request);

        $httpClient->shouldReceive('sendRequest')
            ->with($request)
            ->andReturn($response);

        $response->shouldReceive('getStatusCode')
            ->andReturn(403);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('https://example.com/forbidden.jpg'))
            ->toThrow(InvalidImageException::class, 'Failed to download image. Status code: 403');
    });
});

describe('ImageLoader driver detection', function () {
    test('it uses specified driver when provided', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required for this test.');
        }

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory, 'gd');
        $image = $loader->load(__DIR__.'/../../example/assets/sample.jpg');

        expect($image)->toBeObject();
    });

    test('it auto-detects preferred driver when not specified', function () {
        /** @var ClientInterface $httpClient */
        $httpClient = Mockery::mock(ClientInterface::class);
        /** @var RequestFactoryInterface $requestFactory */
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        /** @var StreamFactoryInterface $streamFactory */
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            $this->markTestSkipped('Either GD or Imagick extension is required.');
        }

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);
        $image = $loader->load(__DIR__.'/../../example/assets/sample.jpg');

        expect($image)->toBeObject();
    });
});
