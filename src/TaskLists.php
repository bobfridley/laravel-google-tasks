<?php

namespace BobFridley\GoogleTasks;

use Carbon\Carbon;
use DateTime;
use Google_Service_Tasks_TaskList;
use Google_Service_Tasks_TaskLists;
use Illuminate\Support\Collection;

class TaskLists
{
    /** @var Google_Service_Tasks_TaskLists */
    public $googleTaskLists;

    /** @var string */
    protected $taskListId;

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->googleTaskLists = new Google_Service_Tasks_TaskLists();
    }

    /**
     * [createFromGoogleTaskLists description]
     * 
     * @param  Google_Service_Tasks_TaskList $googleTaskLists [description]
     * @return [type]                                         [description]
     */
    public static function createFromGoogleTaskLists(Google_Service_Tasks_TaskList $googleTaskLists)
    {
        $tasklists = new static();

        $tasklists->googleTaskLists = $googleTaskLists;

        return $tasklists;
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
     *  * [__get description]
     *  
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = $this->getFieldName($name);

        $value = array_get($this->googleTaskLists, $name);

        if (in_array($name, ['updated']) && $value) {
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

        if (in_array($name, ['updated'])) {
            $this->setDateProperty($name, $value);

            return;
        }

        array_set($this->googleTaskLists, $name, $value);
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
     * [get description]
     * 
     * @param string      $maxResults
     * @param string|null $pageToken
     * @param string      $fields
     * @param string      $tasklist
     *
     * @return \Illuminate\Support\Collection
     */
    public static function get(
    ): Collection {
        $googleTaskLists = static::getGoogleTaskLists();
        $googleLists = $googleTaskLists->listTaskLists();

        return collect($googleLists)
            ->map(function (Google_Service_Tasks_TaskList $tasklist) {
                return TaskLists::createFromGoogleTaskLists($tasklist);
            })
            ->sortBy(function (TaskLists $tasklist) {
                return $tasklist->title;
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
    public static function find($tasklist, $taskId = null): TaskLists
    {
        $service = static::getGoogleTasks($tasklist);

        $googleTasks = $service->getTask($taskId);

        return static::createFromGoogleTaskLists($googleTasks, $taskId);
    }

    /**
     * [save description]
     * 
     * @return [type] [description]
     */
    public function save(): TaskLists
    {
        $method = $this->exists() ? 'updateTask' : 'insertTask';

        $service = $this->getGoogleTasks();

        $googleTasks = $service->$method($this);

        return static::createFromGoogleTaskLists($googleTasks, $service->getTaskList());
    }

    /**
     * [delete description]
     * 
     * @param string $taskListId
     */
    public function delete(string $taskListId = null)
    {
        $this->getGoogleTasks($this->taskId)->deleteTask($taskId ?? $this->id);
    }

    /**
     * @return \BobFridley\GoogleTasks\GoogleTasks
     */
    protected static function getGoogleTaskLists($taskListId = null)
    {
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
        if (str_is('updated', $name)) {
            $this->googleTasks->setUpdated($date);
        }
    }

    /**
     * [getFieldName description]
     * 
     * @param  string $name [description]
     * @return [type]       [description]
     */
    protected function getFieldName(string $name): string
    {
        return [
            'kind'     => 'kind',
            'id'       => 'id',
            'title'    => 'title',
            'updated'  => 'updated',
            'selfLink' => 'selfLink',
        ][$name] ?? $name;
    }
}
