<?php

namespace tests\unit\workflow\source\file;

use Yii;
use yii\codeception\TestCase;
use raoul2000\workflow\source\file\DefaultArrayParser;
use raoul2000\workflow\source\file\WorkflowFileSource;


class DefaultArrayParserTest extends TestCase
{
	use \Codeception\Specify;
	
	public $src;
	
	protected function setUp()
	{
		parent::setUp();
		$this->src = new WorkflowFileSource();
		Yii::$app->set('parser',[
			'class' => DefaultArrayParser::className(),
		]);
	}
	
	/**
	 * 
	 */
	public function testCreateInstance()
	{
		Yii::$app->set('parserA',[
			'class' => DefaultArrayParser::className(),
			'validate' => false
		]);
		verify('validate is assigned',Yii::$app->parserA->validate)->false(); 

		Yii::$app->set('parserB',[
			'class' => DefaultArrayParser::className(),
		]);
		verify('validate default value is true',Yii::$app->parserB->validate)->true();		
	}
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Missing "initialStatusId"#
	 */
	public function testParseNoInitStatus()
	{
		Yii::$app->parser->parse('WID',[
			'status'=> []
		],$this->src);
	}
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowException
	 * @expectedExceptionMessageRegExp #Not a valid status id : incorrect status local id format in 'hello A'#
	 */
	public function testParseInvalidInitStatusID()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'hello A'
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #No status definition found#
	 */
	public function testParseNoStatus()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A'
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Wrong definition for status A : array expected#
	 */
	public function testParseWrongStatusDefinition1()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [ 'A' => 1]
				
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp /Wrong status definition : key = 1 value =[.*0 => 'A'.*]/
	 */
	public function testParseWrongStatusDefinition2()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [ 1 => ['A']]
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Invalid Status definition : array expected#
	 */
	public function testParseWrongStatusDefinition3()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => 'A'
		],$this->src);
	}		
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Invalid metadata definition for status WID/A : array expected#
	 */
	public function testParseWrongMetadataDefinition1()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [ 
			'A' => [
				'metadata' => 1
			]
		]
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Invalid metadata definition for status WID/A : associative array expected#
	 */
	public function testParseWrongMetadataDefinition2()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'A' => [
					'metadata' => ['A','B']
				]
			]
		],$this->src);
	}		
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Wrong definition for between WID/A and B : array expected#
	 */
	public function testParseWrongTransitionDefinition1()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'A' => [
					'transition' => ['B' => 1]
				]
			]
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * 
	 * expectedExceptionMessageRegExp /Wrong transition definition for status WID.A : key = 1 value = [.*0 => 'B'.*]/
	 */
	public function testParseWrongTransitionDefinition2()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'A' => [
					'transition' => [1 => ['B']]
				]
			]
		],$this->src);
	}	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Invalid transition definition format for status WID/A : string or array expected#
	 */
	public function testParseWrongTransitionDefinition3()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'A' => [
					'transition' => 1
				]
			]
		],$this->src);
	}		
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Initial status not defined : WID/C#
	 */
	public function testParseValidationFailedMissingInitStatus()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'C',
				'status' => [
				'A' => [
					'transition' => 'B'
				],
				'B'
			]
		],$this->src);
	}	
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Initial status must belong to workflow : EXT/C#
	 */
	public function testParseValidationFailedExternalInitStatus()
	{	
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'EXT/C',
				'status' => [
				'A' => [
					'transition' => 'B'
				],
				'B'
			]
		],$this->src);
	}		
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Status must belong to workflow : EXT/B#
	 */
	public function testParseValidationFailedExternalStatus1()
	{	
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'A' => [
					'transition' => 'B'
				],
				'EXT/B'
			]
		],$this->src);
	}		
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp #Status must belong to workflow : EXT/A#
	 */
	public function testParseValidationFailedExternalStatus2()
	{	
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'EXT/A' => [
					'transition' => 'B'
				],
				'B'
			]
		],$this->src);
	}
	
	/**
	 * @expectedException raoul2000\workflow\base\WorkflowValidationException
	 * @expectedExceptionMessageRegExp /One or more end status are not defined :.*?/
	 */
	public function testParseValidationFailedMissingStatus()
	{
		Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
				'status' => [
				'A' => [
					'transition' => 'B'
				]
			]
		],$this->src);
	}	

	public function testParseMinimalWorkflow1()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => ['A']
		],$this->src);
		verify('initial status is WID/A',$workflow['initialStatusId'])->equals('WID/A');
		verify('status WID/A is present',\array_key_exists('WID/A', $workflow['status']))->true();
		verify('status WID/A definition is NULL',$workflow['status']['WID/A'])->isEmpty();
	}
	
	public function testParseMinimalWorkflow2()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => ['A'=> null]
		],$this->src);
		verify('initial status is WID/A',$workflow['initialStatusId'])->equals('WID/A');
		verify('status WID/A is present',\array_key_exists('WID/A', $workflow['status']))->true();
		verify('status WID/A definition is NULL',$workflow['status']['WID/A'])->isEmpty();
	}	
	
	public function testParseMinimalWorkflow3()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => ['A'=> []]
		],$this->src);
		verify('initial status is WID/A',$workflow['initialStatusId'])->equals('WID/A');
		verify('status WID/A is present',\array_key_exists('WID/A', $workflow['status']))->true();
		verify('status WID/A definition is NULL',$workflow['status']['WID/A'])->isEmpty();
	}	
	
	public function testParseMinimalWorkflowWithConfig()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => ['A'],
			'property' => 'value'
		],$this->src);
		
		verify('status WID/A definition is NULL',$workflow['property'])->equals('value');
	}		
	
	public function testParseStatusWithConfig()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A' => ['property' => 'value']
			]
		],$this->src);
		
		verify('status WID/A definition is NULL',$workflow['status']['WID/A']['property'])->equals('value');
	}	
	
	
	public function testParseMetadata()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'metadata' => ['color' => 'red']
				]
			]
		],$this->src);
		verify('metadata is set',$workflow['status']['WID/A']['metadata']['color'])->equals('red');
	}		
	
	public function testParseTransitionSingle1()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'transition' => 'B'
				],
				'B'
			]
		],$this->src);
		verify('transition is set',\array_key_exists('WID/B',$workflow['status']['WID/A']['transition']))->true();
		verify('transition has no config set',$workflow['status']['WID/A']['transition']['WID/B'] === [])->true();
	}	
	
	public function testParseTransitionSingle2()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'transition' => ['B']
				],
				'B'
			]
		],$this->src);
		verify('transition is set',\array_key_exists('WID/B',$workflow['status']['WID/A']['transition']))->true();
		verify('transition has no config set',$workflow['status']['WID/A']['transition']['WID/B'] === [])->true();
	}
	
	public function testParseTransitionSingle3()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'transition' => ['B' => []]
				],
				'B'
			]
		],$this->src);
		verify('transition is set',\array_key_exists('WID/B',$workflow['status']['WID/A']['transition']))->true();
		verify('transition has no config set',$workflow['status']['WID/A']['transition']['WID/B'] === [])->true();
	}	

	public function testParseTransitionMulti1()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'transition' => 'B,C'
				],
				'B','C'
			]
		],$this->src);
		verify('transition to B is set',\array_key_exists('WID/B',$workflow['status']['WID/A']['transition']))->true();
		verify('transition to C is set',\array_key_exists('WID/C',$workflow['status']['WID/A']['transition']))->true();
		
		verify('transition to B has no config set',$workflow['status']['WID/A']['transition']['WID/B'] === [])->true();
		verify('transition to C has no config set',$workflow['status']['WID/A']['transition']['WID/C'] === [])->true();
	}		
	
	public function testParseTransitionMulti2()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'transition' => ['B','C']
				],
				'B','C'
			]
		],$this->src);
		verify('transition to B is set',\array_key_exists('WID/B',$workflow['status']['WID/A']['transition']))->true();
		verify('transition to C is set',\array_key_exists('WID/C',$workflow['status']['WID/A']['transition']))->true();
		
		verify('transition to B has no config set',$workflow['status']['WID/A']['transition']['WID/B'] === [])->true();
		verify('transition to C has no config set',$workflow['status']['WID/A']['transition']['WID/C'] === [])->true();
	}		
	
	public function testParseTransitionMultiWidhtConfig()
	{
		$workflow = Yii::$app->parser->parse('WID',[
			'initialStatusId' => 'A',
			'status' => [
				'A'=> [
					'transition' => ['B' => ['kb' => 'vb'] ,'C' => []]
				],
				'B','C'
			]
		],$this->src);
		verify('transition to B is set',\array_key_exists('WID/B',$workflow['status']['WID/A']['transition']))->true();
		verify('transition to C is set',\array_key_exists('WID/C',$workflow['status']['WID/A']['transition']))->true();
		
		verify('transition to B has no config set',$workflow['status']['WID/A']['transition']['WID/B'] === ['kb' => 'vb'])->true();
		verify('transition to C has no config set',$workflow['status']['WID/A']['transition']['WID/C'] === [])->true();
	}
}
