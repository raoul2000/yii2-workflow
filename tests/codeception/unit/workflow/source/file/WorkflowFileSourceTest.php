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

			$this->expectException(
				'yii\base\InvalidConfigException'
			);
			$this->expectExceptionMessage(
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
		$this->specify('Workflow source construct default',function (){

			$src = new WorkflowFileSource();

			expect($src->getClassMapByType(WorkflowFileSource::TYPE_WORKFLOW))->equals(	'raoul2000\workflow\base\Workflow'		);
			expect($src->getClassMapByType(WorkflowFileSource::TYPE_STATUS))->equals(	'raoul2000\workflow\base\Status'		);
			expect($src->getClassMapByType(WorkflowFileSource::TYPE_TRANSITION))->equals('raoul2000\workflow\base\Transition'	);

			expect($src->getDefinitionCache())->equals(null);
			expect($src->getDefinitionLoader())->notNull();
		});



		$this->specify('Workflow source construct with class map',function (){

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



		$this->specify('Workflow source construct with cache',function (){
			// initialized by array
			$src = new WorkflowFileSource([
				'definitionCache' =>  ['class' => 'yii\caching\FileCache']
			]);
			expect_that($src->getDefinitionCache() instanceof yii\caching\FileCache);


			// initialized by component ID
			Yii::$app->set('myCache',['class' => 'yii\caching\FileCache']);
			$src = new WorkflowFileSource([
				'definitionCache' =>  'myCache'
			]);
			expect_that($src->getDefinitionCache() instanceof yii\caching\FileCache);

			// initialized by object
			$cache = Yii::$app->get('myCache');
			Yii::$app->set('myCache',['class' => 'yii\caching\FileCache']);
			$src = new WorkflowFileSource([
				'definitionCache' =>  $cache
			]);
			expect_that($src->getDefinitionCache() instanceof yii\caching\FileCache);

		});
	}
}
