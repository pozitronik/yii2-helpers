<?php
declare(strict_types = 1);

namespace app\controllers;

use yii\web\Controller;

/**
 * Class UsersController
 */
class UsersController extends Controller {

	/**
	 * @return string
	 */
	public function actionIndex():string {
		return 'index';
	}
}