# Workflow Behavior

The *SimpleWorkflowBehavior* is the main component to start using workflows. Once attached to
a model, it provides a set of methods that you will be able to use in order to manage the your model inside a workflow.

## Configuration

The *SimpleWorkflowBehavior* is easely customizable through a large set of configuration options. These options must be
set when attaching the behavior to the model, just like in the example below where a subset of options is used :

```php
namespace app\models;

class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
        	'workflow' => [
        		'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
        		'defaultWorkflowId'      => 'post',
        		'statusAttribute'        => 'my_status',
        		'source'                 => 'myPrivateWorkflowSource'
        		'propagateErrorsToModel' => true
    	    ]
        ];
    }    
 	// etc...
 }
 ```
 
 TBD