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

class InvalidEventTest extends DbTestCase
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

    public function invalidateEvent($event) {
    	$event->invalidate('err_message_1');
    	
    }
    
    public function testPropagateErrorToModel()
    {
    	// prepare item instance
    	
    	$item = new Item00();
    	$item->attachBehavior('workflow', [
    		'class' => SimpleWorkflowBehavior::className(),
    		'defaultWorkflowId' => 'Item04Workflow',
    		'propagateErrorsToModel' => true
    	]);
    	
    	$item->on(WorkflowEvent::beforeEnterStatus('Item04Workflow/B'),[$this, 'invalidateEvent']);    	
    	
    	$item->sendToStatus('Item04Workflow/A');

    	verify('item is in status A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	verify('item has no error', $item->hasErrors())->false();
    	
    	// send to B
    	
    	$item->sendToStatus('B');
    	expect('status is still A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	expect('item has error', $item->hasErrors())->true();
    	expect('error message is set for attribute "status"', count($item->getErrors('status')) )->equals(1);
    	expect('error message is "err_message_1" ', $item->getFirstError('status') )->equals("err_message_1");

    }

    public function testNoPropagateErrorToModel()
    {
    	// prepare item instance
    	 
    	$item = new Item00();
    	$item->attachBehavior('workflow', [
    		'class' => SimpleWorkflowBehavior::className(),
    		'defaultWorkflowId' => 'Item04Workflow',
    		'propagateErrorsToModel' => false
    	]);
    	 
    	$item->on(WorkflowEvent::beforeEnterStatus('Item04Workflow/B'),[$this, 'invalidateEvent']);
    	 
    	$item->sendToStatus('Item04Workflow/A');
    
    	verify('item is in status A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	verify('item has no error', $item->hasErrors())->false();
    	 
    	// send to B
    	 
    	$item->sendToStatus('B');
    	
    	expect('status is still A', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/A');
    	expect('item has no error', $item->hasErrors())->false();
    
    }    
    
}
