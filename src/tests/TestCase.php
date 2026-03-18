<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    /**
     * Create the application for tests with an isolated MariaDB database.
     */
    public function createApplication(): Application
    {
        /** @var Application $app */
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mariadb');
        $app['config']->set('database.connections.mariadb.host', 'db');
        $app['config']->set('database.connections.mariadb.port', '3306');
        $app['config']->set('database.connections.mariadb.database', 'voidforge_test');
        $app['config']->set('database.connections.mariadb.username', 'voidforge');
        $app['config']->set('database.connections.mariadb.password', 'secret');
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');

        DB::purge('mariadb');
        DB::disconnect('mariadb');
        DB::disconnect('mysql');

        return $app;
    }
}
