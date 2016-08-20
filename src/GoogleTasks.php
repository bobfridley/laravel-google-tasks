<?php

namespace BobFridley\GoogleTasks;

use Carbon\Carbon;
use DateTime;
use Google_Service_Tasks;

class GoogleTasks
{
    /** @var \Google_Service_Tasks */
    protected $taskService;

    /** @var string */
    protected $taskListId;

    /** @var string */
    protected $taskId;

    /**
     * [__construct description]
     * 
     * @param Google_Service_Tasks $taskService
     * @param string               $taskListId
     */
    public function __construct(Google_Service_Tasks $taskService, $taskListId)
    {
        $this->taskService = $taskService;

        $this->taskListId = $taskListId;
    }

    /**
     * [getTaskListId description]
     * 
     * @return [type] [description]
     */
    public function getTaskListId(): string
    {
        return $this->taskListId;
    }

    /**
     * [getTaskId description]
     * 
     * @return [type] [description]
     */
    public function getTaskId(): string
    {
        return $this->taskId;
    }

    /**
     * Get all task lists
     *
     * @param string $maxResults  Maximum number of task lists returned on one page. Optional. The default is 100.
     * @param string $pageToken   Token specifying the result page to return. Optional.
     * @param string $fields      Which fields to include in a partial response.
     * 
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasklists/list
     *
     * @return array
     */
    public function listTasklists(
        array $queryParameters = []
    ): array {
        $parameters = [
            'maxResults' => 100,
        ];

        $parameters = array_merge($parameters, $queryParameters);

        return $this
            ->taskService
            ->tasklists
            ->listTasklists($parameters)
            ->getItems();
    }

    /**
     * Get task from specified list
     *
     * @param string               $taskListId      Task list identifier.
     * @param \Carbon\Carbon|null  $completedMax    Upper bound for a task's completion date (as a RFC 3339 timestamp) to filter by. Optional.
     * @param \Carbon\Carbon|null  $completedMin    Lower bound for a task's completion date (as a RFC 3339 timestamp) to filter by. Optional.
     * @param \Carbon\Carbon|null  $dueMax          Upper bound for a task's due date (as a RFC 3339 timestamp) to filter by. Optional.
     * @param \Carbon\Carbon|null  $dueMin          Lower bound for a task's due date (as a RFC 3339 timestamp) to filter by. Optional.
     * @param string               $maxResults      Maximum number of task lists returned on one page. Optional. The default is 100.
     * @param string               $pageToken       Token specifying the result page to return. Optional.
     * @param boolean              $showCompleted   Flag indicating whether completed tasks are returned in the result. Optional. The default is True.
     * @param boolean              $showDeleted     Flag indicating whether deleted tasks are returned in the result. Optional. The default is False.
     * @param boolean              $showHidden      Flag indicating whether hidden tasks are returned in the result. Optional. The default is False.
     * @param \Carbon\Carbon|null  $updatedMin      Lower bound for a task's last modification time (as a RFC 3339 timestamp) to filter by. Optional.
     * @param string               $fields          Selector specifying which fields to include in a partial response.
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/list
     *
     * @return array
     */
    public function listTasks(
        string $taskListId,
        Carbon $dueMin = null,
        array $queryParameters = []
    ): array {
        $parameters = [
            'showCompleted' => false,
            'showDeleted'   => true,
            'showHidden'    => true,
        ];

        if (is_null($dueMin)) {
            $dueMin = Carbon::now()->startOfDay();
        }

        $parameters['dueMin'] = $dueMin->format(DateTime::RFC3339);

        $parameters = array_merge($parameters, $queryParameters);

        return $this
            ->taskService
            ->tasks
            ->listTasks($taskListId, $parameters)
            ->getItems();
    }

    /**
     * Get a single task.
     *
     * @param string $taskId
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/get
     *
     * @return \Google_Service_Tasks
     */
    public function getTask(string $taskId): Google_Service_Tasks
    {
        return $this->service->tasks->get($this->tasklist, $taskId);
    }

    /**
     * Insert a task.
     *
     * @param \BobFridley\GoogleTasks\Tasks|Google_Service_Tasks $task
     *
     * @link https://developers.google.com/google-apps/tasks/v1/reference/tasks/insert
     *
     * @return \Google_Service_Tasks
     */
    public function insertTask($task): Google_Service_Tasks
    {
        if ($task instanceof Tasks) {
            $task = $task->googleTasks;
        }

        return $this->service->tasks->insert($this->tasklist, $task);
    }

    /**
     * Update a task
     *
     * @param \BobFridley\GoogleTasks\Tasks|Google_Service_Tasks $task
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

        return $this->service->tasks->update($this->tasklist, $task->id, $task);
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

        $this->service->tasks->delete($this->tasklist, $taskId);
    }

    /**
     * [getService description]
     * 
     * @return [type] [description]
     */
    public function getService(): Google_Service_Tasks
    {
        return $this->taskService;
    }
}
