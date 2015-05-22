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


class StatusTest extends TestCase
{
	use \Codeception\Specify;

	public $src;

	protected function setUp()
	{
		parent::setUp();
		$this->src = new WorkflowFileSource();
	}

    /**
     * @expectedException raoul2000\workflow\base\WorkflowValidationException
     * @expectedExceptionMessageRegExp #No status definition found#
     */
	public function testStatusNotFoundSuccess()
	{
		$src = new WorkflowFileSource();
		$src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
			'status' => null
		]);

		$this->specify('status is not found', function () use ($src) {
			$status = $src->getStatus('wid/A');
			verify('a Workflow instance is returned', $status )->equals(null);
		});
	}
	
    public function testLoadStatusSuccess()
    {
    	$this->src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
    		'status' => [
				'A' => [
					'label' => 'label A'
    			],
    			'B' => []
    		]
    	]);
    	$this->specify('status can be obtained',function() {
			$w = $this->src->getWorkflow('wid');
			verify('non null workflow instance is returned',  $w != null)->true();

			verify('workflow contains status A', $this->src->getStatus('wid/A') != null)->true();

			verify('initial status is A ', $w->getInitialStatusId())->equals('wid/A');


			verify('status A has correct id', $this->src->getStatus('wid/A')->getId() )->equals('wid/A');
			verify('status A has correct label', $this->src->getStatus('wid/A')->getLabel() )->equals('label A');

			verify('workflow contains status B', $this->src->getStatus('wid/B') != null)->true();
			verify('status B has correct id', $this->src->getStatus('wid/B')->getId() )->equals('wid/B');
			verify('status B has default label', $this->src->getStatus('wid/B')->getLabel() )->equals('B');

			//verify('workflow does not contains status C', $this->src->getStatus('wid/C') == null)->true();
    	});
    }
    public function testLoadStatusSuccess2()
    {
    	$this->src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A',
    		'status' => [
    			'A' => null
    		]
    	]);
    	$this->specify('a null status definition is not allowed',function() {
    		$w = $this->src->getWorkflow('wid');
    		verify('non null workflow instance is returned',  $w != null)->true();
    		verify('status A cannot be loaded', $this->src->getStatus('wid/A') !== null)->true();
    	});
    }
    public function testStatusCached()
    {
    	$this->src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A',
    		'status' => [
    			'A' => []
    		]
    	]);

    	$this->specify('status are loaded once',function() {
    		$this->src->getWorkflow('wid');
    		verify('status instances are the same', spl_object_hash($this->src->getStatus('wid/A')) )->equals(spl_object_hash($this->src->getStatus('wid/A')));
    	});
    }
}
