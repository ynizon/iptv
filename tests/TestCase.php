<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        // RefreshDatabase empties the database: never the real one (this happened once).
        // Checked here, before the traits run
        if ($app['config']->get('database.default') !== 'sqlite' || $app['config']->get('database.connections.sqlite.database') !== ':memory:') {
            throw new RuntimeException('The tests must run on the in-memory database, see phpunit.xml');
        }
        // The category pages are cached in files: not the ones of the application
        $app['config']->set('cache.stores.catalog', ['driver' => 'array']);

        return $app;
    }
}
