<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Traversable;
use function array_keys;
use Closure;
use Throwable;
use yii\helpers\ArrayHelper as YiiArrayHelper;

/**
 * Class ArrayHelper
 */
class ArrayHelper extends YiiArrayHelper {
	public const FLAG_COMPARE_KEYS = 1;//наборы ключей должны совпадать
	public const FLAG_COMPARE_VALUES = 2;//наборы значений должны совпадать
	public const FLAG_COMPARE_KEY_VALUES_PAIRS = 4;//наборы ключ-значение должны совпадать
	public const FLOAT_DELTA = 1.0E-13;//Дельта сравнения двух чисел с плавающей точкой внутри PHP

	/**
	 * Шорткат для объединения массива с массивом, полученным в цикле
	 * @see https://github.com/kalessil/phpinspectionsea/blob/master/docs/performance.md#slow-array-function-used-in-loop
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function loopArrayMerge(array $array1, array $array2):array {
		return array_merge($array1, array_merge(...$array2));
	}

	/**
	 * Расширенная функция, может кидать исключение или выполнять замыкание
	 * @param array|object $array
	 * @param array|Closure|string|int $key
	 * @param null|mixed|Throwable $default
	 * @return mixed
	 * @throws Throwable
	 */
	public static function getValue($array, $key, $default = null) {
		/**
		 * Обход отсутствия строгой типизации в Yii2. \yii\helpers\BaseArrayHelper::getValue() позволяет
		 * передавать null в параметре, но в php 8.1 метод упадёт на проверке property_exists()
		 * @see https://github.com/pozitronik/yii2-badgewidget/issues/5
		 */
		if (null === $key && is_object($array) && isset($array->{null})) return $array->{null};
		$result = parent::getValue($array, $key, $default);
		if ($result === $default) {
			if ($default instanceof Closure) {
				return $default($array, $key);
			}
			if ($default instanceof Throwable) {
				throw $default;
			}
		}
		/** @var null|int|string $result */
		return $result;
	}

	/**
	 * Устанавливает значение в массиве только в том случае, если его ещё не существует
	 * @param array $array
	 * @param array|string|null $path
	 * @param mixed $value
	 * @return bool true если инициализация была проведена
	 * @throws Throwable
	 */
	public static function initValue(array &$array, array|string|null $path, mixed $value):bool {
		if ((null === $currentValue = self::getValue($array, $path)) && $currentValue !== $value) {
			self::setValue($array, $path, $value);
			return true;
		}
		return false;
	}

	/**
	 * Ищет значение в многомерном массиве, если находит его, то возвращает массив со всеми ключами до этого элемента
	 * @param array $array
	 * @param mixed $search
	 * @param array $keys
	 * @return array
	 */
	public static function array_find_deep(array $array, mixed $search, array $keys = []):array {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$sub = self::array_find_deep($value, $search, array_merge($keys, [$key]));
				if (count($sub)) {
					return $sub;
				}
			} elseif ($value === $search) {
				return array_merge($keys, [$key]);
			}
		}

		return [];
	}

	/**
	 * Removes duplicate values from an array with multidimensional support
	 * @param array $array
	 * @param int $sort_flags
	 * @return array
	 */
	public static function array_unique(array $array, int $sort_flags = SORT_STRING):array {
		foreach ($array as &$val) {
			if (is_array($val)) {
				$val = self::array_unique($val, $sort_flags);
			} else {
				return array_unique($array);
			}
		}
		return $array;
	}

	/**
	 * Возвращает разницу между двумя ассоциативными массивами по их ключам.
	 * В отличие от array_diff_key вложенные массивы считает данными
	 * @param array $array1
	 * @param array $array2
	 * @return array
	 */
	public static function diff_keys(array $array1, array $array2):array {
		$result = [];
		foreach ($array1 as $key => $value) {
			if (!array_key_exists($key, $array2)) $result[$key] = $value;
		}
		foreach ($array2 as $key => $value) {
			if (!array_key_exists($key, $array1)) $result[$key] = $value;
		}
		return $result;
	}

	/**
	 * Рекурсивно объединяет массивы так, что существующие ключи обновляются, новые ключи - добавляются.
	 * Пример:
	 * ArrayHelper::merge_recursive(['10' => ['5' => [3, 2]]], ['10' => ['6' => [4, 7]]]);
	 * вернёт:
	 * ['10' => ['5' => [3, 2], '6' => [4, 7]]
	 *
	 * @param array $array1
	 * @param array $array2
	 * @param array|null $_
	 * @return array
	 */
	public static function merge_recursive(array $array1, array $array2, array $_ = null):array {
		$arrays = func_get_args();
		$merged = [];
		while ($arrays) {
			$array = array_shift($arrays);
			if (!$array) continue;
			/** @var array $array */
			foreach ($array as $key => $value)
				if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
					$merged[$key] = self::merge_recursive($merged[$key], $value);
				} else {
					$merged[$key] = $value;
				}
		}
		return $merged;
	}

	/**
	 * Аналог ArrayHelper::map, возвращающий смапленные аттрибуты по ключам.
	 * @param array $array
	 * @param array $attributes Карта атрибутов в формате ['label' => 'attributeName', 'label2' => 'attribute2Name...]
	 * @param string $keyIndexCode Параметр, при подстановке которого в имя атрибута в значении будет возвращен индекс
	 * @param string $valueCode Параметр, при подстановке которого в имя атрибута в значении будет возвращено значение
	 * @return array Массив в формате [['label' => $attribute1, 'label2' => $attribute2]]
	 * @throws Throwable
	 */
	public static function mapEx(array $array, array $attributes, string $keyIndexCode = 'key', string $valueCode = 'value'):array {
		$result = [];
		foreach ($array as $key => $element) {
			$cell = [];
			foreach ($attributes as $label => $attribute) {
				if (($attribute === $keyIndexCode)) {
					$value = (string)$key;
				} elseif ($attribute === $valueCode) {
					$value = $element;
				} else {
					$value = self::getValue($element, $attribute);
				}
				$cell[$label] = $value;
			}
			$result[] = $cell;
		}

		return $result;
	}

	/**
	 * Аналог ArrayHelper::map склеивающий значения нескольких аттрибутов
	 * @param array $array
	 * @param mixed $id
	 * @param array $concat_attributes
	 * @param string $separator
	 * @return array
	 * @throws Throwable
	 */
	public static function cmap(array $array, mixed $id, array $concat_attributes = [], string $separator = ' '):array {
		$result = [];
		foreach ($array as $element) {
			$key = self::getValue($element, $id);
			$value = [];
			foreach ($concat_attributes as $el) {
				$value[] = self::getValue($element, $el);
			}
			$result[$key] = implode($separator, $value);
		}

		return $result;
	}

	/**
	 * Аналог ArrayHelper::map склеивающий значения нескольких аттрибутов, также умеющий склеивать и значения ключей
	 * @param array $array Обрабатываемый массив объектов
	 * @param array $concat_keys Массив атрибутов, подставляемых в ключ. Если атрибут не существует, подставляется непосредственное значение. Если значение - функция, то подставится её результат
	 * @param array $concat_values Массив атрибутов, подставляемых в значение. Если атрибут не существует, подставляется непосредственное значение. Если значение - функция, то подставится её результат
	 * @param string $keys_separator Разделитель для ключей
	 * @param string $values_separator Разделитель для атрибутов
	 * @return array
	 *
	 * @throws Throwable
	 *
	 */
	public static function cmapEx(array $array, array $concat_keys, array $concat_values = [], string $keys_separator = '_', string $values_separator = ' '):array {
		$result = [];
		foreach ($array as $element) {
			$value = [];
			foreach ($concat_keys as $key) {
				if ((is_object($element))) {
					$value[] = $element->$key??$key;
				} else {
					$value[] = self::getValue($element, $key, $key);
				}

			}
			$key = implode($keys_separator, $value);
			$value = [];
			foreach ($concat_values as $el) {
				if ((is_object($element))) {
					$value[] = $element->$el??$el;
				} else {
					$value[] = self::getValue($element, $el, $el);
				}
			}
			$result[$key] = implode($values_separator, $value);
		}

		return $result;
	}

	/**
	 * Мапит значения субмассива к верхнему индексу массива
	 * @param array $array
	 * @param mixed $attribute
	 * @return array
	 * @throws Throwable
	 */
	public static function keymap(array $array, mixed $attribute):array {
		$result = [];
		foreach ($array as $key => $element) {
			$result[$key] = self::getValue($element, $attribute);
		}
		return $result;
	}

	/**
	 * Устанавливает значение последней ячейке массива. Если параметр $value не установлен, удаляет последнюю ячейку массива
	 * @param array $array
	 * @param mixed|null $value
	 */
	public static function setLast(array &$array, mixed $value = null):void {
		if (!count($array)) return;
		end($array);
		if (null === $value) {
			unset($array[key($array)]);
		} else {
			$array[key($array)] = $value;
		}
	}

	/**
	 * Возвращает первый ключ массива (удобно для парсинга конфигов в тех случаях, когда нельзя полагаться на array_key_first())
	 * @param array $array
	 * @return int|string|null
	 * @throws Throwable
	 */
	public static function key(array $array) {
		return self::getValue(array_keys($array), 0);
	}

	/**
	 * @param array|null $array
	 * @param Closure|string $from
	 * @param Closure|string $to
	 * @param null $group
	 * @return array
	 */
	public static function map($array, $from, $to, $group = null):array {
		if (!is_iterable($array)) return [];
		return parent::map($array, $from, $to, $group);
	}

	/**
	 * Мержит массивы строк так, что значения массива объединяются (не перезаписываются).
	 * Теоретически, работает и рекурсивно (не проверялось).
	 * Также теоретически будет работать не только для строк, но для любых значений, которые приводятся к строкам
	 * @param string $glue
	 * @param string[] $array1
	 * @param string[] $array2
	 * @param string[]|null $_
	 * @return array
	 * @throws Throwable
	 */
	public static function mergeImplode(string $glue, array $array1, array $array2, array $_ = null):array {
		$arrays = func_get_args();
		array_shift($arrays);//skip first argument
		$merged = [];
		while ($arrays) {
			$array = array_shift($arrays);
			if (!$array) continue;
			/** @var array $array */
			foreach ($array as $key => $value)
				if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
					$merged[$key] = self::mergeImplode($glue, $merged[$key], $value);
				} elseif (null === $currentValue = self::getValue($merged, $key)) {
					$merged[$key] = $value;
				} else {
					$merged[$key] = implode($glue, [$currentValue, $value]);//no unique filtering!
				}
		}
		return $merged;
	}

	/**
	 * Easy filtering routine
	 * @param array $array
	 * @param array $filterValues array of values, that shall be dropped
	 * @param bool $strict strict types filtering
	 * @return array
	 */
	public static function filterValues(array $array, array $filterValues = ['', false, null], bool $strict = false):array {
		return (array_filter($array, static function($item) use ($filterValues, $strict) {
			return !in_array($item, $filterValues, $strict);
		}));
	}

	/**
	 * Возвращает количество вхождений значения в массив
	 * @param array $array
	 * @param $value
	 * @return int
	 */
	public static function countValue(array $array, $value):int {
		return count(array_keys($array, $value));
	}

	/**
	 * @param array $array
	 * @return mixed
	 */
	public static function getRandItem(array $array) {
		/** @var int|string $rand */
		$rand = array_rand($array);
		return $array[$rand];
	}

	/**
	 * Переименование ключей в ассоциативном массиве.
	 * Например, переименовать ошибки полей в доле на наши.
	 * $map = [входное поле => выходное поле]
	 * Не сохраняет сортировку.
	 *
	 * @param array $array
	 * @param string[] $map
	 * @return array
	 */
	public static function renameKeysByMap(array $array, array $map):array {
		foreach ($map as $search => $replace) {
			if (isset($array[$search])) {
				$array[$replace] = $array[$search];
				unset($array[$search]);
			}
		}
		return $array;
	}

	/**
	 * Переименовать в ключах массива $array все вхождения строки $search на $replace
	 *
	 * @param array $array
	 * @param string $search
	 * @param string $replace
	 * @return array
	 */
	public static function replaceStrInKeys(array $array, string $search, string $replace):array {
		return array_combine(str_replace($search, $replace, array_keys($array)), $array);
	}

	/**
	 * Сравнивает два массива между собой по наборам данных (с учётом вложенности).
	 * Сравнение всегда строгое
	 * @param array|Traversable $array_one
	 * @param array|Traversable $array_two
	 * @param int $flags
	 * @return bool
	 */
	public static function isEqual(array|Traversable $array_one, array|Traversable $array_two, int $flags = self::FLAG_COMPARE_KEYS + self::FLAG_COMPARE_VALUES + self::FLAG_COMPARE_KEY_VALUES_PAIRS):bool {
		if (count($array_one) !== count($array_two)) return false;
		foreach ($array_one as $a1key => $a1value) {
			if (($flags & self::FLAG_COMPARE_KEYS) && !static::keyExists($a1key, $array_two)) {//разница по ключам
				return false;
			}
			if (($flags & self::FLAG_COMPARE_VALUES) && !static::isInWithFloatDelta($a1value, $array_two, true)) {//разница по значениям
				return false;
			}
			if ($flags & self::FLAG_COMPARE_KEY_VALUES_PAIRS) {
				if (!static::keyExists($a1key, $array_two) || !static::isEquals($a1value, $array_two[$a1key])) {
					return false;
				}
			}
			if (static::isTraversable($a1value) && static::isTraversable($array_two[$a1key]??null) && false === static::isEqual($a1value, $array_two[$a1key]??null)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Функция аналогична static::isIn() но для поиска float-значений учитывает дельту PHP
	 * @param mixed $needle
	 * @param array|Traversable $haystack
	 * @param bool $strict
	 * @return bool
	 */
	public static function isInWithFloatDelta(mixed $needle, array|Traversable $haystack, bool $strict = false):bool {
		if (is_float($needle)) {
			foreach ($haystack as $value) {
				if (is_float($value) && static::isFloatEquals($needle, $value)) {
					return true;
				}
			}
			return false;
		}
		return static::isIn($needle, $haystack, $strict);
	}

	/**
	 * @param mixed $floatOne
	 * @param mixed $floatTwo
	 * @param float $delta
	 * @return bool
	 */
	public static function isFloatEquals(mixed $floatOne, mixed $floatTwo, float $delta = self::FLOAT_DELTA):bool {
		if (is_infinite($floatTwo) && is_infinite($floatOne)) {
			return true;
		}

		if ((is_infinite($floatTwo) xor is_infinite($floatOne)) ||
			(is_nan($floatTwo) || is_nan($floatOne)) ||
			abs($floatTwo - $floatOne) > $delta) {
			return false;
		}
		return true;
	}

	/**
	 * @param mixed $scalarOne
	 * @param mixed $scalarTwo
	 * @return bool
	 */
	private static function isEquals(mixed $scalarOne, mixed $scalarTwo):bool {
		if (is_float($scalarOne) && is_float($scalarTwo)) return static::isFloatEquals($scalarOne, $scalarTwo);
		return $scalarOne === $scalarTwo;
	}
}