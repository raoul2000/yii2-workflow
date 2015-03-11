# About Workflows

According to wikipedia :

> A workflow consists of a sequence of connected steps. It is a depiction of a sequence of operations, declared 
as work of a person, a group of persons, an organization of staff, or one or more simple or complex mechanisms. 
Workflow may be seen as any abstraction of real work, segregated in workshare, work split or other types of ordering. 
For control purposes, workflow may be a view on real work under a chosen aspect, thus serving as a virtual 
representation of actual work. 

> (*[read more on Wikipedia](http://en.wikipedia.org/wiki/Workflow)*)

Workflows (also called Petri net) is a vast subject and the aim of this document is not to go deeply in the theorical fields. 
As described in the next chapter, the SimpleWorkflow behavior only implements a simple subset of it.  if you are intrested in 
better understanding theorical basis on the subject, you'll find some [references](#references) at the end of this page. 


## Use case : a blog

To demonstrate how the workflow concept can be used in a valuable way, let's consider a real life example : a Blog. 
In a typical blog webapp, you would find a model for Post with a *status* attribute that accept 3 values defined
as class constants.

*models/Post.php*	
```php
class Post extends \yii\db\ActiveRecord
{
    const STATUS_DRAFT=1;
    const STATUS_PUBLISHED=2;
    const STATUS_ARCHIVED=3;
```

It is quite obvious that theses values define possible states of a Post instance. Moreover, a set of rules are used to 
define how posts will evolve among these 3 statuses : when you first create a post, it is defined as being *draft*, 
then it can be *published* or *archived*. A *published* post can become *draft* (it is then unpublished) or be *archived*. 
At last an *archived* post can be *published* or become *draft*.

What we have just described here are allowed transitions between different statuses of the Post, and if we try to give 
a graphical representation to this description, we'll end up with our first (and very simple) workflow. 

<img src="post-workflow.png" alt="the workflow for Post"/>

Out workflow definition is:

- 3 statuses : draft, published, archived
- 6 possible transitions
- initial status : draft

To handle this very simple workflow, there is not much to do as the user has complete freedom to set a post status : any 
status can be reached from any other status and in this case, *there is no need** for a dedicated extension that would 
handle workflow logic.

## Use case²: a multi-user publishing system

Let's imagine something a little bit more complex.

> Our basic blog is now becoming a multi-user publishing system, where each user is assigned tasks : some are redactors (reporter), 
some make corrections and layout work (they know css), and there is of course some chief editors who are responsible for publication.

If we want to be able to handle posts in our new publishing system, we must think of a more elaborated workflow that will fit this 
new organisation. First of all, let's list possible post statuses : 

- **draft** : when created, a post is always draft. This is the *initial status* of all Posts
- **correction** : the post is being corrected and layout improvements may also be added
- **ready** : the post is ready to be published but not yet online
- **published** : the post is online, available to readers
- **archived** : the post is not directly available to readers, but can be accessed through the archive section of the site

That is not enough. Now we must define possible transitions between these statuses. These transitions strongly depend on how 
the work is going to be organized, how users of our publishing system will interact with each other. For this example we 
will abritrarly state following rules : 

1. A Post must always be corrected before publication
2. the chief editor is responsible for publishing/unpublishing posts
3. the chief editor is responsible for sending a post to archive

That will be enough for this example but of course we could (and probably should) add more business rules.
Now, based on what we have just define, here is the Post workflow : 

<img src="post-workflow-2.png" alt="workflow 2"/>

The first version of the Post worfklow was very simple, and as each status could reach any other status, there was no need for 
the developper to make any tests when a Post changed status. With this last version above, that's another story ! Some logic must 
be implemented in order to prevent *Archived* post to become *Draft*, or *Published* posts to be sent to *Correction*. 

That is when the *SimpleWorkflow*  behavior is useful!

### Workflow Definition

So we have a nice workflow, let's see how the *SimpleWorkflowBehavior* can help in managing our Post models life-cycle inside this workflow.
First we must create a definition for our workflow. 

A Workflow can be defined as a PHP class that contains the method `getDefinition()`. This method returns as PHP array which is the 
workflow definition.

The class is named **PostWorkflow** which is by convention the name of a workflow associated with the *Post* model. It is located in
`@app/models`, the default location where workflow definitions are stored. Note that these conventions and default settings 
can of course be overloaded with values provided by the developer at initialisation (this will be discussed later).

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

A more condensed format is also supported, but for this example we will use this one as it allows more customization.


## <a name="references"></a>References

The SimpleWorkflow behavior, is not dedicated to provide a complete workflow driven model that would replace MVC or any other pattern. 
It should only be considered as a set of tools that facilitate workflow managment for simple applications. 

If you want to know more about the subject, and discover what a complete workflow engine looks like, here is a list of 
intresting links.

- [An activity based Workflow Engine for PHP](http://www.tonymarston.net/php-mysql/workflow.html)
- [Workflow Patterns home page](http://www.workflowpatterns.com/)
- [Galaxia : an open source workflow engine](http://workflow.tikiwiki.org/tiki-index.php?page=homepage)
- [ezComponent : workflow](http://www.ezcomponents.org/docs/api/latest/introduction_Workflow.html)

