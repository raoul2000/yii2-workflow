# Workflow Creation : PHP Source

The way a workflow is defined depends on the [Workflow Source](concept-source.md) component we will be using. By default
the *SimpleWorkflowBehavior* is using an instance of the class `WorkflowFileSource` defined in the namespace `raoul2000\workflow\source\file`.
This component is able to read a workflow definition out of an associative PHP array. In this chapter we are going to learn
how to create this array and thus define the craziest workflow ever !! ... humm ok, maybe not really....

## Identifiers

Identifiers used for both statuses and workflows are case sensitive `strings` that must start with a letter followed by
alpha numerical characters. If you need a delimiter, you can use the minus (-) characters.

Example :

- valid Ids : 'post', 'draft', 'PostWorkflow', 'My-workflow', 'published'
- invalid Ids : 'my workflow', 'draft mode', '01workflow', 'post_workflow'

Note that status Ids are not aimed to be displayed to the user. For this prupose, we'll see below that you can define
a *label* property accessible through the `getLabel()` method implemented by the Status class.

#### Status Ids : absolute / relative

An **absolute status Id** is a composite value that includes the id of the workflow that owns the status. The characters slash (/) is
used to separate both ids. For example, if we have a status with "draft" that belong to the workflow 'PostWorkflow', the absolute status Id is 'PostWorkflow/draft'.

A **relative status id** is then simply a value that identifies a status out of any workflow. Based on the previous example, the relative status id would be "draft"

Most of the time you will not have to deal with absolute status id simply because the *SimpleWorkflowBehavior* will try to turn it into its absolute form with the help of the current context. Let's see that on an example :

```php
$post = new Post();
$post->status = 'published';
$post->save();
echo $post->status;
```

The output is :

```
PostWorkflow/published
```

In this case, the absolute status id has been set to *PostWorkflow/published* because the default workflow Id for the Post model is *PostWorkflow* and this workflow contains a status 'published' (and this 'published' status is also configured as a *initial status* for the workflow *PostWorkflow*).

Actually, the only cases where you would need to use an absolute status id would be :

- when you want your model to leave a workflow and go into another workflow (inter-workflow transition)
- configure a default status that doesn't belong to the default workflow


## Workflow Provider

By Default the *WorkflowFileSource* component reads workflow definition from *Workflow provider* objects. This type of object  implements
the *IWorkflowDefinitionProvider* interface which defines a single method : `getDefinition()`.
This method must return the actual description of our workflow as a PHP array.

`PostWorkflow.php in @app/models`

```php
namespace app\models;
class PostWorkflow implements raoul2000\workflow\base\IWorkflowDefinitionProvider
{
	public function getDefinition() {
		return [
			// the workflow definition
		];
	}
}
```

Let's see how this workflow definition array must be structured.

## The Workflow

The PHP array defining a workflow is an associative array that must contains 2 keys : **initialStatusId** and **status**.

- *initialStatusId* : `string` that represents the ID of the initial status
- *status* : `array` associative array defining each status that belong to the workflow.

```php
[
	'initialStatusId' => 'draft',
	'status' => [
		// definition of statuses
	]
]
```

## Status List Definition

The status list definition is an associative array where keys are status Ids and values are status definitions.
If a status doesn't need any particular definition, it can be defined directly as a string value.

In the example below, both *draft* and *pusblised* have a specific definition, but *archived* doesn't.

```php
[
	'initialStatusId' => 'draft',
	'status' => [
		'draft'     => [ // single status definition ]
		'published' => [ // single status definition ]
		'archived'
	]
]
```

## Single Status Definition

A Single Status Definition is an associative array that may contains 2 specific keys : **transition** and **label**

- *transition* : `array|string` list of ids for all statuses that can be reached
- *label* : `string` user friendly name. If not set, the label is automatically created from the status Id.

```php
[
	'initialStatusId' => 'draft',
	'status' => [
		'draft'     => [
			'label'      => 'Draft State'
			'transition' => // transitions definition
		]
	]
]
```


## Transition Definition

A Transition Definition is an array or a string defining the list of status that can be reached from the current status.
In the example below, we are defining a workflow with following transitions:

- draft -> published
- published -> draft
- published -> archived

As you can see, there is no transition that leaves the status *archived*. Once an item reaches this status it will never
move to another status again: *archived* is called a **final status**.

```php
[
	'initialStatusId' => 'draft',
	'status' => [
		'draft'     => [
			'label'      => 'Draft State'
			'transition' => 'published'
		],
		'published' => [
			'transition' => ['draft','published']
		],
		'archived'
	]
]
```

Alternatively you can also use a comma separated list of status Id to define a transition. For example, transitions for the *published* status above ,
could also be written this way :

```php
'published' => [
	'transition' => 'draft, published'
]
```

In the case you need to configured a transition that targets another workflow, you must use a status id in its  **absolute** form. For example :

```php
'published' => [
	'transition' => 'draft, anotherWorkflow/toPrint'
]
```


## Metadata

Ok, we are now able to create workflows and we can define statuses and transitions between those statuses. As you  can see, the minimum attributes for a status is its *id* and optionally we can set a *label*, but that's all. Well, that's not a lot. What if I need to add more properties to my statuses ? Like for instance it could be nice to associate a color with each status, and display this color to the user (users like colors). The solution is *metadata*.

The *metadata* allows you to add almost any attribute not only to statuses, but also to workflow and transition. Let's see that on an example where we are going to add a *color* and an *icon* metadata to the *published* status.

```php
'published' => [
	'metadata' => [
		'color' => 'green',
		'icon'  => 'glyphicon glyphicon-pencil'
	]
]
```

Later on we will be able to retrieve these value of course, and use them the way we want (for instance with a nice and colorful display).

## Example

As an example we will use our Post workflow designed earlier to manage our publishing platform web app.

![images/post-workflow-2.png](images/post-workflow-2.png)

Below is the definition of this workflow ready to be used by the *SimpleWorkflow* behavior.

```php
namespace app\models;

class PostWorkflow implements raoul2000\workflow\base\IWorkflowDefinitionProvider
{
	public function getDefinition() {
		return [
			'initialStatusId' => 'draft',
			'status' => [
				'draft' => [
					'label'      => 'Draft Document',
					'transition' => ['correction']
					'metadata'   => [
						'color' => 'yellow'
					]
				],
				'correction' => [
					'transition' => ['draft', 'ready'],
					'metadata'   => [
						'color' => 'grey'
					]
				],
				'ready' => [
					'transition' => ['draft', 'correction', 'published'],
					'metadata'   => [
						'color' => 'blue'
					]
				],
				'published' => [
					'transition' => ['ready', 'archived'],
					'metadata'   => [
						'color' => 'green'
					]					
				],
				'archived' => [
					'transition' => ['ready'],
					'metadata'   => [
						'color' => 'black'
					]						
				]
			]
		];
	}
}
```
