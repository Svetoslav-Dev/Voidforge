<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Routing\Middleware\ThrottleRequestsWithRedis;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ThrottleRequests::class,
            ThrottleRequestsWithRedis::class,
        ]);
    }

    /**
     * Create the application for tests with an isolated MariaDB database.
     */
    public function createApplication(): Application
    {
        /** @var Application $app */
        $app = parent::createApplication();

        $app['config']->set('database.default', env('DB_CONNECTION', 'mariadb'));
        $app['config']->set('database.connections.mariadb.host', env('DB_HOST', '127.0.0.1'));
        $app['config']->set('database.connections.mariadb.port', env('DB_PORT', '3306'));
        $app['config']->set('database.connections.mariadb.database', env('DB_DATABASE', 'voidforge_test'));
        $app['config']->set('database.connections.mariadb.username', env('DB_USERNAME', 'voidforge'));
        $app['config']->set('database.connections.mariadb.password', env('DB_PASSWORD', 'secret'));
        $app['config']->set('session.driver', 'array');
        $app['config']->set('cache.default', 'array');
        $app['config']->set('queue.default', 'sync');

        DB::purge('mariadb');
        DB::disconnect('mariadb');
        DB::disconnect('mysql');

        return $app;
    }
}
