<?php
namespace tests\codeception\unit\models;

use Yii;
use raoul2000\workflow\source\file\IWorkflowDefinitionProvider;

class Item08Workflow2 implements IWorkflowDefinitionProvider 
{
    public function getDefinition() {
        return [ 
            'initialStatusId' => 'success',
            'status' => [
                'success' => [
                    'transition' => ['onHold']
                ],
                'onHold' => [
                    'transition' => ['success']
                ],
            ]
        ];
    }
}