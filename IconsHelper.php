<?php
declare(strict_types = 1);

namespace pozitronik\helpers;

use app\modules\history\models\HistoryEventInterface;

/**
 * Class Icons
 * @package app\helpers
 * Хелпер с HTML-иконками
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
		return "<div style='display: block; margin: {$borderRadius}px; text-align: center; line-height:{$radius}px;  width: {$radius}px; height: {$radius}px; color: $color; background: $background; border-radius: 50%; box-shadow: 0 0 0 {$borderRadius}px {$borderColor};'>{$input}</div>";
	}

	/**
	 * @param int $eventType
	 * @return string
	 */
	public static function event_icon(int $eventType):string {
		switch ($eventType) {
			case HistoryEventInterface::EVENT_CREATED:
				return "<i class='fa fa-2x fa-plus-circle' style='color: #51a351;' title='Создание'></i>";
			break;
			case HistoryEventInterface::EVENT_CHANGED:
				return "<i class='fa fa-2x fa-exchange-alt' title='Изменение'></i>";
			break;
			case HistoryEventInterface::EVENT_DELETED:
				return "<i class='fa fa-2x fa-minus-circle' style='color: Tomato;' title='Удаление'></i>";
			break;
			default:
				return "<i class='fa fa-2x fa-question-circle' title='Неизвестный тип события'></i>";
			break;
		}
	}

	/**
	 * @return string
	 */
	public static function view():string {
		return "<i class='fa fa-eye ' title='Просмотр'></i>";
	}

	/**
	 * @return string
	 */
	public static function update():string {
		return "<i class='fa fa-edit ' title='Редактирование'></i>";
	}

	/**
	 * @return string
	 */
	public static function delete():string {
		return "<i class='fa fa-trash-alt ' title='Удаление'></i>";
	}

	/**
	 * @return string
	 */
	public static function trash():string {
		return "<i class='fa fa-trash' title='Отметить к удалению'></i>";
	}

	/**
	 * @return string
	 */
	public static function unlink():string {
		return "<i class='fa fa-unlink' style='color: Tomato;' title='Отвязать'></i>";
	}

	/**
	 * @return string
	 */
	public static function link():string {
		return "<i class='fa fa-link' title='Связать'></i>";
	}

	/**
	 * @return string
	 */
	public static function users():string {
		return "<i class='fa fa-user-friends' title='Пользователи'></i>";
	}

	/**
	 * @return string
	 */
	public static function vacancy():string {
		return "<i class='fa fa-user-check' title='Вакансии'></i>";
	}

	/**
	 * @return string
	 */
	public static function vacancy_red():string {
		return "<i class='fa fa-user-check' style='color: Tomato;' title='Создать вакансию'></i>";
	}

	/**
	 * @return string
	 */
	public static function vacancy_green():string {
		return "<i class='fa fa-user-check' style='color: Green;' title='Создать вакансию'></i>";
	}

	/**
	 * @return string
	 */
	public static function subgroups():string {
		return "<i class='fa fa-users' style='color: Tomato;' title='Подгруппы'></i>";
	}

	/**
	 * @return string
	 */
	public static function attributes():string {
		return "<i class='fa fa-address-card' title='Атрибуты'></i>";
	}

	/**
	 * @return string
	 */
	public static function export():string {
		return "<i class='fa fa-file-export' title='Атрибуты'></i>";
	}

	/**
	 * @return string
	 */
	public static function export_red():string {
		return "<i class='fa fa-file-export' style='color: Tomato;' title='Атрибуты'></i>";
	}

	/**
	 * @return string
	 */
	public static function network():string {
		return "<i class='fa fa-chart-network'></i>";
	}

	/**
	 * @return string
	 */
	public static function dashboard():string {
		return "<i class='fa fa-columns'></i>";
	}

	/**
	 * @return string
	 */
	public static function chart():string {
		return "<i class='fa fa-chart-pie'></i>";
	}

	/**
	 * @return string
	 */
	public static function rule():string {
		return "<i class='fa fa-pencil-ruler'></i>";
	}

	/**
	 * @return string
	 */
	public static function menu():string {
		return "<i class='fa fa-bars'></i>";
	}

	/**
	 * @return string
	 */
	public static function menu_caret():string {
		return "<i class='fa fa-chevron-circle-down'></i>";
	}

	/**
	 * @return string
	 */
	public static function clear():string {
		return "<i class='fa fa-toilet'></i>";
	}

	/**
	 * @return string
	 */
	public static function user():string {
		return "<i class='fa fa-user'></i>";
	}

	/**
	 * @return string
	 */
	public static function user_add():string {
		return "<i class='fa fa-plus'></i>";
	}

	/**
	 * @return string
	 */
	public static function users_edit():string {
		return "<i class='fa fa-user-edit'></i>";
	}

	/**
	 * @return string
	 */
	public static function users_edit_red():string {
		return "<i class='fa fa-user-edit' style='color: Tomato;'></i>";
	}

	/**
	 * @return string
	 */
	public static function hierarchy():string {
		return "<i class='fa fa-level-down-alt'></i>";
	}

	/**
	 * @return string
	 */
	public static function hierarchy_red():string {
		return "<i class='fa fa-level-down-alt' style='color: Tomato;'></i>";
	}

	/**
	 * @return string
	 */
	public static function group():string {
		return "<i class='fa fa-users'></i>";
	}

	/**
	 * @return string
	 */
	public static function add():string {
		return "<i class='fa fa-plus'></i>";
	}

	/**
	 * @return string
	 */
	public static function money():string {
		return "<i class='fa fa-money-bill'></i>";
	}

	/**
	 * @return string
	 */
	public static function history():string {
		return "<i class='fa fa-history'></i>";
	}

	/**
	 * @return string
	 */
	public static function expand():string {
		return "<i class='fa fa-angle-down'></i>";
	}

	/**
	 * @return string
	 */
	public static function collapse():string {
		return "<i class='fa fa-angle-up'></i>";
	}

	/**
	 * @return string
	 */
	public static function maximize():string {
		return "<i class='fa fa-window-maximize'></i>";
	}

	/**
	 * @return string
	 */
	public static function minimize():string {
		return "<i class='fa fa-window-minimize'></i>";
	}
	/**
	 * @return string
	 */
	public static function statistic():string {
		return "<i class='fa fa-abacus'></i>";
	}
	/**
	 * @return string
	 */
	public static function targets():string {
		return "<i class='fa fa-bullseye'></i>";
	}

}