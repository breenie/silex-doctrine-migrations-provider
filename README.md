# silex-doctrine-migrations-provider

A doctrine migrations service provider for Silex 2.x or Pimple 3.x.

## Installation

Install the provider through Composer:

```bash
composer require kurl/silex-doctrine-migrations-provider
```

## Usage

### Parameters

These are the configuration parameters you could configure for the provider:

- `migrations.directory`: The directory with migrations. Required.
- `migrations.namespace`: The namespace for the migration classes. Required.
- `migrations.name`: The name of the migrations suite. Defaults to "Migrations".
- `migrations.table_name`: The database migrations table. Defaults to `migration_versions`.

### Services

The service provider registers the following services which you could use:

- `migrations.em_helper_set`: The Doctrine ORM Entity Manager helper set. It would be registered if you have registered the `orm.em` service supposedly from the `DoctrineServiceProvider.
- `migrations.commands`: An array of Symfony command instances. You could use them to add to your own Console application.


There are also a few other services which the provider uses internally and they are exposed so you could extend or override them with the lazy loading of Pimple:

- `migrations.output_writer`: `Doctrine\DBAL\Migrations\OutputWriter` instance used in the Doctrine migrations configuration.
- `migrations.configuration`: `Doctrine\DBAL\Migrations\Configuration\Configuration` instance used in the console commands.
- `migrations.command_names`: Array of command names. You could add your own here to be lazy loaded into `migrations.commands`.

The provider requires an instance of `Symfony\Component\Console\Application` to be passed to its constructor.

You can register the provider and configure it like so:

```php
<?php

$console = new \Symfony\Component\Console\Application();

$app->register(
    new \Kurl\Silex\Provider\DoctrineMigrationsProvider($console),
    array(
        'migrations.directory' => __DIR__ . '/../path/to/migrations',
        'migrations.name' => 'Acme Migrations',
        'migrations.namespace' => 'Acme\Migrations',
        'migrations.table_name' => 'acme_migrations',
    )
);
```

### Silex

The provider implements `Silex\Api\BootableProviderInterface, so if you use the provider with Silex, booting the application would register the commands for you:

```php
<?php

$console = new \Symfony\Component\Console\Application();

$app->register(
    new \Kurl\Silex\Provider\DoctrineMigrationsProvider($console),
    array(
        'migrations.directory' => __DIR__ . '/../path/to/migrations',
        'migrations.namespace' => 'Acme\Migrations',
    )
);

$app->boot();
$console->run();
```

### Pimple

If you use the provider with Pimple without using Silex, then you'd need to register the helper set and the commands to your Console application yourself.
This is made very easy by the provider as it already registered all the needed services:

```php
<?php

$console = new \Symfony\Component\Console\Application();

$app->register(
    new \Kurl\Silex\Provider\DoctrineMigrationsProvider($console),
    array(
        'migrations.directory' => __DIR__ . '/../path/to/migrations',
        'migrations.namespace' => 'Acme\Migrations',
    )
);

// Register helper set and commands from the `DoctrineMigrationsProvider`
$console->setHelperSet($app['migrations.em_helper_set']);
$console->addCommands($app['migrations.commands']);

$console->run();
```

### Code coverage reports

```sh
$ bin/phpunit --coverage-html build/coverage --coverage-clover build/logs/clover.xml --log-junit build/logs/phpunit.xml
```

## That was it!
