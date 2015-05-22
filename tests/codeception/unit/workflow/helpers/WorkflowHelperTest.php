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
}
