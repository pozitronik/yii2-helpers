<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;
use Yii;

/**
 * Class BuildAppHelper
 * Routines for versioning and building manipulation
 */
class BuildAppHelper {

	public const P_ALL = 0x0;
	public const P_VERSION = 0x1;
	public const P_BUILD = 0x2;
	public const P_BRANCH = 0x4;
	public const P_DATE = 0x8;
	public const P_SHA = 0xF;

	/**
	 * Parse and return a part from version contain string in semver format.
	 * Semver format version info strings examples:
	 * 1.0.0-dev+build-62843.branch-dev.date-2022-04-20.sha-166fb656
	 * 1.2.3+build-121554.branch-release.date-2022-08-15.sha-257ba870
	 *
	 * @param string $versionFilePath Path to a file with version info, or just a raw version info string
	 * @param int $part The required version part constant
	 * @return null|string The required version part, null if file not found or string doesn't contain correct semver
	 */
	public static function VersionInfo(string $versionFilePath = '@app/web/version.txt', int $part = self::P_VERSION):?string {
		if ((false === $file = Yii::getAlias($versionFilePath)) || !file_exists($file) || false === $content = file_get_contents($file)) {
			$content = $versionFilePath;
		}
		$matches = [];
		if (false === preg_match('/^(.+)\+build-(\d+)\.branch-(.+)\.date-(.+)\.sha-(.+)$/', $content, $matches))
			return null;
		if (6 !== count($matches))
			return null;
		return match ($part) {
			self::P_ALL => $matches[0],
			self::P_VERSION => $matches[1],
			self::P_BUILD => $matches[2],
			self::P_BRANCH => $matches[3],
			self::P_DATE => $matches[4],
			self::P_SHA => $matches[5],
			default => null
		};
	}

	/**
	 * Get the latest git commit hash, if possible
	 * @param string $gitDir Path to the repository git directory
	 * @return string|null The last commit hash string, null if git directory not accessible
	 */
	public static function LastCommit(string $gitDir = '@app/.git'):?string {
		try {
			$headFileName = Yii::getAlias("{$gitDir}/HEAD");
			if (!file_exists($headFileName)) return null;
			preg_match('#^ref:(.+)$#', file_get_contents($headFileName), $matches);

			$currentHead = trim($matches[1]);
			$currentHeadFileName = Yii::getAlias("{$gitDir}/{$currentHead}");
			if (file_exists($currentHeadFileName) && (false !== $hash = trim(file_get_contents($currentHeadFileName))))
				return $hash;
		} catch (Throwable) {
			return null;
		}
		return null;
	}
}
