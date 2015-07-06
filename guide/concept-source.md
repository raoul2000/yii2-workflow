# Workflow Source

The *Workflow Source* is a Yii2 component dedicated to read the persistent representation of a workflow and provide on demand to the
*SimpleWorkflowBehavior*, its memory representation in terms of PHP objects.

The main Workflow Source component included in the *SimpleWorkflow* package is `raoul2000\workflow\source\file\WorkflowFileSource`. It is designed
to process workflow definition stored in a file as a regular PHP Array, a PHP class definition or a Graphml file ( for a detail description, refer
to [Workflow File Source Component](source-file.md)).

Note that it is possible that in the future, other workflow source component are provided like for instance a *WorkflowDbSource* that would
read from a database. 

## About Workflow Objects

The *SimpleWorkflow* manipulates objects to manage workflows. There are 3 basic types of objects that you will meet sooner or later. They are
all part of the `raoul2000\workflow\base` namespace:

- `Status` : implements a status in a workflow
- `Transition` : implemented a directed transition between 2 statuses
- `Workflow` : implement a collection of statuses and transitions

The main purpose of a Workflow Source component is to turn a workflow definition into a set of Status, Transition and Workflow objects. 


## Component registration

When the *SimpleWorkflowBehavior* is initialized, it tries to get a reference to the *Workflow Source Component* to use. By default
this component is assumed to have the id **workflowSource**. If no such component is available, the *SimpleWorkflowBehavior* will **create one**,
with the type `raoul2000\workflow\source\file\WorkflowFileSource` (default) and registers it in the Yii2 application, so to make it 
available to other instances of *SimpleWorkflowBehavior*.

This implies that, unless specified otherwise, by default, all *SimpleWorkflowBehavior* are sharing **the same Workflow Source component**.

If you're not familiar with "application Component", please refer to the "[Definitive Guide to Yii2](http://www.yiiframework.com/doc-2.0/guide-structure-application-components.html)"

To summarize :
 
- **workflowSource** : default Id of the workflow source component used by the *SimpleWorkflowBehavior*
- **\raoul2000\workflow\source\file\WorkflowFileSource** : default workflow source component type 

If for instance you want to use another Workflow Source Component instead of the default one, you must configure it like you would do for 
any other Yii2 component and use the expected default Id.

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

Another option is to mix Workflow Source Components and for instance use the default one with all models except for a particular one. To achieve this,
simply configure your custom Workflow Source Component under a custom Id. Let's see that on an example: 

> Let's assume that you have developped a super cool workflow source component, able to read workflow definition from a satelite data stream, live
from deep outer space (if you did so, pull requests are welcome !!). You want to use this source only for the *SpaceShip* model in your app, leaving all other models
with the default source (PHP class). 

To do so, first declare your workflow source as a Yii2 component : 

```php
$config = [
    // ....
    'components' => [
    	// declare your source component under a custom id 
        'mySpaceSource' => [
          'class' => '\my\own\component\AlienWorkflowSource',
        ]
   // ...
``` 

And then use the component *mySpaceSource* as Workflow source for the *SpaceShip* model only :

```php
namespace app\models;
class SpaceShip extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => \raoul2000\workflow\base\SimpleWorkflowBehavior::className(),
			// SpaceShip will use a specific Workflow Source Component
			// All other models are using the default one
			'source' => 'mySpaceSource'
    	];
    }
}
```


## Implementing Your Own Workflow source

You can create your own Workflow Source Component by implementing the `\raoul2000\workflow\source\IWorkflowSource` interface.

 

