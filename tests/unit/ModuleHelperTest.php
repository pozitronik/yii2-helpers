<?php
declare(strict_types = 1);

use app\modules\dummy\DummyModule;
use Codeception\Test\Unit;
use pozitronik\helpers\ModuleHelper;
use yii\base\InvalidConfigException;

/**
 * @covers ModuleHelper
 */
class ModuleHelperTest extends Unit {

	/**
	 * @covers ModuleHelper::LoadModule
	 * @return void
	 * @throws Throwable
	 * @throws InvalidConfigException
	 */
	public function testLoadModuleWithStringConfig():void {
		$dummyModule = ModuleHelper::LoadModule('dummy', DummyModule::class);
		static::assertNotNull($dummyModule);
		static::assertEquals('1.0', $dummyModule->version);
	}

	/**
	 * @covers ModuleHelper::LoadModule
	 * @return void
	 * @throws Throwable
	 * @throws InvalidConfigException
	 */
	public function testLoadModuleWithArrayConfig():void {
		$dummyModule = ModuleHelper::LoadModule('dummy', [
			'class' => DummyModule::class,
			'params' => [
				'version' => '2.0'
			]
		]);
		static::assertNotNull($dummyModule);
		static::assertEquals('2.0', $dummyModule->version);
	}

	/**
	 * @covers ModuleHelper::LoadModule
	 * @return void
	 * @throws Throwable
	 * @throws InvalidConfigException
	 */
	public function testLoadModuleWithoutConfig():void {
		$dummyModule = ModuleHelper::LoadModule('dummy');
		static::assertNotNull($dummyModule);
		static::assertEquals('3.0', $dummyModule->version);
	}
}
