<?php

namespace tests\unit\workflow\helpers;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item04;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\helpers\WorkflowHelper;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;

class WorkflowHelperTest extends TestCase
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

	protected function tearDown()
	{
		parent::tearDown();
	}

	public function testGetNextStatus()
	{
		$model = new Item04();
		$model->enterWorkflow();

		$ar = WorkflowHelper::getNextStatus($model);
		$this->assertEquals( 'Item04Workflow/B', $ar);
		
	}

	public function testGetAllStatusListData()
	{
		$ar = WorkflowHelper::getAllStatusListData('Item04Workflow', Yii::$app->workflowSource);

		$expected = [
			'Item04Workflow/A' => 'Entry',
			'Item04Workflow/B' => 'Published',
			'Item04Workflow/C' => 'node C',
			'Item04Workflow/D' => 'node D',
		];

		$this->assertEquals(4, count(array_intersect_assoc($expected,$ar)));
	}

	public function testGetNextStatusListData()
	{
		$model = new Item04();
		$model->enterWorkflow();

		$ar = WorkflowHelper::getNextStatusListData($model);

		$expected = [
			'Item04Workflow/A' => 'Entry',
			'Item04Workflow/B' => 'Published',
		];

		$this->assertEquals( 2, count($ar));
		$this->assertEquals(2, count(array_intersect_assoc($expected,$ar)));

		$model->sendTostatus('B');
		$ar = WorkflowHelper::getNextStatusListData($model,false,false,true);
		$this->assertEquals( 3, count($ar));

		$this->assertEquals(3, count(array_intersect_assoc([
			'Item04Workflow/A' => 'Entry',
			'Item04Workflow/B' => 'Published',
			'Item04Workflow/C' => 'node C',
		],$ar)));
	}

	public function testGetStatusDropDownData()
	{
		$model = new Item04();
		$model->enterWorkflow();

		$ar = WorkflowHelper::GetStatusDropDownData($model);
		$listData = WorkflowHelper::getAllStatusListData($model->getWorkflow()->getId(), $model->getWorkflowSource());
		 codecept_debug($ar);
		$expected = [
			'Item04Workflow/A' => 'Entry',
			'Item04Workflow/B' => 'Published',
		];

		$this->assertTrue(is_array($ar));
		$this->assertTrue(isset($ar['items']) && is_array($ar['items']));
		$this->assertTrue(isset($ar['options']) && is_array($ar['options']));
		$this->assertEquals( 2, count($ar));

		foreach ($listData as $status => $label) {
			$this->assertTrue( array_key_exists($status, $ar['items']));
		}
		$this->assertTrue( $ar['options']['Item04Workflow/C']['disabled']);
		$this->assertTrue( $ar['options']['Item04Workflow/D']['disabled']);

	}
}
