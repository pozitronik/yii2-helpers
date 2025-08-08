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
	 * @param object|string $className Имя класса/экземпляр класса
	 * @param bool $throwOnFail true - упасть при ошибке, false - вернуть null
	 * @return ReflectionClass|null
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function New(object|string $className, bool $throwOnFail = true):?ReflectionClass {
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
	 * @param string|object $className Имя класса или экземпляр класса
	 * @param null|string[] $parentClassFilter Опциональный фильтр родительского класса
	 * @param bool $throwOnFail true - упасть при ошибке, false - вернуть null
	 * @return object|null
	 * @throws InvalidConfigException
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function LoadClassByName(string|object $className, ?array $parentClassFilter = null, bool $throwOnFail = true):?object {
		if (null === $class = static::New($className, $throwOnFail)) return null;
		if (static::IsInSubclassOf($class, $parentClassFilter)) return new $className();
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
	public static function LoadClassFromFile(string $fileName, ?array $parentClassFilter = null, bool $throwOnFail = true):?object {
		return static::LoadClassByName(static::GetClassNameFromFile($fileName), $parentClassFilter, $throwOnFail);
	}

	/**
	 * Возвращает имя класса, находящегося в файле (при условии одного класса в файле и совпадения имени файла с именем класса)
	 * @param string $fileName
	 * @return string
	 */
	public static function GetClassNameFromFile(string $fileName):string {
		return static::ExtractNamespaceFromFile($fileName).'\\'.PathHelper::ChangeFileExtension($fileName);
	}

	/**
	 * Проверяет, является ли класс потомков одного из перечисленных классов
	 * @param ReflectionClass $class Проверяемый класс
	 * @param null|string[] $subclassesList Список родительских классов для проверки (null - не проверять)
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
	 * @param string $className
	 * @return null|string
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function GetClassShortName(string $className):?string {
		return self::New($className)?->getShortName();
	}

	/**
	 * @param string|object $model
	 * @param int $filter
	 * @return null|array
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function GetMethods(string|object $model, int $filter = ReflectionMethod::IS_PUBLIC):?array {
		return self::New($model)?->getMethods($filter);
	}

	/**
	 * @param mixed $t
	 * Cause is_executable not enough!
	 * @return bool
	 */
	public static function is_closure(mixed $t):bool {
		return $t instanceof Closure;
	}

	/**
	 * Делает публичным закрытый метод класса
	 * @param object|string $className Класс (объект или имя)
	 * @param string $methodName Имя метода, который нужно открыть для доступа
	 * @param bool $throwOnFail true - упасть при ошибке, false - вернуть null
	 * @return null|ReflectionMethod Открытый метод (null при ошибке)
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function setAccessible(object|string $className, string $methodName, bool $throwOnFail = true):?ReflectionMethod {
		if (null === $class = self::New($className, $throwOnFail)) return null;
		try {
			$reflectionMethod = new ReflectionMethod($class->getName(), $methodName);
			$reflectionMethod->setAccessible(true);
		} catch (Throwable) {
			return null;
		}
		return $reflectionMethod;
	}

	/**
	 * Возвращает значение свойства (в т.ч. приватного) $property в классе $className для объекта $object
	 * @param object|string $className Класс (объект или имя)
	 * @param string $property Название свойства
	 * @param object|null $object null для статических переменных, иначе объект переменной
	 * @param bool $throwOnFail
	 * @return mixed
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function getValue(object|string $className, string $property, ?object $object = null, bool $throwOnFail = true):mixed {
		if (null === $class = self::New($className, $throwOnFail)) return null;
		try {
			$reflectionProperty = new ReflectionProperty($class->getName(), $property);
			$reflectionProperty->setAccessible(true);
			return $reflectionProperty->getValue($object);
		} catch (Throwable) {
			return null;
		}
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

	/**
	 * @param object|string $class
	 * @return bool|null
	 * @throws ReflectionException
	 * @throws UnknownClassException
	 */
	public static function isInstantiable(object|string $class):?bool {
		return self::New($class)?->isInstantiable();
	}
}