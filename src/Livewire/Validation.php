<?php

namespace Componist\DeveloperTools\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Component;

class Validation extends Component
{
    public array $table_list = [];

    public string $table = '';

    public array $fields = [];

    public array $validator = [];

    public function mount()
    {
        $this->getTables();
    }

    public function render(): View
    {
        if (count($this->fields) > 0) {
            $this->createValidateString();
        }

        return view('developerTools::livewire.validation')->layout('developerTools::layouts.package');
    }

    public function getTable(string $table): void
    {
        if (! empty($table)) {
            $this->table = $table;
            $temp = json_decode(json_encode(DB::select('SHOW COLUMNS FROM `'.$table.'` ')), true);

            foreach ($temp as $key => $value) {
                $temp[$key]['checked'] = 0;
                $temp[$key]['faker']['Field'] = $value['Field'];
                $temp[$key]['faker']['type'] = null;
                $temp[$key]['faker']['params'] = null;
                $temp[$key]['faker']['default'] = null;
            }
            $this->fields = $temp;
        }
    }

    private function getTables(): void
    {
        foreach (DB::select('SHOW TABLES') as $value) {
            $this->table_list[] = implode(',', json_decode(json_encode($value), true));
        }
    }

    private function createValidateString()
    {
        /*
        $validated = $request->validate([
            'title' => 'required|unique:posts|max:255',
            'body' => 'required',
        ]);
        */

        $validator = [];
        foreach ($this->fields as $field) {
            if ($field['Field'] !== 'id' && $field['Field'] !== 'created_at' && $field['Field'] !== 'updated_at') {
                $fieldName = $field['Field'];

                $params = [];

                if ($field['Null'] === 'NO') {
                    $params[] = 'required';
                }

                if ($field['Null'] === 'YES') {
                    $params[] = 'nullable';
                }

                if (strstr($field['Type'], 'bigint') or strstr($field['Type'], 'int')) {
                    $params[] = 'integer';
                }

                if (strstr($field['Type'], 'varchar') or strstr($field['Type'], 'text') or strstr($field['Type'], 'longtext')) {
                    $params[] = 'string';
                }

                $validator[$fieldName] = implode('|', $params);
            }
        }

        $this->validator = $validator;
    }
}
