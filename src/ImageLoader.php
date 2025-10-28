<?php

declare(strict_types=1);

namespace Farzai\ColorPalette;

use Farzai\ColorPalette\Config\HttpClientConfig;
use Farzai\ColorPalette\Contracts\ImageInterface;
use Farzai\ColorPalette\Exceptions\HttpException;
use Farzai\ColorPalette\Exceptions\InvalidImageException;
use Farzai\ColorPalette\Exceptions\SsrfException;
use Farzai\ColorPalette\Services\ExtensionChecker;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class ImageLoader
{
    private string $preferredDriver;

    /**
     * @var array<string>
     */
    private array $tempFiles = [];

    private ExtensionChecker $extensionChecker;

    private HttpClientConfig $httpConfig;

    public function __construct(
        private readonly ClientInterface $httpClient,
        private readonly RequestFactoryInterface $requestFactory,
        /** @phpstan-ignore-next-line */
        private readonly StreamFactoryInterface $streamFactory,
        private readonly ?ImageFactory $imageFactory = null,
        ?ExtensionChecker $extensionChecker = null,
        ?string $preferredDriver = null,
        ?HttpClientConfig $httpConfig = null
    ) {
        $this->extensionChecker = $extensionChecker ?? new ExtensionChecker;
        $this->preferredDriver = $preferredDriver ?? $this->extensionChecker->detectPreferredDriver();
        $this->httpConfig = $httpConfig ?? new HttpClientConfig;
    }

    public function load(string $source): ImageInterface
    {
        try {
            if (filter_var($source, FILTER_VALIDATE_URL)) {
                $this->validateUrl($source);

                return $this->loadFromUrl($source);
            }

            return $this->loadFromPath($source);
        } catch (\Exception $e) {
            if ($e instanceof InvalidImageException) {
                throw $e;
            }
            throw new InvalidImageException("Failed to load image from source: {$source}", 0, $e);
        }
    }

    public function supports(string $source): bool
    {
        // Check if source is a valid URL
        if (filter_var($source, FILTER_VALIDATE_URL)) {
            return true;
        }

        // Check if source is an existing file path
        return file_exists($source);
    }

    private function loadFromPath(string $path): ImageInterface
    {
        if (! file_exists($path)) {
            throw new InvalidImageException("Image file not found: {$path}");
        }

        try {
            $factory = $this->imageFactory ?? new ImageFactory;

            return $factory->createFromPath($path, $this->preferredDriver);
        } catch (\Exception $e) {
            throw new InvalidImageException("Failed to load image from path: {$path}", 0, $e);
        }
    }

    private function loadFromUrl(string $url): ImageInterface
    {
        $tempFile = null;

        try {
            // Create request with User-Agent header
            $request = $this->requestFactory->createRequest('GET', $url)
                ->withHeader('User-Agent', $this->httpConfig->getUserAgent());

            $response = $this->httpClient->sendRequest($request);

            // Accept all 2xx status codes
            $statusCode = $response->getStatusCode();
            if ($statusCode < 200 || $statusCode >= 300) {
                throw new HttpException(
                    sprintf('Failed to download image. HTTP status code: %d', $statusCode)
                );
            }

            // Validate Content-Length if present
            if ($response->hasHeader('Content-Length')) {
                $contentLength = (int) $response->getHeaderLine('Content-Length');
                if ($contentLength > $this->httpConfig->getMaxFileSizeBytes()) {
                    throw new HttpException(
                        sprintf(
                            'Image file too large: %d bytes (max: %d bytes)',
                            $contentLength,
                            $this->httpConfig->getMaxFileSizeBytes()
                        )
                    );
                }
            }

            // Validate Content-Type if present
            if ($response->hasHeader('Content-Type')) {
                $contentType = $response->getHeaderLine('Content-Type');
                // Extract MIME type without charset
                $mimeType = strtok($contentType, ';');
                if ($mimeType === false || ! $this->isValidImageMimeType($mimeType)) {
                    throw new HttpException(
                        sprintf('Invalid content type: %s. Expected image/* content type.', $mimeType ?: 'unknown')
                    );
                }
            }

            $tempFile = $this->createTempFile();

            // Stream download to file with size limit
            $this->downloadToFile($response->getBody(), $tempFile);

            // Validate actual MIME type using finfo
            $this->validateFileMimeType($tempFile);

            $factory = $this->imageFactory ?? new ImageFactory;

            return $factory->createFromPath($tempFile, $this->preferredDriver);
        } catch (\Exception $e) {
            // Clean up temp file on error
            if ($tempFile !== null && file_exists($tempFile)) {
                @unlink($tempFile);
                $this->tempFiles = array_filter($this->tempFiles, fn ($f) => $f !== $tempFile);
            }

            if ($e instanceof InvalidImageException) {
                throw $e;
            }

            throw new HttpException("Failed to load image from URL: {$url}", 0, $e);
        }
    }

    private function createTempFile(): string
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'img_');

        if ($tempFile === false) {
            throw new InvalidImageException('Failed to create temporary file');
        }

        $this->tempFiles[] = $tempFile;

        return $tempFile;
    }

    /**
     * Validate URL for security (prevent SSRF attacks)
     *
     * @param  string  $url  The URL to validate
     *
     * @throws SsrfException If URL is invalid or points to a private network
     */
    private function validateUrl(string $url): void
    {
        $parsed = parse_url($url);

        if (! $parsed || ! isset($parsed['scheme'])) {
            throw new SsrfException('Invalid URL format');
        }

        // Only allow HTTP/HTTPS (check this first)
        if (! in_array(strtolower($parsed['scheme']), ['http', 'https'], true)) {
            throw new SsrfException(
                sprintf('URL scheme "%s" is not allowed. Only http and https are supported.', $parsed['scheme'])
            );
        }

        if (! isset($parsed['host'])) {
            throw new SsrfException('Invalid URL format');
        }

        $host = $parsed['host'];

        // Strip square brackets from IPv6 addresses
        if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
            $host = substr($host, 1, -1);
        }

        // Validate all IPs (both IPv4 and IPv6)
        $ips = $this->resolveHostToIps($host);

        if (empty($ips)) {
            throw new SsrfException(sprintf('Failed to resolve hostname: %s', $host));
        }

        // Check all resolved IPs
        foreach ($ips as $ip) {
            if ($this->isPrivateOrReservedIp($ip)) {
                throw new SsrfException(
                    sprintf('Access to private/reserved IP addresses is not allowed: %s resolves to %s', $host, $ip)
                );
            }
        }
    }

    /**
     * Resolve hostname to all IPs (both IPv4 and IPv6)
     *
     * @return array<string>
     */
    private function resolveHostToIps(string $host): array
    {
        $ips = [];

        // If it's already an IP, return it
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return [$host];
        }

        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return [$host];
        }

        // Get DNS records for both IPv4 (A) and IPv6 (AAAA)
        $records = @dns_get_record($host, DNS_A + DNS_AAAA);

        if ($records === false) {
            return [];
        }

        foreach ($records as $record) {
            if (isset($record['ip'])) {
                // IPv4 address
                $ips[] = $record['ip'];
            } elseif (isset($record['ipv6'])) {
                // IPv6 address
                $ips[] = $record['ipv6'];
            }
        }

        return array_unique($ips);
    }

    /**
     * Check if IP is private or reserved
     */
    private function isPrivateOrReservedIp(string $ip): bool
    {
        // Handle IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return ! filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        // Handle IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Check for loopback (::1)
            if ($ip === '::1' || strtolower($ip) === '::1') {
                return true;
            }

            // Check for link-local (fe80::/10)
            if (str_starts_with(strtolower($ip), 'fe80:')) {
                return true;
            }

            // Check for unique local addresses (fc00::/7)
            if (str_starts_with(strtolower($ip), 'fc') || str_starts_with(strtolower($ip), 'fd')) {
                return true;
            }

            // Check for IPv4-mapped IPv6 addresses (::ffff:0:0/96)
            if (str_contains(strtolower($ip), '::ffff:')) {
                // Extract IPv4 part and check it
                $parts = explode('::ffff:', strtolower($ip));
                if (isset($parts[1])) {
                    $ipv4 = $parts[1];
                    if (filter_var($ipv4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                        return $this->isPrivateOrReservedIp($ipv4);
                    }
                }

                return true;
            }

            // Use filter_var for other IPv6 reserved ranges
            return ! filter_var(
                $ip,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_IPV6 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            );
        }

        return true; // If not a valid IP, consider it private
    }

    /**
     * Check if MIME type is a valid image type
     */
    private function isValidImageMimeType(string $mimeType): bool
    {
        $validTypes = [
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/tiff',
            'image/svg+xml',
        ];

        return in_array(strtolower(trim($mimeType)), $validTypes, true);
    }

    /**
     * Download response body to file with size limit
     *
     * @throws HttpException
     */
    private function downloadToFile(\Psr\Http\Message\StreamInterface $stream, string $filePath): void
    {
        $handle = fopen($filePath, 'wb');

        if ($handle === false) {
            throw new HttpException('Failed to open temporary file for writing');
        }

        try {
            $bytesWritten = 0;
            $maxSize = $this->httpConfig->getMaxFileSizeBytes();

            while (! $stream->eof()) {
                $chunk = $stream->read(8192); // Read 8KB chunks
                $chunkSize = strlen($chunk);

                if ($bytesWritten + $chunkSize > $maxSize) {
                    throw new HttpException(
                        sprintf(
                            'Image file too large. Downloaded %d bytes, max allowed: %d bytes',
                            $bytesWritten + $chunkSize,
                            $maxSize
                        )
                    );
                }

                $written = fwrite($handle, $chunk);
                if ($written === false) {
                    throw new HttpException('Failed to write to temporary file');
                }

                $bytesWritten += $written;
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Validate file MIME type using finfo
     *
     * @throws HttpException
     */
    private function validateFileMimeType(string $filePath): void
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        if ($finfo === false) {
            // If finfo is not available, skip validation
            return;
        }

        try {
            $mimeType = finfo_file($finfo, $filePath);

            if ($mimeType === false || ! $this->isValidImageMimeType($mimeType)) {
                throw new HttpException(
                    sprintf(
                        'Downloaded file is not a valid image. Detected MIME type: %s',
                        $mimeType ?: 'unknown'
                    )
                );
            }
        } finally {
            finfo_close($finfo);
        }
    }

    /**
     * Explicitly clean up temporary files
     *
     * Call this method when you're done with the ImageLoader to immediately
     * clean up temporary files instead of waiting for object destruction.
     */
    public function cleanup(): void
    {
        foreach ($this->tempFiles as $tempFile) {
            if (file_exists($tempFile)) {
                @unlink($tempFile);
            }
        }
        $this->tempFiles = [];
    }

    public function __destruct()
    {
        $this->cleanup();
    }
}
