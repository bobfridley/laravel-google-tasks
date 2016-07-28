<?php

namespace BobFridley\GoogleTask;

use Illuminate\Support\Facades\Facade;

/**
 * @see \BobFridley\GoogleTasks\GoogleTasks
 */
class GoogleTasksFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'laravel-google-tasks';
    }
}
