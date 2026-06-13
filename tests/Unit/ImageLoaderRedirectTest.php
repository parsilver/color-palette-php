<?php

declare(strict_types=1);

use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Exceptions\HttpException;
use Farzai\ColorPalette\Exceptions\SsrfException;
use Farzai\ColorPalette\ImageLoader;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Redirects are followed by ImageLoader itself (not the transport), and every hop
 * is re-checked against the SSRF rules. Public IP literals are used as the initial
 * URL so validateUrl() never needs DNS (no network dependency in these tests).
 */
describe('ImageLoader redirect SSRF re-validation', function () {
    test('it blocks a redirect whose target is a private/reserved address', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $redirect = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($redirect);
        $redirect->shouldReceive('getStatusCode')->andReturn(302);
        $redirect->shouldReceive('hasHeader')->with('Location')->andReturn(true);
        $redirect->shouldReceive('getHeaderLine')->with('Location')
            ->andReturn('http://169.254.169.254/latest/meta-data/'); // cloud metadata

        $config = new HttpClientConfig(maxRedirects: 3);
        $loader = new ImageLoader($httpClient, $requestFactory, httpConfig: $config);

        expect(fn () => $loader->load('http://8.8.8.8/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it does not follow redirects by default (maxRedirects=0)', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $redirect = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($redirect);
        $redirect->shouldReceive('getStatusCode')->andReturn(302);

        // Default config => maxRedirects=0 => the 3xx is returned unfollowed and
        // rejected by the status-code check.
        $loader = new ImageLoader($httpClient, $requestFactory);

        expect(fn () => $loader->load('http://8.8.8.8/image.jpg'))
            ->toThrow(HttpException::class, 'HTTP status code: 302');
    });

    test('it errors when the redirect budget is exceeded', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $redirect = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($redirect);
        $redirect->shouldReceive('getStatusCode')->andReturn(302);
        $redirect->shouldReceive('hasHeader')->with('Location')->andReturn(true);
        $redirect->shouldReceive('getHeaderLine')->with('Location')->andReturn('http://1.1.1.1/next.jpg');

        $config = new HttpClientConfig(maxRedirects: 2);
        $loader = new ImageLoader($httpClient, $requestFactory, httpConfig: $config);

        expect(fn () => $loader->load('http://8.8.8.8/image.jpg'))
            ->toThrow(HttpException::class, 'Too many redirects');
    });

    test('it follows a redirect to a public target and loads the image', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $redirect = Mockery::mock(ResponseInterface::class);
        $final = Mockery::mock(ResponseInterface::class);
        $stream = Mockery::mock(StreamInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        // First call -> redirect, second call -> final 200.
        $httpClient->shouldReceive('sendRequest')->andReturn($redirect, $final);

        $redirect->shouldReceive('getStatusCode')->andReturn(301);
        $redirect->shouldReceive('hasHeader')->with('Location')->andReturn(true);
        $redirect->shouldReceive('getHeaderLine')->with('Location')->andReturn('http://1.1.1.1/final.jpg');

        $final->shouldReceive('getStatusCode')->andReturn(200);
        $final->shouldReceive('hasHeader')->with('Content-Length')->andReturn(false);
        $final->shouldReceive('hasHeader')->with('Content-Type')->andReturn(true);
        $final->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('image/jpeg');
        $final->shouldReceive('getBody')->andReturn($stream);

        $imageBytes = file_get_contents(__DIR__.'/../../example/assets/sample.jpg');
        $stream->shouldReceive('eof')->andReturn(false, true);
        $stream->shouldReceive('read')->with(8192)->andReturn($imageBytes);

        $config = new HttpClientConfig(maxRedirects: 3);
        $loader = new ImageLoader($httpClient, $requestFactory, httpConfig: $config);

        $image = $loader->load('http://8.8.8.8/image.jpg');

        expect($image)->toBeInstanceOf(ImageInterface::class);
    });
});

describe('ImageLoader redirect URL resolution', function () {
    test('it resolves an absolute-path redirect against the base host and re-validates it', function () {
        // Redirecting to /admin on a host that resolves to a private address must be blocked.
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $redirect = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($redirect);
        $redirect->shouldReceive('getStatusCode')->andReturn(302);
        $redirect->shouldReceive('hasHeader')->with('Location')->andReturn(true);
        // Scheme-relative redirect to a loopback address.
        $redirect->shouldReceive('getHeaderLine')->with('Location')->andReturn('//127.0.0.1/secret');

        $config = new HttpClientConfig(maxRedirects: 3);
        $loader = new ImageLoader($httpClient, $requestFactory, httpConfig: $config);

        expect(fn () => $loader->load('http://8.8.8.8/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });
});
