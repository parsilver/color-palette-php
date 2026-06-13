<?php

declare(strict_types=1);

namespace Farzai\ColorPalette\Config;

class HttpClientConfig
{
    public function __construct(
        private readonly int $timeoutSeconds = 30,
        private readonly int $maxRedirects = 0,
        private readonly int $maxFileSizeBytes = 10485760, // 10MB default
        private readonly string $userAgent = 'Farzai-ColorPalette/1.0',
        private readonly bool $verifySsl = true,
    ) {}

    public function getTimeoutSeconds(): int
    {
        return $this->timeoutSeconds;
    }

    public function getMaxRedirects(): int
    {
        return $this->maxRedirects;
    }

    public function getMaxFileSizeBytes(): int
    {
        return $this->maxFileSizeBytes;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function shouldVerifySsl(): bool
    {
        return $this->verifySsl;
    }

    /**
     * Create a config with custom values
     */
    public static function create(
        ?int $timeoutSeconds = null,
        ?int $maxRedirects = null,
        ?int $maxFileSizeBytes = null,
        ?string $userAgent = null,
        ?bool $verifySsl = null,
    ): self {
        // Derive defaults from the constructor so they live in exactly one place.
        $defaults = new self;

        return new self(
            $timeoutSeconds ?? $defaults->timeoutSeconds,
            $maxRedirects ?? $defaults->maxRedirects,
            $maxFileSizeBytes ?? $defaults->maxFileSizeBytes,
            $userAgent ?? $defaults->userAgent,
            $verifySsl ?? $defaults->verifySsl,
        );
    }
}
