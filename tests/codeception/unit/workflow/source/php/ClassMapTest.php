<?php

namespace tests\unit\workflow\source\php;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item_04;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\source\php\WorkflowPhpSource;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;


class ClassMapTest extends TestCase
{
	use \Codeception\Specify;

	public function testClassMapStatus()
	{
		$this->specify('Replace default status class with custom one',function (){

			$src = new WorkflowPhpSource([
				'namespace' =>'tests\codeception\unit\models',
				'classMap' =>  [
					WorkflowPhpSource::TYPE_STATUS     => 'tests\codeception\unit\models\MyStatus',
				]
			]);

			verify($src->getClassMapByType(WorkflowPhpSource::TYPE_WORKFLOW))->equals(	'raoul2000\workflow\base\Workflow'  );
			verify($src->getClassMapByType(WorkflowPhpSource::TYPE_STATUS))->equals(	'tests\codeception\unit\models\MyStatus'  );
			verify($src->getClassMapByType(WorkflowPhpSource::TYPE_TRANSITION))->equals('raoul2000\workflow\base\Transition');

			$status = $src->getStatus('Item_04Workflow/A');

			expect(get_class($status))->equals('tests\codeception\unit\models\MyStatus');
		});
	}
}
