<?php

namespace tests\unit\workflow\events;

use Yii;
use yii\codeception\DbTestCase;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\models\Item06;
use tests\codeception\unit\models\Item06Behavior;
use raoul2000\workflow\base\WorkflowException;
use raoul2000\workflow\events\WorkflowEvent;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\Transition;
use yii\base\Exception;

class BehaviorEventHandlerTest extends DbTestCase
{
	use \Codeception\Specify;

	protected function setup()
	{
		parent::setUp();

		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\file\WorkflowFileSource',
				'definitionLoader' => [
					'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
					'namespace' => 'tests\codeception\unit\models'
				]
		]);

		Item06Behavior::$maxPostCount = 2;
		Item06Behavior::$countPost = 0;
		Item06Behavior::$countPostToCorrect = 0;
		Item06Behavior::$countPostCorrected = 0;
	}

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testLeaveWorkflowOnAssignNULL()
    {
    	$post = new Item06();
    	$post->name ='post name';
    	$post->enterWorkflow();
    	
    	verify($post->save())->true();
    	verify($post->getWorkflowStatus()->getId())->equals('Item06Workflow/new');
    	$post->status = null;
    	
    	Item06Behavior::$countLeaveWorkflow= 0;
    	expect($post->save())->equals(true);
    	verify(Item06Behavior::$countLeaveWorkflow)->equals(1);
    	
    	$post->enterWorkflow();
    	verify($post->save())->true();
    	verify($post->getWorkflowStatus()->getId())->equals('Item06Workflow/new');
    	
    	$post->canLeaveWorkflow(false);
    	
    	$post->status = null;
    	expect_not($post->save());
    }
    
    public function testLeaveWorkflowOnDelete()
    {
    	$post = new Item06();
    	$post->name ='post name';
    	$post->enterWorkflow();
    	 
    	verify($post->save())->true();
    	verify($post->getWorkflowStatus()->getId())->equals('Item06Workflow/new');
    	
    	Item06Behavior::$countLeaveWorkflow= 0;
    	$post->canLeaveWorkflow(true);
    	expect_that($post->delete());
    	verify(Item06Behavior::$countLeaveWorkflow)->equals(1);
    	 
    	 
    	$post = new Item06();
    	$post->name ='post name';
    	$post->enterWorkflow();
    	 
    	verify($post->save())->true();
    	verify($post->getWorkflowStatus()->getId())->equals('Item06Workflow/new');
    	
    	
    	$post->canLeaveWorkflow(false); // refuse leave workflow
    	
    	// Now, the handler attached to the beforeLeaveWorkflow Event (see Item06Behavior)
    	// will invalidate the event and return false (preventing the DELETE operation)
    	expect_not($post->delete());
    }
        
    public function testEnterWorkflowSuccess()
    {
    	$post = new Item06();
		verify('no post instance created', Item06Behavior::$countPost)->equals(0);

		expect('post is inserted in workflow',$post->enterWorkflow())->true();
		expect('post count is 1',Item06Behavior::$countPost)->equals(1);

		$post1 = new Item06();
		expect('post is inserted in workflow',$post1->enterWorkflow())->true();
		expect('post count is 2',Item06Behavior::$countPost)->equals(2);

		$post2 = new Item06();
		expect('post is not inserted in workflow',$post2->enterWorkflow())->false();
		expect('post count is 2',Item06Behavior::$countPost)->equals(2);
		expect('post2 status is not set',$post2->getWorkflowStatus())->equals(null);
    }
    /**
     * In the use case, a new post can't be published before it has been corrected.
     * the action to correct a post is implemented by the "markAsCorrected" method.
     */
    public function testPublishSuccess()
    {
    	$post = new Item06();
    	verify('no post instance in the workflow', Item06Behavior::$countPost)->equals(0);
    	verify('post is inserted in workflow',$post->enterWorkflow())->true();
    	verify('post count is 1',Item06Behavior::$countPost)->equals(1);

    	expect('fail to send to publish',$post->sendToStatus('Item06Workflow/published'))->false();

    	verify('no post are to correct',	Item06Behavior::$countPostToCorrect)->equals(0);
    	verify('send post to correction', 	$post->sendToStatus('Item06Workflow/correction'))->true();
    	expect('1 post is to correct',		Item06Behavior::$countPostToCorrect)->equals(1);

    	expect('fail to send to publish',$post->sendToStatus('Item06Workflow/published'))->false();

		$post->markAsCorrected();

    	verify('no post have been corrected',	Item06Behavior::$countPostCorrected)->equals(0);
		expect('post has been corrected, it can be published',$post->sendToStatus('Item06Workflow/published'))->true();
		verify('1 post have been corrected',	Item06Behavior::$countPostCorrected)->equals(1);
    }

    public function testArchiveSuccess()
    {
    	$post = new Item06();

    	verify('post is inserted in workflow',$post->enterWorkflow())->true();
    	verify('send post to correction', 	$post->sendToStatus('Item06Workflow/correction'))->true();
    	$post->markAsCorrected();
    	verify('post has been corrected, it can be published',$post->sendToStatus('Item06Workflow/published'))->true();

    	expect('fail to send to archive',$post->sendToStatus('Item06Workflow/archive'))->false();
    	$post->markAsCandidateForArchive();
    	expect('post is sent to archive',$post->sendToStatus('Item06Workflow/archive'))->true();

    }
}
