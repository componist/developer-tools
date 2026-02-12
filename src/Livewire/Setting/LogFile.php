<?php

namespace Componist\DeveloperTools\Livewire\Setting;

use Illuminate\Support\Facades\File;
use Livewire\Component;

class LogFile extends Component
{
    public string $path = '';

    public array $logs = [];

    public ?int $selectedLogIndex = null;

    public int $limit = 100;

    public function mount()
    {
        $this->path = storage_path('logs/laravel.log');
        $this->loadLogs();
    }

    public function loadLogs()
    {
        $this->logs = [];
        $this->selectedLogIndex = null;

        if (!File::exists($this->path)) {
            return;
        }

        // Lese die letzten Zeilen der Datei (für Performance)
        $lines = $this->readLastLines($this->path, 5000);
        $content = implode("\n", $lines);

        // Parse Log-Einträge
        $this->logs = $this->parseLogs($content);
        
        // Begrenze auf die letzten N Einträge
        $this->logs = array_slice($this->logs, -$this->limit);
    }

    public function selectLog($index)
    {
        $this->selectedLogIndex = $index === $this->selectedLogIndex ? null : $index;
    }

    public function deleteLog($index)
    {
        if (!isset($this->logs[$index])) {
            return;
        }

        // Lade die gesamte Log-Datei
        if (!File::exists($this->path)) {
            return;
        }

        $fullContent = File::get($this->path);
        $allLogs = $this->parseLogs($fullContent);

        // Finde den zu löschenden Eintrag in der vollständigen Liste
        $logToDelete = $this->logs[$index];
        $allLogs = array_filter($allLogs, function ($log) use ($logToDelete) {
            return $log['full_content'] !== $logToDelete['full_content'];
        });

        // Schreibe die Datei neu ohne den gelöschten Eintrag
        $newContent = implode("\n", array_map(function ($log) {
            return $log['full_content'];
        }, $allLogs));

        File::put($this->path, $newContent . "\n");

        // Lade die Logs neu
        $this->loadLogs();

        session()->flash('message', 'Log-Eintrag wurde gelöscht.');
    }

    public function deleteAllLogs()
    {
        if (File::exists($this->path)) {
            File::put($this->path, '');
        }

        $this->loadLogs();

        session()->flash('message', 'Alle Log-Einträge wurden gelöscht.');
    }

    private function readLastLines(string $filePath, int $numLines): array
    {
        $file = new \SplFileObject($filePath, 'r');
        $file->seek(PHP_INT_MAX);
        $lastLine = $file->key();
        
        $startLine = max(0, $lastLine - $numLines);
        $file->seek($startLine);
        
        $lines = [];
        while (!$file->eof()) {
            $lines[] = $file->current();
            $file->next();
        }
        
        return $lines;
    }

    private function parseLogs(string $content): array
    {
        $logs = [];
        $lines = explode("\n", $content);
        $currentLog = null;

        foreach ($lines as $line) {
            // Prüfe ob die Zeile ein neuer Log-Eintrag ist (beginnt mit [YYYY-MM-DD)
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+):\s*(.*)$/', $line, $matches)) {
                // Speichere den vorherigen Log-Eintrag
                if ($currentLog !== null) {
                    $logs[] = $currentLog;
                }

                // Starte einen neuen Log-Eintrag
                $currentLog = [
                    'timestamp' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'full_content' => $line,
                ];
            } elseif ($currentLog !== null) {
                // Füge die Zeile zum aktuellen Log-Eintrag hinzu
                $currentLog['full_content'] .= "\n" . $line;
            }
        }

        // Füge den letzten Log-Eintrag hinzu
        if ($currentLog !== null) {
            $logs[] = $currentLog;
        }

        return $logs;
    }

    public function render()
    {
        return view('developerTools::livewire.setting.log-file')->layout('developerTools::layouts.package');
    }
}

