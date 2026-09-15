<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Tests\Unit\Infrastructure;

use Componist\DeveloperTools\Infrastructure\DatabaseSchemaReader;
use PHPUnit\Framework\TestCase;

class DatabaseSchemaReaderTest extends TestCase
{
    public function test_columns_rejects_invalid_table_name(): void
    {
        $this->assertSame([], DatabaseSchemaReader::columns('users; drop'));
    }
}
