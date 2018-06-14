<?php
/**
 * Exercises the migrations provider.
 *
 * @author  chris
 * @created 15/12/14 14:37
 */

namespace Kurl\Silex\Provider\Tests;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Kurl\Silex\Provider\DoctrineMigrationsProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Silex\Application as Silex;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application as Console;
use Doctrine\Migrations\OutputWriter;
use Doctrine\Migrations\Configuration\Configuration;

/**
 * Class DoctrineMigrationsProviderTest
 *
 * @package Kurl\Silex\Provider\Tests
 * @coversDefaultClass Kurl\Silex\Provider\DoctrineMigrationsProvider
 */
class DoctrineMigrationsProviderTest extends TestCase
{
    /**
     * @covers ::register
     * @covers ::boot
     */
    public function testDefaults()
    {
        $app = new Silex();
        $app['db'] = $this->getConnectionMock();

        $console = new Console();

        $app->register(new DoctrineMigrationsProvider($console));

        $this->assertTrue($app->offsetExists('migrations.output_writer'));
        $this->assertInstanceOf(OutputWriter::class, $app['migrations.output_writer']);
        $this->assertNull($app['migrations.namespace']);
        $this->assertNull($app['migrations.directory']);
        $this->assertEquals('Migrations', $app['migrations.name']);
        $this->assertEquals('migration_versions', $app['migrations.table_name']);

        $this->assertMigrationCommandsAbsent($console);

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = '\Kurl\Silex\Provider\Tests\Migrations';

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);
        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCorrectMigrationCommandsCount($app);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $app->boot();

        $this->assertMigrationCommandsPresent($console);
    }

    /**
     * Ensures you get a console even if you don't set one.
     */
    public function testOptionalConsoleConstructor()
    {
        $provider = new DoctrineMigrationsProvider();
        $this->assertInstanceOf(Console::class, $provider->getConsole());
    }

    /**
     * @covers ::register
     * @covers ::boot
     */
    public function testDefaultsWithOrm()
    {
        $app = new Silex();
        $app['db'] = $this->getConnectionMock();
        $app['orm.em'] = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $console = new Console();
        $app->register(new DoctrineMigrationsProvider($console));

        $this->assertMigrationCommandsAbsent($console);

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = 'Kurl\Silex\Provider\Tests\Migrations';

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);

        $app->boot();

        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCorrectMigrationCommandsCount($app, true);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $this->assertMigrationCommandsPresent($console, true);
    }

    /**
     * @covers ::register
     */
    public function testWithNoConsole()
    {
        $app = new Silex();
        $app['db'] = $this->getConnectionMock();

        $app->register(new DoctrineMigrationsProvider());

        $this->assertTrue($app->offsetExists('migrations.output_writer'));
        $this->assertInstanceOf(OutputWriter::class, $app['migrations.output_writer']);
        $this->assertNull($app['migrations.namespace']);
        $this->assertNull($app['migrations.directory']);
        $this->assertEquals('Migrations', $app['migrations.name']);
        $this->assertEquals('migration_versions', $app['migrations.table_name']);

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = 'Kurl\Silex\Provider\Tests\Migrations';

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);
        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCorrectMigrationCommandsCount($app);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $app->boot();
    }

    /**
     * @covers ::register
     */
    public function testWithPimple()
    {
        $app = new Container();
        $app['db'] = $this->getConnectionMock();


        $app->register(new DoctrineMigrationsProvider());

        $this->assertTrue($app->offsetExists('migrations.output_writer'));
        $this->assertInstanceOf(OutputWriter::class, $app['migrations.output_writer']);
        $this->assertNull($app['migrations.namespace']);
        $this->assertNull($app['migrations.directory']);
        $this->assertEquals('Migrations', $app['migrations.name']);
        $this->assertEquals('migration_versions', $app['migrations.table_name']);

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = 'Kurl\Silex\Provider\Tests\Migrations';

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);
        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCorrectMigrationCommandsCount($app);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }
    }

    /**
     * @covers ::register
     * @covers ::boot
     */
    public function testWithConsoleFromContainer()
    {
        $app = new Silex();
        $app['db'] = $this->getConnectionMock();

        $app->register(new DoctrineMigrationsProvider());

        $this->assertTrue($app->offsetExists('migrations.output_writer'));
        $this->assertInstanceOf(OutputWriter::class, $app['migrations.output_writer']);
        $this->assertNull($app['migrations.namespace']);
        $this->assertNull($app['migrations.directory']);
        $this->assertEquals('Migrations', $app['migrations.name']);
        $this->assertEquals('migration_versions', $app['migrations.table_name']);

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = 'Kurl\Silex\Provider\Tests\Migrations';

        $console = new Console();
        $app['console'] = $console;

        $this->assertMigrationCommandsAbsent($console);

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);
        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCorrectMigrationCommandsCount($app);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $app->boot();

        $this->assertMigrationCommandsPresent($console);
    }

    public function dataGetConsole()
    {
        $containerConsole = new Console();
        $constructorConsole = new Console();

        return [
            [null, null, null],
            [$constructorConsole, null, $constructorConsole],
            [null, $containerConsole, $containerConsole],
            [$constructorConsole, $containerConsole, $constructorConsole],
        ];
    }

    /**
     * @param  Console|null $constructorConsole The console instance passed to the constructor
     * @param  Console|null $containerConsole The console instance from $app['console']
     * @param  Console|null $expected The expected result from ::getConsole()
     * @covers ::getConsole
     * @dataProvider dataGetConsole
     */
    public function testGetConsole($constructorConsole, $containerConsole, $expected)
    {
        $app = new Container();
        $app['console'] = $containerConsole;
        $provider = new DoctrineMigrationsProvider($constructorConsole);
        $this->assertSame($expected, $provider->getConsole($app));
    }

    /**
     * @return Connection
     */
    private function getConnectionMock()
    {
        $connection = $this->prophesize(Connection::class);
        $connection->getSchemaManager()->willReturn($this->prophesize(AbstractSchemaManager::class)->reveal());
        $connection->getDatabasePlatform()->willReturn($this->prophesize(AbstractPlatform::class)->reveal());

        return $connection->reveal();
    }

    /**
     * Verifies that none of the Doctrine Migrations commands are attached to the Console application
     *
     * @param Console $console
     */
    private function assertMigrationCommandsAbsent(Console $console)
    {
        $this->assertFalse($console->has('migrations:dump-schema'));
        $this->assertFalse($console->has('migrations:execute'));
        $this->assertFalse($console->has('migrations:generate'));
        $this->assertFalse($console->has('migrations:latest'));
        $this->assertFalse($console->has('migrations:migrate'));
        $this->assertFalse($console->has('migrations:rollup'));
        $this->assertFalse($console->has('migrations:status'));
        $this->assertFalse($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));
        $this->assertFalse($console->has('migrations:up-to-date'));
    }

    /**
     * Verifies that all of the expected Doctrine Migrations commands are attached to the Console application
     *
     * @param Console $console
     * @param bool $hasOrm
     */
    private function assertMigrationCommandsPresent(Console $console, $hasOrm = false)
    {
        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:dump-schema'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:latest'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:rollup'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
        $this->assertTrue($console->has('migrations:up-to-date'));

        if ($hasOrm) {
            $this->assertTrue($console->has('migrations:diff'));
        } else {
            $this->assertFalse($console->has('migrations:diff'));
        }
    }

    /**
     * Verifies that the correct number of Doctrine Migration commands have been attached to the Console application
     *
     * @param Container $container
     * @param bool $hasOrm
     */
    private function assertCorrectMigrationCommandsCount(Container $container, $hasOrm = false)
    {
        $expectedCount = $hasOrm ? 10 : 9;
        $this->assertCount($expectedCount, $container['migrations.command_names']);
        $this->assertCount($expectedCount, $container['migrations.commands']);
    }
}
