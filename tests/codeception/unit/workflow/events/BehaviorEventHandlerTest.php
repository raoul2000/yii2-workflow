<?php

namespace tests\unit\workflow\events;

use Yii;
use yii\codeception\DbTestCase;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use tests\codeception\unit\models\Item_06;
use tests\codeception\unit\models\Item_06Behavior;
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

		Item_06Behavior::$maxPostCount = 2;
		Item_06Behavior::$countPost = 0;
		Item_06Behavior::$countPostToCorrect = 0;
		Item_06Behavior::$countPostCorrected = 0;
	}

    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testEnterWorkflowSuccess()
    {
    	$post = new Item_06();
		verify('no post instance created', Item_06Behavior::$countPost)->equals(0);

		expect('post is inserted in workflow',$post->enterWorkflow())->true();
		expect('post count is 1',Item_06Behavior::$countPost)->equals(1);

		$post1 = new Item_06();
		expect('post is inserted in workflow',$post1->enterWorkflow())->true();
		expect('post count is 2',Item_06Behavior::$countPost)->equals(2);

		$post2 = new Item_06();
		expect('post is not inserted in workflow',$post2->enterWorkflow())->false();
		expect('post count is 2',Item_06Behavior::$countPost)->equals(2);
		expect('post2 status is not set',$post2->getWorkflowStatus())->equals(null);
    }
    /**
     * In the use case, a new post can't be published before it has been corrected.
     * the action to correct a post is implemented by the "markAsCorrected" method.
     */
    public function testPublishSuccess()
    {
    	$post = new Item_06();
    	verify('no post instance in the workflow', Item_06Behavior::$countPost)->equals(0);
    	verify('post is inserted in workflow',$post->enterWorkflow())->true();
    	verify('post count is 1',Item_06Behavior::$countPost)->equals(1);

    	expect('fail to send to publish',$post->sendToStatus('Item_06Workflow/published'))->false();

    	verify('no post are to correct',	Item_06Behavior::$countPostToCorrect)->equals(0);
    	verify('send post to correction', 	$post->sendToStatus('Item_06Workflow/correction'))->true();
    	expect('1 post is to correct',		Item_06Behavior::$countPostToCorrect)->equals(1);

    	expect('fail to send to publish',$post->sendToStatus('Item_06Workflow/published'))->false();

		$post->markAsCorrected();

    	verify('no post have been corrected',	Item_06Behavior::$countPostCorrected)->equals(0);
		expect('post has been corrected, it can be published',$post->sendToStatus('Item_06Workflow/published'))->true();
		verify('1 post have been corrected',	Item_06Behavior::$countPostCorrected)->equals(1);
    }

    public function testArchiveSuccess()
    {
    	$post = new Item_06();

    	verify('post is inserted in workflow',$post->enterWorkflow())->true();
    	verify('send post to correction', 	$post->sendToStatus('Item_06Workflow/correction'))->true();
    	$post->markAsCorrected();
    	verify('post has been corrected, it can be published',$post->sendToStatus('Item_06Workflow/published'))->true();

    	expect('fail to send to archive',$post->sendToStatus('Item_06Workflow/archive'))->false();
    	$post->markAsCandidateForArchive();
    	expect('post is sent to archive',$post->sendToStatus('Item_06Workflow/archive'))->true();

    }
}
