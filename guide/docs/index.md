# What is yii2-workflow ?

**yii2-workflow** is an extension of the [Yii2 Framework](http://www.yiiframework.com/), designed to help you manage workflow in your app. It is the successor of *[simpleWorkflow](http://s172418307.onlinehome.fr/project/sandbox/www/index.php?r=simpleWorkflow/page&view=home)* which was developed some years ago for the 1.x version of Yii. Both extensions try to keep thing simple and easy to use. They rely as much as possible on standard Yii2 features like [Events](http://www.yiiframework.com/doc-2.0/guide-concept-events.html), [components](http://www.yiiframework.com/doc-2.0/guide-concept-components.html), [behaviors](http://www.yiiframework.com/doc-2.0/guide-concept-behaviors.html), etc.

Before going any further in your reading it is important to understand what yii2-workflow is *not* :

- it is not a complete and complex workflow engine
- it does not provide any UI components (basically it's a *behavior* that you add to your *ActiveRecord* models)
- it is not a solution to all your problems (I whish it would though)

# Requirements

Well, the only requirement here is to have installed the latest version of the [Yii2 Framework](http://www.yiiframework.com/) (or at least a version greater or equal to 2.0.3).

# How to install

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

# What's next ?

If you have been using the previous implementation of this extension it could be a good thing to start by reading the [Upgrade From 1.x](upgrade.md) chapter.

If you want to know more about workflow in general in the features provided by **yii2-workflow** in particular, check the [overview](overview.md) chapter.

If you feel like an advanced Worekflow Expert (more or less), the dive into the [Concept](concept-overview.md) chapter.
