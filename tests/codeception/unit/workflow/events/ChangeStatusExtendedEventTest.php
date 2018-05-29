<?php

namespace tests\unit\workflow\events;

use Yii;
use yii\codeception\DbTestCase;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\models\Item04;
use raoul2000\workflow\base\WorkflowException;
use raoul2000\workflow\events\WorkflowEvent;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use yii\base\Exception;

class ChangeStatusExtendedEventTest extends DbTestCase
{
	use \Codeception\Specify;
	public $eventsBefore = [];
	public $eventsAfter = [];

	protected function setup()
	{
		parent::setUp();
		$this->eventsBefore = [];
		$this->eventsAfter = [];

		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\file\WorkflowFileSource',
				'definitionLoader' => [
					'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
					'namespace' => 'tests\codeception\unit\models'
				]
		]);

		Yii::$app->set('eventSequence',[
			'class'=> 'raoul2000\workflow\events\ExtendedEventSequence',
		]);

		$this->model = new Item04();
		$this->model->attachBehavior('workflow', [
			'class' => SimpleWorkflowBehavior::className()
		]);
	}

    protected function tearDown()
    {
    	$this->model->delete();
        parent::tearDown();
    }

    public function testChangeStatusEventOnSaveSuccess()
    {
    	$this->model->on(
    		WorkflowEvent::beforeEnterStatus(),
    		function($event) {
    			$this->eventsBefore[] = $event;
    		}
    	);
    	$this->model->on(
    		WorkflowEvent::afterEnterStatus(),
    		function($event) {
    			$this->eventsAfter[] = $event;
    		}
    	);
    	verify('event handler handlers have been called', count($this->eventsBefore) == 0 &&   count($this->eventsAfter) == 0)->true();

    	$this->model->enterWorkflow();
    	verify('current status is set',$this->model->hasWorkflowStatus())->true();

			// NOTE: Since Yii v2.0.14 it is possible to specify event name as a wildcard pattern
			// This causes handler to be called twice because the ExtendedEventSequence includes events name
			// that contains the '*' character : beforeEnterWorkflow{*}, beforeEnterStatus{*}
			//
    	expect('event handler handlers have been called', count($this->eventsBefore) == 2 &&   count($this->eventsAfter) == 2)->true();

    	$this->model->status = 'Item04Workflow/B';
    	verify('save succeeds',$this->model->save())->true();

    	expect('model has changed to status B',$this->model->getWorkflowStatus()->getId())->equals('Item04Workflow/B');
    	expect('beforeChangeStatus handler has been called',count($this->eventsBefore))->equals(4);
    	expect('afterChangeStatus handler has been called',count($this->eventsAfter))->equals(4);
    }

    public function testChangeStatusEventOnSaveFails()
    {
    	$this->model->on(
    		WorkflowEvent::beforeChangeStatus('Item04Workflow/A', 'Item04Workflow/B'),
    		function($event) {
    			$this->eventsBefore[] = $event;
    			$event->isValid = false;
    		}
    	);
    	$this->model->on(
    		WorkflowEvent::afterChangeStatus('Item04Workflow/A', 'Item04Workflow/B'),
    		function($event) {
    			$this->eventsAfter[] = $event;
    		}
    	);
    	$this->model->enterWorkflow();
    	verify('current status is set',$this->model->hasWorkflowStatus())->true();
    	verify('event handlers have never been called', count($this->eventsBefore) == 0 &&   count($this->eventsAfter) == 0)->true();

    	$this->model->status = 'Item04Workflow/B';
    	verify('save fails',$this->model->save())->false();

    	expect('model has not changed status',$this->model->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	expect('beforeChangeStatus handler has been called',count($this->eventsBefore))->equals(1);
    	expect('afterChangeStatus handler has not been called',count($this->eventsAfter))->equals(0);
    }
}
