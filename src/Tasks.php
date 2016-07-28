<?php

namespace BobFridley\GoogleTasks;

use Carbon\Carbon;
use DateTime;
use Google_Service_Tasks;
use Google_Service_Tasks_TaskLists;
use Illuminate\Support\Collection;

class Tasks
{
    /** @var Google_Service_Tasks */
    public $googleTasks;

    /** @var datetime */
    public $taskDueDate;

    /** @var string */
    protected $listId;

    /** @var int */
    protected $taskId;

    public static function createFromGoogleTasks(Google_Service_Tasks_TaskLists $googleTasks, $listId)
    {
        $task = new static();

        $task->googleTasks = $googleTasks;

        $task->listId = $listId;

        return $task;
    }

    public static function create(array $properties, string $listId = null)
    {
        $task = new static();

        $task->listId = static::getGoogleTasks($listId)->getTaskId();

        foreach ($properties as $name => $value) {
            $task->$name = $value;
        }

        return $task->save();
    }

    public function __construct()
    {
        $this->googleTasks = new Google_Service_Tasks();
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        $name = $this->getFieldName($name);

        $value = array_get($this->googleTasks, $name);

        if (in_array($name, ['due', 'updated']) && $value) {
            $value = Carbon::createFromFormat(DateTime::RFC3339, $value);
        }

        return $value;
    }

    public function __set($name, $value)
    {
        $name = $this->getFieldName($name);

        if (in_array($name, ['due', 'updated'])) {
            $this->setDateProperty($name, $value);

            return;
        }

        array_set($this->googleTasks, $name, $value);
    }

    public function exists(): bool
    {
        return $this->id != '';
    }

    public function status(): string
    {
        return $this->googleTasks['status']);
    }

    public function notes(): string
    {
        return $this->googleTasks['notes']);
    }

    public function title(): string
    {
        return $this->googleTasks['title']);
    }

    public function updated(): string
    {
        return $this->googleTasks['updated']);
    }

    /**
     * @param \Carbon\Carbon|null $dueMin
     * @param array               $queryParameters
     * @param string|null         $listId
     *
     * @return \Illuminate\Support\Collection
     */
    public static function get(
        Carbon $dueMin = null
        array $queryParameters = [],
        string $listId = null
    ): Collection {
        $googleList = static::getGoogleTasks($listId);

        $googleTasks = $googleList->listTasks($dueMin, $queryParameters);
//dd($googleTasks);
        return collect($googleTasks)
            ->map(function (Google_Service_Tasks_TaskList $tasks) use ($taskId) {
                return Tasks::createFromGoogleTasks($tasks, $taskId);
            })
            ->sortBy(function (Tasks $tasks) {
                return $tasks->dueMin;
            })
            ->values();
    }

    /**
     * @param string $listId
     * @param string $taskId
     *
     * @return \BobFridley\GoogleTasks\Tasks
     */
    public static function find($listId, $taskId = null): Tasks
    {
        $googleList = static::getGoogleTasks($listId);

        $googleTasks = $googleList->getEvent($eventId);

        return static::createFromGoogleTasks($googleTasks, $taskId);
    }

    public function save(): Tasks
    {
        $method = $this->exists() ? 'updateTask' : 'insertTask';

        $googleList = $this->getGoogleTasks();

        $googleTasks = $googleList->$method($this);

        return static::createFromGoogleTasks($googleTasks, $googleList->getCalendarId());
    }

    /**
     * @param string $taskId
     */
    public function delete(string $taskId = null)
    {
        $this->getGoogleTasks($this->taskId)->deleteTask($taskId ?? $this->id);
    }

    /**
     * @param string $taskId
     *
     * @return \BobFridley\GoogleTasks\GoogleTasks
     */
    protected static function getGoogleTasks($listId = null)
    {
        $listId = $listId ?? config('laravel-google-tasks.list_id');

        return GoogleTasksFactory::createForTaskList($listId);
    }

    /**
     * @param string         $name
     * @param \Carbon\Carbon $date
     */
    protected function setDateProperty(string $name, Carbon $date)
    {

        if (in_array($name, ['dueMin', 'dueMax'])) {
            $this->taskDueDate->setDateTime($date->format(DateTime::RFC3339));
        }
    }

    protected function getFieldName(string $name): string
    {
        return [
            'title' => 'title',
            'updated' => 'updated',
            'notes' => 'notes',
            'status' => 'status',
            'due' => 'due',
        ][$name] ?? $name;
    }
}
