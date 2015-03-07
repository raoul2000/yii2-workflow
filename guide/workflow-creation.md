# Workflow Creation

In the first release, a workflow is defined as an associative PHP array that is returned by a class that
implements the *raoul2000\workflow\base\IWorkflowDefinitionProvider* interface. In this chapter we are going 
to learn how to create this array and thus define the craziest workflow ever !! ... humm ok, maybe not really.

## Identifiers

TBD

## Workflow Provider

A *Workflow provider* is a class that is contains the method `getDefinition()` who returns an array. This array
is the description of our workflow

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

Let's see how this workflow definition array must be defined.

## The Workflow

The PHP array defining a workflow is an associative array that must contains 2 keys : **initialStatusId** and **status**.

- *initialStatusId* : `string` that represent the ID of the initial status
- *status* : `array` associative array defining each status that belong to the workflow.

```php
[ 
	'initialStatusId' => 'draft',
	'status' => [
		// definition of statuses
	]
]
```

## Status Definition

TBD

The status definition is an associative array that may contain 2 keys :

- *transition* :
- *label* : 