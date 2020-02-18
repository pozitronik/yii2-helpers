<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

/**
 * Class IconsHelper
 * @package pozitronik\helpers
 */
class IconsHelper {

	/**
	 * Берём строку, генерируем из неё HTML-аватар (кругляшок с буковками)
	 * @param string $input -- вводимая строка
	 * @param int $radius -- радиус иконки
	 * @param string $color -- цвет текста
	 * @param string $background -- цвет фона
	 * @param int $borderRadius -- толщина каёмки
	 * @param string $borderColor -- цвет каёмки
	 * @return string -- html-код иконки
	 */
	public static function iconifyString(string $input, int $radius = 15, string $color = 'black', string $background = 'white', int $borderRadius = 2, string $borderColor = "orange"):string {
		$input = Utils::ShortifyString($input);
		$lineHeight = $radius / 4 * 3;
		return "<div style='display: block; float:left; margin: {$borderRadius}px; text-align: center; line-height:{$lineHeight}px;  width: {$radius}px; height: {$radius}px; color: $color; background: $background; border-radius: 50%; box-shadow: 0 0 0 {$borderRadius}px {$borderColor};'>{$input}</div>";
	}

	/**
	 * @param $name
	 * @param $arguments
	 * @return string
	 */
	public static function __callStatic($name, $arguments):string {
		return static::iconifyString($name, 16);
	}

	/**
	 * @return string
	 */
	public static function add():string {
		return static::iconifyString('➕', 16);
	}

	/**
	 * @return string
	 */
	public static function menu():string {
		return static::iconifyString('☰', 16);
	}

	/**
	 * @return string
	 */
	public static function update():string {
		return static::iconifyString('🔃', 16);
	}

	/**
	 * @return string
	 */
	public static function view():string {
		return static::iconifyString('🙈', 16);
	}

	/**
	 * @return string
	 */
	public static function delete():string {
		return static::iconifyString('➖', 16);
	}

}