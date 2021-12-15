<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;

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
}