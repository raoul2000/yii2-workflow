# Workflow Source

The *Workflow Source* is a Yii2 component dedicated to read the persistent representation of a workflow and provide on demand, its memory 
representation in terms of PHP objects.

The main Workflow Source component included in the *SimpleWorkflow* package is `raoul2000\workflow\source\php\WorkflowPhpSource`. It is designed
to process workflow definition provided as regular PHP Arrays.  


## Workflow Objects

The *SimpleWorkflow* manipulates objects to manage workflows. There are 3 basic types of objects that you will meet sooner or later. They are
all part of the `raoul2000\workflow\base` namespace:

- Status 
- Transition
- Workflow 

The main purpose of the *WorkflowPhpSource* component is to turn a PHP array (the workflow definition) into a set Status, Transition and Workflow 
objects. 

## Configuration

When the *SimpleWorkflowBehavior* is initialized, it tries to get a reference to the **Workflow Source Component** to use. By default
this component is assumed to have the id *workflowSource*. If no such component is available, *it will create one* using the *WorkflowPhpSource* 
class, and register it in the Yii2 application so to make it available to other *SimpleWorkflowBehavior*.

To summarize :
 
- **workflowSource** : default Id of the workflow source component used by the *SimpleWorkflowBehavior*
- **WorkflowPhpSource** : default workflow source component type 

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
<?php
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

## Implementing Your Own Workflow source

You can create your own Workflow Source Component by implementing the `\raoul2000\workflow\source\IWorkflowSource` interface.
 

