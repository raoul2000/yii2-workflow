<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item00;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use yii\codeception\DbTestCase;

class AutoInsertTest extends DbTestCase
{
	use \Codeception\Specify;

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

    public function testAutoInsertTRUE()
    {
    	$this->specify('autoInsert True : insert the model in default workflow', function() {
	    	$o = new Item00();
	    	$o->attachBehavior('workflow', [
	    		'class' =>  SimpleWorkflowBehavior::className(), 
	    		'autoInsert' => true,
	    		'defaultWorkflowId' => 'Item04Workflow'
	    	]);
	    	
	    	expect('model as status',
	    		$o->hasWorkflowStatus()
	    	)->true();
	    	
	    	expect('model status is Item04Workflow/A',
	    		$o->getWorkflowStatus()->getId()
	    	)->equals('Item04Workflow/A');
	    	
	    	expect('model status is initial status',
	    		$o->statusEquals($o->getWorkflow()->getInitialStatusId())
	    	)->true();
	    	
    	});
    	
    	$this->specify('autoInsert True : no update if status already set', function() {
    		$o = new Item00();
    		$o->status = 'Item05Workflow/new';
    		$o->attachBehavior('workflow', [
    			'class' =>  SimpleWorkflowBehavior::className(),
    			'autoInsert' => true,
    			'defaultWorkflowId' => 'Item04Workflow'
    		]);
    		
    		expect('model as status',
    			$o->hasWorkflowStatus()
    		)->true();
    		
    		expect('model status is NOT Item04Workflow/A',
    			$o->getWorkflowStatus()->getId()
    		)->notEquals('Item04Workflow/A');
    		
    		expect('model status is Item05Workflow/new',
    			$o->getWorkflowStatus()->getId()
    		)->equals('Item05Workflow/new');
    		
    		expect('model status is initial status',
    			$o->statusEquals($o->getWorkflow()->getInitialStatusId())
    		)->true();
    	});    	
    }
    
    public function testAutoInsertSTATUS()
    {
    	$this->specify('autoInsert Status : insert the model in provided workflow', function() {
    		$o = new Item00();
    		$o->attachBehavior('workflow', [
    			'class' =>  SimpleWorkflowBehavior::className(),
    			'autoInsert' => 'Item05Workflow',
    			'defaultWorkflowId' => 'Item04Workflow'
    		]);
    
    		expect('model as status',
    			$o->hasWorkflowStatus()
    		)->true();
    		
    		expect('model status is Item05Workflow/new',
    			$o->getWorkflowStatus()->getId()
    		)->equals('Item05Workflow/new');
    		
    		expect('model status is initial status',
    			$o->statusEquals($o->getWorkflow()->getInitialStatusId())
    		)->true();
    
    	});
    	
    	$this->specify('autoInsert Status : no update if status already set', function() {
    		$o = new Item00();
    		$o->status = 'Item05Workflow/new';
    		$o->attachBehavior('workflow', [
    			'class' =>  SimpleWorkflowBehavior::className(),
    			'autoInsert' => 'Item04Workflow',
    			'defaultWorkflowId' => 'Item04Workflow'
    		]);
    	
    		expect('model as status',
    			$o->hasWorkflowStatus()
    		)->true();
    		
    		expect('model status is NOT Item04Workflow/A',
    			$o->getWorkflowStatus()->getId()
    		)->notEquals('Item04Workflow/A');
    		
    		expect('model status is Item05Workflow/new',
    			$o->getWorkflowStatus()->getId()
    		)->equals('Item05Workflow/new');
    		
    		expect('model status is initial status',
    			$o->statusEquals($o->getWorkflow()->getInitialStatusId())
    		)->true();
    	});    	
    }    
    public function testAutoInsertDEFAULT()
    {
    	$this->specify('autoInsert Status : insert the model in provided workflow', function() {
    		$o = new Item00();
    		$o->attachBehavior('workflow', [
    			'class' =>  SimpleWorkflowBehavior::className(),
    			'defaultWorkflowId' => 'Item04Workflow'
    		]);
    
    		expect('model as status',
    			$o->hasWorkflowStatus()
    		)->false();
    	});
    }    
    /**
	 * @expectedException raoul2000\workflow\base\WorkflowException
	 * @expectedExceptionMessage failed to load workflow definition : Class tests\codeception\unit\models\NOTFOUND does not exist
	 */	
    public function testAutoInsertFails1()
    {
    	$o = new Item00();
    	$o->attachBehavior('workflow', [
    		'class' =>  SimpleWorkflowBehavior::className(),
    		'autoInsert' => true,
    		'defaultWorkflowId' => 'NOTFOUND'
    	]);
    }   
    /**
     * @expectedException raoul2000\workflow\base\WorkflowException
     * @expectedExceptionMessage failed to load workflow definition : Class tests\codeception\unit\models\NOTFOUND does not exist
     */
    public function testAutoInsertFails2()
    {
    	$o = new Item00();
    	$o->attachBehavior('workflow', [
    		'class' =>  SimpleWorkflowBehavior::className(),
    		'autoInsert' => 'NOTFOUND',
    		'defaultWorkflowId' => 'Item04Workflow'
    	]);
    }     
}
