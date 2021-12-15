<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Closure;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use Throwable;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;

/**
 * Class ReflectionHelper
 * @package app\models\core\helpers
 */
class ReflectionHelper {

	/**
	 * Вытаскивает неймспейс из файла, если он там есть
	 * @param string $path
	 * @return string|false
	 */
	public static function ExtractNamespaceFromFile(string $path) {
		$lines = file($path);
		foreach ($lines as $line) {
			$line = trim($line);
			if (preg_match('/^namespace\W(.*);$/', $line)) {
				return preg_replace('/(^namespace\W)(.*)(;$)/', '$2', $line);
			}
		}
		return false;
	}

	/**
	 * Инициализирует рефлектор, но не загружает класс
	 * @param string|object $className Имя класса/экземпляр класса
	 * @param bool $throwOnFail true - упасть при ошибке, false - вернуть null
	 * @return ReflectionClass|null
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function New($className, $throwOnFail = true):?ReflectionClass {
		if (is_string($className) && !class_exists($className) && !interface_exists($className) && !trait_exists($className)) Yii::autoload($className);
		try {
			return new ReflectionClass($className);
		} catch (ReflectionException $t) {
			if ($throwOnFail) throw $t;
		}
		return null;
	}

	/**
	 * Загружает и возвращает экземпляр класса при условии его существования
	 * @param string $className Имя класса
	 * @param null|string[] $parentClassFilter Опциональный фильтр родительского класса
	 * @param bool $throwOnFail true - упасть при ошибке, false - вернуть null
	 * @return ReflectionClass|object|null
	 * @throws InvalidConfigException
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function LoadClassByName(string $className, ?array $parentClassFilter = null, $throwOnFail = true):?object {
		if (null === $class = self::New($className, $throwOnFail)) return null;
		if (self::IsInSubclassOf($class, $parentClassFilter)) return new $className();
		if ($throwOnFail) throw new InvalidConfigException("Class $className not found in application scope!");
		return null;
	}

	/**
	 * Загружает класс из файла (при условии одного класса в файле и совпадения имени файла с именем класса)
	 * @param string $fileName
	 * @param string[]|null $parentClassFilter Опциональный фильтр родительского класса
	 * @param bool $throwOnFail true - упасть при ошибке, false - вернуть null
	 * @return ReflectionClass|null
	 * @throws ReflectionException
	 * @throws Throwable
	 * @throws UnknownClassException
	 */
	public static function LoadClassFromFile(string $fileName, ?array $parentClassFilter = null, $throwOnFail = true):?object {
		return self::LoadClassByName(self::GetClassNameFromFile($fileName), $parentClassFilter, $throwOnFail);
	}

	/**
	 * Возвращает имя класса, находящегося в файле (при условии одного класса в файле и совпадения имени файла с именем класса)
	 * @param string $fileName
	 * @return string
	 */
	public static function GetClassNameFromFile(string $fileName):string {
		return self::ExtractNamespaceFromFile($fileName).'\\'.PathHelper::ChangeFileExtension($fileName);
	}

	/**
	 * Проверяет, является ли класс потомков одного из перечисленных классов
	 * @param ReflectionClass $class проверяемый класс
	 * @param null|string[] $subclassesList список родительских классов для проверки (null - не проверять)
	 * @return bool
	 */
	public static function IsInSubclassOf(ReflectionClass $class, ?array $subclassesList = null):bool {
		if (null === $subclassesList) return true;
		foreach ($subclassesList as $subclass) {
			if ($class->isSubclassOf($subclass)) return true;
		}
		return false;
	}

	/**
	 * Fast class name shortener
	 * app\modules\salary\models\references\RefGrades => RefGrades
	 * @param string $className
	 * @return string
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function GetClassShortName(string $className):string {
		/** @noinspection NullPointerExceptionInspection */
		return self::New($className)->getShortName();
	}

	/**
	 * @param string|object $model
	 * @param int $filter
	 * @return array
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function GetMethods($model, int $filter = ReflectionMethod::IS_PUBLIC):array {
		/** @noinspection NullPointerExceptionInspection */
		return self::New($model)->getMethods($filter);
	}

	/**
	 * @param mixed $t
	 * Cause is_executable not enough!
	 * @return bool
	 */
	public static function is_closure($t):bool {
		return $t instanceof Closure;
	}

	/**
	 * Делает публичным закрытый метод класса
	 * @param string|object $className -- класс (объект или имя)
	 * @param string $methodName -- имя метода, который нужно открыть для доступа
	 * @param bool $throwOnFail -- true - упасть при ошибке, false - вернуть null
	 * @return null|ReflectionMethod -- открытый метод (null при ошибке)
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 * @noinspection BadExceptionsProcessingInspection
	 */
	public static function setAccessible($className, string $methodName, $throwOnFail = true):?ReflectionMethod {
		if (null === $class = self::New($className, $throwOnFail)) return null;
		try {
			$reflectionMethod = new ReflectionMethod($class->getName(), $methodName);
			$reflectionMethod->setAccessible(true);
		} catch (Throwable $t) {
			return null;
		}
		return $reflectionMethod;
	}

	/**
	 * Возвращает тип атрибута класса, null, если не объявлен
	 * @param object|string $class
	 * @param string $property
	 * @return string|null
	 * @throws ReflectionException
	 */
	public static function getPropertyType(object|string $class, string $property):?string {
		return (new ReflectionProperty($class, $property))?->getType()?->getName();
	}
}