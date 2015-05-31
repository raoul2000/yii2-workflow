<?php
namespace tests\unit\workflow\base;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;
use tests\codeception\unit\models\Item01;
use raoul2000\workflow\base\Workflow;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;

class StatusObjectTest extends TestCase
{
	use\Codeception\Specify;

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
			expect("empty out going transition set", count($s->getTransitions()))->equals(0);
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
	public function testAddTransitionSuccess()
	{
		$this->specify('create a status instance with transition', function ()
		{
			$start = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1'
			]);

			$end = new Status([
				'id' => 'published',
				'workflowId' => 'workflow1'
			]);

			$transition = new Transition([
				'start' => $start,
				'end' => $end
			]);

			$start->addTransition($transition);

			verify('$start status has one transition',count($start->getTransitions()))->equals(1);
			verify('$end status has no transition',count($end->getTransitions()))->equals(0);
		});
	}

	public function testAddTransitionFails1()
	{
		$this->specify('an exception is thrown if empty transition argument is provided', function ()
		{
			$this->setExpectedException(
				'raoul2000\workflow\base\WorkflowException',
				'"transition" object must implement raoul2000\workflow\baseTransition'
			);
			$start = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1'
			]);

			$start->addTransition(null);

		});
	}
	public function testAddTransitionFails2()
	{
		$this->specify('an exception is thrown if Transition instance is not provided', function ()
		{
			$this->setExpectedException(
				'raoul2000\workflow\base\WorkflowException',
				'"transition" object must implement raoul2000\workflow\baseTransition'
			);
			$start = new Status([
				'id' => 'draft',
				'workflowId' => 'workflow1'
			]);

			$start->addTransition($start);

		});
	}

}
