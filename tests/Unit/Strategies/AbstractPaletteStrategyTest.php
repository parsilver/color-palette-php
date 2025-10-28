<?php

use Farzai\ColorPalette\ColorPalette;
use Farzai\ColorPalette\Contracts\ColorInterface;
use Farzai\ColorPalette\Strategies\AbstractPaletteStrategy;

// Create a concrete test implementation to test protected methods
class TestPaletteStrategy extends AbstractPaletteStrategy
{
    public function generate(ColorInterface $baseColor, array $options = []): ColorPalette
    {
        // Simple implementation for testing
        return new ColorPalette([$baseColor]);
    }

    // Expose protected methods for testing
    public function testGetCountOption(array $options, int $default = 5): int
    {
        return $this->getCountOption($options, $default);
    }

    public function testValidateCount(int $count, int $min = self::MIN_COUNT, int $max = self::MAX_COUNT): void
    {
        $this->validateCount($count, $min, $max);
    }
}

beforeEach(function () {
    $this->strategy = new TestPaletteStrategy;
});

describe('AbstractPaletteStrategy - getCountOption', function () {
    test('it returns default when count option is not provided', function () {
        $result = $this->strategy->testGetCountOption([]);

        expect($result)->toBe(5);
    });

    test('it returns custom default when provided', function () {
        $result = $this->strategy->testGetCountOption([], 10);

        expect($result)->toBe(10);
    });

    test('it returns count from options when provided', function () {
        $result = $this->strategy->testGetCountOption(['count' => 7]);

        expect($result)->toBe(7);
    });

    test('it accepts count as string and converts to int', function () {
        $result = $this->strategy->testGetCountOption(['count' => '8']);

        expect($result)->toBe(8);
    });

    test('it accepts minimum valid count', function () {
        $result = $this->strategy->testGetCountOption(['count' => 1]);

        expect($result)->toBe(1);
    });

    test('it accepts maximum valid count', function () {
        $result = $this->strategy->testGetCountOption(['count' => 50]);

        expect($result)->toBe(50);
    });

    test('it throws exception for non-numeric count', function () {
        expect(fn () => $this->strategy->testGetCountOption(['count' => 'invalid']))
            ->toThrow(InvalidArgumentException::class, 'Count option must be numeric');
    });

    test('it throws exception for count below minimum', function () {
        expect(fn () => $this->strategy->testGetCountOption(['count' => 0]))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it throws exception for negative count', function () {
        expect(fn () => $this->strategy->testGetCountOption(['count' => -5]))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it throws exception for count above maximum', function () {
        expect(fn () => $this->strategy->testGetCountOption(['count' => 51]))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it throws exception for count far above maximum', function () {
        expect(fn () => $this->strategy->testGetCountOption(['count' => 1000]))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it handles float count by converting to int', function () {
        $result = $this->strategy->testGetCountOption(['count' => 7.9]);

        expect($result)->toBe(7);
    });

    test('it throws exception for array count', function () {
        expect(fn () => $this->strategy->testGetCountOption(['count' => [5]]))
            ->toThrow(InvalidArgumentException::class, 'Count option must be numeric');
    });

    test('it uses default for null count (null is not numeric)', function () {
        // PHP's is_numeric(null) returns false, so it should use default
        $result = $this->strategy->testGetCountOption(['count' => null], 5);

        expect($result)->toBe(5);
    });

    test('it handles other options in array', function () {
        $result = $this->strategy->testGetCountOption([
            'count' => 10,
            'other' => 'value',
            'another' => 123,
        ]);

        expect($result)->toBe(10);
    });
});

describe('AbstractPaletteStrategy - validateCount', function () {
    test('it accepts valid count within default range', function () {
        expect(fn () => $this->strategy->testValidateCount(5))->not->toThrow(InvalidArgumentException::class);
        expect(fn () => $this->strategy->testValidateCount(1))->not->toThrow(InvalidArgumentException::class);
        expect(fn () => $this->strategy->testValidateCount(50))->not->toThrow(InvalidArgumentException::class);
    });

    test('it accepts count at minimum boundary', function () {
        expect(fn () => $this->strategy->testValidateCount(1))->not->toThrow(InvalidArgumentException::class);
    });

    test('it accepts count at maximum boundary', function () {
        expect(fn () => $this->strategy->testValidateCount(50))->not->toThrow(InvalidArgumentException::class);
    });

    test('it throws exception for count below default minimum', function () {
        expect(fn () => $this->strategy->testValidateCount(0))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it throws exception for negative count', function () {
        expect(fn () => $this->strategy->testValidateCount(-1))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it throws exception for count above default maximum', function () {
        expect(fn () => $this->strategy->testValidateCount(51))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 1 and 50');
    });

    test('it accepts custom min and max values', function () {
        expect(fn () => $this->strategy->testValidateCount(15, 10, 20))
            ->not->toThrow(InvalidArgumentException::class);
    });

    test('it validates against custom minimum', function () {
        expect(fn () => $this->strategy->testValidateCount(5, 10, 20))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 10 and 20');
    });

    test('it validates against custom maximum', function () {
        expect(fn () => $this->strategy->testValidateCount(25, 10, 20))
            ->toThrow(InvalidArgumentException::class, 'Count must be between 10 and 20');
    });

    test('it accepts count at custom minimum boundary', function () {
        expect(fn () => $this->strategy->testValidateCount(10, 10, 20))
            ->not->toThrow(InvalidArgumentException::class);
    });

    test('it accepts count at custom maximum boundary', function () {
        expect(fn () => $this->strategy->testValidateCount(20, 10, 20))
            ->not->toThrow(InvalidArgumentException::class);
    });

    test('it throws exception with correct message for custom range', function () {
        try {
            $this->strategy->testValidateCount(5, 10, 20);
            $this->fail('Expected InvalidArgumentException to be thrown');
        } catch (InvalidArgumentException $e) {
            expect($e->getMessage())->toContain('between 10 and 20');
            expect($e->getMessage())->toContain('got 5');
        }
    });

    test('it handles edge case with same min and max', function () {
        expect(fn () => $this->strategy->testValidateCount(10, 10, 10))
            ->not->toThrow(InvalidArgumentException::class);

        expect(fn () => $this->strategy->testValidateCount(9, 10, 10))
            ->toThrow(InvalidArgumentException::class);

        expect(fn () => $this->strategy->testValidateCount(11, 10, 10))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('AbstractPaletteStrategy - Integration', function () {
    test('it implements PaletteGenerationStrategyInterface', function () {
        expect($this->strategy)->toBeInstanceOf(\Farzai\ColorPalette\Contracts\PaletteGenerationStrategyInterface::class);
    });

    test('it uses MIN_COUNT constant correctly', function () {
        // Test that validation uses the MIN_COUNT value (1)
        expect(fn () => $this->strategy->testValidateCount(1))
            ->not->toThrow(InvalidArgumentException::class);

        expect(fn () => $this->strategy->testValidateCount(0))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it uses MAX_COUNT constant correctly', function () {
        // Test that validation uses the MAX_COUNT value (50)
        expect(fn () => $this->strategy->testValidateCount(50))
            ->not->toThrow(InvalidArgumentException::class);

        expect(fn () => $this->strategy->testValidateCount(51))
            ->toThrow(InvalidArgumentException::class);
    });

    test('it has generate method', function () {
        expect(method_exists($this->strategy, 'generate'))->toBeTrue();
    });

    test('getCountOption and validateCount work together consistently', function () {
        // When getCountOption returns a value, validateCount should accept it
        $count = $this->strategy->testGetCountOption(['count' => 10]);

        expect(fn () => $this->strategy->testValidateCount($count))
            ->not->toThrow(InvalidArgumentException::class);
    });

    test('both methods reject same invalid values', function () {
        // Both should reject count of 0
        expect(fn () => $this->strategy->testGetCountOption(['count' => 0]))
            ->toThrow(InvalidArgumentException::class);

        expect(fn () => $this->strategy->testValidateCount(0))
            ->toThrow(InvalidArgumentException::class);

        // Both should reject count of 51
        expect(fn () => $this->strategy->testGetCountOption(['count' => 51]))
            ->toThrow(InvalidArgumentException::class);

        expect(fn () => $this->strategy->testValidateCount(51))
            ->toThrow(InvalidArgumentException::class);
    });
});
