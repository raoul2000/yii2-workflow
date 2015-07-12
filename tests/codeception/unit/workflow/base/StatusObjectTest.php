<?php
namespace tests\unit\workflow\base;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;
use tests\codeception\unit\models\Item01;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use raoul2000\workflow\source\file\WorkflowFileSource;
use raoul2000\workflow\base\TransitionInterface;
use raoul2000\workflow\base\WorkflowInterface;

class StatusObjectTest extends TestCase
{
	use\Codeception\Specify;
	
	protected function setUp()
	{
		parent::setUp();
		$this->src = new WorkflowFileSource();
	}
	
	public function testStatusCreationSuccess()
	{
		$this->specify('create a status instance', function ()
		{
			$s = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1'
			]);
			expect("status id is 'draft'", $s->getId())->equals('draft');
			expect("workflow id is 'workflow1'", $s->getWorkflowId())->equals('workflow1');
			expect("label is empty string", $s->getLabel())->equals('');
		});

		$this->specify('create a status instance with a label', function ()
		{
			$s = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1',
				'label' => 'my custom label'
			]);

			expect("label is 'my custom label'", $s->getLabel())->equals('my custom label');
		});


	}

	public function testStatusMetadataSuccess()
	{
		$this->specify('create a status instance with metadata', function ()
		{
			$s = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1',
				'metadata' => [
					'color' => "#fffffff",
					'priority' => 1
				]
			]);

			expect("color is obtained as a metadata", $s->getMetadata('color'))->equals('#fffffff');
			expect("priority is obtained as a metadata", $s->getMetadata('priority'))->equals(1);

			expect("metadata can be accessed as properties", $s->color)->equals('#fffffff');
			expect("priority is obtained as a metadata", $s->priority)->equals(1);
		});
	}

	public function testUnknownMetadata()
	{
		$this->specify('status creation fails when no id is provided', function ()
		{
			$this->setExpectedException('raoul2000\workflow\base\WorkflowException');

			$s = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1',
				'metadata' => [
					'color' => "#fffffff",
					'priority' => 1
				]
			]);

			$s->notFoundMetadata;
		});
	}

	public function testMissingId()
	{
		$this->specify('status creation fails when no id is provided', function ()
		{
			$this->setExpectedException('yii\base\InvalidConfigException');

			new Status([
				'workflowId' => 'workflow1'
			]);
		});
	}

	public function testNullId()
	{
		$this->specify('status creation fails when empty id is provided', function ()
		{
			$this->setExpectedException(
				'yii\base\InvalidConfigException',
				'missing status id'
			);

			new Status([
				'id' => null,
				'workflowId' => 'workflow1'
			]);
		});
	}

	public function testMissingWorkflowId()
	{
		$this->specify('create a status instance with no workflow id', function ()
		{
			$this->setExpectedException(
				'yii\base\InvalidConfigException',
				'missing workflow id'
			);
			new Status([
				'id' => 'workflow1'
			]);
		});
	}
	public function testNullWorkflowId()
	{
		$this->specify('create a status instance with empty workflow id', function ()
		{
			$this->setExpectedException(
				'yii\base\InvalidConfigException',
				'missing workflow id'
			);
			new Status([
				'id' => 'workflow1',
				'workflowId' => null
			]);
		});
	}
	
	public function testCreateWithSourceSuccess()
	{
		$this->specify('create a status instance with source component', function ()
		{
			$src = Yii::createObject([
				'class' => WorkflowFileSource::className()
			]);
			
			$start = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1',
				'source' => $src
			]);

			verify('a source component is available', $start->getSource())->notNull();
		});
	}

	public function testCreateWithSourceFails1()
	{
		$this->specify('create a status instance with an invalid source component', function ()
		{
			$src = new \stdClass();
			
			$this->setExpectedException(
				'yii\base\InvalidConfigException',
				'The "source" property must implement interface raoul2000\workflow\source\IWorkflowSource'
			);				
			$start = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1',
				'source' => $src
			]);
		});		
	}
	
	public function testCreateWithSourceFails2()
	{
		$this->specify('create a status instance with an invalid source component', function ()
		{
			$this->setExpectedException(
				'yii\base\InvalidConfigException',
				'The "source" property must implement interface raoul2000\workflow\source\IWorkflowSource'
			);
			new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1',
				'source' => ''
			]);
		});
	}	
	
	public function testStatusAccessorSuccess()
	{
		$this->src->addWorkflowDefinition('wid', [
			'initialStatusId' => 'A',
			'status' => [
				'A' => [
					'label' => 'label A',
					'transition' => ['B','C']
				],
				'B' => [],
				'C' => []
			]
		]);
		$w = $this->src->getWorkflow('wid');
		verify_that($w != null);
		 
		$this->specify('transitions can be obtained through status',function() {
	
			$status = $this->src->getStatus('wid/A');
	
			expect_that($status != null);
	
			$tr = $status->getTransitions();
	
			expect_that(is_array($tr));
			expect(count($tr))->equals(2);
	
			$keys = array_keys($tr);
			
			expect($keys)->equals(['wid/B','wid/C']);
			expect_that( $tr['wid/B'] instanceof TransitionInterface);
			expect_that( $tr['wid/C'] instanceof TransitionInterface);
		});
		
		$this->specify('parent workflow can be obtained through status',function() {
		
			$status = $this->src->getStatus('wid/A');
		
			expect_that($status != null);
		
			$wrk = $status->getWorkflow();
		
			expect_that($wrk != null);
			verify_that( $wrk instanceof WorkflowInterface);
			verify($wrk->getId())->equals('wid');
		});		
	}		
	
	public function testStatusAccessorFails()
	{
		$st = new Status([
			'id' => 'draft',
			'workflowId' => 'workflow1'
		]);
		
		$this->specify('Failed to get transitions when no source is configured', function () use($st)
		{
			$this->setExpectedException(
				'raoul2000\workflow\base\WorkflowException',
				'no workflow source component available'
			);
			$st->getTransitions();
		});
	
		$this->specify('Failed to get workflow object when no source is configured', function () use($st)
		{
			$this->setExpectedException(
				'raoul2000\workflow\base\WorkflowException',
				'no workflow source component available'
			);
			$st->getWorkflow();
		});		
		
		$this->specify('Failed to call isInitialStatus when no source is configured', function () use($st)
		{
			$this->setExpectedException(
				'raoul2000\workflow\base\WorkflowException',
				'no workflow source component available'
			);
			$st->getWorkflow();
		});		
	}	
}
