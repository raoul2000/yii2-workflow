
This page contains a set of *SimpleWorkflow* snippets that you may have to deal with someday. 

## Initializing The *SimpleWorkflowBehavior* behavior

Don't be scared ! most of the time you will not have to use **all** the configuration settings like 
on the following example.

`@app/models/Post.php`

```php
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
        return [
        	[
	            'class'                    => '\raoul2000\workflow\base\SimpleWorkflowBehavior',
	            
	            // model attribute to store status 
	            'statusAttribute'          => 'col_status',
	            
	            // workflow source component name 
	            'source'                   => 'my_workflow_source',
	            
	            'defaultWorkflowId'        => 'MyWorkflow',
	            'statusConverter'          => null,
	            'statusAccessor'           => null,
	            
	            // Event Sequence Component Name
	            'eventSequence'            => 'eventSequence',
	            
	            'propagateErrorsToModel'   => false,
	            'stopOnFirstInvalidEvent'  => true,
			]
        ];
    }
}
```

## Initializing The *WorkflowSource* Component

Again, no worries ! Usually the default Workflow Source component will be just fine to use and you'll probably
never have to create such a component.

`@app/config/web.php`

```php
$config = [
    'components' => [
        'my_workflow_source' => [
          'class'            => 'raoul2000\workflow\source\file\WorkflowFileSource',
          
          // Cache component name
          'definitionCache'  => 'cache',
          
          // load workflow as PHP class from the @app/models/workflows namespace
          'definitionLoader' => [
              'class'      => 'raoul2000\workflow\source\file\PhpClassLoader',
              'namespace'  => '@app/models/workflows'
           ],
           
          // workflow provided by PHP class will be defined as a minimal array
          'parser'           => 'raoul2000\workflow\source\file\MinimalArrayParser',          
           
           // we provide our own implementation for simple workflow base objects
		   'classMap'        => [
				self::TYPE_WORKFLOW   => 'my\custom\implementation\Workflow',
				self::TYPE_STATUS     => 'my\custom\implementation\Status',
				self::TYPE_TRANSITION => 'my\custom\implementation\Transition'
			]	
        ],
        // .. other app components here ..
	]
];	
```
## Insert a Model in A Workflow

```php
$post1 = new Post();
// the safe way : insert into default workflow (defaultWorkflowId)
$post1->enterWorkflow();				// the status change is done here
$post1->save();

$post2 = new Post();
// the safe way : insert into specific workflow
$post2->enterWorkflow('myWorkflow');	// the status change is done here
$post2->save();

// the not-so-safe way
$post3 = new Post();
$post3->status = 'Post/draft'; 			// must be initial status Id
$post3->save();							// the status change is done here

```

## Getting The Current Status Of a Model 


```php
$post = Post::findOne(['id' => 42]);

if( $post->hasWorkflowStatus()) {

	// the safe way	
	echo 'status id    = ' . $post->getWorkflowStatus()->getId();
	echo 'status label = ' . $post->getWorkflowStatus()->getLabel();
	echo 'status color = ' . $post->getWorkflowStatus()->getMetadata('color');

	// the not so safe way
	echo 'status = ' . $post->status;
}
```

## Changing Status


```php
$post1 = Post::find(['id'=>42]);

// Two steps : assignment + call to save() 
$post1->status = 'Post/published';
$post1->save();							// the status change is done here

$post2 = Post::find(['id'=>42]);

// One step : call to sendToStatus()
$post2->sendToStatus('Post/published'); 	// the status change is done here
$post->save();
```

## Leaving A Workflow

```php
$post1 = Post::find(['id'=>42]);

// Two steps : assignment + call to save()
$post1->status = null;
$post1->save();							// the status change is done here

$post2 = Post::find(['id'=>42]);

// One step : call to sendToStatus()
$post2->sendToStatus(null);				// the status change is done here
$post->save();
```

## Comparing Two Statuses

```php
// the usual way : compare status ids
$post->getWorkflowStatus()->getId() == $otherPost->getWorkflowStatus()->getId();

// the lazy way
$post->statusEquals($otherPost->getWorkflowStatus());
```

## Getting The WorkflowSource Used By A Model

```php
$workflowSource = $post->getWorkflowSource();
```
Use a reference to the Workflow Source to access the workflow directly (not through the model). 

## Looping on all Next Statuses

### ask the Status Object

```php
$post = Post::find(['id'=>42]);

if( $post->hasWorkflowStatus()) {

	// let's ask the Status object then
	$transitions = $post
		->getWorkflowStatus()
		->getTransitions();
		
	foreach( $transitions as $transition ) {
		echo $transition->getEndStatus()->getId();
	}	
}
```

### Through the Workflow Source

```php
$post = Post::find(['id'=>42]);

if( $post->hasWorkflowStatus()) {
	
	// ask the WorkflowSource
	$transitions = $post
		->getWorkflowSource()
		->getTransitions($post->getWorkflowStatus()->getId());
		
	foreach( $transitions as $transition ) {
		echo $transition->getEndStatus()->getId();
	}	
}
```
