<?php

namespace raoul2000\workflow\actions;

use yii;
use yii\base\Action;
use yii\base\Exception;
use yii\base\InvalidConfigException;

/**
 * Action change status Workflow
 *
 * @author Angel (Faryshta) Guevara <aguevara@tecnocen.com>
 * @author Carlos (neverabe) Llamosas <carlos@tecnocen.com>
 * @author Alejandro (seether69) Marquez <amarquez@tecnocen.com>
 */
class ChangeStatusAction extends Action
{
    /**
     * @var callable used to find the model to be updated.
     *
     * Must have signature
     * ```php
     * function ($id);
     * ```
     *
     * Where
     * - id: mixed the param to be searched as model.
     *
     * and returns an ActiveRecord instance.
     * or throw `yii\web\HttpException`
     */
    public $findModel;

    /**
     * @var callable method to handle the response for the user.
     *
     * Must have signature
     *
     * ```php
     * function ($changedStatus, $model)
     * ```
     *
     * Where
     * - $changedStatus: boolean if the status were changed correctly
     * - $model: ActiveRecord the model which was updated.
     *
     * With a mixed return depending on the controller.
     */
    public $response;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!is_callable($this->findModel)) {
            throw new InvalidConfigException(
                '`findModel` must be a callable property.'
            );
        }
        if (!is_callable($this->response)) {
            throw new InvalidConfigException(
                '`response` must be a callable property.'
            );
        }
    }

    /**
     * Runs this action with the specified parameters.
     * This method is mainly invoked by the controller
     * @param  integer $id Id of model to find
     * @param  string $status status will change
     * @return closure object Preconfigured response
     */
    public function run($id, $status)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->post());
        $changedStatus = $model->sendToStatus($status);
        $model->save();

        return $this->response(
            $changedStatus,
            $model
        );
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return The loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        return call_user_func($this->findModel, $id);
    }

    /**
     * Function preconfigured response
     * @param  boolean $changedStatus if status changed
     * @param  object $model Model in the response
     * @return closure object preconfigured response
     */
    protected function response($changedStatus, $model)
    {
        return call_user_func(
            $this->response,
            $changedStatus,
            $model
        );
    }
}

