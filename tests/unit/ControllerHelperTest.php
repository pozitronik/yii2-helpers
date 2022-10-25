<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use pozitronik\helpers\ControllerHelper;

/**
 * Class BuildAppHelperTest
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
}