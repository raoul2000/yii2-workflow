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

class DefaultEventTest extends DbTestCase
{
	use \Codeception\Specify;
	
	public $eventsBefore = [];
	public $eventsAfter = [];

	protected function setup()
	{
		parent::setUp();
		$this->eventsBefore = [];
		$this->eventsAfter  = [];    	
		
		Yii::$app->set('workflowSource',[
	    	'class'=> 'raoul2000\workflow\source\file\WorkflowFileSource',
	    	'definitionLoader' => [
		    	'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
		    	'namespace' => 'tests\codeception\unit\models'
    		]
    	]);
	}

    protected function tearDown()
    {
    	$this->model->delete();
        parent::tearDown();
    }

    public function testDefaultEventFired()
    {    	

    	$this->model = new Item04();
    	$this->model->attachBehavior('workflow', [
    			'class' => SimpleWorkflowBehavior::className()
    	]);    	
    	
    	$this->model->on(
    		SimpleWorkflowBehavior::EVENT_BEFORE_CHANGE_STATUS,
    		function($event) {
    			$this->eventsBefore[] = $event;
    		}
    	);

    	$this->model->on(
    			SimpleWorkflowBehavior::EVENT_AFTER_CHANGE_STATUS,
    			function($event) {
    				$this->eventsAfter[] = $event;
    			}
    	);
    	 
    	$this->model->enterWorkflow();
    	verify('current status is set',$this->model->hasWorkflowStatus())->true();
    	
    	
    	expect('event (before) handlers have been called',  count($this->eventsBefore) )->equals(1);
    	expect('event (after) handlers have been called',  count($this->eventsAfter) )->equals(1);
    	
    	expect('start status is null (before)', $this->eventsBefore[0]->getStartStatus())->isEmpty();
    	expect('start status is null (after)', $this->eventsAfter[0]->getStartStatus())->isEmpty();
    
    	
    	expect('transition is null', $this->eventsBefore[0]->getTransition() )->isEmpty();
    	expect('transition is null', $this->eventsAfter[0]->getTransition() )->isEmpty();
    	
    	expect('end status is set', $this->eventsBefore[0]->getEndStatus())->notEmpty();
    	expect('end status is set', $this->eventsAfter[0]->getEndStatus())->notEmpty();
    	
    	expect('end status is workflow initial status',
    		$this->eventsBefore[0]->getEndStatus()->getId())->equals($this->model->getWorkflow()->getInitialStatus()->getId());
    	
    	expect('end status is current model status',
    		$this->model->statusEquals($this->eventsBefore[0]->getEndStatus()))->true();
    }
    
    public function testDefaultEventNotFired1()
    {    
		// Default event is fired even if no eventSequence is used by the behavior
    	$this->model = new Item04();
    	$this->model->attachBehavior('workflow', [
    		'class' => SimpleWorkflowBehavior::className(),
    		'eventSequence' => null
    	]); 
    	
    	
    	$this->model->on(
    		SimpleWorkflowBehavior::EVENT_BEFORE_CHANGE_STATUS,
    		function($event) {
    			$this->eventsBefore[] = $event;
    		}
    	);
    
    	$this->model->on(
    		SimpleWorkflowBehavior::EVENT_AFTER_CHANGE_STATUS,
    		function($event) {
    			$this->eventsAfter[] = $event;
    		}
    	);
    	
    	$this->model->enterWorkflow();
    	verify('current status is set',$this->model->hasWorkflowStatus())->true();
    	 
    	expect('event handlers have been called (before)', count($this->eventsBefore))->equals(1);
    	expect('event handlers have been called (after)', count($this->eventsAfter))->equals(1);
    }
    
    public function testDefaultEventNotFired2()
    {
    	// Default event is NOT fired when fireDefaultEvent is FALSE
    	
    	$this->model = new Item04();
    	$this->model->attachBehavior('workflow', [
    			'class' => SimpleWorkflowBehavior::className(),
    			'eventSequence' => null,
    			'fireDefaultEvent' => false
    	]);
    	 
    	 
    	$this->model->on(
    		SimpleWorkflowBehavior::EVENT_BEFORE_CHANGE_STATUS,
    		function($event) {
    			$this->eventsBefore[] = $event;
    		}
    	);
    
    	$this->model->on(
    		SimpleWorkflowBehavior::EVENT_AFTER_CHANGE_STATUS,
    		function($event) {
    			$this->eventsAfter[] = $event;
    		}
    	);
    	 
    	$this->model->enterWorkflow();
    	verify('current status is set',$this->model->hasWorkflowStatus())->true();
    
    	expect('event handlers have NOT been called (before)', count($this->eventsBefore))->equals(0);
    	expect('event handlers have NOT been called (after)', count($this->eventsAfter))->equals(0);
    }    
}
