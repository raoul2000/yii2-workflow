<?php

namespace tests\unit\workflow\base;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;

use tests\codeception\unit\models\Item01;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\StatusInterface;
use raoul2000\workflow\source\file\WorkflowFileSource;

class WorkflowObjectTest extends TestCase
{
	use \Codeception\Specify;

    public function testWorkflowCreationSuccess()
    {
    	$this->specify('create a workflow instance', function () {
    		$w = new Workflow([
				'id'              => 'workflow1',
    			'initialStatusId' => 'draft'
    		]);
    		expect("workflow id should be 'workflow1'", $w->getId() == 'workflow1' )->true();
    		expect("initial status id should be 'draft'", $w->getInitialStatusId() == 'draft' )->true();
    	});
    }

    public function testMissingIdFails()
    {
    	$this->specify('create a workflow instance with no id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing workflow id'
    		);
    		new Workflow([
    			'initialStatusId' => 'draft'
    		]);
    	});
    }

    public function testEmptyIdFails()
    {
    	$this->specify('create a workflow instance with invalid id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing workflow id'
    		);
    		new Workflow([
    			'id' => null,
    			'initialStatusId' => 'draft'
    		]);
    	});
    }

    public function testMissingInitialStatusIdFails()
    {
    	$this->specify('create a workflow instance with no initial status id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing initial status id'
    		);
    		new Workflow([
    			'id' => 'workflow1'
    		]);
    	});
    }
    public function testEmptyInitialStatusIdFails()
    {
    	$this->specify('create a workflow instance with empty initial status id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing initial status id'
    		);
    		new Workflow([
    			'id' => 'workflow1',
    			'initialStatusId' => null
    		]);
    	});
    }
    
    public function testAccessorFails()
    {
    	// creating a Workflow with 'new' will not allow to use some accessors
    	
    	$w = new Workflow([
    		'id' => 'wid',
    		'initialStatusId' => 'A'
    	]);
    	
    	$this->specify('fails to get initial status if no source component is available', function () use ($w) {
    		$this->setExpectedException(
    			'raoul2000\workflow\base\WorkflowException',
    			'no workflow source component available'
    		);
    		$w->getInitialStatus();
    	});    	
    	
    	$this->specify('fails to get all statues if no source component is available', function () use ($w) {
    		$this->setExpectedException(
    			'raoul2000\workflow\base\WorkflowException',
    			'no workflow source component available'
    		);
    		$w->getAllStatuses();
    	});
    }
        
    public function testWorkflowAccessorSuccess()
    {
    	$src = new WorkflowFileSource();
    	$src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A',
    		'status' => [
    			'A' => [
    				'label' => 'label A',
    				'transition' => ['B','C']
    			],
    			'B' => [],
    			'C' => []
    		]
    	]);
    	$w = $src->getWorkflow('wid');
    	verify_that($w != null);
    		
    	$this->specify('initial status can be obtained through workflow',function() use($w) {
    
    		expect_that($w->getInitialStatus() instanceof StatusInterface);
    		expect_that($w->getInitialStatus()->getId() == $w->getInitialStatusId());

    	});    	
    	
    	$this->specify('all statuses can be obtained through workflow',function() use($w) {
    
    		$statuses = $w->getAllStatuses();
    		
    		expect_that(is_array($statuses) && count($statuses) == 3);
    		expect_that($statuses['wid/A'] instanceof StatusInterface );
    		expect_that($statuses['wid/B'] instanceof StatusInterface );
    		expect_that($statuses['wid/C'] instanceof StatusInterface );    		
    	});
    }        
}
