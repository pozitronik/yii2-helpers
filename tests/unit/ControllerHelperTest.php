<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use pozitronik\helpers\ControllerHelper;

/**
 * Class BuildAppHelperTest
 */
class ControllerHelperTest extends Unit {

	public function testExtractSubFolderControllerId() {
		$this->assertEquals(
			'default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\DefaultController')
		);
		$this->assertEquals(
			'ajax/default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\ajax\DefaultController')
		);
		$this->assertEquals(
			'ajax/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\ajax\DefaultSupaPupaController')
		);
		$this->assertEquals(
			'ajax/anotherfolder/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\controllers\ajax\anotherfolder\DefaultSupaPupaController')
		);

		$this->assertEquals(
			'default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\DefaultController')
		);
		$this->assertEquals(
			'ajax/default',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\ajax\DefaultController')
		);
		$this->assertEquals(
			'ajax/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\ajax\DefaultSupaPupaController')
		);
		$this->assertEquals(
			'ajax/anotherfolder/default-supa-pupa',
			ControllerHelper::ExtractControllerIdWithSubFolders('app\modules\test\controllers\ajax\anotherfolder\DefaultSupaPupaController')
		);
	}
}