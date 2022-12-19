<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionException;
use ReflectionMethod;
use Throwable;
use Yii;
use yii\base\Action;
use yii\base\Controller;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;
use yii\helpers\FileHelper;

/**
 * Class ControllerHelper
 */
class ControllerHelper {

	/**
	 * Загружает динамически класс веб-контроллера Yii2 по его пути
	 * @param string $fileName
	 * @param string|null $moduleId Если null, то контроллер загрузится от приложения
	 * @param string[]|null $parentClassFilter Фильтр по родительскому классу (загружаемый контролер должен от него наследоваться)
	 * @return Controller|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function LoadControllerClassFromFile(string $fileName, ?string $moduleId = null, ?array $parentClassFilter = null):?Controller {
		if (!file_exists(Yii::getAlias($fileName, false))) return null;
		$className = ReflectionHelper::GetClassNameFromFile(Yii::getAlias($fileName));
		if (null === $id = self::ExtractControllerIdWithSubFolders($className)) return null;
		if ((null === $class = ReflectionHelper::New($className)) || !$class->isInstantiable()) return null;
		if (ReflectionHelper::IsInSubclassOf($class, $parentClassFilter)) {
			$module = (null === $moduleId)
				?Yii::$app
				:ModuleHelper::GetModuleById($moduleId);
			if (null === $module) throw new InvalidConfigException("Module $moduleId not found or module not configured properly.");
			return Yii::createObject([
				'class' => $className,
				'id' => $id,
				'module' => $module
			]);
		}

		return null;
	}

	/**
	 * По пути контроллера пытается определить, какому модулю он принадлежит.
	 * Важно: функции для определения путей требуется загрузить все модули, это может привести к неожиданным последствиям
	 * @param string $fileName
	 * @return string|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetControllerModuleIdByFilename(string $fileName):?string {
		$controllerFilePath = FileHelper::normalizePath(PathHelper::ExtractFilePath(Yii::getAlias($fileName)));
		$controllersMap = ModuleHelper::GetAllControllersPaths();
		/*вычленяет только те модули, $controllerPath которых является корневым для искомого контроллера*/
		return ([] === $subarray = array_filter($controllersMap, static fn(string $value) => 0 === strpos($controllerFilePath, $value)))
			?null
			:key($subarray);
	}

	/**
	 * Загружает динамически класс веб-контроллера Yii2 по его id и модулю
	 * @param string $controllerId
	 * @param string|null $moduleId
	 * @return self|null
	 * @throws InvalidConfigException
	 * @throws Throwable
	 */
	public static function GetControllerByControllerId(string $controllerId, ?string $moduleId = null):?object {
		$module = (null === $moduleId)?Yii::$app:ModuleHelper::GetModuleById($moduleId);
		if (null === $module) throw new InvalidConfigException("Module $moduleId not found or module not configured properly.");
		$controllerId = implode('', array_map('ucfirst', preg_split('/-/', $controllerId, -1, PREG_SPLIT_NO_EMPTY)));
		return self::LoadControllerClassFromFile("{$module->controllerPath}/{$controllerId}Controller.php", $moduleId);

	}

	/**
	 * Выгружает список контроллеров в указанном неймспейсе
	 * @param string $path
	 * @param string|null $moduleId
	 * @param string[]|null $parentClassFilter Фильтр по классу родителя
	 * @return Controller[]
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
	 * Возвращает список контроллеров в указанных каталогах
	 * @return string[]
	 * @throws Throwable
	 */
	public static function GetControllersFromPath(array $controllerDirs = ['@app/controllers']):array {
		$result = [];
		foreach ($controllerDirs as $controllerDir => $idPrefix) {
			$controllers = self::GetControllersList((string)$controllerDir, null, [Controller::class]);
			$result[$controllerDir] = ArrayHelper::map($controllers, static fn(Controller $model) => ('' === $idPrefix)
				?$model->id
				:$idPrefix.'/'.$model->id, static fn(Controller $model) => ('' === $idPrefix)?$model->id:$idPrefix.'/'.$model->id);
		}
		return $result;
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
		return self::ConvertControllerNameToId(self::ExtractControllerName($className));
	}

	/**
	 * app/shit/BlaBlaBlaController => BlaBlaBla
	 * @param string $className
	 * @return string
	 */
	public static function ExtractControllerName(string $className):string {
		return preg_replace('/(^.+)(\\\)([A-Z].+)(Controller$)/', '$3', $className);
	}

	/**
	 * BlaBlaBla => bla-bla-bla
	 * @param string $controllerName
	 * @return string
	 */
	public static function ConvertControllerNameToId(string $controllerName):string {
		return mb_strtolower(implode(
			'-',
			preg_split(
				'/([[:upper:]][[:lower:]]+)/',
				$controllerName,
				-1,
				PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
			)
		));
	}

	/**
	 * app\controllers\ajax\DefaultController => ajax/default
	 * Returns the controller ID, including subfolders
	 * @param string $className
	 * @return null|string The controller ID, null, if file name is wrong
	 */
	public static function ExtractControllerIdWithSubFolders(string $className):?string {
		if (preg_match('/([a-zA-Z0-9])+Controller$/', $className)) {
			$controllerName = self::ExtractControllerName($className);
			$controllerId = self::ConvertControllerNameToId($controllerName);

			if (preg_match(sprintf('/controllers\\\([a-zA-Z].+)\\\%s/', $controllerName), $className, $matches)) {
				$folders = mb_strtolower(str_replace('\\', '/', $matches[1]));

				$controllerId = "{$folders}/{$controllerId}";
			}

			return $controllerId;
		}
		return null;
	}

	/**
	 * Returns all loadable controller actions
	 * @param Controller $controller
	 * @param bool $asRequestName Cast action name to request name
	 * @return string[]
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 * @throws Throwable
	 */
	public static function GetControllerActions(Controller $controller, bool $asRequestName = true):array {
		$actionsNames = array_merge(preg_filter('/^action([A-Z])(\w+?)$/', '$1$2',
			ArrayHelper::getColumn(ReflectionHelper::GetMethods($controller::class), 'name')
		), array_keys($controller->actions()));
		foreach ($actionsNames as &$actionName) {
			$actionName = static::IsControllerHasAction($controller, $actionName)
				?$actionName
				:null;
		}
		unset ($actionName);
		$actionsNames = array_filter($actionsNames);
		if ($asRequestName) $actionsNames = array_map(static fn($actionName):string => ControllerHelper::GetActionRequestName($actionName), $actionsNames);

		return $actionsNames;
	}

	/**
	 * Checks if controller has a loadable action method (without creation of a Action object itself)
	 * @param Controller $controller
	 * @param string $actionName
	 * @return bool
	 * @throws Throwable
	 */
	public static function IsControllerHasAction(Controller $controller, string $actionName):bool {
		return ((null !== $class = ArrayHelper::getValue($controller->actions(), $actionName)) && is_subclass_of($class, Action::class)) ||
			static::IsControllerHasActionMethod($controller, static::GetActionRequestName($actionName));
	}

	/**
	 * @param Controller $controller
	 * @param string $actionName
	 * @return bool
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function IsControllerHasActionMethod(Controller $controller, string $actionName):bool {
		if (preg_match('/^(?:[a-z\d_]+-)*[a-z\d_]+$/', $actionName)) {
			$actionName = 'action'.str_replace(' ', '', ucwords(str_replace('-', ' ', $actionName)));
			if (method_exists($controller, $actionName) && (!property_exists($controller, 'disabledActions') || !in_array($actionName, ReflectionHelper::getValue($controller, 'disabledActions', $controller), true))) {
				$method = new ReflectionMethod($controller, $actionName);
				if ($method->isPublic() && $method->getName() === $actionName) return true;
			}
		}
		return false;
	}

	/**
	 * Является ли текущий запрос запросом на ajax-валидацию формы
	 * @return bool
	 */
	public static function IsAjaxValidationRequest():bool {
		return null !== Yii::$app->request->post('ajax');
	}

}