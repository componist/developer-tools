<?php

namespace Componist\DeveloperTools\Livewire;

use Componist\DeveloperTools\Infrastructure\AuthorizesDeveloperTools;
use Componist\DeveloperTools\Infrastructure\DatabaseSchemaReader;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Model extends Component
{
    use AuthorizesDeveloperTools;

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

    public bool $enableSoftDeletes = false;

    public bool $generateCasts = true;

    public bool $hasDeletedAt = false;

    public bool $generateFactory = false;

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

    /**
     * @return array<string, string>
     */
    public function getAvailableFakerTypes(): array
    {
        return [
            'word' => 'Word',
            'sentence' => 'Sentence',
            'paragraph' => 'Paragraph',
            'text' => 'Text (200 Zeichen)',
            'email' => 'E-Mail (unique)',
            'name' => 'Name',
            'firstName' => 'Vorname',
            'lastName' => 'Nachname',
            'company' => 'Firma',
            'randomNumber' => 'Zufallszahl',
            'numberBetween' => 'Zahl 1–100',
            'randomFloat' => 'Dezimalzahl',
            'boolean' => 'Boolean',
            'dateTime' => 'DateTime',
            'date' => 'Datum',
            'time' => 'Uhrzeit',
            'uuid' => 'UUID',
            'slug' => 'Slug',
            'streetAddress' => 'Straße',
            'city' => 'Stadt',
            'countryCode' => 'Ländercode',
            'imageSmall' => 'Bild 200x300 (Picsum)',
            'imageLarge' => 'Bild 800x600 (Picsum)',
            'array' => 'Leeres Array []',
            'null' => 'null',
            'skip' => '— nicht in Factory —',
        ];
    }

    public function updatedTable(string $value): void
    {
        $this->getTable($value);
    }

    public function getTable(string $table): void
    {
        if (! empty($table)) {
            $this->table = $table;
            $temp = DatabaseSchemaReader::columns($table);

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
                $temp[$key]['fake_type'] = $this->getDefaultFakerType($value);
            }
            $this->fields = $temp;

            $this->hasDeletedAt = false;
            foreach ($this->fields as $field) {
                if (($field['Field'] ?? '') === 'deleted_at') {
                    $this->hasDeletedAt = true;
                    $this->enableSoftDeletes = true;

                    break;
                }
            }
            if (! $this->hasDeletedAt) {
                $this->enableSoftDeletes = false;
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

        $useSoftDeletes = $this->enableSoftDeletes;

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

        if (File::put($path, $this->stub) === false) {
            $this->messageSuccess = false;
            $this->message = 'Model konnte nicht geschrieben werden: '.$path;

            return;
        }

        if ($this->generateFactory) {
            $this->createFactoryFile($modelClass);
        }

        $this->messageSuccess = true;
        $this->message = 'Model erstellt: '.$path.($this->generateFactory ? ' (Factory wurde ebenfalls generiert)' : '');
        $this->model = $this->tableSelected();
    }

    private function getTables(): void
    {
        $this->table_list = DatabaseSchemaReader::tableNames();
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

    private function createFactoryFile(string $modelClass): void
    {
        $factoryPath = database_path('factories/'.$modelClass.'Factory.php');
        $dir = dirname($factoryPath);
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        $fieldsLines = [];
        foreach ($this->fields as $field) {
            $name = $field['Field'] ?? '';
            if ($name === '' || in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
                continue;
            }
            $fakeType = $field['fake_type'] ?? 'word';
            if ($fakeType === 'skip') {
                continue;
            }
            $expression = $this->getFakerExpression($fakeType, $field);
            if ($expression !== null) {
                $fieldsLines[] = "            '".$name."' => ".$expression.',';
            }
        }

        $fieldsString = implode("\n", $fieldsLines);

        $content = <<<PHP
<?php

namespace Database\Factories;

use App\Models\\$modelClass;
use Illuminate\\Database\\Eloquent\\Factories\\Factory;

/**
 * @extends Factory<$modelClass>
 */
class {$modelClass}Factory extends Factory
{
    protected \$model = $modelClass::class;

    public function definition(): array
    {
        return [
$fieldsString
        ];
    }
}
PHP;

        File::put($factoryPath, $content);
    }

    /**
     * Default Faker type based on column name and type.
     *
     * @param  array<string, mixed>  $field
     */
    private function getDefaultFakerType(array $field): string
    {
        $name = (string) ($field['Field'] ?? '');
        $type = strtolower((string) ($field['Type'] ?? ''));

        if (in_array($name, ['id', 'created_at', 'updated_at', 'deleted_at'], true)) {
            return 'skip';
        }

        $nameLower = strtolower($name);

        if (str_contains($nameLower, 'email')) {
            return 'email';
        }
        if (str_contains($nameLower, 'first_name') || str_contains($nameLower, 'vorname')) {
            return 'firstName';
        }
        if (str_contains($nameLower, 'last_name') || str_contains($nameLower, 'nachname')) {
            return 'lastName';
        }
        if (str_contains($nameLower, 'name') || str_contains($nameLower, 'title')) {
            return 'name';
        }
        if (str_contains($nameLower, 'company') || str_contains($nameLower, 'firma')) {
            return 'company';
        }
        if (str_contains($nameLower, 'street') || str_contains($nameLower, 'strasse')) {
            return 'streetAddress';
        }
        if (str_contains($nameLower, 'city') || str_contains($nameLower, 'stadt')) {
            return 'city';
        }
        if (str_contains($nameLower, 'country') && str_contains($nameLower, 'code')) {
            return 'countryCode';
        }
        if (str_contains($nameLower, 'uuid') || str_contains($nameLower, 'slug')) {
            return $nameLower === 'slug' ? 'slug' : 'uuid';
        }
        if (str_contains($nameLower, 'image') || str_contains($nameLower, 'bild') || str_contains($nameLower, 'photo')) {
            return 'imageSmall';
        }
        if (str_contains($type, 'int') && ! str_contains($type, 'tinyint(1)')) {
            return 'randomNumber';
        }
        if (str_contains($type, 'decimal') || str_contains($type, 'double') || str_contains($type, 'float')) {
            return 'randomFloat';
        }
        if (str_contains($type, 'text') || str_contains($type, 'longtext')) {
            return 'text';
        }
        if (str_contains($type, 'varchar') || str_contains($type, 'char')) {
            return 'word';
        }
        if (str_contains($type, 'date') || str_contains($type, 'time')) {
            return str_contains($type, 'date') && ! str_contains($type, 'time') ? 'date' : 'dateTime';
        }
        if (str_contains($type, 'json')) {
            return 'array';
        }
        if (str_contains($type, 'bool') || str_contains($type, 'tinyint(1)')) {
            return 'boolean';
        }

        return 'word';
    }

    /**
     * PHP expression for a Faker type. Null for skip.
     *
     * @param  array<string, mixed>  $field
     */
    private function getFakerExpression(string $fakeType, array $field): ?string
    {
        $nullable = (($field['Null'] ?? '') === 'YES');
        $optional = $nullable ? 'fake()->optional(0.8)->' : 'fake()->';

        $expressions = [
            'word' => 'fake()->word()',
            'sentence' => 'fake()->sentence()',
            'paragraph' => 'fake()->paragraph()',
            'text' => 'fake()->text(200)',
            'email' => 'fake()->unique()->email()',
            'name' => 'fake()->name()',
            'firstName' => 'fake()->firstName()',
            'lastName' => 'fake()->lastName()',
            'company' => 'fake()->company()',
            'randomNumber' => 'fake()->randomNumber()',
            'numberBetween' => 'fake()->numberBetween(1, 100)',
            'randomFloat' => 'fake()->randomFloat(2, 0, 1000)',
            'boolean' => 'fake()->boolean()',
            'dateTime' => 'fake()->dateTime()',
            'date' => 'fake()->date()',
            'time' => 'fake()->time()',
            'uuid' => 'fake()->uuid()',
            'slug' => 'fake()->slug()',
            'streetAddress' => 'fake()->streetAddress()',
            'city' => 'fake()->city()',
            'countryCode' => 'fake()->countryCode()',
            'imageSmall' => "'https://picsum.photos/200/300'",
            'imageLarge' => "'https://picsum.photos/800/600'",
            'array' => '[]',
            'null' => 'null',
        ];

        if ($fakeType === 'skip') {
            return null;
        }
        if ($fakeType === 'null') {
            return 'null';
        }
        if (isset($expressions[$fakeType])) {
            $expr = $expressions[$fakeType];
            if (
                $nullable
                && $fakeType !== 'null'
                && ! str_contains($expr, 'optional')
                && str_starts_with($expr, 'fake()->')
            ) {
                $inner = str_replace('fake()->', '', $expr);

                return 'fake()->optional(0.8)->'.$inner;
            }

            return $expr;
        }

        return 'fake()->word()';
    }
}
