<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\fixtures\ItemFixture04;
use tests\codeception\unit\models\Item04;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;

class StatusEqualsTest extends TestCase
{
	use \Codeception\Specify;


	protected function setup()
	{
		parent::setUp();
		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\file\WorkflowFileSource',
			'definitionLoader' => [
				'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
				'namespace' => 'tests\codeception\unit\models'
			]			
		]);
	}
	
    public function testStatusEqualsSuccess()
    {
    	$item = new Item04();
    	
    	expect_that($item->statusEquals());
    	expect_that($item->statusEquals(null));
    	expect_that($item->statusEquals(''));
    	expect_that($item->statusEquals([]));
    	expect_that($item->statusEquals(0));
    	
    	$item->sendToStatus('A');
    	expect_that($item->statusEquals('A'));
    	expect_that($item->statusEquals('Item04Workflow/A'));
    	
    	$itself= $item->getWorkflowStatus();
    	
    	expect_that($item->statusEquals($itself));
    }
    
    
    public function testStatusEqualsFails()
    {    	 
    	$item = new Item04();
    	$item->sendToStatus('A');

    	expect_not($item->statusEquals('B'));
    	expect_not($item->statusEquals('Item04Workflow/B'));
    	expect_not($item->statusEquals('NOTFOUND'));
    	expect_not($item->statusEquals('Item04Workflow/NOTFOUND'));
    	expect_not($item->statusEquals('NOTFOUND/NOTFOUND'));
    	expect_not($item->statusEquals('invalid name'));
    	expect_not($item->statusEquals(''));
    	expect_not($item->statusEquals(null));
    	
    	$statusA = $item->getWorkflowStatus();
    	$item->sendToStatus('B');
    	
    	verify($item->statusEquals('B'));
    	
    	expect_not($item->statusEquals($statusA));
    }    

}
