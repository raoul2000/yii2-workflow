<?php
namespace raoul2000\workflow\base;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * A transition is a link between a start and an end status.
 *
 * If status "A" has a transition to status "B", then it only means that it is possible to go from
 * status "A" to status "B".
 */
interface TransitionInterface
{
	/**
	 * @return string the transition id
	 */
	public function getId();
	/**
	 * Returns the Status instance representing the destination status.
	 *
	 * @return Status the Status instance this transition ends
	 */
	public function getEndStatus();
	/**
	 * Returns the Status instance representing the starting point fo the transition.
	 *
	 * @return Status the Status instance this transition ends
	 */
	public function getStartStatus();
}
