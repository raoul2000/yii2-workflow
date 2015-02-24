<?php
namespace tests\codeception\unit\models;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\Event;
use raoul2000\workflow\events\WorkflowEvent;

class Item_06Behavior  extends Behavior
{
	public $corrected = false;
	public $canBeArchived = false;

	public static $maxPostCount = 2;
	public static $countPost = 0;
	public static $countPostToCorrect = 0;
	public static $countPostCorrected = 0;

	public function events()
	{
		return [
			WorkflowEvent::beforeEnterStatus('Item_06Workflow/new') => "beforeNew",
			WorkflowEvent::afterEnterStatus('Item_06Workflow/new') => "afterNew",
			WorkflowEvent::afterEnterStatus('Item_06Workflow/correction') => "postToCorrect",
			WorkflowEvent::beforeLeaveStatus('Item_06Workflow/correction') => "postCorrected",
			WorkflowEvent::beforeEnterStatus('Item_06Workflow/published') => "checkCanBePublished",
			WorkflowEvent::beforeChangeStatus('Item_06Workflow/published', 'Item_06Workflow/archive') => "canBeArchived",
		];
	}
	public function beforeNew($event)
	{
		if(self::$countPost >= self::$maxPostCount) {
			$event->isValid = false;
		}
	}
	public function afterNew($event)
	{
		self::$countPost++;
	}
	public function postToCorrect($event)
	{
		self::$countPostToCorrect++;
	}
	public function postCorrected($event)
	{
		if( ! $this->corrected) {
			$event->isValid = false;
		} else {
			$this->corrected = true;
			self::$countPostToCorrect--;
			self::$countPostCorrected++;
		}
	}
	public function checkCanBePublished($event)
	{
		if( ! $this->corrected) {
			$event->isValid = false;
		}
	}
	public function canBeArchived($event)
	{
		$event->isValid = ( $this->canBeArchived == true );
	}


	//////////////////////////////////////////////////////////////////

	public function markAsCorrected()
	{
		$this->corrected = true;
	}
	public function markAsCandidateForArchive()
	{
		$this->canBeArchived = true;
	}
}