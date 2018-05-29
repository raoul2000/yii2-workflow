<?php


namespace tests\unit\workflow\source\file;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item04;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\source\file\WorkflowFileSource;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\source\file\GraphmlLoader;


class GraphmlLoaderTest extends TestCase
{
	use \Codeception\Specify;

	public function testParseFail1()
	{
		$this->specify('convertion fails when no custom property "initialStatusId" is defined',function (){

			$this->expectException(
				'raoul2000\workflow\base\WorkflowException'
			);
			$this->expectExceptionMessage(
				"Missing custom workflow property : 'initialStatusId'"
			);

			$l = new GraphmlLoader();
			$filename = Yii::getAlias('@tests/codeception/unit/models/workflow-01.graphml');
			$l->convert($filename);
		});
	}

	public function testParseFail2()
	{
		$this->specify('convertion fails when no node is defined',function (){

			$this->expectException(
				'raoul2000\workflow\base\WorkflowException'
			);
			$this->expectExceptionMessage(
				"no node could be found in this workflow"
			);

			$l = new GraphmlLoader();
			$filename = Yii::getAlias('@tests/codeception/unit/models/workflow-00.graphml');
			$l->convert($filename);
		});
	}

	public function testParseFail3()
	{
		$this->specify('convertion fails when no edge is defined',function (){

			$this->expectException(
				'raoul2000\workflow\base\WorkflowException'
			);
			$this->expectExceptionMessage(
				"no edge could be found in this workflow"
			);

			$l = new GraphmlLoader();
			$filename = Yii::getAlias('@tests/codeception/unit/models/workflow-03.graphml');
			$l->convert($filename);
		});
	}

	public function testParseFail4()
	{
		$this->specify('convertion fails when more then one workflow (graph) is defined',function (){

			$this->expectException(
				'raoul2000\workflow\base\WorkflowException'
			);
			$this->expectExceptionMessage(
				"more than one workflow found"
			);

			$l = new GraphmlLoader();
			$filename = Yii::getAlias('@tests/codeception/unit/models/workflow-04.graphml');
			$l->convert($filename);
		});
	}
}
