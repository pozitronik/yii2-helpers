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
 */
class Utils {

	public const AS_IS = 0;
	public const PRINT_R = 1;
	public const VAR_DUMP = 2;
	public const URL_SEPARATOR = '/';

	/**
	 * Возвращает единообразно кодированное имя файла (применяется во всех загрузках)
	 * @param string $filename Имя файла
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
	public static function log(mixed $some, string $title = ''):void {

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
	 * @param mixed $data Данные для логирования
	 * @param null|string $title Заголовок логируемых данных
	 * @param string $logName Файл вывода
	 * @param int $format Формат вывода данных
	 * @return string $string Возвращаем текстом всё, что налогировали
	 */
	public static function fileLog(mixed $data, ?string $title = null, string $logName = 'debug.log', int $format = self::PRINT_R):string {
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
		if (null === $title) {
			$return_contents .= "\n".date('m/d/Y H:i:s').": ";
			file_put_contents(Yii::getAlias("@app")."/runtime/logs/$logName", "\n".date('m/d/Y H:i:s').": ", FILE_APPEND);
		} else {
			$return_contents .= "\n".date('m/d/Y H:i:s')." $title\n";
			file_put_contents(Yii::getAlias("@app")."/runtime/logs/$logName", "\n".date('m/d/Y H:i:s')." $title\n", FILE_APPEND);
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
	 * @param null|int $length if defined, return only first $length symbols
	 * @return string
	 */
	public static function gen_uuid(?int $length = null):string {
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
		return (is_numeric($significance))?(ceil($number / $significance) * $significance):false;
	}

	/**
	 * Переводит десятичный индекс в число позиционной системы счисления
	 * @param int $n Десятичный индекс
	 * @param string $alphabet Позиционный алфавит
	 * @return string Строка с числом в указанном алфавите.
	 */
	public static function DecToPos(int $n, string $alphabet):string {
		$q = strlen($alphabet);
		$ret = '';
		while (true) {
			$i = $n % $q;
			$n = (int)floor($n / $q);
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
	 * @param string[]|string $firstUrl url в виде строки или массива для Url::to
	 * @param string|string[]|null $secondUrl Аналогично. Если не задан, то используется текущий URL
	 * @return bool
	 */
	public static function isSameUrlPath(array|string $firstUrl, array|string $secondUrl = null):bool {
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
	public static function RGBToArray(string $color, bool $include_alpha = false):array {
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
	 * Get latest commit
	 * @return string
	 * @noinspection BadExceptionsProcessingInspection
	 */
	public static function LastCommit():string {
		try {
			$headFileName = Yii::getAlias('@app/.git/HEAD');
			if (!file_exists($headFileName)) return 'unknown';
			preg_match('#^ref:(.+)$#', file_get_contents($headFileName), $matches);

			$currentHead = trim($matches[1]);
			$currentHeadFileName = Yii::getAlias("@app/.git/{$currentHead}");
			if (file_exists($currentHeadFileName) && (false !== $hash = file_get_contents($currentHeadFileName))) return $hash;
		} catch (Throwable) {
			return 'unknown';
		}
		return 'unknown';
	}

	/**
	 * Return two first word letters of input (used for iconify text)
	 * @param string $input
	 * @return string
	 * @noinspection OffsetOperationsInspection
	 */
	public static function ShortifyString(string $input):string {
		if ([] === $inputA = explode(' ', $input)) return '?';
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

	/**
	 * @param string $term
	 * @param bool $fromQWERTY
	 * @return string
	 */
	public static function SwitchKeyboard(string $term, bool $fromQWERTY = false):string {
		$converter = $fromQWERTY
			?[
				'f' => 'а', ',' => 'б', 'd' => 'в', 'u' => 'г', 'l' => 'д', 't' => 'е', '`' => 'ё',
				';' => 'ж', 'p' => 'з', 'b' => 'и', 'q' => 'й', 'r' => 'к', 'k' => 'л', 'v' => 'м',
				'y' => 'н', 'j' => 'о', 'g' => 'п', 'h' => 'р', 'c' => 'с', 'n' => 'т', 'e' => 'у',
				'a' => 'ф', '[' => 'х', 'w' => 'ц', 'x' => 'ч', 'i' => 'ш', 'o' => 'щ', 'm' => 'ь',
				's' => 'ы', ']' => 'ъ', "'" => "э", '.' => 'ю', 'z' => 'я',
				'F' => 'А', '<' => 'Б', 'D' => 'В', 'U' => 'Г', 'L' => 'Д', 'T' => 'Е', '~' => 'Ё',
				':' => 'Ж', 'P' => 'З', 'B' => 'И', 'Q' => 'Й', 'R' => 'К', 'K' => 'Л', 'V' => 'М',
				'Y' => 'Н', 'J' => 'О', 'G' => 'П', 'H' => 'Р', 'C' => 'С', 'N' => 'Т', 'E' => 'У',
				'A' => 'Ф', '{' => 'Х', 'W' => 'Ц', 'X' => 'Ч', 'I' => 'Ш', 'O' => 'Щ', 'M' => 'Ь',
				'S' => 'Ы', '}' => 'Ъ', '"' => 'Э', '>' => 'Ю', 'Z' => 'Я',
				'@' => '"', '#' => '№', '$' => ';', '^' => ':', '&' => '?', '/' => '.', '?' => ',']
			:[
				'а' => 'f', 'б' => ',', 'в' => 'd', 'г' => 'u', 'д' => 'l', 'е' => 't', 'ё' => '`',
				'ж' => ';', 'з' => 'p', 'и' => 'b', 'й' => 'q', 'к' => 'r', 'л' => 'k', 'м' => 'v',
				'н' => 'y', 'о' => 'j', 'п' => 'g', 'р' => 'h', 'с' => 'c', 'т' => 'n', 'у' => 'e',
				'ф' => 'a', 'х' => '[', 'ц' => 'w', 'ч' => 'x', 'ш' => 'i', 'щ' => 'o', 'ь' => 'm',
				'ы' => 's', 'ъ' => ']', 'э' => "'", 'ю' => '.', 'я' => 'z',
				'А' => 'F', 'Б' => '<', 'В' => 'D', 'Г' => 'U', 'Д' => 'L', 'Е' => 'T', 'Ё' => '~',
				'Ж' => ':', 'З' => 'P', 'И' => 'B', 'Й' => 'Q', 'К' => 'R', 'Л' => 'K', 'М' => 'V',
				'Н' => 'Y', 'О' => 'J', 'П' => 'G', 'Р' => 'H', 'С' => 'C', 'Т' => 'N', 'У' => 'E',
				'Ф' => 'A', 'Х' => '{', 'Ц' => 'W', 'Ч' => 'X', 'Ш' => 'I', 'Щ' => 'O', 'Ь' => 'M',
				'Ы' => 'S', 'Ъ' => '}', 'Э' => '"', 'Ю' => '>', 'Я' => 'Z',
				'"' => '@', '№' => '#', ';' => '$', ':' => '^', '?' => '&', '.' => '/', ',' => '?',
			];

		return strtr($term, $converter);
	}

	/**
	 * Превращает массив ошибок в набор читаемых строк
	 * @param array $errors
	 * @param array|string $separator
	 * @return string
	 */
	public static function Errors2String(array $errors, array|string $separator = "\n"):string {
		$output = [];
		foreach ($errors as $attribute => $attributeErrors) {
			$error = is_array($attributeErrors)?implode($separator, $attributeErrors):$attributeErrors;
			$output[] = "{$attribute}: {$error}";
		}

		return implode($separator, $output);
	}
}
