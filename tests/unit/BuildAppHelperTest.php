<?php
declare(strict_types = 1);

use Codeception\Test\Unit;
use pozitronik\helpers\BuildAppHelper;

/**
 * Class BuildAppHelperTest
 */
class BuildAppHelperTest extends Unit {

	/**
	 * @return void
	 * @covers BuildAppHelper::VersionInfo
	 */
	public function testVersionInfo():void {
		self::assertEquals('1.2.7', BuildAppHelper::VersionInfo('@tests/_data/version.txt'));
		self::assertEquals('121554', BuildAppHelper::VersionInfo('@tests/_data/version.txt', BuildAppHelper::P_BUILD));
		self::assertEquals('master', BuildAppHelper::VersionInfo('@tests/_data/version.txt', BuildAppHelper::P_BRANCH));
		self::assertEquals('2022-08-15', BuildAppHelper::VersionInfo('@tests/_data/version.txt', BuildAppHelper::P_DATE));
		self::assertEquals('257ba870', BuildAppHelper::VersionInfo('@tests/_data/version.txt', BuildAppHelper::P_SHA));
		self::assertEquals('1.2.7+build-121554.branch-master.date-2022-08-15.sha-257ba870', BuildAppHelper::VersionInfo('@tests/_data/version.txt', BuildAppHelper::P_ALL));

		self::assertEquals('dev', BuildAppHelper::VersionInfo('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656'));
		self::assertEquals('62843', BuildAppHelper::VersionInfo('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656', BuildAppHelper::P_BUILD));
		self::assertEquals('dev', BuildAppHelper::VersionInfo('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656', BuildAppHelper::P_BRANCH));
		self::assertEquals('2022-04-20', BuildAppHelper::VersionInfo('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656', BuildAppHelper::P_DATE));
		self::assertEquals('166fb656', BuildAppHelper::VersionInfo('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656', BuildAppHelper::P_SHA));
		self::assertEquals('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656', BuildAppHelper::VersionInfo('dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656', BuildAppHelper::P_ALL));

		self::assertNull(BuildAppHelper::VersionInfo('this-file-not-exists'));
		self::assertNull(BuildAppHelper::VersionInfo('this.version+info-string.is-wrong'));
	}

}