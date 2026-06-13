<?php

use Farzai\ColorPalette\ImageLoader;
use Farzai\ColorPalette\ImageLoaderFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

test('it can create image loader instance', function () {
    $factory = new ImageLoaderFactory;
    $loader = $factory->create();

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it creates new instance each time (no singleton)', function () {
    $factory = new ImageLoaderFactory;
    $loader1 = $factory->create();
    $loader2 = $factory->create();

    expect($loader1)->not->toBe($loader2);
});

test('it accepts custom http client via constructor', function () {
    $mockClient = Mockery::mock(ClientInterface::class);
    $factory = new ImageLoaderFactory($mockClient);
    $loader = $factory->create();

    expect($loader)->toBeInstanceOf(ImageLoader::class);
});

test('it reuses the http client as the request factory when it implements RequestFactoryInterface', function () {
    // A client that doubles as a PSR-17 request factory (like Symfony's Psr18Client).
    // When only such a client is injected, the loader must call createRequest() on
    // the client itself — proving the factory reused it instead of building a
    // separate PSR-17 factory (which is what lets us drop the hard nyholm dependency).
    $client = Mockery::mock(ClientInterface::class, RequestFactoryInterface::class);
    $request = Mockery::mock(RequestInterface::class);
    $response = Mockery::mock(ResponseInterface::class);
    $stream = Mockery::mock(StreamInterface::class);

    $client->shouldReceive('createRequest')->once()
        ->with('GET', 'https://example.com/image.jpg')->andReturn($request);
    $request->shouldReceive('withHeader')->with('User-Agent', Mockery::any())->andReturnSelf();
    $client->shouldReceive('sendRequest')->with($request)->andReturn($response);
    $response->shouldReceive('getStatusCode')->andReturn(200);
    $response->shouldReceive('hasHeader')->with('Content-Length')->andReturn(false);
    $response->shouldReceive('hasHeader')->with('Content-Type')->andReturn(false);
    $response->shouldReceive('getBody')->andReturn($stream);
    $stream->shouldReceive('eof')->andReturn(false, true);
    $stream->shouldReceive('read')->with(8192)->andReturn(file_get_contents(__DIR__.'/../../example/assets/sample.jpg'));

    // Inject ONLY the client — no separate request factory.
    $loader = (new ImageLoaderFactory($client))->create();
    $image = $loader->load('https://example.com/image.jpg');

    expect($image->getWidth())->toBeGreaterThan(0);
});
