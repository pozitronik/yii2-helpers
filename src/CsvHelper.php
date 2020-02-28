<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

/**
 * Class CsvHelper
 */
class CsvHelper {
	/**
	 * Преобразование CSV строк в массив
	 * @param string $file Полный путь к файлу
	 * @param string $delimiter Разделитель строк
	 * @return array
	 */
	public static function csvToArray(string $file, string $delimiter = ';'):array {
		$csvArray = [];

		if (false !== ($handle = fopen($file, 'rb'))) {
			while (false !== ($csvData = fgetcsv($handle, 0, $delimiter))) {
				$csvArray[] = $csvData;
			}
			fclose($handle);
		}

		return $csvArray;
	}

	/**
	 * Преобразование массива в CSV
	 * @param array $array Исходный массив
	 * @param string $delimiter разделитель строк
	 * @return string|null CSV contents
	 */
	public static function arrayToCsv(array $array, string $delimiter = ';'):?string {
		if (false !== ($file = fopen('php://temp/maxmemory:'.(5 * 1024 * 1024), 'wb'))) {
			foreach ($array as $value) fputcsv($file, $value, $delimiter);
			rewind($file);
			return stream_get_contents($file);
		}
		return null;
	}

	/**
	 * @param array $array
	 * @param string|null $fileName Полный путь к файлу, если не задан - создастся временный файл
	 * @param string $delimiter
	 * @return null|string CSV filename
	 */
	public static function arrayToCsvFile(array $array, ?string $fileName = null, string $delimiter = ';'):?string {
		$_fileName = $fileName??tempnam(sys_get_temp_dir(), 'csv');
		if (!$_fileName) return null;
		if (false !== $file = fopen($_fileName, 'wb')) {
			foreach ($array as $value) fputcsv($file, $value, $delimiter);
			fclose($file);
			return $_fileName;
		}
		return null;
	}
}
