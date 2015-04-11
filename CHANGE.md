#version 0.0.6
- add support for multi workflow : more than one workflow can be attached to a model.

The first declared *SimpleWorkflow* behavior handles the **main workflow** and all other *SimpleWorkflow* behavior handle **secondary** workflows.
All *SimpleWorkflow* behavior related to *secondary* workflow must provide configuration settings for :

- statusAttribute
- defaultWorkflowId

Moreover, *SimpleWorkflow* behavior related to *secondary* workflows must NOT be declared as anonymous : a behavior name is required.

Example : *Item08Workflow1* is the primary workflow, *Item08Workflow2* is the secondary workflow
```php
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
    		// The main workflow is ALWAYS declared first
    		[
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    		],    		
    		// the secondary workflow : note that it is declared as 'w2'
    		'w1' => [
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    			'statusAttribute' => 'status_ex',
    			'defaultWorkflowId' => 'SecondaryWorkflow'
    		]
    	];
    }
}
```

To access *SimpleWorkflow* methods related to the main workflow, you can use the usual way. To access *SimpleWorkflow* methods related to the 
secondary workflow, you must use the behavior name.

In both case you can also use direct attribute assignement.

Example
```php
$p = new Post();
// direct attribute assignement
$p->status = 'PostWorkflow/draft';
$p->status_ex = 'SecondaryWorkflow/ready';
// both transitions are committed now
$p->save(); 

// SimpleWorkflowBehavior methods
$o = new Post();
// applied on the main workflow only
$o->enterWorkflow();	 
// applied on the secondary workflow 
$p->getBehavior('w1')->enterWorkflow();
```

See unit test `tests\unit\workflow\behavior\MultiWorkflowTest` for more example.

#version 0.0.5
- add MinimalArrayParser for workflow definition PHP arrays provided as for instance : 

```php
[
	'draft'     => ['ready', 'delivered'],
	'ready'     => ['draft', 'delivered'],
	'delivered' => ['payed', 'archived'],
	'payed'     => ['archived'],
	'archived'  => []
]
```
The *initialStatusId* is the first status defined (here *draft*)

#version 0.0.4
- add tests for raoul2000\workflow\source\php\DefaultArrayParser
- minor fix in DefaultArrayParser

#version 0.0.3
- externalize array parser to normalize workflow php array definition

#version 0.0.2
- change regex for status and workflow ID pattern: now `/^[a-zA-Z]+[[:alnum:]-]*$/`
- Improve WorkflowPhpSource parsing of a workflow defined as a PHP array.

```php
[
	'initialStatusId' => 'A',
	'status' => [
		'A' => [
			'transition' => 'A,  B'
		],
		'B' => [
			'transition' => ['A','C']
		],
		'C'
	]
]
```

# version 0.0.1
- Initial import