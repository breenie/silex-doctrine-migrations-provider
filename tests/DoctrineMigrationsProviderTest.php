<?php
/**
 * Exercises the migrations provider.
 *
 * @author  chris
 * @created 15/12/14 14:37
 */

namespace Kurl\Silex\Provider\Tests;

use Kurl\Silex\Provider\DoctrineMigrationsProvider;
use PHPUnit_Framework_TestCase;
use Pimple\Container;
use Silex\Application as Silex;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application as Console;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Configuration\Configuration;

/**
 * Class DoctrineMigrationsProviderTest
 *
 * @package Kurl\Silex\Provider\Tests
 * @coversDefaultClass Kurl\Silex\Provider\DoctrineMigrationsProvider
 */
class DoctrineMigrationsProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers ::register
     * @covers ::boot
     */
    public function testDefaults()
    {
        $app = new Silex();
        $app['db'] = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $console = new Console();

        $app->register(new DoctrineMigrationsProvider($console));

        $this->assertTrue($app->offsetExists('migrations.output_writer'));
        $this->assertInstanceOf(OutputWriter::class, $app['migrations.output_writer']);
        $this->assertNull($app['migrations.namespace']);
        $this->assertNull($app['migrations.directory']);
        $this->assertEquals('Migrations', $app['migrations.name']);
        $this->assertEquals('migration_versions', $app['migrations.table_name']);

        $this->assertFalse($console->has('migrations:execute'));
        $this->assertFalse($console->has('migrations:generate'));
        $this->assertFalse($console->has('migrations:migrate'));
        $this->assertFalse($console->has('migrations:status'));
        $this->assertFalse($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = '\Kurl\Silex\Provider\Tests\Migrations';

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);
        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCount(5, $app['migrations.command_names']);
        $this->assertCount(5, $app['migrations.commands']);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $app->boot();

        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));
    }

    /**
     * @covers ::register
     * @covers ::boot
     */
    public function testDefaultsWithOrm()
    {
        $app = new Silex();
        $app['db'] = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $app['orm.em'] = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();

        $console = new Console();
        $app->register(new DoctrineMigrationsProvider($console));

        $this->assertFalse($console->has('migrations:execute'));
        $this->assertFalse($console->has('migrations:generate'));
        $this->assertFalse($console->has('migrations:migrate'));
        $this->assertFalse($console->has('migrations:status'));
        $this->assertFalse($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));

        $app['migrations.directory'] = __DIR__ . '/Migrations';
        $app['migrations.namespace'] = 'Kurl\Silex\Provider\Tests\Migrations';

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);

        $app->boot();

        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCount(6, $app['migrations.command_names']);
        $this->assertCount(6, $app['migrations.commands']);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
        $this->assertTrue($console->has('migrations:diff'));
    }

    /**
     * @covers ::register
     */
    public function testWithNoConsole()
    {
        $app = new Silex();
        $app['db'] = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

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
        $this->assertCount(5, $app['migrations.command_names']);
        $this->assertCount(5, $app['migrations.commands']);

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
        $app['db'] = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();


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
        $this->assertCount(5, $app['migrations.command_names']);
        $this->assertCount(5, $app['migrations.commands']);

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
        $app['db'] = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

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

        $this->assertFalse($console->has('migrations:execute'));
        $this->assertFalse($console->has('migrations:generate'));
        $this->assertFalse($console->has('migrations:migrate'));
        $this->assertFalse($console->has('migrations:status'));
        $this->assertFalse($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));

        $this->assertInstanceOf(Configuration::class, $app['migrations.configuration']);
        $this->assertTrue(is_array($app['migrations.command_names']));
        $this->assertTrue(is_array($app['migrations.commands']));
        $this->assertCount(5, $app['migrations.command_names']);
        $this->assertCount(5, $app['migrations.commands']);

        foreach ($app['migrations.command_names'] as $commandName) {
            $this->assertTrue(is_string($commandName));
        }

        foreach ($app['migrations.commands'] as $command) {
            $this->assertInstanceOf(Command::class, $command);
        }

        $app->boot();

        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));
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
}
