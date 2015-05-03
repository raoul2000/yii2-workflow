<?php

namespace tests\unit\workflow\events;

use Yii;
use yii\codeception\DbTestCase;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;
use raoul2000\workflow\events\WorkflowEvent;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use yii\base\Exception;
use tests\codeception\unit\models\Item00;

class StopOnFirstInvalidEventTest extends DbTestCase
{
	use \Codeception\Specify;

	public $item;
	protected function setup()
	{
		parent::setUp();

		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
		]);
	}

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function invalidateEvent1($event) {
    	$event->invalidate('err_message_1');    	
    }
    public function invalidateEvent2($event) {
    	$event->invalidate('err_message_2');
    }
    
    public function testStopOnFirstInvalidEventTrue()
    {
    	// prepare item instance
    	
    	$item = new Item00();
    	$item->attachBehavior('workflowBehavior', [
    		'class' => SimpleWorkflowBehavior::className(),
    		'defaultWorkflowId' => 'Item04Workflow',
    		'propagateErrorsToModel' => true,
    		'stopOnFirstInvalidEvent' => true
    	]);
    	
    	$item->on(WorkflowEvent::beforeLeaveStatus('Item04Workflow/A'),[$this, 'invalidateEvent1']);
    	$item->on(WorkflowEvent::beforeEnterStatus('Item04Workflow/B'),[$this, 'invalidateEvent2']);
    	
		verify('stopOnFirstInvalidEvent is true', $item->stopOnFirstInvalidEvent)->true();
		
    	$item->sendToStatus('Item04Workflow/A');
		
    	verify('item is in status A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	verify('item has no error', $item->hasErrors())->false();
    	
    	// send to B
    	
    	$item->sendToStatus('B');
    	
    	expect('status is still A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	expect('item has error', $item->hasErrors())->true();
    	expect('1 error message is set for attribute "status"', count($item->getErrors('status')) )->equals(1);
    	
    	$errorMessages = $item->getErrors('status');
    	
    	expect('First error message is "err_message_1" ',$errorMessages[0] )->equals("err_message_1");    	
    }

    public function testStopOnFirstInvalidEventFalse()
    {
    	// prepare item instance
    	
    	$item = new Item00();
    	$item->attachBehavior('workflowBehavior', [
    		'class' => SimpleWorkflowBehavior::className(),
    		'defaultWorkflowId' => 'Item04Workflow',
    		'propagateErrorsToModel' => true,
    		'stopOnFirstInvalidEvent' => false
    	]);
    	
    	$item->on(WorkflowEvent::beforeLeaveStatus('Item04Workflow/A'),[$this, 'invalidateEvent1']);
    	$item->on(WorkflowEvent::beforeEnterStatus('Item04Workflow/B'),[$this, 'invalidateEvent2']);
    	
		verify('stopOnFirstInvalidEvent is true', $item->stopOnFirstInvalidEvent)->false();
		
    	$item->sendToStatus('Item04Workflow/A');
		
    	verify('item is in status A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	verify('item has no error', $item->hasErrors())->false();
    	
    	// send to B
    	
    	$item->sendToStatus('B');
    	
    	expect('status is still A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	expect('item has error', $item->hasErrors())->true();
    	expect('1 error message is set for attribute "status"', count($item->getErrors('status')) )->equals(2);
    	
    	$errorMessages = $item->getErrors('status');
    	
    	expect('First error message is "err_message_1" ',$errorMessages[0] )->equals("err_message_1");
    	expect('Second error message is "err_message_2" ',$errorMessages[1] )->equals("err_message_2");    	
    } 
    
}
