<?php

namespace Componist\DeveloperTools\Livewire\Database;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Tools extends Component
{
    public array $content = [];

    public function mount()
    {
        $this->content = $this->getDatabaseTables();
    }

    public function render()
    {

        return view('developerTools::livewire.database.tools')->layout('developerTools::layouts.package');
    }

    public function clearTables(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->content as $value) {
            if ($value['checked']) {
                DB::table($value['name'])->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->content = $this->getDatabaseTables();
        dump('Tables have been cleared');
    }

    public function deleteTables()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($this->content as $value) {
            if ($value['checked']) {
                Schema::drop($value['name']);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->content = $this->getDatabaseTables();
        dump('Tables have been cleared');
    }

    private function getDatabaseTables(): array
    {
        $tables = [];
        foreach (DB::select('SHOW TABLES') as $table) {
            $tableName = implode(',', json_decode(json_encode($table), true));

            $tables[] = [
                'name' => $tableName,
                'checked' => false,
            ];
        }

        return $tables;
    }
}
