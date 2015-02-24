<?php

namespace tests\codeception\unit\models;

use Yii;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use raoul2000\workflow\validation\WorkflowScenario;

/**
 * @property integer $id
 * @property string $name
 * @property string $status
 */
class Item_05 extends \yii\db\ActiveRecord
{
	public $category = 'default';
	public $tags = null;
	public $author = "default";
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

	public function rules() {
		return [
			[['status'], '\raoul2000\workflow\validation\WorkflowValidator'],
// 			[['status'], 'checkAvailableSlots',
// 				'on' => WorkflowEvent::enterStatus('Item_05Workflow/correction')
// 			],
			['name','required',
				'on' => WorkflowScenario::changeStatus('Item_05Workflow/new', 'Item_05Workflow/correction') ],

			['category', 'required',
				'on' => WorkflowScenario::enterWorkflow('Item_05Workflow')],

			['category', 'compare', 'compareValue' => 'done',
				'on' => WorkflowScenario::leaveWorkflow()],

			['tags', 'required',
				'on' => WorkflowScenario::leaveStatus('Item_05Workflow/correction')],

			['author', 'required' ,
				'on' => WorkflowScenario::enterStatus('Item_05Workflow/published')]
		];

	}

    public function behaviors()
    {
        return [
        	'workflow' => [
        		'class' => SimpleWorkflowBehavior::className()
    	    ]
        ];
    }
}
