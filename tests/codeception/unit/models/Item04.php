<?php

namespace tests\codeception\unit\models;

use Yii;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\source\file\IWorkflowDefinitionProvider;

/**
 * @property integer $id
 * @property string $name
 * @property string $status
 */
class Item04 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    public function behaviors()
    {
        return [
        	'workflow' => [
        		'class' => SimpleWorkflowBehavior::className(),
    	    ]
        ];
    }
}
