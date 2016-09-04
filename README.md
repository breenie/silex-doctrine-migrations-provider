# silex-doctrine-migrations-provider

A doctrine migrations provider for Silex.

## Installation

Install the provider through Composer:

```bash
composer require kurl/silex-doctrine-migrations-provider
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
