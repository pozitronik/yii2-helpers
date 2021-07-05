<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\ArrayHelper;

/**
 * Class ModuleHelper
 */
class ModuleHelper {
	/**
	 * @param string $name - id модуля из web.php
	 * @param null|array $moduleConfigurationArray - конфиг модуля из web.php вида
	 * [
	 *        'class' => Module::class,
	 *        ...
	 * ]
	 * null - подтянуть конфиг автоматически
	 *
	 * @return null|Module|Component - загруженный экземпляр модуля
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	private static function LoadModule(string $name, ?array $moduleConfigurationArray = null):?Component {
		$moduleConfigurationArray = $moduleConfigurationArray??ArrayHelper::getValue(Yii::$app->modules, $name, []);
		$module = Yii::createObject($moduleConfigurationArray, [$name]);
		if ($module instanceof Module) return $module;
		return null;
	}

	/**
	 * Возвращает список подключённых в конфигурации приложения модулей.
	 * @param string[]|null $whiteList Массив с перечислением имён модулей, включаемых в перечисление, null - все модули
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function ListModules(?array $whiteList = null):array {
		$modules = [];
		$appModules = (null === $whiteList)?Yii::$app->modules:array_intersect_key(Yii::$app->modules, array_flip($whiteList));

		foreach ($appModules as $name => $module) {
			if (is_object($module)) {
				if ($module instanceof Module) $modules[$name] = $module;
			} elseif (null !== $loadedModule = self::LoadModule($name, $module)) {
				$modules[$name] = $loadedModule;
			}
		}
		return $modules;
	}

	/**
	 * Возвращает модуль по его id
	 * @param string $moduleId
	 * @return Module|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetModuleById(string $moduleId):?Component {
		return ArrayHelper::getValue(self::ListModules(), $moduleId);
	}

	/**
	 * Возвращает модуль по его имени класса
	 * @param string $className
	 * @return Module|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetModuleByClassName(string $className):?Component {
		$config = array_filter(Yii::$app->modules, static function($element) use ($className) {
			if (is_array($element)) {
				return $className === ArrayHelper::getValue($element, 'class');
			}
			if (is_object($element)) {//module already loaded
				return $className === get_class($element);
			}
			return false;
		});
		if (null === $moduleName = ArrayHelper::getValue(array_keys($config), 0)) return null;
		if (is_object($config[$moduleName])) return $config[$moduleName];
		return self::LoadModule($moduleName, $config[$moduleName]);
	}

	/**
	 * Возвращает массив путей к контроллерам модулей, например для построения навигации
	 * @return string[]
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetAllControllersPaths():array {
		$result = [];
		foreach (self::ListModules() as $module) {
			$result[$module->id] = $module->controllerPath;
		}
		return $result;
	}

	/**
	 * Вернёт набор параметров модуля по имени класса
	 * @param string $className
	 * @return array
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function params(string $className):array {
		if ((null !== $module = self::GetModuleByClassName($className))) {
			return $module->params;
		}
		return [];
	}

}