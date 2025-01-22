<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Avto\Orm\DealAvtoTable;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;
use \Bitrix\Main\Loader;

//use \Bitrix\Crm\CCrmDeal;

// модели работающие с инфоблоками

class LarAvtoDeal extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable // \Bitrix\Main\Errorable

{

    /**
     * @return array
     */
    public function configureActions()
    {
        //если действия не нужно конфигурировать, то пишем просто так. И будет конфиг по умолчанию
        return [];
    }


    /**
     * @return void
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function executeComponent()
    {
        if ($this->checkModules()) {

            $list_id = 'report_kpi';
            $grid_options = new GridOptions($list_id);
            $sort = $grid_options->GetSorting($this->getUISorting());

            $nav_params = $grid_options->GetNavParams();
            $nav = new PageNavigation($list_id);
            $nav->allowAllRecords(true)
                ->setPageSize($nav_params['nPageSize'])
                ->initFromUri();
            if ($nav->allRecordsShown()) {
                $nav_params = false;
            } else {
                $nav_params['iNumPage'] = $nav->getCurrentPage();
            }

            $ui_filter = $this->getUIFilter();

            $filterOption = new Bitrix\Main\UI\Filter\Options($list_id);
            $filterData = $filterOption->getFilter([]);

            $autoId = intval($_REQUEST['avto_id']);
            $arDeal = DealAvtoTable::getList([
                'filter' => [
                    'avto_id' => $autoId,
                ],
                'select' => ['deal_id',
                ],
            ])->fetchAll();


            $arDealId = [];
            foreach ($arDeal as $key => $value) {
                $arDealId[] = $value['deal_id'];
            }
            if (empty($arDealId)) {
                $arDealId = null;
            }

            $prepareFiter = [];
            if (isset($filterData['DATE_CREATE_from'])) {
                $prepareFiter['>DATE_CREATE'] = $filterData['DATE_CREATE_from'];
                $prepareFiter['<DATE_CREATE'] = $filterData['DATE_CREATE_to'];
            }
            $prepareFiter["@ID"] = $arDealId;

            $arSelectFields = array("ID", "TITLE", "DATE_CREATE");

            $res = CCrmDeal::GetListEx($sort['sort'], $prepareFiter, false, $nav_params, $arSelectFields);

            $list = [];
            while ($row = $res->GetNext()) {
                $list[] = [
                    'data' => [
                        "ID" => $row['ID'],
                        "TITLE" => "<a href='/crm/deal/details/" . $row['ID'] . "/'>" . $row['TITLE'] . '</a>',
                        "DATE_CREATE" => $row['DATE_CREATE'],
                    ],
                ];
            }

            $this->arResult['SHOW_ROW_CHECKBOXES'] = false;
            $this->arResult['COLUMNS'] = $this->getColumn();
            $this->arResult['FILTER_ID'] = $list_id;
            $this->arResult['GRID_ID'] = $list_id;
            $this->arResult['FILTER'] = $ui_filter;
            $this->arResult['LISTS'] = $list;
            $nav->setRecordCount($res->selectedRowsCount());
            $this->arResult['NAV'] = $nav;
            $this->includeComponentTemplate();

        }
    }

    /**
     * @return bool
     * @throws \Bitrix\Main\LoaderException
     */
    protected function checkModules(): bool
    {
        if (!Loader::includeModule('crm')) {
            echo("Не подключен модуль crm");
            return false;
        }
        return true;
    }

    /**
     * @return array[]
     */
    private function getUISorting(): array
    {
        return ['sort' =>
            ['TITLE' => 'DESC'],
            'vars' => ['by' => 'by', 'order' => 'order'],
        ];
    }

    /**
     * @return array[]
     */
    private function getUIFilter(): array
    {
        $uiFilter = [

            [
                'id' => 'DATE_CREATE',
                'name' => 'Дата создания',
                'type' => 'date',
                'default' => true,
            ],
        ];
        return $uiFilter;
    }

    /**
     * @return array[]
     */
    private function getColumn(): array
    {
        $columns = [
            array(
                'id' => 'ID',
                'name' => 'ID',
                'sort' => 'ID',
                'default' => true,
            ),
            array(
                'id' => 'TITLE',
                'name' => 'Название',
                'sort' => 'TITLE',
                'default' => true,
            ),
            array(
                'id' => 'DATE_CREATE',
                'name' => 'Дата создания',
                'sort' => 'DATE_CREATE',
                'default' => true,
            ),

        ];
        return $columns;
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteAction($id): bool
    {
        $result = DoctorsTable::delete($id);
        return !empty($result);
    }

    /**
     * @param $item
     * @param $key
     * @return void
     */
    private function convertValueToIntager(&$item, $key)
    {
        if (is_numeric($item)) {
            if (strpos($item, '.') === false) {
                $item = (int)$item;
            }

        }
    }

}
