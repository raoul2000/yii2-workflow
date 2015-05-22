<?php

namespace tests\unit\workflow\source\file;

use Yii;
use yii\codeception\TestCase;
use raoul2000\workflow\source\file\DefaultArrayParser;
use raoul2000\workflow\source\file\WorkflowFileSource;
use raoul2000\workflow\source\file\MinimalArrayParser;
use yii\helpers\VarDumper;


class MinimalArrayParserTest extends TestCase
{
	use \Codeception\Specify;
	
	public $src;
	
	protected function setUp()
	{
		parent::setUp();
		Yii::$app->set('parser',[
			'class' => MinimalArrayParser::className(),
		]);
		
		$this->src = new WorkflowFileSource();
	}
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage Workflow definition must be provided as an array
	 */
	public function testParseInvalidType()
	{
		Yii::$app->parser->parse('WID',null,$this->src);
	}
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage Missing argument : workflow Id
	 */
	public function testMissingWorkflowId()
	{
		Yii::$app->parser->parse('',null,$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage Workflow definition must be provided as associative array
	 */
	public function testNonAssociativeArray1()
	{
		Yii::$app->parser->parse('WID',['a'],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage Workflow definition must be provided as associative array
	 */
	public function testNonAssociativeArray2()
	{
		Yii::$app->parser->parse('WID',['a'=> [], 'b'],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage Status must belong to workflow : EXT/a
	 */
	public function testExternalStatusError()
	{
		Yii::$app->parser->parse('WID',[
			'EXT/a' => [],
			'b' => []
		],$this->src);
	}
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage  Associative array not supported (status : WID/a)
	 */
	public function testEndStatusAssociativeError()
	{
		Yii::$app->parser->parse('WID',[
			'a' => ['b' => 'value'],
			'b' => []
		],$this->src);
	}
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessage End status list must be an array for status  : WID/a
	 */
	public function testEndStatusTypeNotSupported()
	{
		Yii::$app->parser->parse('WID',[
			'a' => 4,
			'b' => []
		],$this->src);
	}		
	
	public function testParseArraySuccess()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'a' => ['b','c'],
			'b' => ['a'],
			'c' => []
		],$this->src);
				
		verify('status "a" is set ', array_key_exists('WID/a',($workflow['status'])) )->true();
		verify('status "b" is set ', array_key_exists('WID/b',($workflow['status'])) )->true();
		verify('status "c" is set ', array_key_exists('WID/c',($workflow['status'])) )->true();
		
		verify('status transitions from "a" are set ', $workflow['status']['WID/a']['transition'])->equals(['WID/b'=>[],'WID/c'=>[]]);
		verify('status transitions from "b" are set ', $workflow['status']['WID/b']['transition'])->equals(['WID/a'=>[]]);
		verify('status transitions from "a" are set ', $workflow['status']['WID/c'])->equals(null);
	}		
	
	public function testParseStringSuccess()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'a' => 'b,c',
			'b' => 'a',
			'c' => []
		],$this->src);
				
		verify('status "a" is set ', array_key_exists('WID/a',($workflow['status'])) )->true();
		verify('status "b" is set ', array_key_exists('WID/b',($workflow['status'])) )->true();
		verify('status "c" is set ', array_key_exists('WID/c',($workflow['status'])) )->true();
		
		verify('status transitions from "a" are set ', $workflow['status']['WID/a']['transition'])->equals(['WID/b'=>[],'WID/c'=>[]]);
		verify('status transitions from "b" are set ', $workflow['status']['WID/b']['transition'])->equals(['WID/a'=>[]]);
		verify('status transitions from "a" are set ', $workflow['status']['WID/c'])->equals(null);
	}
}
