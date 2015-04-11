<?php

namespace tests\codeception\unit\models;

use Yii;
use raoul2000\workflow\validation\WorkflowScenario;
use raoul2000\workflow\validation\WorkflowValidator;
/**
 * This is the model class for table "item".
 *
 * @property integer $id
 * @property string $name
 * @property string $status
 * @property string $status_ex
 */
class Item08 extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * (non-PHPdoc)
     * @see \yii\base\Component::behaviors()
     */
    public function behaviors()
    {
    	return [
    		'w1' => [
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    			'defaultWorkflowId' => 'Item08Workflow1'
    		],    		
    		'w2' => [
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    			'statusAttribute' => 'status_ex',
    			'defaultWorkflowId' => 'Item08Workflow2'
    		]
    	];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'status' => 'Status',
        	'status_ex' => 'Status Ex.',
        ];
    }
}
