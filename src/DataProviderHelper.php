<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use yii\data\BaseDataProvider;

/**
 * Class DataProviderHelper
 * Хелперы для датапровайдеров
 */
class DataProviderHelper {

	/**
	 * @param BaseDataProvider $dataProvider
	 * @return string[]
	 * @see GridView::guessColumns
	 */
	public static function GuessDataProviderColumns(BaseDataProvider $dataProvider):array {
		$columns = [];
		$models = $dataProvider->getModels();
		$model = reset($models);
		if (is_array($model) || is_object($model)) {
			foreach ($model as $name => $value) {
				if (null === $value || is_scalar($value) || is_callable([$value, '__toString'])) {
					$columns[] = (string)$name;
				}
			}
		}
		return $columns;
	}
}