<?php

namespace tests\unit\workflow\source\file;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item01;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\source\file\WorkflowFileSource;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;


class WorkflowFileSourceTest extends TestCase
{
	use \Codeception\Specify;


	public function testConstructFails1()
	{
		$this->specify('Workflow source construct fails if classMap is not an array',function (){
	
			$this->setExpectedException(
				'yii\base\InvalidConfigException',
				'Invalid property type : \'classMap\' must be a non-empty array'
			);
	
			new WorkflowFileSource([
				'namespace' =>'a\b\c',
				'classMap' => null
			]);
		});
	}
	
	public function testConstructSuccess()
	{
		$this->specify('Workflow source construct succeeds',function (){

			$src = new WorkflowFileSource([
				'classMap' =>  [
					WorkflowFileSource::TYPE_WORKFLOW   => 'my\namespace\Workflow',
					WorkflowFileSource::TYPE_STATUS     => 'my\namespace\Status',
					WorkflowFileSource::TYPE_TRANSITION => 'my\namespace\Transition'
				]
			]);
			expect($src->getClassMapByType(WorkflowFileSource::TYPE_WORKFLOW))->equals(	'my\namespace\Workflow'		);
			expect($src->getClassMapByType(WorkflowFileSource::TYPE_STATUS))->equals(	'my\namespace\Status'		);
			expect($src->getClassMapByType(WorkflowFileSource::TYPE_TRANSITION))->equals('my\namespace\Transition'	);
		});
	}
}
