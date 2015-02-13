# silex-doctrine-migrations-provider

A doctrine migrations provider for Silex.

## Installation

Add the dependency to the require in your `composer.json`.

```json
{
    "require": {
        "doctrine/migrations": "1.0.*@dev",
        "kurl/silex-doctrine-migrations-provider": "~0.1"
    }
}
```

## Usage

Add the provider with your config...

```php
<?php

$console = new \Symfony\Component\Console\Application();

$app->register(
    new \Kurl\Silex\Provider\DoctrineMigrationsProvider($console), 
    array(
        'migrations.directory'  => __DIR__ . '/../path/to/migrations',
        'migrations.name'       => 'Acme Migrations',
        'migrations.namespace'  => 'Acme\Migrations',
        'migrations.table_name' => 'acme_migrations',
    )
);

$app->boot();
$console->run();
```

### Code coverage reports

```sh
$ bin/phpunit --coverage-html build/coverage --coverage-clover build/logs/clover.xml --log-junit build/logs/phpunit.xml
```

## That was it!
