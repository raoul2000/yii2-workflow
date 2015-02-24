<?php
namespace tests\codeception\unit\models;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\Event;

class EventTrackerBehavior  extends Behavior
{
	public $afterFind = 0;
	public $beforeInsert = 0;
	public $beforeUpdate = 0;
	public $afterUpdate = 0;
	public $afterInsert = 0;

	public function events()
	{
		return [
			ActiveRecord::EVENT_AFTER_FIND => 'afterFindHandler',
			ActiveRecord::EVENT_BEFORE_INSERT => 'beforeInsertHandler',
			ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeUpdateHandler',
			ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdateHandler',
			ActiveRecord::EVENT_AFTER_INSERT => 'afterInsertHandler',
		];
	}
	public function afterFindHandler()
	{
		$this->afterFind ++;
	}
	public function beforeInsertHandler()
	{
		$this->beforeInsert ++;
	}
	public function beforeUpdateHandler()
	{
		$this->beforeUpdate ++;
	}
	public function afterUpdateHandler()
	{
		$this->afterUpdate ++;
	}
	public function afterInsertHandler()
	{
		$this->afterInsert ++;
	}

}