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