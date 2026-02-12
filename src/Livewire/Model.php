<?php

namespace Componist\DeveloperTools\Livewire;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Model extends Component
{
    /** @var array<mixed> */
    public array $table_list = [];

    public string $table = '';

    /** @var array<mixed> */
    public array $fields = [];

    public string $stub = '';

    public array $model = [];

    public string $fillable = '';

    public ?string $message = null;

    public bool $messageSuccess = true;

    public bool $useHasFactory = true;

    public bool $enableSoftDeletes = true;

    public bool $generateCasts = true;

    public bool $hasDeletedAt = false;

    public function mount(): void
    {
        $this->getTables();
    }

    public function render(): View
    {
        if ($this->table) {
            $this->model = $this->tableSelected();
            $this->fillable = $this->createFillableArrayContent();
        } else {
            $this->model = [];
            $this->fillable = '';
        }

        return view('developerTools::livewire.model')->layout('developerTools::layouts.package');
    }

    public function updatedTable(string $value): void
    {
        $this->getTable($value);
    }

    public function getTable(string $table): void
    {
        if (! empty($table)) {
            $this->table = $table;
            $temp = json_decode(json_encode(DB::select('SHOW COLUMNS FROM `'.$table.'`')), true);

            if (! is_array($temp)) {
                $this->fields = [];

                return;
            }

            foreach ($temp as $key => $value) {
                $temp[$key]['checked'] = 0;
                $temp[$key]['faker']['Field'] = $value['Field'] ?? '';
                $temp[$key]['faker']['type'] = null;
                $temp[$key]['faker']['params'] = null;
                $temp[$key]['faker']['default'] = null;
            }
            $this->fields = $temp;

            $this->hasDeletedAt = false;
            foreach ($this->fields as $field) {
                if (($field['Field'] ?? '') === 'deleted_at') {
                    $this->hasDeletedAt = true;

                    break;
                }
            }
        }
    }

    public function createModelFile(): void
    {
        $this->message = null;

        if (empty($this->table) || empty($this->model)) {
            $this->messageSuccess = false;
            $this->message = 'Bitte zuerst eine Tabelle auswählen.';

            return;
        }

        if ($this->model['existsModel'] === true) {
            $this->messageSuccess = false;
            $this->message = 'Model existiert bereits: App\Models\\'.$this->model['modelClass'];

            return;
        }

        $modelClass = $this->model['modelClass'];
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $modelClass)) {
            $this->messageSuccess = false;
            $this->message = 'Ungültiger Klassenname.';

            return;
        }

        $path = app_path('Models/'.$modelClass.'.php');
        $dir = dirname($path);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $stubPath = __DIR__.'/../stub/create_model.stub';
        if (! File::exists($stubPath)) {
            $this->messageSuccess = false;
            $this->message = 'Stub-Datei fehlt: '.$stubPath;

            return;
        }

        $this->stub = File::get($stubPath);
        $this->stub = str_replace('[modelClass]', $modelClass, $this->stub);

        $useSoftDeletes = $this->enableSoftDeletes && $this->hasDeletedAt;

        $useHasFactoryImport = $this->useHasFactory
            ? "use Illuminate\\Database\\Eloquent\\Factories\\HasFactory;\n"
            : '';

        $useSoftDeletesImport = $useSoftDeletes
            ? "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n"
            : '';

        $traits = [];
        if ($this->useHasFactory) {
            $traits[] = 'HasFactory';
        }
        if ($useSoftDeletes) {
            $traits[] = 'SoftDeletes';
        }

        $traitsString = $traits !== []
            ? '    use '.implode(', ', $traits).';'
            : '';

        $this->stub = str_replace('[useHasFactoryImport]', $useHasFactoryImport, $this->stub);
        $this->stub = str_replace('[useSoftDeletesImport]', $useSoftDeletesImport, $this->stub);
        $this->stub = str_replace('[traits]', $traitsString, $this->stub);

        $this->stub = str_replace('[fillable]', $this->createFillableArrayContent(), $this->stub);

        $castsString = $this->generateCasts ? $this->createCastsArrayContent() : '';
        $this->stub = str_replace('[casts]', $castsString, $this->stub);
        $this->stub = str_replace('[table]', $this->createTablePropertyContent(), $this->stub);

        if (File::put($path, $this->stub) !== false) {
            $this->messageSuccess = true;
            $this->message = 'Model erstellt: '.$path;
            $this->model = $this->tableSelected();
        } else {
            $this->messageSuccess = false;
            $this->message = 'Model konnte nicht geschrieben werden: '.$path;
        }
    }

    private function getTables(): void
    {
        $this->table_list = [];
        foreach (DB::select('SHOW TABLES') as $value) {
            $this->table_list[] = implode(',', json_decode(json_encode($value), true));
        }
    }

    private function tableSelected(): array
    {
        $modelClass = Str::pascal(Str::singular($this->table));
        $path = app_path('Models/'.$modelClass.'.php');
        $existsModel = File::exists($path);

        return [
            'modelClass' => $modelClass,
            'existsModel' => $existsModel,
        ];
    }

    private function createFillableArrayContent(): string
    {
        $fillable = [];
        foreach ($this->fields as $field) {
            $name = $field['Field'] ?? '';
            if ($name !== '' && $name !== 'id') {
                $fillable[] = '"'.$name.'"';
            }
        }

        return 'protected $fillable = ['.implode(', ', $fillable).'];';
    }

    private function createCastsArrayContent(): string
    {
        $lines = [];
        foreach ($this->fields as $field) {
            $name = $field['Field'] ?? '';
            $type = $field['Type'] ?? '';
            if ($name === '') {
                continue;
            }
            $cast = $this->getCastType($type);
            if ($cast !== null) {
                $lines[] = "\t\t'".$name."' => '".$cast."',";
            }
        }
        if ($lines === []) {
            return '';
        }

        return "\n\t/**\n\t * The attributes that should be cast.\n\t *\n\t * @var array<string, string>\n\t */\n\tprotected \$casts = [\n".implode("\n", $lines)."\n\t];";
    }

    private function getCastType(string $type): ?string
    {
        $type = strtolower($type);
        if (str_contains($type, 'int') || str_contains($type, 'bigint')) {
            return 'integer';
        }
        if (str_contains($type, 'tinyint(1)') || $type === 'boolean') {
            return 'boolean';
        }
        if (str_contains($type, 'varchar') || str_contains($type, 'char')) {
            return 'string';
        }
        if (str_contains($type, 'text') || str_contains($type, 'longtext')) {
            return 'string';
        }
        if (str_contains($type, 'double')) {
            return 'double';
        }
        if (str_contains($type, 'float')) {
            return 'float';
        }
        if (str_contains($type, 'decimal')) {
            return 'decimal:2';
        }
        if (str_contains($type, 'timestamp') || str_contains($type, 'datetime') || str_contains($type, 'date')) {
            return 'datetime';
        }
        if (str_contains($type, 'json')) {
            return 'array';
        }

        return 'string';
    }

    private function createTablePropertyContent(): string
    {
        $expectedTable = Str::snake(Str::pluralStudly($this->model['modelClass'] ?? ''));
        $actualTable = $this->table;
        if ($actualTable === $expectedTable || $expectedTable === '') {
            return '';
        }

        return "\n\tprotected \$table = '".addslashes($actualTable)."';";
    }
}
