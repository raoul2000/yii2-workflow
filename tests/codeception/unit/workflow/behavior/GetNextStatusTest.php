<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\DbTestCase;
use tests\codeception\unit\models\Item04;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\fixtures\ItemFixture04;
use tests\codeception\unit\models\Item05;
use raoul2000\workflow\events\WorkflowEvent;

class GetNextStatusTest extends DbTestCase
{
	use \Codeception\Specify;

	public function fixtures()
	{
		return [
			'items' => ItemFixture04::className(),
		];
	}
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

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testGetNextStatusInWorkflow()
    {
    	$item = $this->items('item1');
    	$this->assertTrue($item->workflowStatus->getId() == 'Item04Workflow/B');

    	$this->specify('2 status are returned as next status',function() use ($item) {

    		$n = $item->getNextStatuses();

    		expect('array is returned',is_array($n) )->true();
    		expect('array has 2 items',count($n) )->equals(2);
    		expect('status Item04Workflow/A is returned as index',isset($n['Item04Workflow/A']) )->true();
    		expect('status Item04Workflow/A is returned as Status',$n['Item04Workflow/A']['status']->getId() )->equals('Item04Workflow/A');

    		expect('status Item04Workflow/C is returned as index',isset($n['Item04Workflow/C']) )->true();
    		expect('status Item04Workflow/A is returned as Status',$n['Item04Workflow/C']['status']->getId() )->equals('Item04Workflow/C');
    	});
    }

    public function testGetNextStatusOnEnter()
    {
    	$item = new Item04();

    	$this->assertTrue($item->hasWorkflowStatus() == false);

    	$this->specify('the initial status is returned as next status',function() use ($item) {

    		$n = $item->getNextStatuses();

    		expect('array is returned',is_array($n) )->true();
    		expect('array has 1 items',count($n) )->equals(1);
     		expect('status Item04Workflow/A is returned as index',isset($n['Item04Workflow/A']) )->true();
     		expect('status Item04Workflow/A is returned as Status',$n['Item04Workflow/A']['status']->getId() )->equals('Item04Workflow/A');

     		verify('status returned is the initial status',$item
     			->getWorkflowSource()
     			->getWorkflow('Item04Workflow')
     			->getInitialStatusId() )->equals($n['Item04Workflow/A']['status']->getId());
    	});
    }

    public function testGetNextStatusFails()
    {
    	$item = new Item04();
    	$item->detachBehavior('workflow');
    	$item->attachBehavior('workflowForTest', [
    		'class' => SimpleWorkflowBehavior::className(),
    		'defaultWorkflowId' => 'INVALID_ID'
    	]);

    	$this->specify('getNextStatus throws exception if default workflow Id is invalid',function() use ($item) {
			$this->setExpectedException(
				'raoul2000\workflow\base\WorkflowException',
				"Invalid workflow Id : 'INVALID_ID'"
    		);
    		$item->getNextStatuses();
    	});
    }


    public function testReturnReportWithEventsOnEnterWorkflow()
    {
    	$model = new Item04();
    	$model->on(
    		WorkflowEvent::beforeEnterStatus('Item04Workflow/A'),
    		function($event)  {
    			$event->invalidate('my error message');
    		}
    	);

    	$report = $model->getNextStatuses(false,true);
    	$this->assertCount(1, $report);
    	$this->assertArrayHasKey('Item04Workflow/A', $report);
    	$this->assertInstanceOf('raoul2000\workflow\base\Status', $report['Item04Workflow/A']['status']);

    	$this->assertCount(2, $report['Item04Workflow/A']['event']);

    	$this->assertEquals(
    		[
	            0 => [
	                'name' => 'beforeEnterWorkflow{Item04Workflow}',
	                'success' => null
	            ],
	            1 => [
	                'name' => 'beforeEnterStatus{Item04Workflow/A}',
	                'success' => false,
	                'messages' => [
	                    0 => 'my error message'
	                ]
	            ]
	        ],
			$report['Item04Workflow/A']['event']
    	);
		$this->assertEquals(false, $report['Item04Workflow/A']['isValid']);
    }

    public function testReturnReportWithValidation()
    {
    	// prepare
    	$model = new Item05();
    	$model->status = 'Item05Workflow/new';
    	verify_that($model->save());

    	// test
    	$report = $model->getNextStatuses(true,false);
    	$this->assertCount(2, $report,' report contains 2 entries as 2 statuses can be reached from "new"');

    	$this->assertArrayHasKey('Item05Workflow/correction', $report,'  a transition exists between "new" and "correction" ');
    	$this->assertTrue($report['Item05Workflow/correction']['isValid'] == false);
    	$this->assertInstanceOf('raoul2000\workflow\base\Status', $report['Item05Workflow/correction']['status']);
    	$this->assertEquals('Item05Workflow/correction', $report['Item05Workflow/correction']['status']->getId());

    	$this->assertEquals(
    		[
	            0 => [
	                'scenario' => 'leave status {Item05Workflow/new}',
	                'success' => null
	            ],
	            1 => [
	                'scenario' => 'from {Item05Workflow/new} to {Item05Workflow/correction}',
	                'success' => false,
	                'errors' => [
	                    'name' => [
	                        0 => 'Name cannot be blank.'
	                    ]
	                ]
	            ],
	            2 => [
	                'scenario' => 'enter status {Item05Workflow/correction}',
	                'success' => null
	            ]
    		],
    		$report['Item05Workflow/correction']['validation']
    	);


    	$this->assertArrayHasKey('Item05Workflow/published',  $report,'  a transition exists between "new" and "published" ');
    	$this->assertTrue($report['Item05Workflow/published']['isValid'] == true);
    	$this->assertInstanceOf('raoul2000\workflow\base\Status', $report['Item05Workflow/published']['status']);
    	$this->assertEquals('Item05Workflow/published', $report['Item05Workflow/published']['status']->getId());

    	$this->assertEquals(
			[
	            0 => [
	                'scenario' => 'leave status {Item05Workflow/new}',
	                'success' => null
	            ],
	            1 => [
	                'scenario' => 'from {Item05Workflow/new} to {Item05Workflow/published}',
	                'success' => null
	            ],
	            2 => [
	                'scenario' => 'enter status {Item05Workflow/published}',
	                'success' => true
	            ]
	        ],
    		$report['Item05Workflow/published']['validation']
    	);
    }

    public function testReturnReportWithNothing()
    {
    	// prepare
    	$model = new Item05();
    	$model->status = 'Item05Workflow/new';
    	verify_that($model->save());

    	// test
    	$report = $model->getNextStatuses();
    	$this->assertCount(2, $report,' report contains 2 entries as 2 statuses can be reached from "new"');
    	$this->assertArrayHasKey('Item05Workflow/correction', $report,'  a transition exists between "new" and "correction" ');
    	$this->assertArrayHasKey('Item05Workflow/published',  $report,'  a transition exists between "new" and "published" ');

    	$this->assertTrue( !isset($report['Item05Workflow/correction']['isValid']));
    	$this->assertTrue( !isset($report['Item05Workflow/correction']['validation']));
    	$this->assertTrue( !isset($report['Item05Workflow/correction']['event']));

    	$this->assertTrue( !isset($report['Item05Workflow/published']['isValid']));
    	$this->assertTrue( !isset($report['Item05Workflow/published']['validation']));
    	$this->assertTrue( !isset($report['Item05Workflow/published']['event']));

    }

    public function testReturnEmptyReport()
    {
    	$model = $this->items('item4'); // status = D
    	$report = $model->getNextStatuses();
    	$this->assertCount(0, $report,' report contains no entries : D does not have any next status ');

    }
}
