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
use Silex\Application as Silex;
use Symfony\Component\Console\Application as Console;

/**
 * Class DoctrineMigrationsProviderTest
 *
 * @package Kurl\Silex\Provider\Tests
 */
class DoctrineMigrationsProviderTest extends PHPUnit_Framework_TestCase
{
    /**
     * Basic sanity checks.
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

        $app->boot();

        $this->assertTrue($console->has('migrations:execute'));
        $this->assertTrue($console->has('migrations:generate'));
        $this->assertTrue($console->has('migrations:migrate'));
        $this->assertTrue($console->has('migrations:status'));
        $this->assertTrue($console->has('migrations:version'));
    }
}
