<?php

namespace tests\unit\workflow\base;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;

use tests\codeception\unit\models\Item_01;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\Status;

class WorkflowObjectTest extends TestCase
{
	use \Codeception\Specify;

    public function testWorkflowCreationSuccess()
    {
    	$this->specify('create a workflow instance', function () {
    		$w = new Workflow([
				'id'              => 'workflow1',
    			'initialStatusId' => 'draft'
    		]);
    		expect("workflow id should be 'workflow1'", $w->getId() == 'workflow1' )->true();
    		expect("initial status id should be 'draft'", $w->getInitialStatusId() == 'draft' )->true();
    	});
    }

    public function testMissingIdFails()
    {
    	$this->specify('create a workflow instance with no id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing workflow id'
    		);
    		new Workflow([
    			'initialStatusId' => 'draft'
    		]);
    	});
    }

    public function testEmptyIdFails()
    {
    	$this->specify('create a workflow instance with invalid id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing workflow id'
    		);
    		new Workflow([
    			'id' => null,
    			'initialStatusId' => 'draft'
    		]);
    	});
    }

    public function testMissingInitialStatusIdFails()
    {
    	$this->specify('create a workflow instance with no initial status id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing initial status id'
    		);
    		new Workflow([
    			'id' => 'workflow1'
    		]);
    	});
    }
    public function testEmptyInitialStatusIdFails()
    {

    	$this->specify('create a workflow instance with empty initial status id', function () {
    		$this->setExpectedException(
    			'yii\base\InvalidConfigException',
    			'missing initial status id'
    		);
    		new Workflow([
    			'id' => 'workflow1',
    			'initialStatusId' => null
    		]);
    	});
    }
}
