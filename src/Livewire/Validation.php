<?php

namespace Componist\DeveloperTools\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Validation extends Component
{
    /** @var array<int, string> */
    public array $table_list = [];

    public string $table = '';

    /** @var array<int, array<string, mixed>> */
    public array $fields = [];

    /** @var array<string, string> */
    public array $validator = [];

    public ?string $message = null;

    public bool $messageSuccess = true;

    public bool $generateFormRequestClass = false;

    /**
     * Vorgeschlagener Form-Request-Klassename basierend auf Tabellenname (z. B. "posts" → "StorePostRequest").
     * Livewire Computed Property: $this->formRequestClassName
     */
    public function getFormRequestClassNameProperty(): string
    {
        if ($this->table === '') {
            return 'StoreRequest';
        }

        $singular = Str::singular($this->table);

        return 'Store'.Str::studly($singular).'Request';
    }

    /**
     * Vollständiger Form-Request-Stub (Laravel Form Request) zum Kopieren.
     * Siehe https://laravel.com/docs/12.x/validation#creating-form-requests
     * Livewire Computed Property: $this->formRequestStub
     */
    public function getFormRequestStubProperty(): string
    {
        if ($this->validator === []) {
            return '';
        }

        $lines = [];
        foreach ($this->validator as $field => $rules) {
            $lines[] = "        '".addslashes($field)."' => '".addslashes($rules)."'";
        }
        $rulesBlock = implode(",\n", $lines);
        $className = $this->formRequestClassName;

        return <<<PHP
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class {$className} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string> */
    public function rules(): array
    {
        return [
{$rulesBlock}
        ];
    }
}
PHP;
    }

    public function mount(): void
    {
        $this->getTables();
    }

    public function updatedTable(): void
    {
        if ($this->table !== '') {
            $this->getTable($this->table);
        } else {
            $this->fields = [];
            $this->validator = [];
            $this->message = null;
        }
    }

    public function render(): View
    {
        return view('developerTools::livewire.validation')->layout('developerTools::layouts.package');
    }

    public function getTable(string $table): void
    {
        $table = trim($table);
        if ($table === '') {
            return;
        }

        // Nur erlaubte Tabellennamen (Whitelist) – verhindert SQL-Injection
        if (! in_array($table, $this->table_list, true)) {
            return;
        }

        $this->table = $table;
        $temp = json_decode(json_encode(DB::select('SHOW COLUMNS FROM `'.$table.'`')), true);

        if (! is_array($temp)) {
            $this->fields = [];
            $this->validator = [];

            return;
        }

        foreach ($temp as $key => $value) {
            $temp[$key]['checked'] = 0;
            $temp[$key]['faker'] = [
                'Field' => $value['Field'],
                'type' => null,
                'params' => null,
                'default' => null,
            ];
        }
        $this->fields = $temp;
        $this->createValidateString();
    }

    private function getTables(): void
    {
        $this->table_list = [];
        foreach (DB::select('SHOW TABLES') as $value) {
            $decoded = json_decode(json_encode($value), true);
            if (is_array($decoded)) {
                $this->table_list[] = implode(',', $decoded);
            }
        }
    }

    public function createFormRequestFile(): void
    {
        $this->message = null;

        if ($this->table === '' || $this->validator === []) {
            $this->messageSuccess = false;
            $this->message = 'Bitte zuerst eine Tabelle auswählen. Es müssen Validierungsregeln vorhanden sein.';

            return;
        }

        $className = $this->formRequestClassName;
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $className)) {
            $this->messageSuccess = false;
            $this->message = 'Ungültiger Klassenname für Form Request.';

            return;
        }

        $path = app_path('Http/Requests/'.$className.'.php');
        $dir = dirname($path);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        if (File::exists($path)) {
            $this->messageSuccess = false;
            $this->message = 'Form Request existiert bereits: App\Http\Requests\\'.$className;

            return;
        }

        $stub = $this->formRequestStub;
        if ($stub === '') {
            $this->messageSuccess = false;
            $this->message = 'Form Request Stub konnte nicht erzeugt werden.';

            return;
        }

        if (File::put($path, $stub) === false) {
            $this->messageSuccess = false;
            $this->message = 'Form Request konnte nicht geschrieben werden: '.$path;

            return;
        }

        $this->messageSuccess = true;
        $this->message = 'Form Request erstellt: '.$path;
    }

    private function createValidateString(): void
    {
        $validator = [];
        $typeLower = null;

        foreach ($this->fields as $field) {
            $fieldName = $field['Field'] ?? '';
            if ($fieldName === '' || in_array($fieldName, ['id', 'created_at', 'updated_at'], true)) {
                continue;
            }

            $params = [];
            $rawType = $field['Type'] ?? '';
            $typeLower = strtolower($rawType);

            // required / nullable
            if (($field['Null'] ?? '') === 'NO') {
                $params[] = 'required';
            } else {
                $params[] = 'nullable';
            }

            // Typ-Regeln (str_contains statt strstr, mehr Typen)
            if (str_contains($typeLower, 'bigint') || str_contains($typeLower, 'int')) {
                $params[] = 'integer';
            } elseif (str_contains($typeLower, 'tinyint(1)') || $typeLower === 'boolean') {
                $params[] = 'boolean';
            } elseif (str_contains($typeLower, 'decimal') || str_contains($typeLower, 'numeric') || str_contains($typeLower, 'float') || str_contains($typeLower, 'double')) {
                $params[] = 'numeric';
            } elseif (str_contains($typeLower, 'date') && ! str_contains($typeLower, 'datetime')) {
                $params[] = 'date';
            } elseif (str_contains($typeLower, 'datetime') || str_contains($typeLower, 'timestamp')) {
                $params[] = 'date';
            } elseif (str_contains($typeLower, 'json')) {
                $params[] = 'array';
            } elseif (str_contains($typeLower, 'varchar') || str_contains($typeLower, 'char') || str_contains($typeLower, 'text') || str_contains($typeLower, 'longtext')) {
                $params[] = 'string';
                // max-Länge für varchar auslesen (z. B. varchar(255) -> max:255)
                if (preg_match('/varchar\((\d+)\)/', $rawType, $m)) {
                    $params[] = 'max:'.$m[1];
                }
            }

            $validator[$fieldName] = implode('|', $params);
        }

        $this->validator = $validator;
    }
}
