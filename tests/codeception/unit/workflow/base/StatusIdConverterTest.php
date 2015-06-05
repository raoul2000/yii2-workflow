<?php
namespace tests\unit\workflow\base;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;
use tests\codeception\unit\models\Item01;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\StatusIdConverter;

class StatusIdConverterTest extends TestCase
{
	use\Codeception\Specify;

	public function testCreateFails()
	{
		$this->specify('a map parameter must be provided', function(){
			Yii::createObject(['class'=> 'raoul2000\workflow\base\StatusIdConverter']);
		},['throws' => 'yii\base\InvalidConfigException']);

		$this->specify(' the map parameter must be an array', function() {
			Yii::createObject(['class'=> 'raoul2000\workflow\base\StatusIdConverter', 'map' => 'string']);
		},['throws' => 'yii\base\InvalidConfigException']);
		
		$this->specify(' the map parameter must be a non empty array', function() {
			Yii::createObject(['class'=> 'raoul2000\workflow\base\StatusIdConverter', 'map' => [] ]);
		},['throws' => 'yii\base\InvalidConfigException']);		
	}

	public function testCreateSuccess()
	{
		$this->specify('a status converter is created successfully', function(){
			Yii::createObject([
				'class'=> 'raoul2000\workflow\base\StatusIdConverter',
				'map' => [
					'Post/ready' => '1',
					'Post/draft' => '2',
					'Post/deleted' => '3',
					StatusIdConverter::VALUE_NULL => '0'
				]
			]);
		});	
	}

	public function testConvertionSuccess()
	{
		$c = Yii::createObject([
			'class'=> 'raoul2000\workflow\base\StatusIdConverter',
			'map' => [
				'Post/ready' => '1',
				'Post/draft' => '2',
				'Post/deleted' => '3',
				StatusIdConverter::VALUE_NULL => '0',
				'Post/new' => StatusIdConverter::VALUE_NULL
			]
		]);

		$this->assertEquals('1', $c->toModelAttribute('Post/ready'));
		$this->assertEquals('2', $c->toModelAttribute('Post/draft'));
		$this->assertEquals('3', $c->toModelAttribute('Post/deleted'));
		$this->assertEquals(null, $c->toModelAttribute('Post/new'));
		$this->assertEquals('0', $c->toModelAttribute(null));

		$this->assertEquals('Post/ready', $c->toSimpleWorkflow(1));
		$this->assertEquals('Post/draft', $c->toSimpleWorkflow(2));
		$this->assertEquals('Post/deleted', $c->toSimpleWorkflow(3));
		$this->assertEquals(null, $c->toSimpleWorkflow(0));
		$this->assertEquals('Post/new', $c->toSimpleWorkflow(null));
	}

	public function testConvertionRuntimeMapAssignement()
	{
		$c = Yii::createObject([
			'class'=> 'raoul2000\workflow\base\StatusIdConverter',
			'map' => [
				'Post/ready' => '1',
				'Post/draft' => '2',
				'Post/deleted' => '3',
				StatusIdConverter::VALUE_NULL => '0',
				'Post/new' => StatusIdConverter::VALUE_NULL
			]
		]);
	
		$this->assertEquals('1', $c->toModelAttribute('Post/ready'));
		$this->assertEquals('2', $c->toModelAttribute('Post/draft'));
		$this->assertEquals('3', $c->toModelAttribute('Post/deleted'));
		
		$c->setMap([
			'Post/ready' => '11',
			'Post/draft' => '22',
			'Post/deleted' => '33',			
			StatusIdConverter::VALUE_NULL => '0',
			'Post/new' => StatusIdConverter::VALUE_NULL			
		]);
		$this->assertEquals('11', $c->toModelAttribute('Post/ready'));
		$this->assertEquals('22', $c->toModelAttribute('Post/draft'));
		$this->assertEquals('33', $c->toModelAttribute('Post/deleted'));		
		$this->assertEquals(null, $c->toSimpleWorkflow(0));
		$this->assertEquals('Post/new', $c->toSimpleWorkflow(null));	

	}	
	
	public function testConvertionFails()
	{
		$c = Yii::createObject([
			'class'=> 'raoul2000\workflow\base\StatusIdConverter',
			'map' => [
				'Post/ready' => '1',
			]
		]);

		$this->specify(' an exception is thrown if value is not found', function() use ($c) {
			$c->toSimpleWorkflow('not found');
		},['throws' => 'yii\base\Exception']);

		$this->specify(' an exception is thrown if value is not found', function() use ($c) {
			$c->toModelAttribute('not found');
		},['throws' => 'yii\base\Exception']);
	}
}
