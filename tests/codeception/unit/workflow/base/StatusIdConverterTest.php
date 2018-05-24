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
	use \Codeception\Specify;
	use \Codeception\AssertThrows;

	public function testCreateFails()
	{

		$this->specify('a map parameter must be provided', function(){
			$this->assertThrowsWithMessage( 'yii\base\InvalidConfigException' ,'missing map', function() {
				Yii::createObject(['class'=> 'raoul2000\workflow\base\StatusIdConverter']);
			});
		});

		$this->specify(' the map parameter must be an array', function() {
			$this->assertThrowsWithMessage( 'yii\base\InvalidConfigException',  'The map must be an array', function() {
				Yii::createObject(['class'=> 'raoul2000\workflow\base\StatusIdConverter', 'map' => 'string']);
			});
		});

		$this->specify(' the map parameter must be a non empty array', function() {
			$this->assertThrowsWithMessage('yii\base\InvalidConfigException',  'missing map', function() {
				Yii::createObject(['class'=> 'raoul2000\workflow\base\StatusIdConverter', 'map' => [] ]);
			});
		});
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
			$this->assertThrowsWithMessage(
				'yii\base\Exception' ,
				'Conversion to SimpleWorkflow failed : no value found for id = not found',
				function() use ($c) {
					$c->toSimpleWorkflow('not found');
				}
			);
		});


		$this->specify(' an exception is thrown if value is not found', function() use ($c) {
			$this->assertThrowsWithMessage(
				'yii\base\Exception' ,
				'Conversion from SimpleWorkflow failed : no key found for id = not found',
				function() use ($c) {
					$c->toModelAttribute('not found');
				}
			);
		});
	}
}
