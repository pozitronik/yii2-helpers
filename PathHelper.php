<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use RuntimeException;

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
	 * @param string $filename
	 * @return string
	 */
	public static function ExtractFileName(string $filename):string {
		return pathinfo($filename, PATHINFO_BASENAME);
	}
}