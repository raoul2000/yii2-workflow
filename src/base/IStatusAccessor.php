<?php
namespace raoul2000\workflow\base;

use raoul2000\workflow\base\Status;
use yii\db\BaseActiveRecord;

/**
 *
 *
 */
interface IStatusAccessor
{
	/**
	 * This method is invoked each time a status value must be read.
	 *
	 * @param BaseActiveRecord $model
	 * @return string the status Id
	 */
	public function readStatus(BaseActiveRecord $model);
	/**
	 * This method is invoked each time a status value must be updated.
	 *
	 * Updating a status value differs from actually saving the status in persistent storage (the database).
	 *
	 * @param BaseActiveRecord $model
	 * @param Status $status
	 * @param string $statusId
	 */
	public function updateStatus(BaseActiveRecord $model, Status $status = null);

	/**
	 * This method is invoked when the status needs to be saved.
	 * @param BaseActiveRecord $model
	 */
	public function commitStatus($model);
}
