<?php

declare(strict_types=1);

namespace Componist\DeveloperTools\Livewire\Database;

use Componist\Core\Traits\addLivewireControlleFunctions;
use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Tools extends Component
{
    use addLivewireControlleFunctions;
    use AuthorizesDeveloperTools;

    /** @var list<array{name: string, checked: bool}> */
    public array $content = [];

    public function mount(): void
    {
        $this->content = $this->getDatabaseTables();
    }

    public function render(): View
    {
        return view('developerTools::livewire.database.tools')->layout('developerTools::layouts.package');
    }

    public function clearTables(): void
    {
        foreach ($this->content as $value) {
            if (! empty($value['checked']) && $this->isAllowedTable($value['name'])) {
                DB::table($value['name'])->truncate();
            }
        }

        $this->content = $this->getDatabaseTables();
        $this->flashMessage('success', 'Ausgewählte Tabellen wurden geleert.');
    }

    public function deleteTables(): void
    {
        foreach ($this->content as $value) {
            if (! empty($value['checked']) && $this->isAllowedTable($value['name'])) {
                Schema::drop($value['name']);
            }
        }

        $this->content = $this->getDatabaseTables();
        $this->flashMessage('warning', 'Ausgewählte Tabellen wurden gelöscht.');
    }

    /**
     * @return list<array{name: string, checked: bool}>
     */
    private function getDatabaseTables(): array
    {
        $tables = [];
        foreach (Schema::getTableListing() as $table) {
            $name = str_contains($table, '.') ? substr((string) strrchr($table, '.'), 1) : $table;
            if (! $this->isAllowedTable($name)) {
                continue;
            }
            $tables[] = [
                'name' => $name,
                'checked' => false,
            ];
        }

        return $tables;
    }

    private function isAllowedTable(string $table): bool
    {
        if (! preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $table)) {
            return false;
        }

        $blocked = [
            'migrations',
            'users',
            'sessions',
            'password_reset_tokens',
            'personal_access_tokens',
            'failed_jobs',
            'jobs',
            'job_batches',
            'cache',
            'cache_locks',
        ];

        return ! in_array(strtolower($table), $blocked, true) && Schema::hasTable($table);
    }
}
