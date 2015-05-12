# Workflow Source

The *Workflow Source* is a Yii2 component dedicated to read the persistent representation of a workflow and provide on demand, its memory 
representation in terms of PHP objects.

The main Workflow Source component included in the *SimpleWorkflow* package is `raoul2000\workflow\source\php\WorkflowPhpSource`. It is designed
to process workflow definition provided as regular PHP Arrays. 

Note that it is possible that in the future, other workflow source component are provided like for instance a *WorkflowDbSource* that would
read from a database. 

## Workflow Objects

The *SimpleWorkflow* manipulates objects to manage workflows. There are 3 basic types of objects that you will meet sooner or later. They are
all part of the `raoul2000\workflow\base` namespace:

- Status 
- Transition
- Workflow 

The main purpose of the *WorkflowPhpSource* component is to turn a PHP array (the workflow definition) into a set Status, Transition and Workflow 
objects. 

## Configuration

### Component registration

When the *SimpleWorkflowBehavior* is initialized, it tries to get a reference to the **Workflow Source Component** to use. By default
this component is assumed to have the id *workflowSource*. If no such component is available, *it will create one* using the *WorkflowPhpSource* 
class, and register it in the Yii2 application so to make it available to other *SimpleWorkflowBehavior*.

To summarize :
 
- **workflowSource** : default Id of the workflow source component used by the *SimpleWorkflowBehavior*
- **\raoul2000\workflow\source\php\WorkflowPhpSource** : default workflow source component type 

If for instance you want to use another Workflow Source Component instead of the default one, you must configure it like you would do for 
any other Yii2 component and keep the default Id.

```php
$config = [
    // ....
    'components' => [
        'workflowSource' => [
          'class' => '\my\own\component\SuperCoolWorkflowSource',
        ]
   // ...
``` 
With this configuration, all *SimpleWorkflowBehavior* are going to use your *SuperCoolWorkflowSource* to get Status, Transition and Workflow objects.

Another option is to mix Workflow Source Components and for instance use the default one with all models except for your Post model. To achieve this,
simply configure your custom Workflow Source Component under a custom Id.

```php
$config = [
    // ....
    'components' => [
    	// This is my custom Workflow Source Component with a custom id
        'mySuperSource' => [
          'class' => '\my\own\component\SuperCoolWorkflowSource',
        ]
   // ...
``` 

And then use the component *mySuperSource* as Workflow source for the Post model only :

```php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
			// Post will use a specific Workflow Source Component
			// All other models are using the default one
			'source' => 'mySuperSource'
    	];
    }
}
```


### Namespace : workflow location

By default the `WorkflowPhpSource`component is loading workflows from the `app\models` namespace. 
So for example in the following delcaration, the default workflow associated with the *Post* model will be loaded from 
the class `app\models\MyWorkflow` : 

```php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
			'defaultWorkflowId' => 'MyWorkflow'
    	];
    }
}
```
If you need to change the default namespace value (and in general if you need to change any configuration setting), you must
declare it as an Yii2 application component. In the example below, we are defining the source component using the default Id (*workflowSource*)
and set the namespace to the location where workflow definitions are supposed to be located.

```php
$config = [
    'components' => [
        'workflowSource' => [
          'class' => '\raoul2000\workflow\source\php\WorkflowPhpSource',
          'namespace' => '\app\models\workflows'
        ]
``` 

As you may have guessed, there is only one namespace per workflow source component so you are encouraged to locate all your workflows in the
same folder. In the case you must load workflows from various location, you should declare another workflow source component (one per namespace) but
remember that each workflow source component serves workflows from only one namespace (folder).
 

## Implementing Your Own Workflow source

You can create your own Workflow Source Component by implementing the `\raoul2000\workflow\source\IWorkflowSource` interface.
 

