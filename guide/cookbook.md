

This page contains a set of *SimpleWorkflow* snippets that you may have to deal with someday


## Initialization
 
Don't be scared ! most of the time you will not have to use **all** the configuration settings like on the following examples.. 


### The *SimpleWorkflowBehavior* behavior

`@app/models/Post.php`
```php
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
            'class' => '\raoul2000\workflow\base\SimpleWorkflowBehavior',
            // model attribute to store status
            'statusAttribute' => 'col_status',
            // workflow source component name
            'source' => 'my_workflow_source',
            'defaultWorkflowId' => 'MyWorkflow',
            'statusConverter' => null,
            'statusAccessor' => null,
            // Event Sequence Component Name
            'eventSequence'  => 'eventSequence',
            'propagateErrorsToModel' => false,
            'stopOnFirstInvalidEvent' => true,
        ];
    }
}
```

## The *WorkflowSource* Component

`@app/config/web.php`
```php
$config = [
    'components' => [
        'my_workflow_source' => [
          'class' => 'raoul2000\workflow\source\file\WorkflowFileSource',
          // Cache component name
          'definitionCache' => 'cache',
          // load workflow as PHP class from the @app/models/workflows namespace
          'definitionLoader' => [
              'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
              'namespace'  => '@app/models/workflows'
           ],
          // workflow provided by PHP class will be defined as a minimal array
          'parser' => 'raoul2000\workflow\source\file\MinimalArrayParser',           
           // we provide our own implementation for simple workflow
           // base objects
		   'classMap' => [
				self::TYPE_WORKFLOW   => 'my\custom\implementation\Workflow',
				self::TYPE_STATUS     => 'my\custom\implementation\Status',
				self::TYPE_TRANSITION => 'my\custom\implementation\Transition'
			]	
        ],
        // .. other app components here ..
	]
];	
```


To be continued ...

