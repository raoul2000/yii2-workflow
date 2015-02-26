<?php

namespace tests\unit\workflow\events;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;

use tests\codeception\unit\models\Item04;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\events\WorkflowEvent;
use raoul2000\workflow\base\WorkflowException;

class EnterWorkflowReducedEventTest extends TestCase
{
	use \Codeception\Specify;

	protected function setup()
	{
		parent::setUp();
		$this->eventsBefore = [];
		$this->eventsAfter = [];

		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
		]);
		Yii::$app->set('eventSequence',[
			'class'=> 'raoul2000\workflow\events\ReducedEventSequence',
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

    public function testOnEnterWorkflowSuccess()
    {
    	$this->model->on(
    		WorkflowEvent::beforeEnterWorkflow('Item04Workflow'),
    		function($event) {
    			$this->eventsBefore[] = $event;
    		}
    	);
    	$this->model->on(
    		WorkflowEvent::afterEnterWorkflow('Item04Workflow'),
    		function($event) {
    			$this->eventsAfter[] = $event;
    		}
    	);

    	verify('event handler handlers have been called', count($this->eventsBefore) == 0 &&   count($this->eventsAfter) == 0)->true();

    	$this->model->enterWorkflow();

    	verify('current status is set',$this->model->hasWorkflowStatus())->true();

    	expect('beforeChangeStatus handler has been called',count($this->eventsBefore))->equals(1);
    	expect('afterChangeStatus handler has been called',count($this->eventsAfter))->equals(1);
    }

    public function testOnEnterWorkflowError()
    {
    	$this->model->on(
    		WorkflowEvent::beforeEnterWorkflow('Item04Workflow'),
    		function($event) {
    			$this->eventsBefore[] = $event;
    			$event->isValid = false;
    		}
    	);
    	$this->model->on(
    		WorkflowEvent::afterEnterWorkflow('Item04Workflow'),
    		function($event) {
    			$this->eventsAfter[] = $event;
    		}
    	);

    	verify('event handler handlers have been called', count($this->eventsBefore) == 0 &&   count($this->eventsAfter) == 0)->true();

    	$this->model->enterWorkflow();

    	verify('current status is not set',$this->model->hasWorkflowStatus())->false();

    	expect('beforeChangeStatus handler has been called',count($this->eventsBefore))->equals(1);
    	expect('afterChangeStatus handler has not been called',count($this->eventsAfter))->equals(0);
    }
}
