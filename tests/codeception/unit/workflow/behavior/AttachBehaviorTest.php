<?php

namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item01;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;

class AttachBehaviorTest extends TestCase
{
	use \Codeception\Specify;


    public function testAttachCorrect()
    {
    	$model = new Item01();

    	$this->specify('behavior can be attached to ActiveRecord', function () use ($model) {
    		$behaviors = $model->behaviors();
    		expect('model should have the "workflow" behavior attached', isset($behaviors['workflow']) )->true();
    		expect('model has a SimpleWorkflowBehavior attached', SimpleWorkflowBehavior::isAttachedTo($model) )->true();
    	});
    }

    public function testAttachFails1()
    {
    	$this->specify('behavior cannot be attached to a non-ActiveRecord object', function () {
    		$model = Yii::createObject("yii\base\Component",[]);
    		$model->attachBehavior('workflow', SimpleWorkflowBehavior::className());
    	},['throws' => 'yii\base\InvalidConfigException']);
    }

    public function testAttachFails2()
    {
    	$this->specify('the status attribute cannot be empty', function () {
    		$model = new Item01();
    		expect('model has a SimpleWorkflowBehavior attached', SimpleWorkflowBehavior::isAttachedTo($model) )->true();
    		$model->detachBehavior('workflow');
    		expect('model has a NO SimpleWorkflowBehavior attached', SimpleWorkflowBehavior::isAttachedTo($model) )->false();
    		$model->attachBehavior('workflow', [ 'class' =>  SimpleWorkflowBehavior::className(), 'statusAttribute' => '' ]);
    	},['throws' => 'yii\base\InvalidConfigException']);
    }

    public function testAttachFails3()
    {
    	$this->specify('the status attribute must exist in the owner model', function () {
    		$model = new Item01();
    		$model->detachBehavior('workflow');
    		$model->attachBehavior('workflow', [ 'class' =>  SimpleWorkflowBehavior::className(), 'statusAttribute' => 'not_found' ]);
    	},['throws' => 'yii\base\InvalidConfigException']);
    }
}
