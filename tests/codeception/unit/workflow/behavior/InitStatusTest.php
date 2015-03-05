<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item01;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;

class InitStatusTest extends TestCase
{
	use \Codeception\Specify;

	protected function setup()
	{
		parent::setUp();
		Item01::deleteAll();
		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
		]);

	}

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testInitStatusOnAttachSuccess()
    {
		$this->specify('current status initialization is ok', function() {

			$model = new Item01();
			$model->status = 'Workflow1/A';
			$model->attachBehavior('workflow', [
				'class' => SimpleWorkflowBehavior::className(),
				'defaultWorkflowId' => 'Workflow1'
			]);

			verify('current status is set', $model->getWorkflowStatus() != null)->true();
			verify('current status is set (use attribute notation)', $model->workflowStatus != null)->true();
			verify('current status is Status instance', get_class($model->getWorkflowStatus()))->equals('raoul2000\workflow\base\Status');
		});
    }

    public function testInitStatusOnAttachFails()
    {
    	$this->specify('status initialisation fails when status not found', function(){
    		$model = new Item01();
    		$model->status = 'Workflow1/X';
    		$this->setExpectedException(
    			'raoul2000\workflow\base\WorkflowException', 
    			'No status found with id Workflow1/X'
    		);
    		$model->attachBehavior('workflow', [
    			'class' => SimpleWorkflowBehavior::className(),
    			'defaultWorkflowId' => 'Workflow1'
    		]);
    	});
    }

    public function testSaveModelNoChangeSuccess()
    {
		$this->specify('a model can be saved with status not set', function() {

			$model = new Item01();
			$model->attachBehavior('workflow', [
				'class' => SimpleWorkflowBehavior::className(),
				'defaultWorkflowId' => 'Workflow1'
			]);
			expect('model is saved', $model->save())->true();
		});
    }

    public function testInitStatusAfterFindSuccess()
    {
    	$this->specify('status initialisation when reading model from db (after find)', function(){

    		$model = new Item01();
    		$model->detachBehavior('workflow');
    		$model->id = 1;
    		$model->name = 'name';
    		$model->status = 'Workflow1/B';
    		$model->save(false);

    		$model = Item01::findOne(1);

    		$model->attachBehavior('workflow', [
    			'class' => SimpleWorkflowBehavior::className(),
    			'defaultWorkflowId' => 'Workflow1'
    		]);

    		verify('current model status is "B"',$model->getWorkflowStatus()->getId())->equals('Workflow1/B');
    	});
    }
    
    public function testInitStatusAfterFindFails()
    {
    	$this->specify('status initialisation success when saving model', function(){

    		$model = new Item01();
    		$model->detachBehavior('workflow');
    		$model->id = 1;
    		$model->name = 'name';
    		$model->status = 'Workflow1/X';
    		$model->save(false);

    		$this->setExpectedException(
    			'raoul2000\workflow\base\WorkflowException', 
    			'No status found with id Workflow1/X'
    		);

    		$model = Item01::findOne(1);

    	});
    }
}
