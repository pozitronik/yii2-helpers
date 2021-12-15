<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\ArrayHelper;

/**
 * Class ModuleHelper
 */
class ModuleHelper {
	/**
	 * @param string $name id модуля из web.php
	 * @param null|array $moduleConfigurationArray Конфиг модуля из web.php вида
	 * [
	 *        'class' => Module::class,
	 *        ...
	 * ]
	 * null - подтянуть конфиг автоматически
	 *
	 * @return null|Module Загруженный экземпляр модуля
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	private static function LoadModule(string $name, ?array $moduleConfigurationArray = null):?Module {
		$moduleConfigurationArray = $moduleConfigurationArray??ArrayHelper::getValue(Yii::$app->modules, $name, []);
		$module = Yii::createObject($moduleConfigurationArray, [$name]);
		if ($module instanceof Module) return $module;
		return null;
	}

	/**
	 * Возвращает список подключённых в конфигурации приложения модулей.
	 * @param string[]|null $whiteList Массив с перечислением имён модулей, включаемых в перечисление, null - все модули
	 * @param bool $doLoad true - вернуть список, загрузив все модули, false - вернуть as is (загруженные модули
	 * вернутся, как модули, остальные вернутся, как конфигурации).
	 * @return array
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function ListModules(?array $whiteList = null, bool $doLoad = true):array {
		$modules = [];
		$appModules = (null === $whiteList)?Yii::$app->modules:array_intersect_key(Yii::$app->modules, array_flip($whiteList));
		foreach ($appModules as $name => $module) {
			if (($module instanceof Module)) {//загруженный модуль
				$modules[$name] = $module;
			} elseif ($doLoad) {
				if (null !== $loadedModule = self::LoadModule($name, $module)) $modules[$name] = $loadedModule;
			} else {
				$modules[$name] = $module;
			}
		}
		return $modules;
	}

	/**
	 * Возвращает модуль по его id
	 * @param string $moduleId
	 * @param bool $doLoad true - вернуть список, загрузив все модули, false - вернуть as is (загруженные модули
	 * вернутся, как модули, остальные вернутся, как конфигурации).
	 * @return Module|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetModuleById(string $moduleId, bool $doLoad = true):?Module {
		return ArrayHelper::getValue(self::ListModules([$moduleId], $doLoad), $moduleId);
	}


	/**
	 * Возвращает модуль по его имени класса
	 * @param string $className
	 * @return Module|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetModuleByClassName(string $className):?Module {
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