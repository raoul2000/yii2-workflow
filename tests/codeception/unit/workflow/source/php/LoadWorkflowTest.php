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


class LoadWorkflowTest extends TestCase
{
	use \Codeception\Specify;

	public $src;

	protected function setUp()
	{
		parent::setUp();
		$this->src = new WorkflowPhpSource();
	}


    public function testLoadWorkflowSuccess1()
    {
    	$src = new WorkflowPhpSource();
    	$src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
			'status' => [
				'A' => [
					'label' => 'Entry',
					'transition' => ['B','A']
				],
				'B' => [
					'label' => 'Published',
					'transition' => ['A','C']
				],
				'C' => [
					'label' => 'node C',
					'transition' => ['A','D']
				]
			]
		]);
    	
    	verify($src->getStatus('wid/A'))->notNull();
    	verify($src->getStatus('wid/B'))->notNull();
    	verify($src->getStatus('wid/C'))->notNull();
    	
    	verify(count($src->getTransitions('wid/A')))->equals(2);
    }
    
    public function testLoadWorkflowSuccess2()
    {
    	$src = new WorkflowPhpSource();
    	$src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A',
    		'status' => [
    			'A' => [
    				'label' => 'Entry',
    				'transition' => 'A,B'
    			],
    			'B' => [
    				'label' => 'Published',
    				'transition' => '  A  , B  '
    			],
    		]
    	]);
    	 
    	verify($src->getStatus('wid/A'))->notNull();
    	verify($src->getStatus('wid/B'))->notNull();
    	 
    	verify(count($src->getTransitions('wid/A')))->equals(2);
    }    
}
