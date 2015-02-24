<?php
namespace tests\unit\workflow\validation;

use Yii;
use yii\codeception\TestCase;
use tests\codeception\unit\models\Item_05;
use yii\base\InvalidConfigException;
use raoul2000\workflow\base\SimpleWorkflowBehavior;
use yii\codeception\DbTestCase;

class ValidatorTest extends TestCase
{
	use \Codeception\Specify;

	protected function setup()
	{
		parent::setUp();
		Yii::$app->set('workflowSource',[
			'class'=> 'raoul2000\workflow\source\php\WorkflowPhpSource',
			'namespace' => 'tests\codeception\unit\models'
		]);
		Item_05::deleteAll();
	}

	public function testValidateFailsOnTransition()
	{
		$model = new Item_05();
		$model->status = 'Item_05Workflow/new';

		expect_that($model->save());
		expect_not($model->hasErrors());

		$this->specify('model validation fails on transition for attribute "name"', function () use ($model) {

			$model->name = null;
			$model->status = 'Item_05Workflow/correction';

			verify('validation fails',$model->validate())->false();
			verify('the model has errors',$model->hasErrors())->true();
			verify('the model has exactly one error',count($model->getErrors()) == 1)->true();

			verify('the correct error message is set', $model->getFirstError('name'))->equals('Name cannot be blank.');
			verify('the model status was not changed', $model->getWorkflowStatus()->getId())->equals('Item_05Workflow/new');
			verify('the status attribute was not changed', $model->status)->equals('Item_05Workflow/correction');
		});
	}

	public function testValidateSuccessOnTransition()
	{
		$model = new Item_05();
		$model->status = 'Item_05Workflow/new';

		expect_that($model->save());
		expect_not($model->hasErrors());

		$this->specify('model validation success on transition for attribute "name"', function () use ($model) {

			$model->name = 'Alan';
			$model->status = 'Item_05Workflow/correction';

			verify('validation success',$model->validate())->true();
			verify('the model has no error',$model->hasErrors())->false();

			verify('the model status was not changed', $model->getWorkflowStatus()->getId())->equals('Item_05Workflow/new');
			verify('the status attribute has changed', $model->status)->equals('Item_05Workflow/correction');
		});
	}
	public function testValidationIsSkipped()
	{
		$model = new Item_05();
		$model->status = 'Item_05Workflow/new';

		expect_that($model->save());
		expect_not($model->hasErrors());

		$this->specify('model validation is skipped if save is done with no validation', function () use ($model) {

			$model->name = null;
			$model->status = 'Item_05Workflow/correction';

			verify('save is successful when no validation is done',$model->save(false))->true();
			verify('the model has no errors',$model->hasErrors())->false();
			verify('the model status has changed', $model->getWorkflowStatus()->getId())->equals('Item_05Workflow/correction');
			verify('the status attribute has changed', $model->status)->equals('Item_05Workflow/correction');
		});
	}
	public function testValidateFailsOnEnterWorkflow()
	{

		$this->specify('model validation fails on enter workflow for attribute "category"', function () {

			$model = new Item_05();
			$model->status = 'Item_05Workflow/new';
			$model->category = null;

			verify('validation fails',$model->validate())->false();
			verify('the model has errors',$model->hasErrors())->true();
			verify('the model has exactly one error',count($model->getErrors()) == 1)->true();

			verify('the correct error message is set', $model->getFirstError('category'))->equals('Category cannot be blank.');
			verify('the model status was not changed', $model->getWorkflowStatus())->equals(null);
			verify('the status attribute was not changed', $model->status)->equals('Item_05Workflow/new');
		});
	}

	public function testValidateSuccessOnEnterWorkflow()
	{
		$this->specify('model validation success on enter workflow for attribute "category"', function () {

			$model = new Item_05();
			$model->status = 'Item_05Workflow/new';
			$model->category = 'sport';

			verify('validation success',$model->validate())->true();
			verify('the model has no error',$model->hasErrors())->false();

			verify('the model status was not changed', $model->getWorkflowStatus())->equals(null);
			verify('the status attribute was changed', $model->status)->equals('Item_05Workflow/new');
		});
	}

	public function testValidateFailsOnLeaveWorkflow()
	{
		$model = new Item_05();
		$model->status = 'Item_05Workflow/new';
		expect_that($model->save());
		expect_that($model->getWorkflowStatus()->getId() == 'Item_05Workflow/new' );

		$this->specify('model validation fails on leave workflow for attribute "category"', function () use($model) {

			$model->status = null;	// leaving workflow
			$model->category = 'some incorrect value';

			verify('validation fails',$model->validate())->false();
			verify('the model has errors',$model->hasErrors())->true();
			verify('the model has exactly one error',count($model->getErrors()) == 1)->true();

			verify('the correct error message is set', $model->getFirstError('category'))->equals('Category must be repeated exactly.');
			verify('the model status was not changed', $model->getWorkflowStatus()->getId())->equals('Item_05Workflow/new');
			verify('the status attribute was not changed', $model->status)->equals(null);
		});
	}

	public function testValidateSuccessOnLeaveWorkflow()
	{
		$model = new Item_05();
		$model->status = 'Item_05Workflow/new';
		expect_that($model->save());
		expect_that($model->getWorkflowStatus()->getId() == 'Item_05Workflow/new' );

		$this->specify('model validation success on leave workflow for attribute "category"', function () use($model) {

			$model->status = null;	// leaving workflow
			$model->category = 'done';

			verify('validation success',$model->validate())->true();
			verify('the model has no error',$model->hasErrors())->false();

			verify('the model status was not changed', $model->getWorkflowStatus()->getId())->equals('Item_05Workflow/new');
			verify('the status attribute is NULL', $model->status)->equals(null);
		});
	}

	public function testValidateSuccessOnFromStatus()
	{
		$model = new Item_05();
		$model->sendToStatus('Item_05Workflow/new');
		$model->sendToStatus('Item_05Workflow/correction');

		expect_that($model->getWorkflowStatus()->getId() == 'Item_05Workflow/correction' );

		$this->specify('model validation success on leave status "correction" for attribute "tags"', function () use($model) {

			$model->status = 'Item_05Workflow/published';
			$model->tags = "tag1,tag2";

			verify('validation success',$model->validate())->true();
			verify('the model has no error',$model->hasErrors())->false();

			verify('the model status was not changed', $model->getWorkflowStatus()->getId())->equals('Item_05Workflow/correction');
			verify('the status attribute is NULL', $model->status)->equals('Item_05Workflow/published');
		});
	}

	public function testValidateFailsOnFromStatus()
	{
		$model = new Item_05();
		$model->sendToStatus('Item_05Workflow/new');
		$model->sendToStatus('Item_05Workflow/correction');

		expect_that($model->getWorkflowStatus()->getId() == 'Item_05Workflow/correction' );

		$this->specify('model validation fails on leave status "correction" for attribute "tags"', function () use($model) {

			$model->status = 'Item_05Workflow/published';
			$model->tags = null;

			verify('validation error',$model->validate())->false();
			verify('the model has errors',$model->hasErrors())->true();
			verify('the model has exactly one error',count($model->getErrors()) == 1)->true();

			verify('the correct error message is set', $model->getFirstError('tags'))->equals('Tags cannot be blank.');

		});
	}

	public function testValidateSuccessOnToStatus()
	{
		$model = new Item_05();
		$model->sendToStatus('Item_05Workflow/new');
		$model->sendToStatus('Item_05Workflow/correction');

		expect_that($model->getWorkflowStatus()->getId() == 'Item_05Workflow/correction' );

		$this->specify('model validation success on enter status "published" for attribute "author"', function () use($model) {

			$model->status = 'Item_05Workflow/published';
			$model->tags = 'some tag';
			$model->author = 'Platon';

			verify('validation success',$model->validate())->true();
			verify('the model has no error',$model->hasErrors())->false();
		});
	}

	public function testValidateFailsOnToStatus()
	{
		$model = new Item_05();
		$model->sendToStatus('Item_05Workflow/new');
		$model->sendToStatus('Item_05Workflow/correction');

		expect_that($model->getWorkflowStatus()->getId() == 'Item_05Workflow/correction' );

		$this->specify('model validation success on enter status "published" for attribute "author"', function () use($model) {

			$model->status = 'Item_05Workflow/published';
			$model->tags = 'some tag';
			$model->author = null;

			verify('validation error',$model->validate())->false();
			verify('the model has errors',$model->hasErrors())->true();
			verify('the model has exactly one error',count($model->getErrors()) == 1)->true();

			verify('the correct error message is set', $model->getFirstError('author'))->equals('Author cannot be blank.');
		});
	}
}