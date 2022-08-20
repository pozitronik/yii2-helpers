<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;
use yii\base\BaseObject;

/**
 * Class CacheHelper
 */
class CacheHelper {

	/**
	 * По атрибутам функции вычисляет её "подпись" для ключа кеша.
	 * @param array $parameters Массив аргументов функции (всегда func_get_args())
	 * @param array $attributes Массив дополнительных аргументов, для включения в подпись
	 * @return string
	 */
	public static function MethodParametersSignature(array $parameters, array $attributes = []):string {
		return serialize($parameters + $attributes);
	}

	/**
	 * Вычисляет уникальную подпись метода и его параметров. Подписи всегда идентичны для одного метода с одним набором параметров
	 * Пример:
	 * <?php
	 * class SomeClass {
	 *    public $id = 1;
	 *
	 *    function SomeFunction(string $a = "first", int $b = 2, bool $c = true) {
	 *        echo CacheHelper::MethodSignature(__METHOD__, func_get_args(), ["id" => $this->id]);
	 *    }
	 * }
	 *
	 * (new SomeClass())->SomeFunction();
	 * ?>
	 *
	 * @param string $method всегда __METHOD__
	 * @param array $parameters всегда func_get_args()
	 * @param array $attributes Массив дополнительных аргументов, для включения в подпись
	 * @return string
	 * @throws Throwable
	 */
	public static function MethodSignature(string $method = __METHOD__, array $parameters = [], array $attributes = []):string {
		$parametersSignature = static::MethodParametersSignature($parameters, $attributes);
		return "{$method}({$parametersSignature})";
	}

	/**
	 * Вычисляет уникальную подпись метода класса.
	 * @param BaseObject|string $object
	 * @param string $method
	 * @param array $parameters
	 * @param array $attributes
	 * @return string
	 * @noinspection PhpPossiblePolymorphicInvocationInspection
	 * @noinspection PhpDocSignatureInspection
	 */
	public static function ObjectMethodSignature(object|string $object = __CLASS__, string $method = __METHOD__, array $parameters = [], array $attributes = []):string {
		$objectKey = null;//assume it's static
		if (is_object($object)) {
			if ($object->hasProperty('primaryKey')) $objectKey = serialize($object->primaryKey);
			$object = $object::class;
		}
		$parametersSignature = static::MethodParametersSignature($parameters, $attributes);
		return "{$object}{$objectKey}{$method}({$parametersSignature})";
	}

	/**
	 * Вычисляет уникальную подпись для объекта с любыми идентификаторами
	 * @param object|string $object
	 * @param array $identifiers
	 * @return string
	 */
	public static function ObjectSignature(object|string $object = __CLASS__, array $identifiers = []):string {
		$objectKey = null;//assume it's static
		if (is_object($object)) {
			if ($object->hasProperty('primaryKey')) $objectKey = serialize($object->primaryKey);
			$object = $object::class;
		}
		(null === $objectKey)
			?$identifierArray[$object] = $identifiers
			:$identifierArray[$object][$objectKey] = $identifiers;
		return serialize($identifierArray);
	}
}