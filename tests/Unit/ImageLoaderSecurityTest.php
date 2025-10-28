<?php

use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\Exceptions\HttpException;
use Farzai\ColorPalette\Exceptions\SsrfException;
use Farzai\ColorPalette\ImageLoader;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

describe('ImageLoader SSRF Protection', function () {
    test('it blocks localhost URL', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://localhost/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks 127.0.0.1 URL', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://127.0.0.1/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks IPv6 localhost', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://[::1]/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks private network 10.0.0.0/8', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://10.0.0.1/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks private network 192.168.0.0/16', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://192.168.1.1/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks private network 172.16.0.0/12', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://172.16.0.1/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks IPv6 unique local addresses fc00::/7', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://[fc00::1]/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks IPv6 link-local addresses fe80::/10', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('http://[fe80::1]/image.jpg'))
            ->toThrow(SsrfException::class, 'private/reserved');
    });

    test('it blocks file:// protocol', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('file:///etc/passwd'))
            ->toThrow(SsrfException::class, 'not allowed');
    });

    test('it blocks ftp:// protocol', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('ftp://example.com/image.jpg'))
            ->toThrow(SsrfException::class, 'not allowed');
    });
});

describe('ImageLoader File Size Limits', function () {
    test('it rejects files larger than Content-Length limit', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('hasHeader')->with('Content-Length')->andReturn(true);
        $response->shouldReceive('getHeaderLine')->with('Content-Length')->andReturn('20000000'); // 20MB

        $config = new HttpClientConfig(maxFileSizeBytes: 10485760); // 10MB
        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory, null, null, null, $config);

        expect(fn () => $loader->load('https://example.com/large-image.jpg'))
            ->toThrow(HttpException::class, 'too large');
    });

    test('it rejects files during streaming when exceeding size limit', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $stream = Mockery::mock(StreamInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('hasHeader')->with('Content-Length')->andReturn(false);
        $response->shouldReceive('hasHeader')->with('Content-Type')->andReturn(false);
        $response->shouldReceive('getBody')->andReturn($stream);

        // Simulate reading more than the limit
        $largeChunk = str_repeat('x', 8192);
        $stream->shouldReceive('eof')->andReturn(false);
        $stream->shouldReceive('read')->with(8192)->andReturn($largeChunk);

        $config = new HttpClientConfig(maxFileSizeBytes: 1000); // Very small limit
        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory, null, null, null, $config);

        expect(fn () => $loader->load('https://example.com/image.jpg'))
            ->toThrow(HttpException::class, 'too large');
    });
});

describe('ImageLoader MIME Type Validation', function () {
    test('it rejects non-image Content-Type', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('hasHeader')->with('Content-Length')->andReturn(false);
        $response->shouldReceive('hasHeader')->with('Content-Type')->andReturn(true);
        $response->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('text/html');

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('https://example.com/malicious.jpg'))
            ->toThrow(HttpException::class, 'Invalid content type');
    });

    test('it accepts valid image MIME types', function () {
        $validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        foreach ($validTypes as $mimeType) {
            $httpClient = Mockery::mock(ClientInterface::class);
            $requestFactory = Mockery::mock(RequestFactoryInterface::class);
            $streamFactory = Mockery::mock(StreamFactoryInterface::class);
            $request = Mockery::mock(RequestInterface::class);
            $response = Mockery::mock(ResponseInterface::class);
            $stream = Mockery::mock(StreamInterface::class);

            $requestFactory->shouldReceive('createRequest')->andReturn($request);
            $request->shouldReceive('withHeader')->andReturnSelf();
            $httpClient->shouldReceive('sendRequest')->andReturn($response);
            $response->shouldReceive('getStatusCode')->andReturn(200);
            $response->shouldReceive('hasHeader')->with('Content-Length')->andReturn(false);
            $response->shouldReceive('hasHeader')->with('Content-Type')->andReturn(true);
            $response->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn($mimeType);
            $response->shouldReceive('getBody')->andReturn($stream);

            $imageContent = file_get_contents(__DIR__.'/../../example/assets/sample.jpg');
            $stream->shouldReceive('eof')->andReturn(false, true);
            $stream->shouldReceive('read')->with(8192)->andReturn($imageContent);

            $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

            // Should not throw - just verify no exception
            try {
                $loader->load('https://example.com/image.jpg');
                expect(true)->toBeTrue();
            } catch (\Exception $e) {
                // Ignore other exceptions, we're only testing MIME type validation
                if (str_contains($e->getMessage(), 'Invalid content type')) {
                    throw $e;
                }
            }
        }
    });

    test('it handles Content-Type with charset parameter', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $stream = Mockery::mock(StreamInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('hasHeader')->with('Content-Length')->andReturn(false);
        $response->shouldReceive('hasHeader')->with('Content-Type')->andReturn(true);
        $response->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('image/jpeg; charset=utf-8');
        $response->shouldReceive('getBody')->andReturn($stream);

        $imageContent = file_get_contents(__DIR__.'/../../example/assets/sample.jpg');
        $stream->shouldReceive('eof')->andReturn(false, true);
        $stream->shouldReceive('read')->with(8192)->andReturn($imageContent);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        // Should not throw - just verify no exception
        try {
            $loader->load('https://example.com/image.jpg');
            expect(true)->toBeTrue();
        } catch (\Exception $e) {
            // Ignore other exceptions, we're only testing MIME type validation
            if (str_contains($e->getMessage(), 'Invalid content type')) {
                throw $e;
            }
        }
    });
});

describe('ImageLoader HTTP Status Code Handling', function () {
    test('it accepts 200 OK', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $stream = Mockery::mock(StreamInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(200);
        $response->shouldReceive('hasHeader')->andReturn(false);
        $response->shouldReceive('getBody')->andReturn($stream);

        $imageContent = file_get_contents(__DIR__.'/../../example/assets/sample.jpg');
        $stream->shouldReceive('eof')->andReturn(false, true);
        $stream->shouldReceive('read')->with(8192)->andReturn($imageContent);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);
        $image = $loader->load('https://example.com/image.jpg');

        expect($image)->toBeObject();
    });

    test('it accepts 201 Created', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);
        $stream = Mockery::mock(StreamInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(201);
        $response->shouldReceive('hasHeader')->andReturn(false);
        $response->shouldReceive('getBody')->andReturn($stream);

        $imageContent = file_get_contents(__DIR__.'/../../example/assets/sample.jpg');
        $stream->shouldReceive('eof')->andReturn(false, true);
        $stream->shouldReceive('read')->with(8192)->andReturn($imageContent);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);
        $image = $loader->load('https://example.com/image.jpg');

        expect($image)->toBeObject();
    });

    test('it rejects 1xx status codes', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(100);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('https://example.com/image.jpg'))
            ->toThrow(HttpException::class, 'HTTP status code: 100');
    });

    test('it rejects 3xx status codes', function () {
        $httpClient = Mockery::mock(ClientInterface::class);
        $requestFactory = Mockery::mock(RequestFactoryInterface::class);
        $streamFactory = Mockery::mock(StreamFactoryInterface::class);
        $request = Mockery::mock(RequestInterface::class);
        $response = Mockery::mock(ResponseInterface::class);

        $requestFactory->shouldReceive('createRequest')->andReturn($request);
        $request->shouldReceive('withHeader')->andReturnSelf();
        $httpClient->shouldReceive('sendRequest')->andReturn($response);
        $response->shouldReceive('getStatusCode')->andReturn(301);

        $loader = new ImageLoader($httpClient, $requestFactory, $streamFactory);

        expect(fn () => $loader->load('https://example.com/image.jpg'))
            ->toThrow(HttpException::class, 'HTTP status code: 301');
    });
});
