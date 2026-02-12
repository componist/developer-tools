<?php

namespace Componist\DeveloperTools\Livewire;

use Illuminate\Support\Facades\DB;
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

    public function mount(): void
    {
        $this->getTables();
    }

    public function render(): View
    {
        if ($this->table) {
            $this->model = $this->tableSecelted();
            $this->fillable = $this->createFillableArrayContent();
        } else {
            $this->model = [];
            $this->fillable = '';
        }

        return view('developerTools::livewire.model')->layout('developerTools::layouts.package');
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

    public function createModelFile()
    {
        $this->getStubContent();
        dump($this->model);

        $this->stub = str_replace('[modelClass]', $this->model['modelClass'], $this->stub);
        $this->stub = str_replace('[fillable]', $this->fillable, $this->stub);

        dd($this->stub);
        // table wurde ausgewählt
        // model file wurde nicht gefunden, kann erstellt werden

    }

    private function getTables(): void
    {
        foreach (DB::select('SHOW TABLES') as $value) {

            $this->table_list[] = implode(',', json_decode(json_encode($value), true));
        }
    }

    private function tableSecelted(): array
    {
        $modelClass = Str::pascal(Str::singular($this->table));
        $existsModel = false;

        if (file_exists(app_path('Models/'.$modelClass.'.php'))) {
            $existsModel = true;
        }

        return [
            'modelClass' => $modelClass,
            'existsModel' => $existsModel,
        ];
    }

    private function getStubContent(): void
    {
        $this->stub = file_get_contents(__DIR__.'../../stub/create_model.stub');
    }

    private function createFillableArrayContent()
    {
        $string = 'protected $fillable = [';

        $count = 0;

        foreach ($this->fields as $field) {

            $count++;

            if ($field['Field'] != 'id') {

                if (count($this->fields) == $count) {
                    $string .= '"'.$field['Field'].'"';
                } else {
                    $string .= '"'.$field['Field'].'",';
                }

            }
        }
        $string .= '];';

        return $string;
    }
}
