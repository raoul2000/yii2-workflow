<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item01;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use yii\codeception\DbTestCase;
use tests\codeception\unit\fixtures\ItemFixture04;

class AfterFindTest extends DbTestCase
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

    public function testInitStatusOnAfterFind()
    {
		$this->specify('item1 can be read from db', function() {
			$item = $this->items('item1');
			verify('current status is set', $item->getWorkflowStatus()->getId())->equals('Item04Workflow/B');
		});

		$this->specify('item2 cannot be read from db (invalid status)', function() {
			$this->items('item2');
		},['throws' => 'raoul2000\workflow\base\WorkflowException']);

		$this->specify('item3 can be read from db : short name', function() {
			$this->items('item3');
		});
    }
}
