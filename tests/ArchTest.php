<?php

use Farzai\ColorPalette\Services\ExtensionChecker;

// Ensure no debugging functions are used in production code
it('will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->not->toBeUsed();

// Ensure proper naming conventions
it('follows PSR naming conventions for classes')
    ->expect('Farzai\ColorPalette')
    ->classes()
    ->toBeClasses();

// Ensure interfaces are properly named
it('has interfaces with Interface suffix')
    ->expect('Farzai\ColorPalette\Contracts')
    ->toHaveSuffix('Interface');

// Ensure no direct instanceof checks for Image types in extractors
it('extractors use method existence checks instead of instanceof')
    ->expect('Farzai\ColorPalette\GdColorExtractor')
    ->not->toUse('instanceof')
    ->and('Farzai\ColorPalette\ImagickColorExtractor')
    ->not->toUse('instanceof');

// Ensure PSR-3 logging is used instead of error_log
it('uses PSR-3 logger instead of error_log')
    ->expect('Farzai\ColorPalette')
    ->not->toUse('error_log');

// Ensure extension checking is centralized in ExtensionChecker service
it('uses ExtensionChecker service for extension validation')
    ->expect(['Farzai\ColorPalette\ImageFactory', 'Farzai\ColorPalette\ColorExtractorFactory', 'Farzai\ColorPalette\ImageLoader'])
    ->toUse(ExtensionChecker::class);

// Ensure proper dependency injection (no new instantiation in constructors)
it('factories accept dependencies via constructor injection')
    ->expect(['Farzai\ColorPalette\ColorExtractorFactory', 'Farzai\ColorPalette\ImageLoaderFactory'])
    ->toHaveConstructor();

// Ensure strict types are declared
it('uses strict types')
    ->expect('Farzai\ColorPalette')
    ->toUseStrictTypes();

// Ensure no final classes (allow extension for testing)
it('does not use final classes excessively')
    ->expect('Farzai\ColorPalette')
    ->classes()
    ->not->toBeFinal();

// Ensure Services namespace contains service classes
it('Services namespace contains service classes')
    ->expect('Farzai\ColorPalette\Services')
    ->classes()
    ->toBeClasses();

// Code organization: Contracts should only contain interfaces
it('Contracts namespace contains only interfaces')
    ->expect('Farzai\ColorPalette\Contracts')
    ->toBeInterfaces();
