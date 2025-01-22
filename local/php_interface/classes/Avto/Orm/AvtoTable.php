<?php
namespace Avto\Orm;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

/**
 * Class AvtoTable
 * 
 * Fields:
 * <ul>
 * <li> id int mandatory
 * <li> id_client int optional
 * <li> car_brand string(50) optional
 * <li> model string(50) optional
 * <li> year int optional
 * <li> color string(50) optional
 * <li> mileage int optional
 * </ul>
 *
 * @package Bitrix\Lars
 **/

class AvtoTable extends DataManager
{
	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return 'b_lars_avto';
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
					'title' => Loc::getMessage('AVTO_ENTITY_ID_FIELD'),
				]
			),
			new IntegerField(
				'id_client',
				[
					'title' => Loc::getMessage('AVTO_ENTITY_ID_CLIENT_FIELD'),
				]
			),
			new StringField(
				'car_brand',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('AVTO_ENTITY_CAR_BRAND_FIELD'),
				]
			),
			new StringField(
				'model',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('AVTO_ENTITY_MODEL_FIELD'),
				]
			),
			new IntegerField(
				'year',
				[
					'title' => Loc::getMessage('AVTO_ENTITY_YEAR_FIELD'),
				]
			),
			new StringField(
				'color',
				[
					'validation' => function()
					{
						return[
							new LengthValidator(null, 50),
						];
					},
					'title' => Loc::getMessage('AVTO_ENTITY_COLOR_FIELD'),
				]
			),
			new IntegerField(
				'mileage',
				[
					'title' => Loc::getMessage('AVTO_ENTITY_MILEAGE_FIELD'),
				]
			),
		];
	}
}