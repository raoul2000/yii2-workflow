<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\DbTestCase;
use tests\codeception\unit\models\Item_01;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\fixtures\ItemFixture_04;
use tests\codeception\unit\models\Item_04;

class EnterWorkflowTest extends DbTestCase
{
	use \Codeception\Specify;

	public function fixtures()
	{
		return [
			'items' => ItemFixture_04::className(),
		];
	}
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

    public function testEnterWorkflowSuccess()
    {
    	$item = new Item_04();

    	$this->specify('model is inserted in the default workflow',function() use ($item) {

    		verify('current status is not set',$item->hasWorkflowStatus())->false();

    		$item->enterWorkflow();
    		verify('current status is set',$item->hasWorkflowStatus())->true();

    		verify('current status is ok',$item->workflowStatus->getId())->equals('Item_04Workflow/A');
    		//verify('current status is the initial status for the current workflow', $item->engine->getInitialStatus($item->getWorkflowId())->getId() )->equals($item->currentStatus->id);

			verify('item can be saved',$item->save())->true();

			$newitem = Item_04::findOne(['id' => $item->id]);
			verify('current status is set',$newitem->hasWorkflowStatus())->true();
			verify('current status is ok',$newitem->workflowStatus->getId())->equals('Item_04Workflow/A');

    	});
    }

    public function testEnterWorkflowFails1()
    {
    	$item = new Item_04();
    	$this->specify('enterWorkflow fails if the model is already in a workflow',function() use ($item) {

    		verify('current status is not set',$item->hasWorkflowStatus())->false();
    		$item->sendToStatus('Item_04Workflow/A');
    		verify('current status is set',$item->hasWorkflowStatus())->true();
			$this->setExpectedException('raoul2000\workflow\base\WorkflowException', 'Model already in a workflow');
			$item->enterWorkflow();
		});
	}

	public function testEnterWorkflowFails2()
	{
		$item = new Item_04();
		$this->specify('enterWorkflow fails if workflow not found for ID',function() use($item) {

    		$this->setExpectedException(
    			'raoul2000\workflow\base\WorkflowException',
    			'failed to load workflow definition : Class tests\codeception\unit\models\INVALIDID does not exist'
			);

    		$item->enterWorkflow('INVALIDID');
    	});
    }
}
