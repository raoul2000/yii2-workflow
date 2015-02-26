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
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
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
