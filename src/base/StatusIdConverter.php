<?php
namespace raoul2000\workflow\base;

use Yii;

use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\base\Exception;
use yii\base\InvalidCallException;

/**
 * This class implements a status Id converter.
 *
 * The conversion is based on an array where key are valid status ID from the simpleWorkflow
 * behavior point of view, and values are status ID suitable to be stored in the owner model.
 *
 * A typical usage for this converter is when the definition of the status column in the underlying table
 * is not able to store a string value and when modifying column type is not an option. If
 * for instance the status column type is integer, then the following example conversion table
 * could be used :
 *
 * <pre>
 * $map = [
 *     'post/new' => 12,
 *     'post/corrected' => 25,
 *     'post/published' => 1,
 *     'post/archived' => 6,
 *     StatusIdConverter::VALUE_NULL => 'some value',
 *     'workflow/Status' => StatusIdConverter::VALUE_NULL
 * ]
 * </pre>
 *
 * Note that if the NULL value must be part of the conversion, you should use the VALUE_NULL
 * constant instead of the actual 'null' value.<br/>
 * For example in the conversion table below, the fact for the owner model to be outside a workflow,
 * would mean that the actual status column would be set to 25. In the same way, any model with a
 * status column equals to NULL, is considered as being in status 'post/toDelete' :
 *
 * <pre>
 * $map = [
 *      StatusIdConverter::VALUE_NULL => 25,
 *     'post/toDelete' => StatusIdConverter::VALUE_NULL
 * ];
 * </pre>
 *
 * @see IStatusIdConverter
 */
class StatusIdConverter extends BaseObject implements IStatusIdConverter
{
	const VALUE_NULL = 'null';
	private $_map = [];

	/**
	 * Contruct an instance of the StatusIdConverter.
	 * The parameter `map` must be defined in the configuration array passed as argument. It contains the 
	 * associative array used to convert statuses.
	 *
	 * @param array $config
	 * @throws InvalidConfigException
	 */
	public function __construct($config = [])
	{
		if ( ! empty($config['map'])) {
			if ( ! is_array($config['map'])) {
				throw new InvalidConfigException('The map must be an array');
			}
			$this->_map = $config['map'];
			unset($config['map']);
		} else {
			throw new InvalidConfigException('missing map');
		}
		parent::__construct($config);
	}
	/**
	 * @return array the convertion map used by this converter
	 */
	public function getMap()
	{
		return $this->_map;
	}
	/**
	 * Replace the convertion map initialized in constructor by the one passed as argument.
	 * 
	 * @param array $map
	 * @throws InvalidCallException
	 */
	public function setMap($map)
	{
		if ( ! is_array($map)) {
			throw new InvalidCallException('The map argument must be an array');
		}
		$this->_map = $map;		
	}
	/**
	 * (non-PHPdoc)
	 * @see IStatusIdConverter::toSimpleWorkflow()
	 */
	public function toSimpleWorkflow($id)
	{
		if ($id === null) {
			$id = self::VALUE_NULL;
		}
		$statusId = array_search($id, $this->_map);
		if ($statusId === false) {
			throw new Exception('Conversion to SimpleWorkflow failed : no value found for id = '.$id);
		}
		return ($statusId == self::VALUE_NULL ? null : $statusId);
	}

	/**
	 * (non-PHPdoc)
	 * @see IStatusIdConverter::toModelAttribute()
	 */
	public function toModelAttribute($id)
	{
		if ($id === null) {
			$id = self::VALUE_NULL;
		}

		if (! array_key_exists($id,	$this->_map) ) {
			throw new Exception('Conversion from SimpleWorkflow failed : no key found for id = '.$id);
		}
		$value = $this->_map[$id];
		return ($value === self::VALUE_NULL ? null : $value);
	}
}
