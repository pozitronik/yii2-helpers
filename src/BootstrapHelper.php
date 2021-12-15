<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Exception;
use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;

/**
 * Class BootstrapHelper
 * Bootstrap detection helper, based on Kartik BootstrapTrait
 */
class BootstrapHelper {
	/**
	 * @var null|string the bootstrap library version.
	 *
	 * To use with bootstrap 3 - you can set this to any string starting with 3 (e.g. `3` or `3.3.7` or `3.x`)
	 * To use with bootstrap 4 - you can set this to any string starting with 4 (e.g. `4` or `4.1.1` or `4.x`)
	 *
	 * This property can be set up globally in Yii application params in your Yii2 application config file.
	 *
	 * For example:
	 * `Yii::$app->params['bsVersion'] = '4.x'` to use with Bootstrap 4.x globally
	 *
	 * If this property is set, this setting will override the `Yii::$app->params['bsVersion']`. If this is not set, and
	 * `Yii::$app->params['bsVersion']` is also not set, this will default to `3.x` (Bootstrap 3.x version).
	 */
	public static ?string $bsVersion = null;

	/**
	 * @var bool flag to detect whether bootstrap 4.x version is set
	 */
	protected static ?bool $_isBs4 = null;

	/**
	 * Validate if Bootstrap 4.x version
	 * @return bool
	 * @throws InvalidConfigException
	 * @throws Exception
	 */
	public static function isBs4():bool {
		if (null === self::$_isBs4) {
			self::configureBsVersion();
		}
		return self::$_isBs4;
	}

	/**
	 * Configures the bootstrap version settings
	 * @return string the bootstrap lib parsed version number
	 * @throws Exception
	 */
	protected static function configureBsVersion():string {
		$v = self::$bsVersion??ArrayHelper::getValue(Yii::$app->params, 'bsVersion', '3');
		$ver = static::parseVer($v);
		self::$_isBs4 = '4' === $ver;
		return $ver;
	}

	/**
	 * Parses and returns the major BS version
	 * @param string $ver
	 * @return string
	 */
	protected static function parseVer(string $ver):string {
		return substr(trim($ver), 0, 1);
	}
}