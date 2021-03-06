<?php

namespace BobFridley\GoogleTasks;

use Carbon\Carbon;
use DateTime;
use Google_Service_Tasks_Task;
use Illuminate\Support\Collection;

class Tasks
{
    /** @var Google_Service_Tasks_Task */
    public $googleTasks;

    /** @var string */
    protected $taskListId;

    /** @var int */
    protected $taskId;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->googleTasks = new Google_Service_Tasks_Task();
    }

    /**
     * [createFromGoogleTasks description]
     * 
     * @param  Google_Service_Tasks_Task $googleTasks [description]
     * @param  [type]                    $taskListId  [description]
     * @return [type]                                 [description]
     */
    public static function createFromGoogleTasks(Google_Service_Tasks_Task $googleTasks, $taskListId)
    {
        $tasks = new static();

        $tasks->googleTasks = $googleTasks;

        $tasks->taskListId = $taskListId;

        return $tasks;
    }

    /**
     * [create description]
     * 
     * @param  array       $properties [description]
     * @param  string|null $tasklist   [description]
     * @return [type]                  [description]
     */
    public static function create(array $properties, string $tasklist = null)
    {
        $task = new static();

        $task->tasklist = static::getGoogleTasks($tasklist)->getTaskId();

        foreach ($properties as $name => $value) {
            $task->$name = $value;
        }

        return $task->save();
    }

    /**
     * [__get description]
     * 
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = $this->getFieldName($name);

        $value = array_get($this->googleTasks, $name);

        if (in_array($name, ['due', 'updated']) && $value) {
            $value = new Carbon($value, 'America/New_York');
            // $value->setTimezone($value->getTimezone());
        }

        return $value;
    }

    /**
     * [__set description]
     * 
     * @param [type] $name  [description]
     * @param [type] $value [description]
     */
    public function __set($name, $value)
    {
        $name = $this->getFieldName($name);

        if (in_array($name, ['due', 'updated'])) {
            $this->setDateProperty($name, $value);

            return;
        }

        array_set($this->googleTasks, $name, $value);
    }

    /**
     * [exists description]
     * 
     * @return [type] [description]
     */
    public function exists(): bool
    {
        return $this->id != '';
    }

    /**
     * @param string      $maxResults
     * @param string|null $pageToken
     * @param string      $fields
     * @param string      $tasklist
     *
     * @return \Illuminate\Support\Collection
     */
    public static function get(
        Carbon  $dueMin = null,
        array $queryParameters = [],
        string $taskListId = null
    ): Collection {
        $googleTaskList = static::getGoogleTaskList($taskListId);
        $googleTasks = $googleTaskList->listTasks($taskListId, $dueMin, $queryParameters);

        return collect($googleTasks)
            ->map(function (Google_Service_Tasks_Task $task) use ($taskListId) {
                return Tasks::createFromGoogleTasks($task, $taskListId);
            })
            ->sortBy(function (Tasks $task) {
                return $task->due;
            })
            ->values();
    }

    /**
     * [find description]
     * 
     * @param string $tasklist
     * @param string $taskId
     *
     * @return \BobFridley\GoogleTasks\Tasks
     */
    public static function find($tasklist, $taskId = null): Tasks
    {
        $service = static::getGoogleTasks($tasklist);

        $googleTasks = $service->getTask($taskId);

        return static::createFromGoogleTasks($googleTasks, $taskId);
    }

    /**
     * [save description]
     * 
     * @return [type] [description]
     */
    public function save(): Tasks
    {
        $method = $this->exists() ? 'updateTask' : 'insertTask';

        $service = $this->getGoogleTasks();

        $googleTasks = $service->$method($this);

        return static::createFromGoogleTasks($googleTasks, $service->getTaskList());
    }

    /**
     * [delete description]
     * 
     * @param string $taskId
     */
    public function delete(string $taskId = null)
    {
        $this->getGoogleTasks($this->taskId)->deleteTask($taskId ?? $this->id);
    }

    /**
     * [getGoogleTaskList description]
     * 
     * @param string $taskListId|null
     *
     * @return \BobFridley\GoogleTasks\GoogleTasks
     */
    protected static function getGoogleTaskList($taskListId = null)
    {
        $taskListId = $taskListId ?? config('laravel-google-tasks.tasklist');

        return GoogleTasksFactory::createForTaskLists($taskListId);
    }

    /**
     * [setDateProperty description]
     * 
     * @param string         $name
     * @param \Carbon\Carbon $date
     */
    protected function setDateProperty(string $name, Carbon $date)
    {
        if (str_is('due', $name)) {
            $this->googleTasks->setDue($date);
        }

        if (str_is('updated', $name)) {
            $this->googleTasks->setUpdated($date);
        }
    }

    /**
     * [getFieldName description]
     * 
     * @param  string $name [description]
     * 
     * @return [type]       [description]
     */
    protected function getFieldName(string $name): string
    {
        return [
            'id'       => 'id',
            'parent'   => 'parent',
            'position' => 'position',
            'title'    => 'title',
            'updated'  => 'updated',
            'notes'    => 'notes',
            'status'   => 'status',
            'due'      => 'due'
        ][$name] ?? $name;
    }
}
