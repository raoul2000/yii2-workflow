# Workflow Creation

In the first release, a workflow is defined as an associative PHP array that is returned by a class that
implements the *raoul2000\workflow\base\IWorkflowDefinitionProvider* interface. In this chapter we are going 
to learn how to create this array and thus define the craziest workflow ever !! ... humm ok, maybe not really.

## Identifiers

Identifiers used for both statuses and workflows are `strings` that must start with a letter followed by 
alpha numerical characters. If you need a delimiter, you can use the minus (-) characters.

Example :

- valid Ids : 'post', 'draft', 'PostWorkflow', 'My-workflow', 'published'
- invalid Ids : 'my workflow', 'draft mode', '01workflow', 'post_workflow'

Note that status Ids are not aimed to be displayed to the user. For this prupose, we'll see below that you can define
a *label* property. 

#### Absolute Status Ids

An absolute status Id is a composite value that includes the id of the workflow that owns the status. The characters slash (/) is then
used to separate both ids.

For example, if we have a status with "draft" that belong to the workflow 'post', the absolute status Id is 'post/draft'.

Most of the time, you will not use absolute status id, but note that internally they are required by the *WorkflowPhpSource* component.


## Workflow Provider

A *Workflow provider* is a class that implements the *IWorkflowDefinitionProvider* interface 
which defines a single method : `getDefinition()`. This method must return the description of our workflow as a PHP array.

*PostWorkflow.php in @app/models*
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

## Metadata

Ok, we are now able to create workflows ! We can define statuses and transitions between them. As you you can see, the minimum attributes for a status is
its *id* and optionally we can set a *label*, but that's all. Well, that's not a lot. What if I need to add more properties to my statuses ? Like for instance
it could be nice to associate a color with each status, and display this color to the user (users like colors). The solution is *metadata*.

The *metadata* allows you to add almost any attribute not only to statuses, but also to workflow and transition. Let's see that on an example where we are
going to add a *color* and an *icon* metadata to the *published* status. 

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

As an example we will use our Post workflow desinged earlier to manage our publishing plateform web app. 

<img src="post-workflow-2.png" alt="post workflow"/>

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
					'transition' => ['correction']
				],
				'correction' => [
					'transition' => ['draft','ready']
				],
				'ready' => [
					'transition' => ['draft', 'correction', 'published']
				],
				'published' => [
					'transition' => ['ready', 'archived']
				],
				'archived' => [
					'transition' => ['ready']
				]
			]
		];
	}
}
```

  
