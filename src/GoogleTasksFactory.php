<?php

namespace BobFridley\GoogleTasks;

use Google_Client;
use Google_Service_Tasks;

class GoogleTasksFactory
{
    public static function createForTaskList($listId): GoogleTasks
    {
        $config = config('laravel-google-tasks');

        $client = new Google_Client();

        $credentials = $client->loadServiceAccountJson(
            $config['client_secret_json'],
            'https://www.googleapis.com/auth/tasks'
        );

        $client->setAssertionCredentials($credentials);

        $service = new Google_Service_Tasks($client);

        return new GoogleTasks($service, $listId);
    }
}
