<?php
declare(strict_types = 1);

namespace unit;

use Codeception\Test\Unit;
use pozitronik\helpers\ArrayHelper;
use Throwable;

/**
 * Class ArrayHelperTest
 */
class ArrayHelperTest extends Unit {

	/**
	 * @return void
	 * @throws Throwable
	 */
	public function testInitValue():void {
		$testArray = ['a' => 1, 'b' => 2, 'c' => 3, 's' => [
			'alpha' => 10,
			'beta' => 20
		]];

		$verifyArray = $testArray;

		self::assertFalse(ArrayHelper::initValue($testArray, 'a', 42));
		self::assertFalse(ArrayHelper::initValue($testArray, 's.alpha', 42));

		self::assertEquals($verifyArray, $testArray);

		self::assertTrue(ArrayHelper::initValue($testArray, 'd', 37));
		self::assertTrue(ArrayHelper::initValue($testArray, 'e.p.x', 56));
		self::assertTrue(ArrayHelper::initValue($testArray, 's.alpha.gamma', 42));

		self::assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 's' => [
			'alpha' => [
				'gamma' => 42,
				10
			],
			'beta' => 20,
		], 'd' => 37,
			'e' => [
				'p' => [
					'x' => 56
				]
			]
		], $testArray);
	}

	/**
	 * Такие дела
	 * @return void
	 */
	public function testFloatEquals():void {
		$piPHP = 3.1415926535897;
		$piPg  = 3.14159265358969;
		self::assertNotEquals($piPHP, $piPg);
		self::assertNotSame($piPHP, $piPg);
		self::assertTrue(ArrayHelper::isFloatEquals($piPHP, $piPg, 0.0000000000001));
	}

	/**
	 * @return void
	 */
	public function testIsEqual():void {
		$array_one = ["red", "green", "blue"];
		$array_two = ["red", "green", "yellow"];
		$array_three = ["red", "blue", "green"];
		$array_four = ["a" => "red", "b" => "green", "c" => "yellow"];
		$array_five = ["x" => "red", "z" => "yellow", "y" => "green"];
		$array_six = ["x" => "red", "z" => "yellow", "y" => "green"];
		$array_seven = ["null", null, "not null"];
		$array_eight = ["c" => "not null", "b" => "null", "a" => null];
		$array_nine = [[M_PI, [["a" => "red"]]], [null, "string", 0], [false, true]];
		$array_ten = ["a" => [M_PI, [["a" => 'red']]], "b" => [null, "string", 0], "c" => [false, true]];

		self::assertTrue(ArrayHelper::isEqual($array_one, $array_two, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertFalse(ArrayHelper::isEqual($array_one, $array_two, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertFalse(ArrayHelper::isEqual($array_one, $array_two, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));
		self::assertFalse(ArrayHelper::isEqual($array_one, $array_two, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS + ArrayHelper::FLAG_COMPARE_KEYS + ArrayHelper::FLAG_COMPARE_VALUES));

		self::assertTrue(ArrayHelper::isEqual($array_one, $array_three, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_one, $array_three, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertFalse(ArrayHelper::isEqual($array_one, $array_three, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));
		self::assertTrue(ArrayHelper::isEqual($array_one, $array_three, ArrayHelper::FLAG_COMPARE_KEYS + ArrayHelper::FLAG_COMPARE_VALUES));

		self::assertFalse(ArrayHelper::isEqual($array_four, $array_five, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_four, $array_five, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertFalse(ArrayHelper::isEqual($array_four, $array_five, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

		self::assertTrue(ArrayHelper::isEqual($array_five, $array_six, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_five, $array_six, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertTrue(ArrayHelper::isEqual($array_five, $array_six, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));
		self::assertTrue(ArrayHelper::isEqual($array_five, $array_six, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS + ArrayHelper::FLAG_COMPARE_KEYS + ArrayHelper::FLAG_COMPARE_VALUES));

		self::assertTrue(ArrayHelper::isEqual($array_one, $array_seven, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertFalse(ArrayHelper::isEqual($array_one, $array_seven, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertFalse(ArrayHelper::isEqual($array_one, $array_seven, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

		self::assertTrue(ArrayHelper::isEqual($array_seven, $array_seven, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_seven, $array_seven, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertTrue(ArrayHelper::isEqual($array_seven, $array_seven, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

		self::assertFalse(ArrayHelper::isEqual($array_seven, $array_eight, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_seven, $array_eight, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertFalse(ArrayHelper::isEqual($array_seven, $array_eight, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

		self::assertTrue(ArrayHelper::isEqual($array_nine, $array_nine, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_nine, $array_nine, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertTrue(ArrayHelper::isEqual($array_nine, $array_nine, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

		self::assertFalse(ArrayHelper::isEqual($array_nine, $array_ten, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_nine, $array_ten, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertFalse(ArrayHelper::isEqual($array_nine, $array_ten, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

		self::assertTrue(ArrayHelper::isEqual($array_ten, $array_ten, ArrayHelper::FLAG_COMPARE_KEYS));
		self::assertTrue(ArrayHelper::isEqual($array_ten, $array_ten, ArrayHelper::FLAG_COMPARE_VALUES));
		self::assertTrue(ArrayHelper::isEqual($array_ten, $array_ten, ArrayHelper::FLAG_COMPARE_KEY_VALUES_PAIRS));

	}

}