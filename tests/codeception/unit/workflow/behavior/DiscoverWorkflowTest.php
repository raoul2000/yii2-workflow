<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;
use tests\codeception\unit\models\Item01;
use raoul2000\workflow\base\SimpleWorkflowBehavior;

class DiscoverWorkflowTest extends TestCase
{
	use \Codeception\Specify;

    public function testDefaultWorkflowIdCreation()
    {
    	$this->specify('a workflow Id is created if not provided', function () {
    		$model = new Item01();
    		expect('model should have workflow id set to "Item01"', $model->getDefaultWorkflowId() == 'Item01Workflow' )->true();
    	});
    }
    public function testConfiguredWorkflowId()
    {
    	$this->specify('use the configured workflow Id', function () {
    		$model = new Item01();
    		$model->attachBehavior('workflow', [
    			'class' => SimpleWorkflowBehavior::className(),
    			'defaultWorkflowId' => 'myWorkflow'
    		]);
    		expect('model should have workflow id set to "myWorkflow"', $model->getDefaultWorkflowId() == 'myWorkflow' )->true();
    	});
    }
}
