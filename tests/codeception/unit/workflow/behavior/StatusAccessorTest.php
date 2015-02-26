<?php
namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use yii\base\InvalidConfigException;
use tests\codeception\unit\models\Item07;
use tests\codeception\unit\models\StatusAccessor07;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\base\WorkflowException;

class StatusAccessorTest extends TestCase
{
	use\Codeception\Specify;

	public $item;
	public $statusAccessor = null;

	protected function setup()
	{
		parent::setUp();

		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
		]);


		Yii::$app->set('status_accessor',[
			'class'=> 'tests\codeception\unit\models\StatusAccessor07'
		]);

		$this->statusAccessor = Yii::$app->get('status_accessor');
		$this->statusAccessor->resetCallCounters();
		StatusAccessor07::$instanceCount = 1;
	}

	public function testOnConstructSuccess()
	{
		$this->statusAccessor->statusToReturnOnGet = 'Item07Workflow/B';

		$item = new Item07();

		verify(StatusAccessor07::$instanceCount)->equals(1);

		$this->specify('on instance creation getStatus is invoked', function() use ($item) {
			expect('getStatus has been called ',$this->statusAccessor->callGetStatusCount)->equals(1);
			expect('item status is Item07Workflow/B', $item->getworkflowStatus()->getId())->equals('Item07Workflow/B');
		});
	}
	public function testOnConstructFails()
	{
		$this->statusAccessor->statusToReturnOnGet = 'NOT FOUND';
		$this->setExpectedException('raoul2000\workflow\base\WorkflowException',"Not a valid status id format: failed to get workflow id - status = 'NOT FOUND'");
		new Item07();
	}
	public function testOnEnterWorkflowByMethodSuccess()
	{
		$this->statusAccessor->statusToReturnOnGet = null;

		$item = new Item07();

		verify(StatusAccessor07::$instanceCount)->equals(1);
		verify(spl_object_hash($this->statusAccessor))->equals(spl_object_hash($item->getStatusAccessor()));

		verify($item->getWorkflowStatus())->equals(null);

		// by method call (no save)
		$item->enterWorkflow();

		expect(StatusAccessor07::$instanceCount)->equals(1);
		expect($item->getWorkflowStatus()->getId())->equals('Item07Workflow/A');
		expect('setStatus has been called ',$this->statusAccessor->callSetStatusCount)->equals(1);

		verify(spl_object_hash($this->statusAccessor))->equals(spl_object_hash($item->getStatusAccessor()));

	}
	public function testOnEnterWorkflowAssignAndSaveSuccess()
	{
		$this->statusAccessor->statusToReturnOnGet = null;

		$item = new Item07();

		verify(StatusAccessor07::$instanceCount)->equals(1);
		verify(spl_object_hash($this->statusAccessor))->equals(spl_object_hash($item->getStatusAccessor()));

		verify($item->getWorkflowStatus())->equals(null);

		// by assignation + save
		$item->statusAlias = 'Item07Workflow/A';
		$saveIsOk = $item->save();
		verify('model could be saved',$saveIsOk)->true();
		verify('item status is now initial status',$item->getWorkflowStatus()->getId())->equals('Item07Workflow/A');
		expect('setStatus has been called once',$this->statusAccessor->callSetStatusCount)->equals(1);
		expect('commitStatus has been called ',$this->statusAccessor->callCommitStatusCount)->equals(1);
	}

	public function testOnEnterWorkflowFails()
	{
		$this->statusAccessor->statusToReturnOnGet = 'Item07Workflow/B';

		$item = new Item07();

		verify(StatusAccessor07::$instanceCount)->equals(1);
		verify('item status is Item07Workflow/B', $item->getworkflowStatus()->getId())->equals('Item07Workflow/B');
		verify('getStatus has been called ',$this->statusAccessor->callGetStatusCount)->equals(1);

		$this->setExpectedException('raoul2000\workflow\base\WorkflowException',"Model already in a workflow");

 		$item->enterWorkflow();
	}
	public function testCallCommitStatusSuccess()
	{
		$this->statusAccessor->statusToReturnOnGet = 'Item07Workflow/B';

		$item = new Item07();

		verify('getStatus has been called ',$this->statusAccessor->callGetStatusCount)->equals(1);

		// change status by assignation + save
		$item->statusAlias = 'Item07Workflow/C';
		verify('model can be saved', $item->save())->true();

		expect('setStatus has been called ',$this->statusAccessor->callSetStatusCount)->equals(1);
		expect('commitStatus has been called ',$this->statusAccessor->callCommitStatusCount)->equals(1);

		// change status by method call
		$item->sendToStatus('Item07Workflow/A');

		verify('model status has changed', $item->getWorkflowStatus()->getId())->equals('Item07Workflow/A');

		expect('setStatus has been called ',$this->statusAccessor->callSetStatusCount)->equals(2);
		expect('commitStatus has not bee called',$this->statusAccessor->callCommitStatusCount)->equals(1);

	}
}
