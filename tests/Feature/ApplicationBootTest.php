<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApplicationBootTest extends TestCase
{
    public function test_application_boots_in_the_testing_environment(): void
    {
        $this->assertTrue($this->app->environment('testing'));
    }
}
