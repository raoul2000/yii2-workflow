# yii2-workflow

![Alt text](https://travis-ci.org/raoul2000/yii2-workflow.svg?branch=master "Optional title") 
[![Latest Stable Version](https://poser.pugx.org/raoul2000/yii2-workflow/v/dev.svg)](https://packagist.org/packages/raoul2000/yii2-workflow)
[![Total Downloads](https://poser.pugx.org/raoul2000/yii2-workflow/downloads.svg)](https://packagist.org/packages/raoul2000/yii2-workflow) 



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

# Quick Start 

## Configuration

For this "*Quick start Guide*" we will be using default configuration settings, but note that *yii2-workflow* is designed to be highly
flexible so to adapt to a lot of execution contexts... well at least that was my goal.

## Create A Workflow
 
A workflow is defined as a PHP class that implements the `\raoul2000\workflow\base\IWorkflowDefinitionProvider` interface. This interface
declares the *getDefinition()* method that must return an array representing the workflow. 

Let's define a very *simple workflow* that will be used to manage posts in a basic blog system.

<img src="guide/images/workflow1.png"/>

Here is the PHP class that implements the definition for our workflow :

*PostWorkflow.php in @app/models*
```php
<?php
namespace app\models;

class PostWorkflow implements \raoul2000\workflow\base\IWorkflowDefinitionProvider 
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

## Attach To The Model

Now let's have a look to our Post model. We decide to store the status of a post in a column named `status` of type STRING(40). 

The last step is to associate the workflow definition with posts models. To do so we must declare the *SimpleWorkflowBehavior* behavior 
in the Post model class and let the default configuration settings do the rest.
 
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

That's it ! We are ready to play with *SimpleWorkflowBehavior*.

## Use It !

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
	 
If you do the same thing but instead of *draft* set the status to *publish* and try to save it, the following exception is thrown :

	Not an initial status : PostWorkflow/publish ("PostWorkflow/draft" expected)

That's because in your workflow definition the **initial status** is  set to *draft* and not *publish*.

Ok, one more example for the fun ! This time we are not going to perform the transition when the Post is saved (like we did in the previous
example), but immediately by invoking the `sendToStatus` method. Our Post is going to try to reach status *publish* passing through *deleted* 
which is strictly forbidden by the workflow. Will it be successful in this risky attempt of breaking workflow rules ?   

```php
$post = new Post();
$post->sendToStatus('draft');
$post->sendToStatus('deleted');
$post->sendToStatus('publish');	// danger zone !
```

Game Over ! There is no transition between *deleted* and *publish*, and that's what *SimpleWorkflow* tries to explain to our
fearless post object.

	Workflow Exception – raoul2000\workflow\base\WorkflowException
	No transition found between status PostWorkflow/deleted and PostWorkflow/publish
	
Yes, that's severe, but there was many ways to avoid this exception like for instance by first validating that the transition was possible. 

## What's Next ?

This is just one way of using the *SimpleWorkflowBehavior* but there's much more and hopefully enough to assist you
in your workflow management inside your Yii2 web app.

In the meantime you can have a look to the [Usage Guide](guide) (still under dev) and send any feedback. 


License
-------

**yii2-workflow** is released under the BSD 3-Clause License. See the bundled `LICENSE.md` for details.
