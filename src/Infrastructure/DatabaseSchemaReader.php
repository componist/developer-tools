<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Infrastructure;

use Illuminate\Support\Facades\Schema;

final class DatabaseSchemaReader
{
    /**
     * @return list<string>
     */
    public static function tableNames(): array
    {
        $tables = [];

        foreach (Schema::getTableListing() as $table) {
            $name = str_contains($table, '.') ? substr((string) strrchr($table, '.'), 1) : $table;
            $tables[] = $name;
        }

        sort($tables);

        return array_values(array_unique($tables));
    }

    /**
     * @return list<array{Field: string, Type: string, Null: string, Key: string, Default: mixed, Extra: string}>
     */
    public static function columns(string $table): array
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            return [];
        }

        if (! Schema::hasTable($table)) {
            return [];
        }

        $columns = [];

        foreach (Schema::getColumns($table) as $column) {
            $columns[] = [
                'Field' => (string) ($column['name'] ?? ''),
                'Type' => (string) ($column['type_name'] ?? ($column['type'] ?? '')),
                'Null' => ($column['nullable'] ?? false) ? 'YES' : 'NO',
                'Key' => '',
                'Default' => $column['default'] ?? null,
                'Extra' => ($column['auto_increment'] ?? false) ? 'auto_increment' : '',
            ];
        }

        return $columns;
    }
}
