<?php
namespace tests\unit\workflow\behavior;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item08;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use yii\codeception\DbTestCase;
use tests\codeception\unit\fixtures\ItemFixture04;

class MultiWorkflowTest extends DbTestCase {
	
	use \Codeception\Specify;
	
	protected function setup()
	{
		parent::setUp();
		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
		]);
	}	
	
	public function testSetStatusAssignedSuccess()
	{
		$o = new Item08();
		
		$o->status = 'draft';
		$o->status_ex = 'success';
		expect_that($o->save());
		verify_that( $o->status == 'Item08Workflow1/draft');
		verify_that( $o->status_ex == 'Item08Workflow2/success');
		
		$o = new Item08();		
		$o->status = 'draft';
		expect_that($o->save());
		verify_that( $o->status == 'Item08Workflow1/draft');
		verify_that( $o->status_ex == null);	

		$o = new Item08();
		$o->status_ex = 'success';
		expect_that($o->save());
		verify_that( $o->status == null);
		verify_that( $o->status_ex == 'Item08Workflow2/success');		
	}	
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowException
	 * @expectedExceptionMessageRegExp #No status found with id Item08Workflow2/DUMMY#
	 */	
	public function testSetStatusAssignedFails1()
	{
		$o = new Item08();
	
		$o->status = 'draft';
		$o->status_ex = 'DUMMY';
		$o->save();
	}
		
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowException
	 * @expectedExceptionMessageRegExp #No status found with id Item08Workflow1/DUMMY#
	 */
	public function testSetStatusAssignedFails2()
	{
		$o = new Item08();
	
		$o->status = 'DUMMY';
		$o->status_ex = 'succcess';
		$o->save();
	}
		
	public function testSetStatusBehaviorSuccess()
	{
		$o = new Item08();
		
		$o->getBehavior('w1')->sendToStatus('draft');
		$o->getBehavior('w2')->sendToStatus('success');

		verify_that( $o->getBehavior('w1')->getWorkflowStatus()->getId() == 'Item08Workflow1/draft');
		verify_that( $o->getBehavior('w2')->getWorkflowStatus()->getId() == 'Item08Workflow2/success');
		
		$o->getBehavior('w1')->sendToStatus('correction');
		$o->getBehavior('w2')->sendToStatus('onHold');
		
		verify_that( $o->getBehavior('w1')->getWorkflowStatus()->getId() == 'Item08Workflow1/correction');
		verify_that( $o->getBehavior('w2')->getWorkflowStatus()->getId() == 'Item08Workflow2/onHold');		
	}	
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowException
	 * @expectedExceptionMessageRegExp #No status found with id Item08Workflow1/DUMMY#
	 */	
	public function testSetStatusBehaviorFails1()
	{
		$o = new Item08();
	
		$o->getBehavior('w1')->sendToStatus('DUMMY');
	}	
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowException
	 * @expectedExceptionMessageRegExp #No status found with id Item08Workflow2/DUMMY#
	 */
	public function testSetStatusBehaviorFails2()
	{
		$o = new Item08();
	
		$o->getBehavior('w2')->sendToStatus('DUMMY');
	}	
	
	public function testEnterWorkflowSuccess()
	{
		$o = new Item08();
	
		$o->getBehavior('w1')->enterWorkflow();
		$o->getBehavior('w2')->enterWorkflow();
	
		verify_that( $o->getBehavior('w1')->getWorkflowStatus()->getId() == 'Item08Workflow1/draft');
		verify_that( $o->getBehavior('w2')->getWorkflowStatus()->getId() == 'Item08Workflow2/success');
	}	
}