<?php

namespace BobFridley\GoogleTasks\Test\Integration;

use Orchestra\Testbench\TestCase as Orchestra;
use BobFridley\GoogleTasks\GoogleTasksServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @var string */
    protected $taskListId;

    public function setUp()
    {
        $this->taskListId = 'abc123';

        parent::setUp();
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            GoogleTasksServiceProvider::class,
        ];
    }

    /**
     * @param \Illuminate\Foundation\Application $app
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('laravel-google-tasks.client_email', 'sqlite');
    }
}