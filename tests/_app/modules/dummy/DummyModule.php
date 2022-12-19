<?php
declare(strict_types = 1);

namespace app\modules\dummy;

use yii\base\Module;

/**
 * Class DummyModule
 */
class DummyModule extends Module {

	/**
	 * @inheritDoc
	 */
	public function getVersion():string {
		return $this->params['version']??'1.0';
	}
}