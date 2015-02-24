<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\DbTestCase;
use tests\codeception\unit\models\Item_01;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\fixtures\ItemFixture_04;

class ChangeStatusTest extends DbTestCase
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

    public function testChangeStatusOnSaveFailed()
    {
    	$item = $this->items('item1');
    	$this->assertTrue($item->workflowStatus->getId() == 'Item_04Workflow/B');

    	$this->setExpectedException(
    		'raoul2000\workflow\base\WorkflowException', 'Status not found : Item_04Workflow/Z'
    	);

    	$item->status = 'Item_04Workflow/Z';
    	$item->save(false);
    }

    public function testChangeStatusByMethodFailed()
    {
    	$item = $this->items('item1');
    	$this->assertTrue($item->workflowStatus->getId() == 'Item_04Workflow/B');

    	$this->setExpectedException(
    		'raoul2000\workflow\base\WorkflowException', 'Status not found : Item_04Workflow/Z'
    	);

		$item->sendToStatus('Item_04Workflow/Z');
    }

    public function testChangeStatusOnSaveSuccess()
    {
    	$item = $this->items('item1');
    	$this->specify('success saving model and perform transition',function() use ($item) {

    		$item->status = 'Item_04Workflow/C';
    		verify('current status is ok',$item->workflowStatus->getId())->equals('Item_04Workflow/B');
    		expect('save returns true',$item->save(false))->equals(true);
    		verify('model status attribute has not been modified',$item->status)->equals('Item_04Workflow/C');
    		verify('model current status has not been modified',$item->getWorkflowStatus()->getId())->equals('Item_04Workflow/C');
    	});
    }
}
