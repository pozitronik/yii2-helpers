<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use pozitronik\helpers\Utils;

/**
 * class UtilsTest
 */
class UtilsTest extends Unit {
	/**
	 * @var UnitTester
	 */
	protected $tester;

	/**
	 * @return void
	 */
	public function testErrors2String():void {
		$this->tester->assertEquals(
			"name: Ошибка #1: 0: Внутренняя ошибка #1\nОшибка #2: 0: Внутренняя ошибка #2\nОшибка #3: 0: Внутренняя ошибка #4",
			Utils::Errors2String([
				'name' => [
					'Ошибка #1' => [
						'Внутренняя ошибка #1'
					],
					'Ошибка #2' => [
						'Внутренняя ошибка #2',
						'Ошибка #3' => [
							'Внутренняя ошибка #4'
						],
					]
				]
			])
		);
	}
}
