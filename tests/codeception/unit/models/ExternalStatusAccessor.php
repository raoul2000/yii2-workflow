<?php
namespace tests\codeception\unit\models;

use Yii;
use yii\db\BaseActiveRecord;
use yii\db\QueryBuilder;
use yii\db\Query;
use raoul2000\workflow\base\Status;
use raoul2000\workflow\base\IStatusAccessor;

class ExternalStatusAccessor implements IStatusAccessor
{
	private $_status;

	/* (non-PHPdoc)
	 * @see \raoul2000\workflow\IStatusAccessor::getStatus()
	 */
	public function getStatus(BaseActiveRecord $model) {

		if($model->isNewRecord == false) {
			echo 'loading status for item '.$model->id;
			$post = $this->loadStatusRow($model->id);
			$result = $post['value'];
			echo ' status = '.$result.'<br/>';
			return $result;
		} else {
			return null;
		}
	}

	public function commitStatus($model)
	{

			echo 'saving model id = '.$model->id,' status = '.$this->_status.'<br/>';
			Yii::$app->db->createCommand()->insert('status', [
				'item_id' => $model->id,
				'value' => $this->_status,
				'created_at' => time()
			])->execute();

	}
	private function loadStatusRow($id)
	{
		$command = Yii::$app->db->createCommand('SELECT value FROM status WHERE item_id=:ITEM_ID and '
			.' id in ( SELECT MAX(id) FROM status )');
		$command->bindValue(':ITEM_ID', $id);
		return $command->queryOne();
	}
	/* (non-PHPdoc)
	 * @see \raoul2000\workflow\IStatusAccessor::setStatus()
	 */
	public function setStatus(BaseActiveRecord $model, Status $status = null) {
		echo 'setStatus model <br/>';
		$this->_status = $status != null ? $status->getId() : null;
	}
	/* (non-PHPdoc)
	 * @see \raoul2000\workflow\base\IStatusAccessor::readStatus()
	 */
	public function readStatus(BaseActiveRecord $model) {
		// TODO: Auto-generated method stub

	}

	/* (non-PHPdoc)
	 * @see \raoul2000\workflow\base\IStatusAccessor::updateStatus()
	 */
	public function updateStatus(BaseActiveRecord $model, Status $status = null) {
		// TODO: Auto-generated method stub

	}

}