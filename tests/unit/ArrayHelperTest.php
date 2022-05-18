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

}