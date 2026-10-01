<?php

namespace Tests;

use App\Models\CompanySetting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Settings are memoised per process; tests seed them with raw inserts.
        CompanySetting::flushResolved();
    }
}
