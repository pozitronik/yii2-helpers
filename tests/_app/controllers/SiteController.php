<?php
declare(strict_types = 1);

namespace app\controllers;

use app\models\Dummy;
use Yii;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\ErrorAction;

/**
 * class SiteController
 */
class SiteController extends Controller {

	/**
	 * @inheritDoc
	 */
	public function actions() {
		return [
			'definedError' => ErrorAction::class,
			'notAction' => Dummy::class
		];
	}

	/**
	 * @return string
	 */
	public function actionError():string {
		$exception = Yii::$app->errorHandler->exception;

		if (null !== $exception) {
			return Html::encode($exception->getMessage());
		}
		return "Status: {$exception->statusCode}";
	}

	/**
	 * @return string
	 */
	public function actionCamelCase():string {
		return 'camel-case';
	}
}

