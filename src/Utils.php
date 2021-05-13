<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use Exception;
use Yii;
use Throwable;
use yii\data\BaseDataProvider;
use yii\helpers\Url;

/**
 * Class Utils
 * @package app\helpers
 */
class Utils {

	public const AS_IS = 0;
	public const PRINT_R = 1;
	public const VAR_DUMP = 2;
	public const URL_SEPARATOR = '/';

	/**
	 * Возвращает единообразно кодированное имя файла (применяется во всех загрузках)
	 * @param string $filename - имя файла
	 * @return string
	 */
	public static function CypherFileName(string $filename):string {
		$name = pathinfo($filename, PATHINFO_FILENAME);
		$ext = pathinfo($filename, PATHINFO_EXTENSION);
		return (trim(str_replace('#', '-', $name)).'_'.md5($name.microtime()).'.'.$ext);
	}

	/**
	 * @param mixed $some
	 * @param string $title
	 */
	public static function log($some, string $title = ''):void {

		print "<pre>$title\n";
		if (is_bool($some)) {
			/** @noinspection ForgottenDebugOutputInspection */
			var_dump($some);
		} else {
			/** @noinspection ForgottenDebugOutputInspection */
			print_r($some);
		}
		print "</pre>";
	}

	/**
	 * @param mixed $data - данные для логирования
	 * @param bool|false|string $title - заголовок логируемых данных
	 * @param string $logName - файл вывода
	 * @param int $format - формат вывода данных
	 * @return string $string - возвращаем текстом всё, что налогировали
	 */
	public static function fileLog($data, $title = false, $logName = 'debug.log', $format = self::PRINT_R):string {
		$return_contents = '';
		if ($format === self::AS_IS && !is_scalar($data)) $format = self::PRINT_R;
		switch ($format) {
			case self::PRINT_R:
				$data = print_r($data, true);
			break;
			case self::VAR_DUMP:
				ob_start();
				var_dump($data);
				$data = ob_get_clean();
			break;
		}
		if ($title) {
			$return_contents .= "\n".date('m/d/Y H:i:s')." $title\n";
			file_put_contents(Yii::getAlias("@app")."/runtime/logs/$logName", "\n".date('m/d/Y H:i:s')." $title\n", FILE_APPEND);
		} else {
			$return_contents .= "\n".date('m/d/Y H:i:s').": ";
			file_put_contents(Yii::getAlias("@app")."/runtime/logs/$logName", "\n".date('m/d/Y H:i:s').": ", FILE_APPEND);
		}
		$return_contents .= $data;
		file_put_contents(Yii::getAlias("@app")."/runtime/logs/$logName", $data, FILE_APPEND);
		return $return_contents;
	}

	/**
	 * @param string $path
	 * @return string
	 */
	public static function process_path(string $path):string {
		$pathinfo = pathinfo($path);
		$dir = $pathinfo['dirname'];
		if ('..' === $pathinfo['basename']) {
			$dir = dirname($dir);
		}
		if (empty($dir)) $dir = '.';
		return $dir;
	}

	/** @noinspection PhpDocMissingThrowsInspection */
	/**
	 * RFC-4122 UUID
	 * @param int|bool|false $length if defined, return only first $length symbols
	 * @return bool|string
	 */
	public static function gen_uuid($length = false) {
		$UUID = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', // 32 bits for "time_low"
			random_int(0, 0xffff), random_int(0, 0xffff),

			// 16 bits for "time_mid"
			random_int(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			random_int(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			random_int(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			random_int(0, 0xffff), random_int(0, 0xffff), random_int(0, 0xffff));
		return $length?substr($UUID, 0, $length):$UUID;
	}

	/**
	 * Генерирует псевдослучайную строку заданной длины на заданном алфавите
	 * @param int $length
	 * @param string $keyspace
	 * @return string
	 * @throws Exception
	 */
	public static function random_str(int $length, string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'):string {
		$pieces = [];
		$max = mb_strlen($keyspace, '8bit') - 1;
		for ($i = 0; $i < $length; ++$i) {
			$pieces [] = $keyspace[random_int(0, $max)];
		}
		return implode('', $pieces);
	}

	/**
	 * @param string $data
	 * @return bool
	 */
	public static function is_json(string $data):bool {
		json_decode($data);
		return (JSON_ERROR_NONE === json_last_error());
	}

	/**
	 * Округление до целого числа в большую сторону
	 * @param int $number
	 * @param int $significance
	 * @return bool|float
	 */
	public static function ceiling(int $number, int $significance = 1000) {
		return (is_numeric($number) && is_numeric($significance))?(ceil($number / $significance) * $significance):false;
	}

	/**
	 * Переводит десятичный индекс в число позиционной системы счисления
	 * @param int|float|false $n - десятичный индекс
	 * @param string $alphabet - позиционный алфавит
	 * @return string - строка с числом в указанном алфавите.
	 */
	public static function DecToPos(int $n, string $alphabet):string {
		$q = strlen($alphabet);
		$ret = '';
		while (true) {
			$i = $n % $q;
			$n = floor($n / $q);
			$ret = $alphabet[$i].$ret;
			if ($n < 1) break;
		}
		return $ret;
	}

	/**
	 * Нужно, чтобы andFilterWhere не генерировал условия вида like '%' при LikeContainMode == false
	 * Используем в моделях для поиска
	 * @param null|string $param
	 * @return string
	 * @throws Throwable
	 */
	public static function MakeLike(?string $param = null):string {
		if (empty($param)) return '';
		return ArrayHelper::getValue(Yii::$app->params, 'LikeContainMode', true)?"%$param%":"$param%";
	}

	/**
	 * @return string
	 * @throws Exception
	 */
	public static function generateLogin(/*string $username*/):string {
		return self::random_str(5);//ololo
	}

	/**
	 * Высчитывает сумму значений в колонке DataProvider
	 * @param BaseDataProvider $provider
	 * @param string $columnName
	 * @return int
	 * @throws Throwable
	 */
	public static function pageTotal(BaseDataProvider $provider, string $columnName):int {
		$total = 0;
		foreach ($provider->models as $item) {
			$total += ArrayHelper::getValue($item, $columnName, 0);
		}
		return $total;
	}

	/**
	 * Склонение числительных
	 * @param int $number
	 * @param string[] $titles
	 * @return string
	 */
	public static function pluralForm(int $number, array $titles):string {
		return $number." ".$titles[($number % 100 > 4 && $number % 100 < 20)?2:[2, 0, 1, 1, 1, 2][min($number % 10, 5)]];
	}

	/**
	 * Сравнивает два относительных/абсолютных url (без учёта get-параметров), выясняя, ведут ли они на один и тот же путь.
	 *
	 * @param string|array $firstUrl url в виде строки или массива для Url::to
	 * @param string|array|null $secondUrl аналогично. Если не задан, то используется текущий URL
	 * @return bool
	 */
	public static function isSameUrlPath($firstUrl, $secondUrl = null):bool {
		$secondUrl = $secondUrl??Yii::$app->request->pathInfo;
		$firstUrl = self::setAbsoluteUrl(parse_url(Url::to($firstUrl), PHP_URL_PATH));
		$secondUrl = self::setAbsoluteUrl(parse_url(Url::to($secondUrl), PHP_URL_PATH));
		return (mb_strtolower($firstUrl) === mb_strtolower($secondUrl));
	}

	/**
	 * Превращает любой url в абсолютный (самым тупым способом)
	 * @param string $url
	 * @return string
	 */
	public static function setAbsoluteUrl(string $url):string {
		return ('' === $url || self::URL_SEPARATOR === $url[0])?$url:self::URL_SEPARATOR.$url;
	}

	/**
	 * @param array $rgb
	 * @return array
	 * @throws Throwable
	 */
	private static function RGBContrast(array $rgb):array {
		return [
			(ArrayHelper::getValue($rgb, 0, 0) < 128)?255:0,
			(ArrayHelper::getValue($rgb, 1, 0) < 128)?255:0,
			(ArrayHelper::getValue($rgb, 2, 0) < 128)?255:0
		];
	}

	/**
	 * @param string $color
	 * @param bool $include_alpha
	 * @return array
	 */
	private static function RGBToArray(string $color, $include_alpha = false):array {
		$pattern = '~^rgba?\((25[0-5]|2[0-4]\d|1\d{2}|\d\d?)\s*,\s*(25[0-5]|2[0-4]\d|1\d{2}|\d\d?)\s*,\s*(25[0-5]|2[0-4]\d|1\d{2}|\d\d?)\s*(?:,\s*([01]\.?\d*?))?\)$~';
		if (!preg_match($pattern, $color, $matches)) {
			return [];  // disqualified / no match
		}
		return array_slice($matches, 1, $include_alpha?4:3);
	}

	/**
	 * @param null|string $rgbString
	 * @return string
	 * @throws Throwable
	 */
	public static function RGBColorContrast(?string $rgbString):string {
		if (empty($rgbString)) return "rgb(255,255,255)";
		$rgb = self::RGBToArray($rgbString);
		$rgbContrast = self::RGBContrast($rgb);
		return "rgb({$rgbContrast[0]},{$rgbContrast[1]},{$rgbContrast[2]})";
	}

	/**
	 * Get latest
	 * @return string
	 */
	public static function LastCommit():string {
		$headFileName = Yii::getAlias('@app/.git/HEAD');
		if (!file_exists($headFileName)) return 'unknown';
		preg_match('#^ref:(.+)$#', file_get_contents($headFileName), $matches);

		$currentHead = trim($matches[1]);
		$currentHeadFileName = sprintf(Yii::getAlias("@app/.git/{$currentHead}"));
		if (file_exists($currentHeadFileName) && (false !== $hash = file_get_contents($currentHeadFileName))) return $hash;
		return 'unknown';
	}

	/**
	 * Return two first word letters of input (used for iconify text)
	 * @param string $input
	 * @return string
	 * @noinspection OffsetOperationsInspection
	 */
	public static function ShortifyString(string $input):string {
		if (false === $inputA = explode(' ', $input)) return '?';
		switch (count($inputA)) {
			case 0:
				$input = '?';
			break;
			case 1:
				$input = mb_substr($input, 0, 1);
			break;
			default:
				$input = mb_strtoupper(mb_substr($inputA[0], 0, 1).mb_substr($inputA[1], 0, 1));
			break;
		}
		return $input;
	}

	/**
	 * Заменяет каждое слово звёздочками, оставляя по $unmasked символов с каждой стороны
	 * @param string $input
	 * @param int $unmasked
	 * @return string
	 */
	public static function MaskString(string $input, int $unmasked = 2):string {
		$keywords = preg_split("/[\s.@\-:()\/]/", $input);
		$keywords = array_map(static function($value, $key) use ($unmasked) {
			$vLength = mb_strlen($value);
			if ($vLength <= $unmasked) return $value;
			return mb_substr($value, 0, $unmasked).(($vLength < $unmasked * 2)?'*':str_repeat('*', $vLength - $unmasked * 2)).mb_substr($value, $vLength - $unmasked, $unmasked);
		}, $keywords, array_keys($keywords));
		return implode(' ', $keywords);
	}

	/**
	 * Разбивает строку, вставляя в месте разделения символ $splitter
	 * @param string $input
	 * @param int $minlength -- минимальная длина неразбиваемого участка
	 * @param string $splitter -- символ подстановки
	 * @param string $delimiter -- символ, по которому будет идти разделение
	 * @return string
	 * @throws Throwable
	 */
	public static function SplitString(string $input, int $minlength, string $splitter = "\n", string $delimiter = ' '):string {
		$result = [];
		$parts = explode($delimiter, $input);
		$index = 0;
		foreach ($parts as $part) {
			ArrayHelper::setValue($result, $index, ArrayHelper::getValue($result, $index, '').$part.$delimiter);
			if (mb_strlen($result[$index]) >= $minlength) {
				$result[$index] = trim($result[$index], $delimiter);
				$index++;
			}
		}
		return trim(implode($splitter, $result), $delimiter);
	}
}
