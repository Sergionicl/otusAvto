<?php
namespace Avto\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;

/**
 * Class DealAvtoTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> avto_id int optional
 * <li> deal_id int optional
 * </ul>
 *
 * @package Bitrix\Lars
 **/

class DealAvtoTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_lars_deal_avto';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap()
	{
		return [
			new IntegerField(
				'id',
				[
					'primary' => true,
					'autocomplete' => true,
					'title' => Loc::getMessage('DEAL_AVTO_ENTITY_ID_FIELD'),
				]
			),
			new IntegerField(
				'avto_id',
				[
					'title' => Loc::getMessage('DEAL_AVTO_ENTITY_AVTO_ID_FIELD'),
				]
			),
			new IntegerField(
				'deal_id',
				[
					'title' => Loc::getMessage('DEAL_AVTO_ENTITY_DEAL_ID_FIELD'),
				]
			),
		];
	}
}