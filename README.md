# Developer Tools – Debug Helper

Dieses Package stellt zwei Helper-Funktionen für die lokale Entwicklung bereit:

- `dev_dump(...$vars)`
- `dev_dd(...$vars)`

Beide Funktionen geben **nur dann** etwas aus, wenn der aktuelle Request den Query-Parameter `dev=1` (oder allgemein einen "truthy" `dev`-Wert) enthält. So kannst du Debug-Ausgaben gezielt aktivieren, ohne deinen Code ständig umzuschreiben.

## Installation

### 1. Package einbinden (im Hauptprojekt bereits erledigt)

Das Package ist im Projekt bereits per PSR‑4 eingebunden:

```json
"autoload": {
    "psr-4": {
        "Componist\\\\DeveloperTools\\\\": "packages/developer-tools/src"
    },
    "files": [
        "packages/developer-tools/src/helpers.php"
    ]
}
```

Falls du die Einbindung anpassen möchtest, achte darauf, dass:

- der Namespace `Componist\\DeveloperTools\\` auf `packages/developer-tools/src` zeigt und
- die Helper-Datei `packages/developer-tools/src/helpers.php` im `autoload.files`-Block eingetragen ist.

### 2. Autoloader aktualisieren

Nach Änderungen an der `composer.json` muss der Autoloader neu generiert werden:

```bash
composer dump-autoload
```

Danach stehen die Helper global im gesamten Projekt zur Verfügung.

## Nutzung der Helper

### `dev_dump(...$vars): void`

Wrapper um den Symfony `VarDumper`, der nur aktiv ist, wenn `dev=1` im Request gesetzt ist.

```php
dev_dump($user, $request->all());
```

- Ohne `?dev=1` im Query-String passiert nichts.
- Mit `?dev=1` werden die übergebenen Variablen mit dem Symfony VarDumper ausgegeben.

### `dev_dd(...$vars): void`

Wrapper um Laravels `dd()` (dump and die), ebenfalls nur aktiv bei `dev=1`.

```php
dev_dd($order, $paymentData);
```

- Ohne `?dev=1` wird **nicht** abgebrochen – die Funktion kehrt sofort zurück.
- Mit `?dev=1` verhält sie sich wie ein normales `dd()`: Ausgabe der Variablen und Abbruch der Ausführung.

## Typische Einsatzszenarien

- Debugging in Controllern, Livewire-Komponenten oder Services, ohne versehentlich Dumps in der Produktion sichtbar zu machen.
- Temporäre Debug-Ausgaben, die nur bei Bedarf per `?dev=1` aktiviert werden.

Beispiele:

```php
public function show(int $id)
{
    $user = User::findOrFail($id);

    dev_dump($user); // nur sichtbar mit ?dev=1

    return view('users.show', compact('user'));
}
```

```php
dev_dd($payload); // nur "dd" mit ?dev=1, ansonsten keine Wirkung
```

# Componist Developer Tools

- [x] show Modal fillable array , casts array
- [x] show Validation settings from database  table
- [x] create Migration from database table
- [x] get database schema
- [x] snippest for GRUD, PEST
- [ ] create blueprint yaml from database
- [ ] create Laravel Modal from database schema
- [ ] edit database schema / create / delete/ reorder columns
- [x] routing testing
- [x] clear or delete database
- [ ] create robots.txt file
- [x] create sitemap file from all public routes
- [x] show all register commands
- [ ] job liste with run job
- [x] get .env
- [ ] get config files
- [x] show logs



This package is only intended for developers and in the local system.

```php

if (env('APP_DEBUG') === true) {
  // routings
}
  ```


## install
### #1. copy this folder to root/packages/developer-tools


### #2. in your composer.json include to autoload -> psr-4

```json
//.....
"autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/",
           
            "Componist\\DeveloperTools\\": "packages/developer-tools/src",
           
        }
    },
//.....
```


### #3. in your root/bootstrap/providers.php

```php
return [
    
    Componist\DeveloperTools\DeveloperToolsServiceProvider::class,
   
];

```


### #4. show in url with 

```
https://your-url/componist

(URL-Prefix ist in der Config `config/developer-tools.php` unter `route_prefix` änderbar.)
```