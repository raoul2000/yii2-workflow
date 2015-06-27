<?php

namespace tests\codeception\unit\models;

use Yii;

/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property string $name
 * @property string $status
 */
class Component01 extends \yii\base\Component
{
	public $status;
}
