<?php
/**
 * A silex provider for doctrine migrations.
 *
 * @author  chris
 * @created 15/12/14 10:12
 */

namespace Kurl\Silex\Provider;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Tools\Console\Command\AbstractCommand;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class DoctrineMigrationsProvider
 *
 * @package Kurl\Silex\Provider
 */
class DoctrineMigrationsProvider implements ServiceProviderInterface
{

    /**
     * The console application.
     *
     * @var Console
     */
    protected $console;

    /**
     * Creates a new doctrine migrations provider.
     *
     * @param Console $console
     */
    public function __construct(Console $console = null)
    {
        $this->console = $console;
    }

    /**
     * Registers services on the given app.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['migrations.output_writer'] = new OutputWriter(
            function ($message) {
                $output = new ConsoleOutput();
                $output->writeln($message);
            }
        );

        $app['migrations.directory']  = null;
        $app['migrations.name']       = 'Migrations';
        $app['migrations.namespace']  = null;
        $app['migrations.table_name'] = 'migration_versions';
    }

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     *
     * @param Application $app
     */
    public function boot(Application $app)
    {
        $helperSet = new HelperSet(array(
            'connection' => new ConnectionHelper($app['db']),
            'dialog'     => new DialogHelper(),
        ));

        if (isset($app['orm.em'])) {
            $helperSet->set(new EntityManagerHelper($app['orm.em']), 'em');
        }

        $console = $this->getConsole($app);

        $console->setHelperSet($helperSet);

        $commands = array(
            'Doctrine\DBAL\Migrations\Tools\Console\Command\ExecuteCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\GenerateCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\MigrateCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\StatusCommand',
            'Doctrine\DBAL\Migrations\Tools\Console\Command\VersionCommand'
        );

        // @codeCoverageIgnoreStart
        if (true === $console->getHelperSet()->has('em')) {
            $commands[] = 'Doctrine\DBAL\Migrations\Tools\Console\Command\DiffCommand';
        }
        // @codeCoverageIgnoreEnd

        $configuration = new Configuration($app['db'], $app['migrations.output_writer']);

        $configuration->setMigrationsDirectory($app['migrations.directory']);
        $configuration->setName($app['migrations.name']);
        $configuration->setMigrationsNamespace($app['migrations.namespace']);
        $configuration->setMigrationsTableName($app['migrations.table_name']);

        $configuration->registerMigrationsFromDirectory($app['migrations.directory']);

        foreach ($commands as $name) {
            /** @var AbstractCommand $command */
            $command = new $name();
            $command->setMigrationConfiguration($configuration);
            $console->add($command);
        }
    }

    /**
     * Gets the console application.
     *
     * @param Application $app
     * @return Console|null
     */
    public function getConsole(Application $app = null)
    {
        return $this->console ?: ((isset($app['console'])) ? $app['console'] : new Console());
    }
}
