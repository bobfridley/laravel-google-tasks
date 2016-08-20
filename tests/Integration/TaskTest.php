<?php

namespace BobFridley\GoogleTasks\Test;

use Carbon\Carbon;
use DateTime;
use Mockery;
use BobFridley\GoogleTasks\Test\Integration\TestCase;
use BobFridley\GoogleTasks\Tasks;

class EventTest extends TestCase
{
    /** @var \BobFridley\GoogleTasks\Tasks|Mockery\Mock */
    protected $task;

    public function setUp()
    {
        parent::setUp();

        $this->task = new Tasks();
    }

    /** @test */
    public function it_can_set_a_min_due_date()
    {
        $now = Carbon::now();

        $this->task->minDue = $now;

        $this->assertEquals($now->startOfDay()->format('Y-m-d'), $this->task->googleTasks['minDue']);

        $this->assertEquals($now, $this->task->minDue);
    }

    /** @test */
    public function it_can_set_a_max_due_date()
    {
        $now = Carbon::now();

        $this->task->maxDue = $now;

        $this->assertEquals($now->format('Y-m-d'), $this->task->googleTasks['maxDue']);
    }

    /** @test */
    public function it_can_set_a_title()
    {
        $this->task->title = 'testtitle';

        $this->assertEquals('testtitle', $this->task->googleTask['summary']);
    }

}