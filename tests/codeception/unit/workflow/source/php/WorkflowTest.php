<?php

namespace tests\unit\workflow\source\php;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item_01;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use raoul2000\workflow\source\php\WorkflowPhpSource;
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
		$this->src = new WorkflowPhpSource();
	}

	public function testIsValidWorkflowId()
	{
		$this->assertFalse($this->src->isValidWorkflowId('workflow id'));
		$this->assertFalse($this->src->isValidWorkflowId('-workflowId'));
		$this->assertFalse($this->src->isValidWorkflowId(' workflowId'));
		$this->assertFalse($this->src->isValidWorkflowId('workflowId/'));

		$this->assertTrue($this->src->isValidWorkflowId('workflowId'));
		$this->assertTrue($this->src->isValidWorkflowId('workflow_Id'));
		$this->assertTrue($this->src->isValidWorkflowId('WORKFLOW_id'));
	}

	public function testIsValidStatusId()
	{
		$this->assertFalse($this->src->isValidStatusId('id'));
		$this->assertFalse($this->src->isValidStatusId('/id'));
		$this->assertFalse($this->src->isValidStatusId('id/'));
		$this->assertFalse($this->src->isValidStatusId('/'));
		$this->assertFalse($this->src->isValidStatusId('workflow id/status id'));

		$this->assertTrue($this->src->isValidStatusId('workflow_id/status_id'));
		$this->assertTrue($this->src->isValidStatusId('ID/ID'));
	}

	public function testParseStatusId()
	{
		list($wId, $lid) = $this->src->parseStatusId('Wid/Id');
		$this->assertEquals('Wid', $wId);
		$this->assertEquals('Id', $lid);
		$this->assertTrue(count($this->src->parseStatusId('Wid/Id')) == 2);
	}
	public function testAddWorkflowDefinition()
	{
		$this->src->addWorkflowDefinition('wid', ['initialStatusId' => 'A']);
		$wdef = $this->src->getWorkflowDefinition('wid');

		$this->assertTrue(is_array($wdef));
		$this->assertEquals(1, count($wdef));
		$this->assertEquals('A',$wdef['initialStatusId']);

		$this->specify('an exception is thrown when trying to get a not_found workflow definition',function () {
			$this->src->getWorkflowDefinition('not_found');
		},['throws' => 'raoul2000\workflow\base\WorkflowException']);

	}
	public function testGetClassname()
	{
		$this->src->namespace = 'a\b\c';
		$this->assertEquals('a\b\c\PostWorkflow', $this->src->getClassname('PostWorkflow'));
		$this->src->namespace = '';
		$this->assertEquals('\PostWorkflow', $this->src->getClassname('PostWorkflow'));

		$this->specify('exception thrown on invalid workflow id', function() {
			$this->src->getClassname('');
		},['throws'=> 'raoul2000\workflow\base\WorkflowException']);

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

//     	$this->specify('workflow id inconsistency : provided workflow id differs from configured one', function ()
//     	{
//     		$this->src->addWorkflowDefinition('wid', [
//     			'id' => 'otherId',
//     			'initialStatusId' => 'A'
//     		]);
//     		$this->src->getWorkflow('wid');
//     	},['throws' => 'raoul2000\workflow\base\WorkflowException']);
    }

    public function testLoadMinimalWorkflowSuccess()
    {
    	$src = new WorkflowPhpSource();
    	$src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A'
    	]);

		$this->_testLoadMinimalWorkflowSuccess($src);

		$src->addWorkflowDefinition('wid', [
			'id' => 'wid',
			'initialStatusId' => 'A'
		]);
		$this->_testLoadMinimalWorkflowSuccess($src);


		$src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
			'status' => null
		]);
		$this->_testLoadMinimalWorkflowSuccess($src);
    }

    private function _testLoadMinimalWorkflowSuccess($src)
    {
    	$this->specify('can load workflow', function () use ($src) {
    		$w = $src->getWorkflow('wid');
    		verify('a Workflow instance is returned', get_class($w) )->equals('raoul2000\workflow\base\Workflow');
    		verify('workflow id is consistent', $w->getId())->equals('wid');
    	});

    	$this->specify('fail to load not defined status', function () use ($src) {
    		verify('null is returned',$src->getStatus('wid/A'))->equals(null);
    	});
    }
    public function testWorkflowCached()
    {
    	$this->src->addWorkflowDefinition('wid', [
    		'initialStatusId' => 'A'
    	]);

    	$this->specify('workflow are loaded once',function() {
    		verify('workflow instances are the same', spl_object_hash($this->src->getWorkflow('wid')) )->equals(spl_object_hash($this->src->getWorkflow('wid')));
    	});
    }
}
