<?php
/**
 * Exercises the migrations provider.
 *
 * @author  chris
 * @created 15/12/14 14:37
 */

namespace Kurl\Silex\Provider\Tests;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\OutputWriter;
use Kurl\Silex\Provider\DoctrineMigrationsProvider;
use PHPUnit_Framework_TestCase;
use Silex\Application as Silex;
use Symfony\Component\Console\Application as Console;
use Symfony\Component\Console\Command\Command;

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
        $app['db'] = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();

        $console = new Console();

        $app->register(new DoctrineMigrationsProvider($console));

        $this->assertTrue($app->offsetExists('migrations.output_writer'));
        $this->assertInstanceOf('Doctrine\DBAL\Migrations\OutputWriter', $app['migrations.output_writer']);
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
        $app['migrations.namespace'] = 'Kurl\Silex\Provider\Tests\Migrations';

        $app->boot();

        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
        $this->assertFalse($console->has('migrations:diff'));
    }

    /**
     * @covers ::boot
     */
    public function testDefaultsWithOrm()
    {
        $app = new Silex();
        $app['db'] = $this->getMockBuilder('Doctrine\DBAL\Connection')->disableOriginalConstructor()->getMock();
        $app['orm.em'] = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();

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

        $app->boot();

        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
        $this->assertTrue($console->has('migrations:diff'));
    }

    /**
     * Ensures you get a console even if you don't set one.
     */
    public function testOptionalConsoleConstructor()
    {
        $provider = new DoctrineMigrationsProvider();
        $this->assertInstanceOf(Console::class, $provider->getConsole());
    }
}
