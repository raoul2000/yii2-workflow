#version 0.0.20
- remove unnecessary use in SimpleWorkflowBehavior
- test against StatusInterface and not anymore Status
- update doc
- minor change in the `WorkflowFileSource->parseStatusId()` method
- fix : restore model errors after `getNextStatuses()`

**important notice**
Because Yii2 ActiveRecord implementation does not fire any event when the method [refresh()](http://www.yiiframework.com/doc-2.0/yii-db-baseactiverecord.html#refresh%28%29-detail) 
is called, the attached `SimpleWorkflowBehavior` has no
way to know that the status attribute may have change and that the actual model status maintained internally by the behavior, needs to be updated.
A [feature request](https://github.com/yiisoft/yii2/issues/9604) has been created but until it gets accepted and implemented you must
call `initStatus()` after each call to `refresh()`. For example : 

```php
$post->refresh();
$post->initStatus();
```
Note that calling `initStatus()` is usually never done by the developer but by the behavior itself when specific `ActiveRecords` event are fired.

#version 0.0.19
- fix GraphmlLoader to not only process ShapeNode elements, and use node label in yEd as Status Id.

#version 0.0.18
- add method `WorkflowInterface->getAllStatuses()` allowing to get a list of all statuses belonging to a workflow, 
using the workflow instance.

You can now write : 

```php
// assuming our $post has a status let's get a list of all statuses in the current workflow
$allStatuses = $post->getWorkflow()->getAllStatuses();
```
*This feature extends the one implemented in version 0.0.11*

#version 0.0.17
- add method `StatusInterface->isInitialStatus()` to test if a status instance is the initial status of the workflow it belongs to.
- add argument `$includeCurrent` in `WorkflowHelper::getNextStatusListData(...)`. When TRUE the current model status is added to the returned array. 
When FALSE (default) only next statuses are included.
- rollback previous change : `$emptyStringAsNull` is removed. The behavior now applies *empty()* to detect unset status 

#version 0.0.16
- add the configuration setting `$emptyStringAsNull` to *SimpleWorkflowBehavior*. 

When TRUE, the status attribute is considered as null if it contains an empty string
If set to FALSE this is developer responsability to nullify the status attribute value, 
because an empty string is considered as an invalid status id.

#version 0.0.15
- add *default Event*

The Default event **is not related to the configured event sequence**, it's a built-in event, fired by the *SimpleWorkflowBehavior*,
in its before and after form, whenever a model changes status.  Like any other event, it is an instance of the WorkflowEvent with a name set to :

- SimpleWorkflowBehavior::EVENT_BEFORE_CHANGE_STATUS
- SimpleWorkflowBehavior::EVENT_AFTER_CHANGE_STATUS

Depending on the value returned by *getStartStatus()*, *getEndStatus()* it is possible to identify the kind of transition that is being 
performed by the model :

- *getStartStatus() == null* : the model is **entering** into the workflow through the initial status ( returned by *getEndStatus()* )
- *getEndStatus() == null* : the model is **leaving** the workflow from status returned by *getStartStatus()*.

In both cases, a call to *getTransition()* returns NULL : entering or leaving a workflow is not considered as a transition.
 
#version 0.0.14
- improve leave workflow management

The action to **delete** the owner model is now considered as *leaving the workflow* : the *leave workflow* event
sequence is fired. Previously, the only way for a model to leave a workflow was by assigning NULL to the status attribute and saving
the model (or by calling `sendToStatus(null)`); 

#version 0.0.13
- the *SimpleWorkflowBehavior* can now be safely attached to any object that inherits from \yii\base\Object.

**warning** : The *SimpleWorkflowBehavior* has been first designed to be attached to an `ActiveRecord` instance and thus integrates in the life cycle
of such objects. By installing event handlers on various `ActiveRecord` events, it automatically handles status persistence. If the behavior
is attached to another type of object, the developer must understand and (possibly) implement all the features that otherwise would be available by default.

#version 0.0.12
- **add status conversion map setter** to the class `raoul2000\workflow\base\StatusIdConverter`. The map is still required by the constructor
but it can be updated at runtime using the `setMap()` method. (see [dynamic maps for status conversion issue](https://github.com/raoul2000/yii2-workflow/issues/9)) 
- Both status converter and status accessor components can now be configured as an object instance. 

For example, assuming that variable `$myConverter` contains a reference to an object instance 
that implements `raoul2000\workflow\base\StatusIdConverter`, you can now write :

```php
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			[
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    			'statusConverter' => $myConverter
    		]
    	];
    }
}
```
In the previous version it was only possible to initialize the `statusConverter` parameter to a string, representing the id of 
a component registered in `Yii::$app`.

This also applies to `statusAccessor` parameter.

- **lazy component initialization** for status converter and status accessor. If configured, these component are actually initialized (assigned)
and validated when accessed for the first time.
- add **cache** to `WorkflowFileSource` component. If set, the `definitionCache` parameter defines the cache object to use.

To initialize the `WorkflowFileSource` component to use a cache : 

```php
$config = [
    'components' => [
        'workflowSource' => [
          'class' => 'raoul2000\workflow\source\file\WorkflowFileSource',
          'definitionCache' => [
          		'class' => 'yii\caching\FileCache',
          ],
        ],
``` 
#version 0.0.11
- update unit tests
- check interface implemented instead of class
- remove *StatusInterface.addTransition()* method
- add workflow source component to WorkflowBaseObject constructor. Update Status and WorkflowInterface to enable accessing 
workflow items from Status or Workflow instances.

For example it is now possible to do the following : 

```php
// let's get a status instance from our Post model
$status = $post->getWorkflowStatus();

// get an array containing out going Transitions objects 
$status->getTransitions();

// get the parent workflow
$status->getWorkflow();

// get the initial status of the parent workflow
$status->getWorkflow()->getInitialStatus();
```

#version 0.0.10
**Massive refactoring of the workflow source component architecture** to allow loading workflow definition from virtually
*any* file (and not only PHP class).

**WARNING** : this modification may break back compatibility so pay attention to the following major changes : 

- namespace `raoul2000\workflow\source\php` has been renamed `raoul2000\workflow\source\file`
- class `WorkflowPhpSource` has been renamed `WorkflowFileSource`
- the `namespace` configuration setting has been removed from the source component
- add configuration attribute `definitionLoader` to `WorkflowFileSource`
- `IWorkflowDefinitionProvider` has been moved to namespace `raoul2000\workflow\source\file`

The `WorkflowFileSource` component is dedicated to load workflow definition from *any file*, for this reason, 
it relies on a *WorkflowDefinitionLoader* component that is used to :

1. locate the file containing the workflow definition
2. load the workflow definition
3. convert it into a PHP array having the structure expected by the `WorkflowFileSource` component.

By Default, the *WorkflowFileSource* component uses a `PhpClassLoader` instance, maintaining this way the default feature that 
allows a workflow definition to be retrieved from a PHP class. The default namespace remains `app\models` and if you want to change
it, you must explicitely declare the Workflow source component with appropriate settings (like required in the previous versions).

In the example below, we declare a Workflow Source component, using the `PhpClassLoader` and with a customized namespace attribute 
(in our example, workflow definition classes are located in `app\models\workflows`).

```php
$config = [
    // ....
    'components' => [
        'workflowSource' => [
          'class' => '\raoul2000\workflow\source\file\WorkflowFileSource',
          'definitionLoader' => [
              'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
              'namespace' => 'app\models\workflows'
		  ]
   // ...
```





#version 0.0.9
- add *propagateErrorsToModel* configuration setting to the *SimpleWorkflowBehavior*

If TRUE, all errors that may be registred on an invalidated 'before' event, are assigned to
the status attribute of the owner model (allowing to display them to the user).

Example : 
```php
/**
 * This is the model class for table "Post".
 *
 * @property integer $id
 * @property string $name
 * @property string $status
 */
class Post extends \yii\db\ActiveRecord
{
	public function init() 
	{
		$this->on(
		    WorkflowEvent::beforeEnterStatus('Post/to-publish'),
		    function ($event) {
		        // test if the model can enter in status 'publish'
		        // ...
		        if( $error ) {
		        	$event->invalidate('the post can\'t be published');
		        }
		    }
		);	
	}
	
    public function behaviors()
    {
    	return [
			[
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    			'propagateErrorsToModel' => true
    		]
    	];
    }
}

$post = Post::findOne(1);
$post->status = 'Post/to-publish';
if( $post->save() == false ) {
	echo 'error : '.$item->getFirstError('status');	 // the post can\'t be published
}
```

- add *stopOnFirstInvalidEvent* configuration setting to the *SimpleWorkflowBehavior*
	
if TRUE, all "before" events are fired event if one of them is invalidated by an attached handler.
When FALSE, the first invalidated event interrupts the event sequence.
  
#version 0.0.8
- **WARNING** : relocate WorkflowHelper, now in namespace `raoul2000\workflow\helpers`
- add helper function getAllStatusListData()

**getAllStatusListData()** returns an associative array containing all statuses that belong to a workflow.
The array returned is suitable to be used as list data value in (for instance) a dropdown list control.
 
Usage example : assuming model Post has a *SimpleWorkflowBehavior* the following code displays a dropdown list
 containing all statuses defined in $post current the workflow : 
 
```php
echo Html::dropDownList(
		'status',
 		null,
 		WorkflowHelper::getAllStatusListData(
 			$post->getWorkflow()->getId(),
 			$post->getWorkflowSource()
 		)
)
```

#version 0.0.7
- update doc
- rename `SimpleWorkflowBehavior::_createTransitionItems` to `SimpleWorkflowBehavior::createTransitionItems`
- add *autoInsert* feature.

**this feature is not enabled** 

The *autoInsert* feature allows to automatically insert a model into a workflow when the model is created and only
if there is no previous status set.

Example : 
```php
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			[
    			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
    			'autoInsert' => true,
    			'defaultWorkflowId' => 'MyWorkflow'
    		]
    	];
    }
}

$post = new Post();
echo $post->getWorkflowStatus()->getId();	// output : MyWorkflow/new
```

Note that no event is fired when a model is auto-inserted into a workflow.
If *autoInsert* is a string, it must be the ID of the workflow where the model will be automatically inserted to. 
If *autoInsert* is a TRUE boolean, the model is inserted into its default workflow.

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