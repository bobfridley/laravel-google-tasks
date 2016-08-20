<?php

namespace BobFridley\GoogleTasks;

use Google_Client;
use Google_Service_Tasks;
use Google_Auth_AssertionCredentials;

class GoogleTasksFactory
{
    protected $requestedScopes = array();

    /**
     * [createForTaskLists description]
     * 
     * @param  string $taskListId [description]
     * 
     * @return [type]             [description]
     */
    public static function createForTaskLists($taskListId): GoogleTasks
    {
        $requestedScopes = [
            'https://www.googleapis.com/auth/tasks'
        ];

        $config = config('laravel-google-tasks');

        $client_email = $config['client_email'];
        $private_key = file_get_contents($config['private_key']);
        $scopes = $requestedScopes;
        $user_to_impersonate = $config['user_to_impersonate'];

        $credentials = new Google_Auth_AssertionCredentials(
            $client_email,
            $scopes,
            $private_key,
            'notasecret',
            'http://oauth.net/grant_type/jwt/1.0/bearer',
            $user_to_impersonate
        );

        $client = new Google_Client();
        $client->setAssertionCredentials($credentials);
        
        if ($client->getAuth()->isAccessTokenExpired()) {
            $client->getAuth()->refreshTokenWithAssertion();
        }

        $taskService = new Google_Service_Tasks($client);

        return new GoogleTasks($taskService, $taskListId);
    }
}
