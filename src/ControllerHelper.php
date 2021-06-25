<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\helpers\Url;

/**
 * Class ControllerHelper
 */
class ControllerHelper {

	/**
	 * Загружает динамически класс веб-контроллера Yii2 по его пути
	 * @param string $fileName
	 * @param string|null $moduleId
	 * @param string[]|null $parentClassFilter Фильтр по родительскому классу (загружаемый контролер должен от него наследоваться)
	 * @return self|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function LoadControllerClassFromFile(string $fileName, ?string $moduleId, ?array $parentClassFilter = null):?object {
		$className = ReflectionHelper::GetClassNameFromFile($fileName);
		$class = ReflectionHelper::New($className);
		/** @noinspection NullPointerExceptionInspection */
		if (ReflectionHelper::IsInSubclassOf($class, $parentClassFilter)) {
			if (null === $moduleId) {
				$module = Yii::$app;
			} else {
				$module = ModuleHelper::GetModuleById($moduleId);
				if (null === $module) throw new InvalidConfigException("Module $moduleId not found or module not configured properly.");
			}
			return new $className(self::ExtractControllerId($className), $module);
		}

		return null;
	}

	/**
	 * Загружает динамически класс веб-контроллера Yii2 по его id и модулю
	 * @param string $controllerId
	 * @param string|null $moduleId
	 * @return self|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetControllerByControllerId(string $controllerId, ?string $moduleId):?object {
		if (null === $module = ModuleHelper::GetModuleById($moduleId)) throw new InvalidConfigException("Module $moduleId not found or module not configured properly.");
		$controllerId = implode('', array_map('ucfirst', preg_split('/-/', $controllerId, -1, PREG_SPLIT_NO_EMPTY)));
		return self::LoadControllerClassFromFile("{$module->controllerPath}/{$controllerId}Controller.php", $moduleId);

	}

	/**
	 * Выгружает список контроллеров в указанном неймспейсе
	 * @param string $path
	 * @param string|null $moduleId
	 * @param string[]|null $parentClassFilter Фильтр по классу родителя
	 * @return self[]
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetControllersList(string $path, ?string $moduleId = null, ?array $parentClassFilter = null):array {
		$result = [];

		$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(Yii::getAlias($path)), RecursiveIteratorIterator::SELF_FIRST);
		/** @var RecursiveDirectoryIterator $file */
		foreach ($files as $file) {
			if ($file->isFile() && 'php' === $file->getExtension() && null !== $controller = self::LoadControllerClassFromFile($file->getRealPath(), $moduleId, $parentClassFilter)) {
				$result[] = $controller;
			}
		}
		return $result;
	}

	/**
	 * По имени экшена возвращает абсолютный Url в приложении
	 * @param string $action
	 * @param array $params
	 * @return array
	 * @example SomeController::GetActionUrl('index', ['id' => 1]) => ['/some/index', 'id' => 1]
	 */
	public static function GetActionUrl(string $action, array $params = []):array {
		$controllerId = static::ExtractControllerId(static::class);
		$route = Utils::setAbsoluteUrl($controllerId.Utils::setAbsoluteUrl($action));
		if ([] !== $params) {
			array_unshift($params, $route);
		} else {
			$params = [$route];
		}
		return $params;
	}

	/**
	 * По имени экшена возвращает строковой абсолютный Url в приложении
	 * @param string $action
	 * @param array $params
	 * @param bool|string $scheme @see Url::to() $scheme parameter
	 * @return string
	 * @example SomeController::to('index', ['id' => 1]) => '/some/index?id=1'
	 */
	public static function to(string $action, array $params = [], $scheme = false):string {
		return Url::to(self::GetActionUrl($action, $params), $scheme);
	}

	/**
	 * Возвращает все экшены контроллера
	 * @param bool $asRequestName Привести имя экшена к виду в запросе
	 * @return string[]
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function GetControllerActions(bool $asRequestName = true):array {
		$names = ArrayHelper::getColumn(ReflectionHelper::GetMethods(static::class), 'name');
		$names = preg_filter('/^action([A-Z])(\w+?)/', '$1$2', $names);
		if ($asRequestName) {
			foreach ($names as &$name) $name = self::GetActionRequestName($name);
		}
		return $names;
	}

	/**
	 * Переводит вид имени экшена к виду запроса, который этот экшен дёргает.
	 * @param string $action
	 * @return string
	 * @example actionSomeActionName => some-action-name
	 * @example OtherActionName => other-action-name
	 */
	public static function GetActionRequestName(string $action):string {
		/** @var array $lines */
		$lines = preg_split('/(?=[A-Z])/', $action, -1, PREG_SPLIT_NO_EMPTY);
		if ('action' === $lines[0]) unset($lines[0]);
		return mb_strtolower(implode('-', $lines));
	}

	/**
	 * Вытаскивает из имени класса контроллера его id
	 * app/shit/BlaBlaBlaController => bla-bla-bla
	 * @param string $className
	 * @return string
	 */
	public static function ExtractControllerId(string $className):string {
		$controllerName = preg_replace('/(^.+)(\\\)([A-Z].+)(Controller$)/', '$3', $className);//app/shit/BlaBlaBlaController => BlaBlaBla
		return mb_strtolower(implode('-', preg_split('/([[:upper:]][[:lower:]]+)/', $controllerName, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY)));
	}
}