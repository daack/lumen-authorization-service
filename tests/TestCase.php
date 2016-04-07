<?php

use App\User;
use Predis\Client as Redis;

class TestCase extends Laravel\Lumen\Testing\TestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Insert and create a user instance
     *
     * @return App\User
     */
    protected function createUser($attributes = [])
    {
        return factory(App\User::class)->create($attributes);
    }

    /**
     * Mock class
     *
     * @param  string $class
     * @return Mockery
     */
    protected function mock($class)
    {
        $mock = Mockery::mock($class);

        $this->app->instance($class, $mock);

        return $mock;
    }

    /**
     * Redis client
     *
     * @return Predis\Client
     */
    protected function redis()
    {
        $config = config('database.redis.default');

        return new Redis([
            'host' => $config['host'],
            'port' => $config['port'],
        ]);
    }
}
