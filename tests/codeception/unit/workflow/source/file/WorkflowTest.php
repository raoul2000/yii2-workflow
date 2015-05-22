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


class WorkflowTest extends TestCase
{
	use \Codeception\Specify;

	public $src;

	protected function setUp()
	{
		parent::setUp();
		$this->src = new WorkflowFileSource();
	}

	public function testIsValidWorkflowId()
	{
		$this->assertFalse($this->src->isValidWorkflowId('workflow id'));
		$this->assertFalse($this->src->isValidWorkflowId('-workflowId'));
		$this->assertFalse($this->src->isValidWorkflowId(' workflowId'));
		$this->assertFalse($this->src->isValidWorkflowId('workflowId/'));
		$this->assertFalse($this->src->isValidWorkflowId('1'));
		$this->assertFalse($this->src->isValidWorkflowId('WORKFLOW_id'));

		$this->assertTrue($this->src->isValidWorkflowId('workflowId'));
		$this->assertTrue($this->src->isValidWorkflowId('workflow-Id'));
		$this->assertTrue($this->src->isValidWorkflowId('workflow01-Id02'));
		$this->assertTrue($this->src->isValidWorkflowId('w01-2'));
	}

	public function testIsValidStatusId()
	{
		$this->assertFalse($this->src->isValidStatusId('id'));
		$this->assertFalse($this->src->isValidStatusId('/id'));
		$this->assertFalse($this->src->isValidStatusId('id/'));
		$this->assertFalse($this->src->isValidStatusId('/'));
		$this->assertFalse($this->src->isValidStatusId('workflow_id/status_id'));
		$this->assertFalse($this->src->isValidStatusId('workflow id/status id'));

		$this->assertTrue($this->src->isValidStatusId('ID/ID'));
		$this->assertTrue($this->src->isValidStatusId('workflow-id/status-id'));
	}

	public function testParseStatusId()
	{
		list($wId, $lid) = $this->src->parseStatusId('Wid/Id');
		$this->assertEquals('Wid', $wId);
		$this->assertEquals('Id', $lid);
		$this->assertTrue(count($this->src->parseStatusId('Wid/Id')) == 2);
	}
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #No status definition found#
	 */	
	public function testAddInvalidWorkflowDefinition()
	{
		$this->src->addWorkflowDefinition('wid', ['initialStatusId' => 'A']);
	}

    public function testFailToLoadWorkflowClass()
    {
    	$this->specify('incorrect status id format', function () {
    		$this->src->getStatus('id');
    	},['throws' => 'raoul2000\workflow\base\WorkflowException']);

    	$this->specify('empty provider fails to load workflow from non-existant workflow class', function () {
    		$this->src->getWorkflow('id');
    	},['throws' => 'raoul2000\workflow\base\WorkflowException']);

    	$this->specify('empty provider fails to load status from non-existant workflow class', function () {
    		$this->src->getStatus('w/s');
    	},['throws' => 'raoul2000\workflow\base\WorkflowException']);

    	$this->specify('empty provider fails to load transition from non-existant workflow class', function ()  {
    		$this->src->getTransitions('w/s');
    	},['throws' => 'raoul2000\workflow\base\WorkflowException']);
    }

    public function testLoadMinimalWorkflowSuccess()
    {
    	$src = new WorkflowFileSource();
    	$src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A',
    		'status' => ['A']
    	]);
    	
    	$this->specify('can load workflow', function () use ($src) {
    		$w = $src->getWorkflow('wid');
    		verify('a Workflow instance is returned', get_class($w) )->equals('raoul2000\workflow\base\Workflow');
    		verify('workflow id is consistent', $w->getId())->equals('wid');
    	});
    }

    public function testWorkflowCached()
    {
    	$this->src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A',
    		'status' => ['A']
    	]);

    	$this->specify('workflow are loaded once',function() {
    		verify('workflow instances are the same', spl_object_hash($this->src->getWorkflow('wid')) )->equals(spl_object_hash($this->src->getWorkflow('wid')));
    	});
    }
}
