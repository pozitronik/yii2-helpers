<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Throwable;
use ZipArchive;

/**
 * Class ZipHelper
 * @package app\components\helpers
 */
class ZipHelper extends ZipArchive {
	/**
	 * Есть ли папка
	 * @return bool
	 */
	public function isThereAFolder():bool {
		for ($i = 0; $i < $this->numFiles; $i++) {
			if (count(array_values(explode(DIRECTORY_SEPARATOR, $this->getNameIndex($i)))) > 1) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Извлечение архива
	 * @param string $fileName Извлекаемый файл
	 * @param string $extractPath Путь извлечения файла
	 * @return bool
	 */
	public static function extract(string $fileName, string $extractPath):bool {
		$zip = new self();
		if (true === $zip->open($fileName)) {
			if ($zip->isThereAFolder()) {
				$zip->close();
				unlink($fileName);
				return false;
			}

			$zip->extractTo($extractPath);
			$zip->close();
			unlink($fileName);
			return true;
		}

		return false;
	}

	/**
	 * Архивирует список файлов, отдаёт имя получившегося архива
	 * @param array $fileList Массив путей архивируемых файлов
	 * @param string $zipName Имя архива
	 * @return null|string Путь к получившемся архиву | false при ошибке упаковки
	 */
	public static function zipFiles(array $fileList, string $zipName = 'download'):?string {
		$zip = new self();
		$name = sys_get_temp_dir().DIRECTORY_SEPARATOR.$zipName.'.zip';
		if (file_exists($name)) {
			unlink($name);
		}
		if ($zip->open($name, self::CREATE)) {
			foreach ($fileList as $file) {
				$zip->addFile($file->file_path, $file->file_name);
			}
			$zip->close();
			return $name;
		}

		return null;
	}

	/**
	 * Сжатие переданного файла
	 * @param string $file
	 * @param string $newName
	 * @return null|string
	 */
	public static function compress(string $file, string $newName = 'download'):?string {
		$zip = new self();
		$zipName = sys_get_temp_dir().DIRECTORY_SEPARATOR.$newName.'.zip';
		if (file_exists($zipName)) {
			unlink($zipName);
		}
		if ($zip->open($zipName, self::CREATE)) {
			$zip->addFile($file, $newName);
			$zip->close();

			return $zipName;
		}

		return null;
	}

	/**
	 * Сжатие множества файлов переданных массивом
	 * @param string[] $files
	 * @return null|string
	 */
	public static function compressFiles(array $files):?string {
		$zip = new self();
		$zipName = sys_get_temp_dir().DIRECTORY_SEPARATOR.time().'.zip';
		if (file_exists($zipName)) {
			unlink($zipName);
		}
		if (!empty($files) && $zip->open($zipName, self::CREATE)) {
			foreach ($files as $key => $value) {
				$zip->addFile($value, $key);
			}
			$zip->close();

			return $zipName;
		}

		return null;
	}

	/**
	 * Сжатие папки с файлами целиком(включая папку)
	 * @param string $source
	 * @param string $destination
	 * @param string $dirInArchive
	 * @return bool
	 * @throws Throwable
	 */
	public static function compressDirectory(string $source, string $destination, string $dirInArchive = ''):bool {
		$zip = new self();
		return true === $zip->open($destination, self::CREATE) && self::zipDirectory($source, $zip, $dirInArchive)->close();
	}

	/**
	 * @param string $srcDir
	 * @param self $zip todo: разобраться, почему сделано так
	 * @param string $dirInArchive
	 * @return ZipHelper
	 */
	private static function zipDirectory(string $srcDir, ZipHelper $zip, string $dirInArchive = ''):self {
		$srcDir = str_replace("\\", '/', $srcDir);
		$dirInArchive = str_replace("\\", '/', $dirInArchive);
		$dirHandle = opendir($srcDir);
		while (false !== ($file = readdir($dirHandle))) {
			if (is_dir($srcDir.$file)) {
				$zip->addEmptyDir($dirInArchive.$file);
				$zip = self::zipDirectory(
					$srcDir.$file.DIRECTORY_SEPARATOR,
					$zip,
					$dirInArchive.$file.DIRECTORY_SEPARATOR
				);
			} else {
				$zip->addFile($srcDir.$file, $dirInArchive.$file);
			}
		}

		return $zip;
	}
}
