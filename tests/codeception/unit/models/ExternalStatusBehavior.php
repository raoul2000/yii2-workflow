<?php
namespace tests\codeception\unit\models;

use Yii;
use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\base\Event;
use raoul2000\workflow\events\WorkflowEvent;

class ExternalStatusBehavior  extends Behavior
{
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
}