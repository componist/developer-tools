<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['developer-tools.admin_user_ids' => []]);
        config(['app.debug' => true]);
    }
}
