<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\DbTestCase;
use tests\codeception\unit\models\Item01;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\fixtures\ItemFixture04;

class ChangeStatusTest extends DbTestCase
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

    public function testChangeStatusOnSaveFailed()
    {
    	$item = $this->items('item1');
    	$this->assertTrue($item->workflowStatus->getId() == 'Item04Workflow/B');

    	$this->expectException(
    		'raoul2000\workflow\base\WorkflowException'
    	);
    	$this->expectExceptionMessage(
    		'No status found with id Item04Workflow/Z'
    	);

    	$item->status = 'Item04Workflow/Z';
    	$item->save(false);
    }

    public function testChangeStatusByMethodFailed()
    {
    	$item = $this->items('item1');
    	$this->assertTrue($item->workflowStatus->getId() == 'Item04Workflow/B');

    	$this->expectException(
    		'raoul2000\workflow\base\WorkflowException'
    	);
    	$this->expectExceptionMessage(
    		'No status found with id Item04Workflow/Z'
    	);

		$item->sendToStatus('Item04Workflow/Z');
    }

    public function testChangeStatusOnSaveSuccess()
    {
    	$item = $this->items('item1');
    	$this->specify('success saving model and perform transition',function() use ($item) {

    		$item->status = 'Item04Workflow/C';
    		verify('current status is ok',$item->workflowStatus->getId())->equals('Item04Workflow/B');
    		expect('save returns true',$item->save(false))->equals(true);
    		verify('model status attribute has not been modified',$item->status)->equals('Item04Workflow/C');
    		verify('model current status has not been modified',$item->getWorkflowStatus()->getId())->equals('Item04Workflow/C');
    	});
    }
}
