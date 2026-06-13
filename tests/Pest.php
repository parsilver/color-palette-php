<?php

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

/*
 * Bind Mockery's PHPUnit integration to every test that uses mocks so that
 * mock expectations (e.g. ->once(), ->with(...)) are verified and Mockery is
 * closed after each test. Without this, unmet expectations pass silently.
 */
uses(MockeryPHPUnitIntegration::class)->in('Unit', 'Integration');
