<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use RuntimeException;
use Throwable;
use Yii;
use yii\helpers\BaseFileHelper;

/**
 * Class PathHelper
 * Хелпер для работы с путями и файловыми объектами
 */
class PathHelper {

	/**
	 * Создаём каталог с нужными проверками
	 * @param string $path
	 * @param int $mode
	 * @return bool
	 */
	public static function CreateDirIfNotExisted(string $path, int $mode = 0777):bool {
		if (file_exists($path)) {
			if (is_dir($path)) return true;
			throw new RuntimeException(sprintf('Имя "%s" занято', $path));
		}
		if (!mkdir($path, $mode, true) && !is_dir($path)) {
			throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
		}
		return true;
	}

	/**
	 * @param string $filename
	 * @param string $new_extension
	 * @return string
	 */
	public static function ChangeFileExtension(string $filename, string $new_extension = ''):string {
		return '' === $new_extension?pathinfo($filename, PATHINFO_FILENAME):pathinfo($filename, PATHINFO_FILENAME).".$new_extension";
	}

	/**
	 * @param string $filename
	 * @param string $new_name
	 * @return string
	 */
	public static function ChangeFileName(string $filename, string $new_name = ''):string {
		return '' === $new_name?pathinfo($filename, PATHINFO_EXTENSION):"$new_name.".pathinfo($filename, PATHINFO_EXTENSION);
	}

	/**
	 * Имя файла с расширением
	 * @param string $filename
	 * @return string
	 */
	public static function ExtractBaseName(string $filename):string {
		return pathinfo($filename, PATHINFO_BASENAME);
	}

	/**
	 * Имя файла без расширения
	 * @param string $filename
	 * @return string
	 */
	public static function ExtractFileName(string $filename):string {
		return pathinfo($filename, PATHINFO_FILENAME);
	}

	/**
	 * Расширение файла
	 * @param string $filename
	 * @return string
	 */
	public static function ExtractFileExt(string $filename):string {
		return pathinfo($filename, PATHINFO_EXTENSION);
	}

	/**
	 * Путь файла без имени
	 * @param string $filename
	 * @return string
	 */
	public static function ExtractFilePath(string $filename):string {
		return pathinfo($filename, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;
	}

	/**
	 * Находит разницу между двумя путями, возвращая относительный путь меж ними
	 * @param string $path
	 * @param string $basePath
	 * @param bool $caseSensitivity
	 * @return string
	 */
	public static function RelativePath(string $path, string $basePath = "@app", bool $caseSensitivity = false):string {
		$path = BaseFileHelper::normalizePath($path);
		$basePath = BaseFileHelper::normalizePath(Yii::getAlias($basePath));

		if (!$caseSensitivity) {
			$path = mb_strtolower($path);
			$basePath = mb_strtolower($basePath);
		}

		$fileNameSplit = explode(DIRECTORY_SEPARATOR, $path);
		$basePathSplit = explode(DIRECTORY_SEPARATOR, $basePath);

		while ($fileNameSplit && $basePathSplit && $fileNameSplit[0] === $basePathSplit[0]) {
			array_shift($basePathSplit);
			array_shift($fileNameSplit);
		}

		return implode(DIRECTORY_SEPARATOR, $fileNameSplit);
	}

	/**
	 * Переводит путь в ФС в ссылку (примитивно)
	 * @param string $path
	 * @return string
	 * @throws Throwable
	 */
	public static function PathToUrl(string $path):string {
		$path = str_replace(DIRECTORY_SEPARATOR, "/", $path);
		if ("/" !== ArrayHelper::getValue($path, 0)) $path = "/".$path;
		return $path;
	}

	/**
	 * Проверяет, находится ли $path внутри хотя бы одного пути в $pathBranches
	 * @param string $path
	 * @param string[] $pathBranches
	 * @return bool
	 */
	public static function InPath(string $path, array $pathBranches):bool {
		$pathBranches = array_map(static function($value) {
			return BaseFileHelper::normalizePath(Yii::getAlias($value, false));
		}, $pathBranches);

		foreach ($pathBranches as $parentDir) {
			if (false !== strrpos(BaseFileHelper::normalizePath(Yii::getAlias($path, false)), $parentDir)) return true;
		}
		return false;
	}
}