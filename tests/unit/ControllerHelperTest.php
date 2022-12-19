<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use pozitronik\helpers\ControllerHelper;
use yii\base\InvalidConfigException;
use yii\base\UnknownClassException;

/**
 * Class ControllerHelperTest
 * @covers ControllerHelper
 */
class ControllerHelperTest extends Unit {

	/**
	 * @return void
	 * @covers ControllerHelper::ExtractControllerIdWithSubFolders
	 */
	public function testExtractSubFolderControllerId():void {
		static::assertEquals(
			'default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\DefaultController')
		);
		static::assertEquals(
			'ajax/default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\ajax\DefaultController')
		);
		static::assertEquals(
			'ajax/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\ajax\DefaultSupaPupaController')
		);
		static::assertEquals(
			'ajax/anotherfolder/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\ajax\anotherfolder\DefaultSupaPupaController')
		);

		static::assertEquals(
			'default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\DefaultController')
		);
		static::assertEquals(
			'ajax/default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\ajax\DefaultController')
		);
		static::assertEquals(
			'ajax/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\ajax\DefaultSupaPupaController')
		);
		static::assertEquals(
			'ajax/anotherfolder/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\ajax\anotherfolder\DefaultSupaPupaController')
		);
	}

	/**
	 * @covers ControllerHelper::IsControllerHasActionMethod
	 * @return void
	 * @throws Throwable
	 * @throws InvalidConfigException
	 */
	public function testIsControllerHasActionMethod():void {
		$controller = Yii::$app->createControllerByID('site');
		static::assertNotNull($controller);
		static::assertTrue(ControllerHelper::IsControllerHasActionMethod($controller, 'error'));
		static::assertFalse(ControllerHelper::IsControllerHasActionMethod($controller, 'definedError'));
		static::assertFalse(ControllerHelper::IsControllerHasActionMethod($controller, 'camelCase'));
		static::assertFalse(ControllerHelper::IsControllerHasActionMethod($controller, 'actionIndex'));
		static::assertFalse(ControllerHelper::IsControllerHasActionMethod($controller, 'notAction'));
	}

	/**
	 * @covers ControllerHelper::IsControllerHasAction
	 * @return void
	 * @throws Throwable
	 * @throws InvalidConfigException
	 */
	public function testIsControllerHasAction():void {
		$controller = Yii::$app->createControllerByID('site');
		static::assertNotNull($controller);
		static::assertTrue(ControllerHelper::IsControllerHasAction($controller, 'error'));
		static::assertTrue(ControllerHelper::IsControllerHasAction($controller, 'definedError'));
		static::assertTrue(ControllerHelper::IsControllerHasAction($controller, 'camelCase'));
		static::assertFalse(ControllerHelper::IsControllerHasAction($controller, 'index'));
		static::assertFalse(ControllerHelper::IsControllerHasAction($controller, 'notAction'));
	}

	/**
	 * @covers ControllerHelper::GetControllerActions
	 * @return void
	 * @throws ReflectionException
	 * @throws Throwable
	 * @throws InvalidConfigException
	 * @throws UnknownClassException
	 */
	public function testGetControllerActions():void {
		$controller = Yii::$app->createControllerByID('site');
		static::assertNotNull($controller);
		static::assertEquals(['error', 'camel-case', 'defined-error'], ControllerHelper::GetControllerActions($controller));
	}
}