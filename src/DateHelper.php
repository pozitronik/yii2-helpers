<?php
/** @noinspection MissingParameterTypeDeclarationInspection */
/** @noinspection MissingReturnTypeInspection */
/** @noinspection ReturnFalseInspection */
declare(strict_types = 1);

namespace pozitronik\helpers;

use DateTime;
use DateInterval;
use Exception;
use Throwable;

/**
 * Class DateHelper
 * @package app\helpers
 */
class DateHelper {

	/*Еврибади гоин крейзи*/
	public const SECONDS_IN_YEAR = 31536000;
	public const SECONDS_IN_MONTH = 2592000;
	public const SECONDS_IN_DAY = 86400;
	public const SECONDS_IN_HOUR = 3600;
	public const SECONDS_IN_MINUTE = 60;

	/**
	 * Мы постоянно используем такую дату, меня задалбывает вспоминать или копипастить, пусть будет алиас
	 * @return string
	 */
	public static function lcDate():string {
		return date('Y-m-d H:i:s');
	}

	/**
	 * Прибавляет заданное кол-во к месяцу
	 * @param null|int $int
	 * @param null|int $month
	 * @return false|string
	 */
	public static function monthPlus(?int $int = null, ?int $month = null) {
		if (null === $int) {
			return date('m');
		}

		if (null !== $month) {
			$month += $int;
		} else {
			$month = date('m') + $int;
		}

		return self::zeroAddMoth($month);
	}

	/**
	 * Отнимает заданное кол-во от месяца
	 * @param null|int $int
	 * @param null|int $month
	 * @return false|null|string
	 */
	public static function monthMinus(?int $int = null, ?int $month = null) {
		if (null === $int) {
			return date('m');
		}

		if (null !== $month) {
			return self::zeroAddMoth($month - $int);
		}

		return self::zeroAddMoth(date('m') - $int);
	}

	/**
	 * Добавляет ноль к месяцу если это необходимо
	 * @param int $month
	 * @return string
	 */
	public static function zeroAddMoth(int $month):string {
		return 1 === strlen((string)$month)?'0'.$month:(string)$month;
	}

	/**
	 * Проверяет попадает ли выбранная дата в интервал дат
	 * @param string $date Проверяемая дата (Y-m-d)
	 * @param array $interval Массив интервала дат ['start' => 'Y-m-d', 'end' => 'Y-m-d']
	 * @return bool
	 * @throws Exception
	 */
	public static function isBetweenDate(string $date, array $interval):bool {
		$d = new DateTime($date);
		$d1 = new DateTime($interval['start']);
		$d2 = new DateTime($interval['end']);
		return ($d2 >= $d && $d1 <= $d) || ($d1 >= $d && $d2 <= $d);
	}

	/**
	 * Возвращает DateTime конца недели, в которой находится день $currentDate
	 * @param DateTime $currentDate - обсчитываемая дата, по ней и вычисляется неделя
	 * @return DateTime
	 * @throws Throwable
	 */
	public static function getWeekEnd(DateTime $currentDate):?DateTime {
		$currentWeekDay = $currentDate->format('w');
		$t = 7 - $currentWeekDay;
		$td = clone $currentDate;
		return $td->add(new DateInterval("P{$t}D"));
	}

	/**
	 * Простое сравнение двух дат с возможностью задать произвольное форматирование
	 * @param string $dateStart
	 * @param string $dateEnd
	 * @param string $format
	 * @return string
	 * @throws Exception
	 */
	public static function diff(string $dateStart, string $dateEnd, string $format):string {
		$date1 = new DateTime($dateStart);
		$date2 = new DateTime($dateEnd);
		$diff = $date1->diff($date2);

		return $diff->format($format);
	}

	/**
	 * Проверяет, соответствует ли строка указанному формату даты
	 * @param string $date
	 * @param string $format
	 * @return bool
	 */
	public static function isValidDate(string $date, string $format = 'Y-m-d H:i:s'):bool {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) === $date;
	}

	/**
	 * Получение полных дней между двумя датами
	 * @param string $dateStart
	 * @param string $dateEnd
	 * @return false|int
	 * @throws Exception
	 */
	public static function fullDays(string $dateStart, string $dateEnd) {
		$date1 = new DateTime($dateStart);
		$date2 = new DateTime($dateEnd);

		return $date1->diff($date2)->days;
	}

	/**
	 * @param int $date - timestamp
	 * @return int
	 */
	public static function getDayEnd(int $date):int {
		return mktime(0, 0, 0, date("m", $date), date("d", $date) + 1, date("y", $date));
	}

	/**
	 * Выдаёт форматированное в заданный формат время
	 * @param bool|int|null $delay - количество секунд для преобразования
	 * @param bool $short_format
	 * @return string|false
	 * @throws Exception
	 */
	public static function seconds2times($delay, bool $short_format = false) {
		if (null === $delay) $delay = false;
		if (true === $delay) return "Отключено";
		if (false === $delay) return false;//используется для оптимизации статистики SLA
		$str_delay = (string)$delay;
		$sign = '';
		if (0 === strncmp($str_delay, '-', 1)) {
			$sign = '-';
			$str_delay = substr($str_delay, 1);
		}

		$dtF = new DateTime("@0");
		$dtT = new DateTime("@$str_delay");
		$diff = $dtF->diff($dtT);
		$periods = [
			self::SECONDS_IN_YEAR,
			self::SECONDS_IN_DAY,
			self::SECONDS_IN_HOUR,
			self::SECONDS_IN_MINUTE,
			1
		];// секунд в году|дне|часе|минуте|секунде
		$format_values = $short_format?['%yг ', '%aд ', '%h:', '%I:', '%S']:['%y лет ', '%a д. ', '%h час. ', '%i мин. ', '%s сек.'];
		$format_string = '';
		for ($level = 0; 5 !== $level; $level++) {
			if ($str_delay >= $periods[$level]) $format_string .= $format_values[$level];
		}
		return ('' === $format_string)?'0 сек.':($sign.$diff->format($format_string));
	}

	/**
	 * Аналог SQL-функции UNIX_TIMESTAMP
	 * Возвращает таймстамп даты SQL-формата
	 * @param string $date
	 * @return int|false
	 */
	public static function unix_timestamp(string $date) {
		if (!$date) return false;
		$dt = DateTime::createFromFormat("Y-m-d H:i:s", $date);
		return $dt->getTimestamp();
	}

	/**
	 * Конвертирует таймстамп в дату
	 * @param int $timestamp
	 * @return false|string
	 */
	public static function from_unix_timestamp(int $timestamp) {
		return date("Y-m-d H:i:s", $timestamp);
	}

	/**
	 * @param array $interval
	 * @return int
	 * @deprecated (just to check usage)
	 */
	public static function interval2seconds(array $interval):int {
		$seconds = 0;
		foreach ($interval as $time => $value) {
			switch ($time) {
				case 'd':
					/** @noinspection SummerTimeUnsafeTimeManipulationInspection */
					$seconds += $value * 24 * 60 * 60;
				break;
				case 'h':
					$seconds += $value * 60 * 60;
				break;
				case 'i':
					$seconds += $value * 60;
				break;
				case 's':
					$seconds += $value;
				break;
			}
		}
		return $seconds;
	}
}
