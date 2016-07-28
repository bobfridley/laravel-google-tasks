<?php

namespace BobFridley\GoogleTasks;

use Illuminate\Support\ServiceProvider;

class GoogleTasksServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-google-tasks.php' => config_path('laravel-google-tasks.php'),
        ], 'config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-google-tasks.php', 'laravel-google-tasks');

        $this->app->bind(GoogleTasks::class, function () {

            $task_id = config('laravel-google-tasks.task_id');

            return GoogleTasksFactory::createForTaskList($task_id);
        });

        $this->app->alias(GoogleTasks::class, 'laravel-google-tasks');
    }
}
