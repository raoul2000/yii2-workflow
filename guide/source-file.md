# Workflow File Source Component

The workflow file source component reads workflow definitions from files, and provide Status, Workflow and Transition object.
It is not usually accessed directly but though the *SimpleWorkflowBehavior* who loads it by default.

To be able to handle various file formats, the *WorkflowFileSource* component relies on a modulable architecture where the task of
locating and loading a file is delegated to a class implementing the `WorkflowDefinitionLoader` interface. There are currently 3 types
of workflow definition loader available with yii2-workflow:

- `PhpClassLoader` : loads the workflow definition from a class that implemented the `IWorkflowDefinitionProvider` interface. This is 
**the default loader** used by the file source component.
- `PhpArrayLoader` : loads the workflow definition from a PHP file that must returns an array representing the workflow definition.
- `GraphmlLoader` : loads the workflow definition from a Graphml file.

The two first loader are somewhat equivalent in the way they expect to read workflow definition: they both expected to get a PHP array.
On the other side, the GraphmlLoader expects to read an XML file.

## PHP class Loader

Loads the workflow definition from a PHP class.

### Namespace : workflow location

By default the `PhpClassLoader`component loads workflows from the `app\models` namespace. 
So for example in the following delcaration, the default workflow associated with the *Post* model will be loaded from 
the class `app\models\MyWorkflow` : 

```php
namespace app\models;
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			'class' => '\raoul2000\workflow\base\SimpleWorkflowBehavior',
			'defaultWorkflowId' => 'MyWorkflow'
    	];
    }
}
```

If you need to change the default namespace value you have two options : the fast one and the not so fast one.

#### The magic alias

As the *PhpClassLoader* is the default loader used with the default source component, it is a common task to change the namespace value
used to load PHP classes. Consequently having to explicitely declare component just to change one configuration setting is too much work (and
we know good developpers are lazy). For this purpose the alias `@workflowDefinitionNamespace` is available to define globally the namespace value.

For instance, in you `index.php` file, declare this alias : 

```php
Yii::setAlias('@workflowDefinitionNamespace','app\\models\\workflows');
``` 

By doing so, all workflow definition classes will be loaded from the `app\models\workflows` namespace. Note that this alias overrides any specific
namespace configuration that you may have defined the *standard ways*.


#### Standard

In general if you need to change any configuration setting, you must explicitely declare it as an Yii2 application component and no rely
on *SimpleworkflowBehavior* to do it for you. 
In the example below, we are defining the source component using the default Id (*workflowSource*)
and set the namespace used by `PhpClassLoader` to the location where workflow definitions are supposed to be located (here *@app/models/workflows*).

```php
$config = [
    'components' => [
        'workflowSource' => [
          'class' => 'raoul2000\workflow\source\file\WorkflowFileSource',
          'definitionLoader' => [
	          'class' => 'raoul2000\workflow\source\file\PhpClassLoader',
	          'namespace'  => '@app/models/workflows'
           ]          
        ],
``` 

As you may have guessed, there is only one namespace per workflow source component so you are encouraged to locate all your workflows in the
same folder. In the case you must load workflows from various location, you should declare another workflow source component (one per namespace) but
remember that each workflow source component serves workflows from only one namespace (folder).
 


## PHP array Loader

Loads workflow definition for a PHP array stoed in a file.

First configure the workflow file source component to use the PHPArrayLoader. In this example, workflow definition files are assumed
to be located in `@app/models/workflows`.

```php
$config = [
    'components' => [
        'workflowSource' => [
          'class' => 'raoul2000\workflow\source\file\WorkflowFileSource',
          'definitionLoader' => [
	          'class' => 'raoul2000\workflow\source\file\PhpArrayLoader',
	          'path'  => '@app/models/workflows'
           ]          
        ],
```


Now if we want to define the definition for the workflow *post*, we just create the file *post.php* in the folder `@app/models/workflows`.

```php
return [
	'initialStatusId' => 'draft',
	'status' => [
		'draft' => [
			'transition' => ['publish','deleted'
			]
		],
		'publish' => [
			'transition' => ['draft','deleted']
		],
		'deleted' => [
			'transition' => ['draft']
		]
	]
];
``` 

## graphml document Loader

Loads workflow definition from a graphml file.

From the [The GraphML File Format](http://graphml.graphdrawing.org/)web site : 

> GraphML is a comprehensive and easy-to-use file format for graphs. It consists of a language core to describe the structural 
properties of a graph and a flexible extension mechanism to add application-specific data.

Graphml file can be generated by various graph design applications like [yEd](https://www.yworks.com/en/products_yed_download.html) (that I use).

Before being able to load a Graphml file, you must ensure that following requirements are completed :

- nodes labels are assigned to status Id and all nodes must have a label (i.e. all status must have an id)
- a custom property called *initialStatusId* must be added to the workflow and assigned with a valid status Id

```php
$config = [
    'components' => [
        'workflowSource' => [
          'class' => 'raoul2000\workflow\source\file\WorkflowFileSource',
          'definitionLoader' => [
	          'class' => 'raoul2000\workflow\source\file\GraphmlLoader',
	          'path'  => '@app/models/workflows'
           ]          
        ],
```

TBD
