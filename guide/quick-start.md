# Quick Start 

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist raoul2000/yii2-workflow "*"
```

or add

```
"raoul2000/yii2-workflow": "*"
```

to the require section of your `composer.json` file.

## Create A Workflow

A workflow is defined as a PHP class that implements the raoul2000\workflow\base\IWorkflowDefinitionProvider interface. This interface
defines the *getDefinition()* method that must return an array representing the workflow. 

Let's define a very *simple workflow* that will be used to manage posts in a basic blog system.

<img src="images/workflow1.png"/>

Here is the PHP class that implements the definition for ou workflow :

*PostWorkflow.php in @app/models*
```php
<?php
namespace app\models;

class PostWorkflow implements raoul2000\workflow\base\IWorkflowDefinitionProvider 
{
	public function getDefinition() {
		return [
			'initialStatusId' => 'draft',
			'status' => [
				'draft' => [
					'transition' => ['publish','deleted']
				],
				'publish' => [
					'transition' => ['draft','deleted']
				],
				'deleted' => [
					'transition' => ['draft']
				]
			]
		];
	}
}
```
## Link to the Model

Now let's have a look to our Post model. To be able to save the status of a post, we must use a dedicated column of type STRING. 
In this example, the column is named *status*.

The last step is then to associate the workflow definition with posts models. To do so we must declare the *SimpleWorkflowBehavior* behavior in the Post model class.

 
```php
<?php

namespace app\models;
/**
 * @property integer $id
 * @property string $title
 * @property string $body
 * @property string $status column used to store the status of the post
 */
class Post extends \yii\db\ActiveRecord
{
    public function behaviors()
    {
    	return [
			\raoul2000\workflow\base\SimpleWorkflowBehavior::className()
    	];
    }
    // ...
```

And that's it ! We are ready to play with *SimpleWorkflowBehavior*.

## Use it !

Now that we are all setup, we can use the *SimpleWorkflowBehavior* methods to set/get the status of our posts : the *SimpleWorkflowBehavior* will 
take care that the post doesn't reach a status where it is not supposed to go, depending on the workflow definition that we have created.

```php
	$post = new Post();
	$post->status = 'draft';
	$post->save();
	echo 'post status is : '. $post->workflowStatus->label;
```
This will print the following message :

	post status is : Draft
 
If you create a new post, set its status to *publish* and try to save it, the following exception is thrown :

	Not an initial status : PostWorkflow/publish ("PostWorkflow/draft" expected)

That's because in your workflow definition the **initial status** is  set to *draft* and not *publish*. 

## What's next ?

This is just one way of using the *SimpleWorkflowBehavior* but there's much more and hopefully enough to assist you
in your workflow management inside your Yii2 web app.

