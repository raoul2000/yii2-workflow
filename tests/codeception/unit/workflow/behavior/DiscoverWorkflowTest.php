<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;

use tests\codeception\unit\models\Item01;
use tests\codeception\unit\models\Item03;
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
// NOT (YET?) SUPPORTED
//     public function testWorkflowProvidedByModel()
//     {
//     	$this->specify('the provided workflow is accessible', function () {
//     		$model = new Item03();
//     		expect('model should have workflow is set to "Item03Workflow"', $model->getDefaultWorkflowId() == 'Item03Workflow' )->true();
//     		$source = $model->getWorkflowSource();
//     		$w =  $source->getWorkflow('Item03Workflow');
//     		expect('provided workflow definition has been injected in the source component', $w != null)->true();
//     		expect('a status can be retrieved for the provided workflow', $source->getStatus('Item03Workflow/C') != null)->true();
//     	});
//     }
}
