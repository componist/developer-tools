<?php

namespace Componist\DeveloperTools\Livewire\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Schema extends Component
{
    /** @var array{tables: array<int, array{id: string, name: string, x: int, y: int, columns: array}>, relations: array<int, array>} */
    public array $schemaForView = [
        'tables' => [],
        'relations' => [],
    ];

    public function mount(): void
    {
        try {
            $this->schemaForView = $this->buildSchemaFromDatabase();
        } catch (\Throwable $e) {
            report($e);
            $this->schemaForView = ['tables' => [], 'relations' => []];
        }
    }

    /**
     * Baut das Anzeige-Schema aus der verbundenen Datenbank (Tabellen, Spalten, Relationen).
     *
     * @return array{tables: array, relations: array}
     */
    private function buildSchemaFromDatabase(): array
    {
        $driver = DB::getDriverName();
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return ['tables' => [], 'relations' => []];
        }

        $tables = [];
        $tableNameToId = [];
        $nextTableId = 1;
        $nextColId = 1;
        $tableSpacingX = 220;
        $tableSpacingY = 200;
        $gridSize = 20;

        $snap = fn (int $v) => (int) (round($v / $gridSize) * $gridSize);

        $tableList = DB::select('SHOW TABLES');
        if (empty($tableList)) {
            return ['tables' => [], 'relations' => []];
        }

        $dbName = DB::getDatabaseName();
        $foreignKeys = $this->getForeignKeys($dbName);

        foreach ($tableList as $idx => $row) {
            $tableName = (string) array_values((array) $row)[0];
            $tableId = 't' . $nextTableId++;
            $tableNameToId[$tableName] = $tableId;

            $columns = [];
            $colNameToId = [];
            $rawColumns = DB::select('SHOW COLUMNS FROM `' . $tableName . '`');
            $pkColumns = [];
            foreach ($rawColumns as $c) {
                $c = (array) $c;
                if (($c['Key'] ?? '') === 'PRI') {
                    $pkColumns[] = $c['Field'];
                }
            }

            $colIdx = 0;
            foreach ($rawColumns as $c) {
                $c = (array) $c;
                $colName = (string) $c['Field'];
                $colId = 'c' . $nextColId++;
                $colNameToId[$colName] = $colId;

                $isPk = in_array($colName, $pkColumns, true);
                $isFk = isset($foreignKeys[$tableName][$colName]);
                $internalType = $this->mysqlTypeToInternal(
                    (string) ($c['Type'] ?? 'varchar(255)'),
                    $isPk,
                    $isFk
                );

                $columns[] = [
                    'id' => $colId,
                    'name' => $colName,
                    'type' => $internalType,
                    'nullable' => strtoupper((string) ($c['Null'] ?? 'NO')) === 'YES',
                ];
                $colIdx++;
            }

            $col = ($idx % 6);
            $row = (int) floor($idx / 6);
            $tables[] = [
                'id' => $tableId,
                'name' => $tableName,
                'x' => $snap(40 + $col * $tableSpacingX),
                'y' => $snap(40 + $row * $tableSpacingY),
                'columns' => $columns,
            ];
        }

        $relations = $this->buildRelations($foreignKeys, $tableNameToId, $tables);
        $nextRelId = 1;
        foreach ($relations as &$rel) {
            $rel['id'] = 'r' . $nextRelId++;
            $rel['x1'] = $rel['x1'] ?? 0;
            $rel['y1'] = $rel['y1'] ?? 0;
            $rel['x2'] = $rel['x2'] ?? 0;
            $rel['y2'] = $rel['y2'] ?? 0;
        }

        return [
            'tables' => $tables,
            'relations' => $relations,
        ];
    }

    /**
     * Liest Foreign-Key-Constraints aus der information_schema.
     *
     * @return array<string, array<string, array{referenced_table: string, referenced_column: string}>>
     */
    private function getForeignKeys(string $dbName): array
    {
        $rows = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
             AND REFERENCED_TABLE_NAME IS NOT NULL
             AND REFERENCED_TABLE_SCHEMA = ?",
            [$dbName, $dbName]
        );

        $out = [];
        foreach ($rows as $row) {
            $row = (array) $row;
            $t = (string) $row['TABLE_NAME'];
            $c = (string) $row['COLUMN_NAME'];
            if (! isset($out[$t])) {
                $out[$t] = [];
            }
            $out[$t][$c] = [
                'referenced_table' => (string) $row['REFERENCED_TABLE_NAME'],
                'referenced_column' => (string) $row['REFERENCED_COLUMN_NAME'],
            ];
        }
        return $out;
    }

    /**
     * Baut die Relationen für die Anzeige (fromTable/toTable = interne IDs).
     *
     * @param array<string, array<string, array{referenced_table: string, referenced_column: string}>> $foreignKeys
     * @param array<string, string> $tableNameToId
     */
    private function buildRelations(array $foreignKeys, array $tableNameToId, array $tables): array
    {
        $relations = [];
        $tableById = [];
        $colIdByTableAndName = [];
        foreach ($tables as $t) {
            $tableById[$t['id']] = $t;
            foreach ($t['columns'] as $col) {
                $colIdByTableAndName[$t['name']][$col['name']] = $col['id'];
            }
        }

        foreach ($foreignKeys as $fromTableName => $cols) {
            $fromTableId = $tableNameToId[$fromTableName] ?? null;
            if (! $fromTableId) {
                continue;
            }
            foreach ($cols as $fromColName => $ref) {
                $toTableName = $ref['referenced_table'];
                $toColName = $ref['referenced_column'];
                $toTableId = $tableNameToId[$toTableName] ?? null;
                if (! $toTableId) {
                    continue;
                }
                $fromColId = $colIdByTableAndName[$fromTableName][$fromColName] ?? null;
                $toColId = $colIdByTableAndName[$toTableName][$toColName] ?? null;
                if (! $fromColId || ! $toColId) {
                    continue;
                }
                $relations[] = [
                    'fromTable' => $fromTableId,
                    'fromColumn' => $fromColId,
                    'toTable' => $toTableId,
                    'toColumn' => $toColId,
                    'fromColName' => $fromColName,
                    'toColName' => $toColName,
                    'relationType' => '1:N',
                ];
            }
        }

        return $relations;
    }

    private function mysqlTypeToInternal(string $mysqlType, bool $isPk, bool $isFk): string
    {
        if ($isPk) {
            return 'PK';
        }
        if ($isFk) {
            return 'FK';
        }
        $t = strtoupper($mysqlType);
        if (str_starts_with($t, 'INT') || str_starts_with($t, 'BIGINT') || str_starts_with($t, 'SMALLINT') || str_starts_with($t, 'MEDIUMINT') || str_starts_with($t, 'TINYINT')) {
            return 'int';
        }
        if (str_starts_with($t, 'TINYINT(1)') || str_starts_with($t, 'BOOL')) {
            return 'bool';
        }
        if (str_starts_with($t, 'TEXT') || str_starts_with($t, 'LONGTEXT') || str_starts_with($t, 'MEDIUMTEXT')) {
            return 'text';
        }
        if (str_starts_with($t, 'DATE') && ! str_contains($t, 'TIME')) {
            return 'date';
        }
        if (str_contains($t, 'TIMESTAMP') || str_contains($t, 'DATETIME')) {
            return 'datetime';
        }
        if (str_starts_with($t, 'DECIMAL') || str_starts_with($t, 'NUMERIC')) {
            return 'decimal';
        }
        if (str_starts_with($t, 'FLOAT') || str_starts_with($t, 'DOUBLE') || str_starts_with($t, 'REAL')) {
            return 'float';
        }
        if (str_starts_with($t, 'UUID') || str_starts_with($t, 'CHAR(36)')) {
            return 'uuid';
        }
        if (str_starts_with($t, 'JSON')) {
            return 'json';
        }
        if (str_starts_with($t, 'CHAR') || str_starts_with($t, 'VARCHAR')) {
            return 'varchar';
        }
        return 'varchar';
    }

    public function render(): View
    {
        return view('developerTools::livewire.database.schema', [
            'schemaForView' => $this->schemaForView,
        ])->layout('developerTools::layouts.package');
    }
}
