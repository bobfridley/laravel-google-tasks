<?php

namespace BobFridley\GoogleTasks;

use Carbon\Carbon;
use DateTime;
use Google_Service_Tasks;
use Google_Service_Tasks_TaskLists;
use Google_Service_Tasks_TaskList;

class GoogleTasks
{
    /** @var \Google_Service_Tasks */
    protected $tasksService;

    /** @var string */
    protected $listId;

    /** @var string */
    protected $taskId;

    public function __construct(Google_Service_Tasks $tasksService, $listId)
    {
        $this->tasksService = $tasksService;

        $this->listId = $listId;
    }

    public function getListId(): string
    {
        return $this->id;
    }

    public function getTaskId(): string
    {
        return $this->id;
    }

    /**
     * Get task lists
     *
     * @param \Carbon\Carbon $maxResults
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/list
     *
     * @return array
     */
    public function listTasks(
        Carbon $dueMin = null,
        array $queryParameters = []
    ): array {
        $parameters = [
            'maxResults' => 100,
            'showCompleted' => true,
            'showDeleted' => true,
            'showHidden' => true,
        ];

        if (is_null($dueMin)) {
            $dueMin = Carbon::now()->startOfDay();
        }

        $parameters['dueMin'] = $dueMin->format(DateTime::RFC3339);

        $parameters = array_merge($parameters, $queryParameters);

        return $this
            ->tasksService
            ->listTasks($this->listId, $parameters)
            ->getItems();
    }

    /**
     * Get a single task.
     *
     * @param string $taskId
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/get
     *
     * @return \Google_Service_Tasks_TaskList
     */
    public function getTask(string $taskId): Google_Service_Tasks_TaskList
    {
        return $this->tasksService->tasks->get($this->listId, $taskId);
    }

    /**
     * Insert a task.
     *
     * @param \BobFridley\GoogleTasks\Tasks|Google_Service_Tasks $task
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/delete
     *
     * @return \Google_Service_Tasks
     */
    public function insertTask($task): Google_Service_Tasks
    {
        if ($task instanceof Tasks) {
            $task = $task->googleTasks;
        }

        return $this->tasksService->task->insert($this->listId, $task);
    }

    /**
     * Update a task
     *
     * @param \BobFridley\GoogleTasks\Tasks|Google_Service_Tasks $tasks
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/update
     *
     * @return \Google_Service_Tasks
     */
    public function updateTask($task): Google_Service_Tasks
    {
        if ($task instanceof Tasks) {
            $task = $task->googleTasks;
        }

        return $this->tasksService->task->update($this->listId, $task->id, $task);
    }

    /**
     * Delete a task
     *
     * @param string|\BobFridley\GoogleTasks\Tasks $taskId
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/delete
     */
    public function deleteTask($taskId)
    {
        if ($taskId instanceof Tasks) {
            $taskId = $taskId->id;
        }

        $this->tasksService->tasks->delete($this->listId, $taskId);
    }

    public function getService(): Google_Service_Tasks
    {
        return $this->tasksService;
    }
}
