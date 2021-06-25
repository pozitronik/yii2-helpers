<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;

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
	 * Возвращает список подключённых модулей. Список можно задать в конфигурации, либо же вернутся все подходящие модули, подключённые в Web.php
	 * @return Module[] Массив подключённых модулей
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function ListModules():array {
		$modules = [];
		foreach (Yii::$app->modules as $name => $module) {
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

	/**
	 * Возвращает путь внутри модуля. Путь всегда будет абсолютный, от корня
	 * @param string|array $route -- контроллер и экшен + параметры
	 * @return string
	 * @throws InvalidConfigException
	 * @throws Throwable
	 * @example SalaryModule::to(['salary/index','id' => 10]) => /salary/salary/index?id=10
	 * @example UsersModule::to('users/index') => /users/users/index
	 */
	public static function to($route = ''):string {
		if ((null === $module = Module::getInstance()) && null === $module = self::GetModuleByClassName(static::class)) {
			throw new InvalidConfigException("Модуль ".static::class." не подключён");
		}
		if (is_array($route)) {/* ['controller{/action}', 'actionParam' => $paramValue */
			ArrayHelper::setValue($route, 0, Utils::setAbsoluteUrl($module->id.Utils::setAbsoluteUrl(ArrayHelper::getValue($route, 0))));
		} else {/* 'controller{/action}' */
			if ('' === $route) $route = $module->defaultRoute;
			$route = Utils::setAbsoluteUrl($module->id.Utils::setAbsoluteUrl($route));
		}
		return Url::to($route);
	}

	/**
	 * Генерация html-ссылки внутри модуля (аналог Html::a(), но с автоматическим учётом путей модуля).
	 * @param string $text
	 * @param array|string|null $url
	 * @param array $options
	 * @return string
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function a(string $text, $url = null, array $options = []):string {
		$url = static::to($url);
		return Html::a($text, $url, $options);
	}


}