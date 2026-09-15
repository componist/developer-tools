# AGENTS – Developer Tools

## Zweck

Lokale Entwickler-Hilfen (Schema, Artisan, Logs, Env, Truncate, Dev-Stubs).

## Struktur

```
src/Infrastructure/DatabaseSchemaReader.php
src/Support/AuthorizesDeveloperTools.php
src/Livewire/…
```

## Security

- Gate `manage-developer-tools` (`DEVELOPER_TOOLS_ADMIN_IDS` / `isAdmin`)
- Routen: `auth` + `can:manage-developer-tools`
- Debug-UI nur `APP_DEBUG` **und** `local`/`testing`
- `/dev`, `/backup` nie ohne Auth
- Trait `AuthorizesDeveloperTools` in allen Livewire-Komponenten

## Don’t

- Don’t: Truncate/Drop ohne Gate; `/dev` öffentlich lassen
