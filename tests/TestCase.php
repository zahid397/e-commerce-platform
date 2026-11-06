<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();
        $connection = $app['config']->get('database.default');
        $config = $app['config']->get('database.connections.'.$connection, []);

        if (
            ! $app->environment('testing')
            || ! empty($config['url'])
            || ! (
                ($connection === 'sqlite' && ($config['database'] ?? null) === ':memory:')
                || ($connection === 'mysql' && ($config['database'] ?? null) === 'ecommerce_platform_test')
            )
        ) {
            throw new RuntimeException('Tests require APP_ENV=testing and SQLite :memory: or the dedicated ecommerce_platform_test MySQL database.');
        }

        return $app;
    }
}
