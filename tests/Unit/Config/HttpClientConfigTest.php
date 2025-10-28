<?php

use Farzai\ColorPalette\Config\HttpClientConfig;

describe('HttpClientConfig', function () {
    test('it has default values', function () {
        $config = new HttpClientConfig;

        expect($config->getTimeoutSeconds())->toBe(30);
        expect($config->getMaxRedirects())->toBe(0);
        expect($config->getMaxFileSizeBytes())->toBe(10485760); // 10MB
        expect($config->getUserAgent())->toBe('Farzai-ColorPalette/1.0');
        expect($config->shouldVerifySsl())->toBeTrue();
    });

    test('it can be created with custom timeout', function () {
        $config = new HttpClientConfig(timeoutSeconds: 60);

        expect($config->getTimeoutSeconds())->toBe(60);
    });

    test('it can be created with custom max redirects', function () {
        $config = new HttpClientConfig(maxRedirects: 5);

        expect($config->getMaxRedirects())->toBe(5);
    });

    test('it can be created with custom max file size', function () {
        $config = new HttpClientConfig(maxFileSizeBytes: 5242880); // 5MB

        expect($config->getMaxFileSizeBytes())->toBe(5242880);
    });

    test('it can be created with custom user agent', function () {
        $config = new HttpClientConfig(userAgent: 'MyApp/2.0');

        expect($config->getUserAgent())->toBe('MyApp/2.0');
    });

    test('it can be created with SSL verification disabled', function () {
        $config = new HttpClientConfig(verifySsl: false);

        expect($config->shouldVerifySsl())->toBeFalse();
    });

    test('it can be created with all custom values', function () {
        $config = new HttpClientConfig(
            timeoutSeconds: 120,
            maxRedirects: 3,
            maxFileSizeBytes: 20971520, // 20MB
            userAgent: 'CustomAgent/1.0',
            verifySsl: false
        );

        expect($config->getTimeoutSeconds())->toBe(120);
        expect($config->getMaxRedirects())->toBe(3);
        expect($config->getMaxFileSizeBytes())->toBe(20971520);
        expect($config->getUserAgent())->toBe('CustomAgent/1.0');
        expect($config->shouldVerifySsl())->toBeFalse();
    });

    test('it can be created using static create method', function () {
        $config = HttpClientConfig::create(
            timeoutSeconds: 45,
            maxRedirects: 2,
            maxFileSizeBytes: 15728640,
            userAgent: 'TestApp/3.0',
            verifySsl: true
        );

        expect($config->getTimeoutSeconds())->toBe(45);
        expect($config->getMaxRedirects())->toBe(2);
        expect($config->getMaxFileSizeBytes())->toBe(15728640);
        expect($config->getUserAgent())->toBe('TestApp/3.0');
        expect($config->shouldVerifySsl())->toBeTrue();
    });

    test('it uses default values for null parameters in static create', function () {
        $config = HttpClientConfig::create();

        expect($config->getTimeoutSeconds())->toBe(30);
        expect($config->getMaxRedirects())->toBe(0);
        expect($config->getMaxFileSizeBytes())->toBe(10485760);
        expect($config->getUserAgent())->toBe('Farzai-ColorPalette/1.0');
        expect($config->shouldVerifySsl())->toBeTrue();
    });

    test('it allows partial custom values with static create', function () {
        $config = HttpClientConfig::create(
            timeoutSeconds: 90,
            userAgent: 'PartialApp/1.0'
        );

        expect($config->getTimeoutSeconds())->toBe(90);
        expect($config->getMaxRedirects())->toBe(0); // default
        expect($config->getMaxFileSizeBytes())->toBe(10485760); // default
        expect($config->getUserAgent())->toBe('PartialApp/1.0');
        expect($config->shouldVerifySsl())->toBeTrue(); // default
    });
});
