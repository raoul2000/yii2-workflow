<?php

namespace tests\unit\workflow\source\php;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item01;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\source\php\WorkflowPhpSource;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\base\Workflow;


class TransitionTest extends TestCase
{
	use \Codeception\Specify;

	public $src;

	protected function setUp()
	{
		parent::setUp();
		$this->src = new WorkflowPhpSource();
	}

	/**
	 *
	 */
	public function testTransitionNotFound()
	{
		$this->src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
			'status' => [
				'A' => []
			]
		]);

		$this->specify('empty transition set', function () {
			$tr = $this->src->getTransitions('wid/A');
			verify('empty transition set is returned', count($tr) )->equals(0);
		});
	}

    public function testTransitionSuccess()
    {
    	$this->src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
    		'status' => [
				'A' => [
					'transition' => ['B' => []]
    			],
    			'B' => []
    		]
    	]);

    	$this->specify('end and start status can be obtained',function() {
			$tr = $this->src->getTransitions('wid/A');

			verify('empty transition set is returned', count($tr) )->equals(1);

			reset($tr);
			//$startId = key($tr);
			$transition = current($tr);

			verify('transition is a Transition', get_class($transition))->equals('raoul2000\workflow\base\Transition');

			verify('start status is a Status instance',get_class($transition->getStartStatus()) )->equals('raoul2000\workflow\base\Status');
			verify('start status is A', $transition->getStartStatus()->getId())->equals('wid/A');

			verify('end status is a Status instance',get_class($transition->getStartStatus()) )->equals('raoul2000\workflow\base\Status');
			verify('end status is B', $transition->getEndStatus()->getId())->equals('wid/B');
    	});
    }
    public function testTransitionCached()
    {
    	$this->src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
    		'status' => [
				'A' => [
					'transition' => ['B' => []]
    			],
    			'B' => []
    		]
    	]);
    	$tr = $this->src->getTransitions('wid/A');
    	reset($tr);
    	//$startId = key($tr);
    	$transition1 = current($tr);

    	$tr=null;
    	$tr = $this->src->getTransitions('wid/A');
    	reset($tr);
    	//$startId = key($tr);
    	$transition2 = current($tr);

    	$this->assertTrue(spl_object_hash($transition1) == spl_object_hash($transition2));
    }
}
