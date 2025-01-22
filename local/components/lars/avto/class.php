<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Avto\Orm\AvtoTable;
use Avto\Orm\DealAvtoTable;
use \Bitrix\Main\Loader;

// модели работающие с инфоблоками

class LarAvto extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable // \Bitrix\Main\Errorable

{

    /**
     * @return array
     */
    public function configureActions()
    {
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
        Loader::includeModule('crm');

        if (isset($_REQUEST['contact_id'])) {
            $contactId = $_REQUEST['contact_id'];
            $mode = 1;
        } else {
            $placeOpt = json_decode($_REQUEST['PLACEMENT_OPTIONS']);
            $mode = $this->arParams['LIST_SINGLE'];
        }
        $this->arResult['DEAL_ID'] = 0;
        $this->arResult['CHECKED_ID'] = 0;
        switch ($mode) {
            case '1'://Список
                if (!isset($contactId)) {
                    $contactId = $placeOpt->ID;
                }

                $arAvto = AvtoTable::getList([
                    'filter' => [
                        'id_client' => $contactId, // $contactId
                    ],
                    'select' => ['*',
                    ],
                ])->fetchAll();

                array_walk_recursive($arAvto, array($this, 'convertValueToIntager'));
                $this->arResult['AVTOS'] = json_encode($arAvto);
                $this->arResult['MODE'] = 'LIST';
                $this->arResult['CONTACT_ID'] = $contactId;
                //$this->arResult['READABLE'] = $readable;
                $this->includeComponentTemplate();
                break;
            case '2':
                $dealId = $placeOpt->ENTITY_VALUE_ID;
                $contactId = \CCrmDeal::getById($dealId)['CONTACT_ID'];
                if (!isset($contactId)) {
                    echo "Нет контактныйх данных";
                    exit;
                }
                $mock = 1;
                $arAvto = AvtoTable::getList([
                    'filter' => [
                        'id_client' => $contactId,
                    ],
                    'select' => ['*',
                    ],
                ])->fetchAll();

                $res = DealAvtoTable::getList(['filter' => ['deal_id' => $dealId]]);
                while ($ar = $res->fetch()) {
                    // pr($archeckedId);
                    $this->arResult['CHECKED_ID'] = $ar['avto_id'];
                }

                array_walk_recursive($arAvto, array($this, 'convertValueToIntager'));
                $this->arResult['AVTOS'] = json_encode($arAvto);
                $this->arResult['MODE'] = 'CHECK';
                $this->arResult['CONTACT_ID'] = $contactId;
                $this->arResult['DEAL_ID'] = $dealId;
                //$this->arResult['READABLE'] = $readable;
                $this->includeComponentTemplate();
                break;
        }
    }

    /**
     * @param $id
     * @return bool
     */
    public function deleteAction(int $id): bool
    {
        $result = DoctorsTable::delete($id);
        return !empty($result);
    }

    /**
     * @param array|null $formData
     * @return int|null
     * @throws Exception
     */
    public function saveAvtoAction(?array $formData): ?int
    {
        $res = AvtoTable::add($formData);
        if ($res->isSuccess()) {
            return $res->getId();
        } else {
            return $res->getErrorMessages();
        }
    }

    /**
     * @param int $dealId
     * @return void
     * @throws \Bitrix\Main\LoaderException
     */
    public function deleteDealAction(int $dealId): void
    {
        Loader::includeModule('crm');
        $bCheckRight = true;
        $entityObject = new \CCrmDeal($bCheckRight);
        $result = $entityObject->delete($dealId);

    }

    /**
     * @param int $dealId
     * @return bool|array
     * @throws \Bitrix\Main\LoaderException
     */
    public function changeDealStageAction(int $dealId): bool|array
    {
        $errors = [];
        Loader::includeModule('crm');
        $bCheckRight = true;
        $arFields = ['STAGE_ID' => 'UC_AL9MMH'];
        $entityObject = new \CCrmDeal($bCheckRight);
        $isUpdateSuccess = $entityObject->Update($dealId,
            $arFields,
            $bCompare = true,
            $bUpdateSearch = true,
            $arOptions = [
                'CURRENT_USER' => \CCrmSecurityHelper::GetCurrentUserID(),
                'IS_SYSTEM_ACTION' => false,
                'REGISTER_SONET_EVENT' => true,
                'ENABLE_SYSTEM_EVENTS' => true,
                'SYNCHRONIZE_STAGE_SEMANTICS' => true,
                'DISABLE_USER_FIELD_CHECK' => false,
                'DISABLE_REQUIRED_USER_FIELD_CHECK' => false,
            ]
        );
        if (!$isUpdateSuccess) {
            $errors[] = 'Не удалось перевести на стадию';
        }
        return true;
    }

    /**
     * @param int|null $avtoId
     * @param int|null $dealId
     * @return array|array[]|int
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function checkAvtoAction(?int $avtoId, ?int $dealId)
    {
        $res = DealAvtoTable::getList(['filter' => ['deal_id' => $dealId]]);
        while ($deal = $res->fetch()) {
            DealAvtoTable::delete($deal['id']);
        }
        Loader::includeModule('crm');
        $res = DealAvtoTable::getList(['filter' =>
            [
                'avto_id' => $avtoId,
            ]
        ]);
        $otherDeal = [];
        while ($dealAvto = $res->fetch()) {
            $ar = \CCrmDeal::GetListEx([], [
                    '!STAGE_ID' => 'WON',
                    'ID' => $dealAvto['deal_id'],
                ]
            )->fetch();
            if ($ar) {
                $otherDeal[] = $dealAvto;
            }
        }

        if (!empty($otherDeal)) {
            return ['errors' => $otherDeal];
        }

        $res = DealAvtoTable::add(["fields" => [
            "deal_id" => $dealId,
            "avto_id" => $avtoId,
        ]]);

        if ($res->isSuccess()) {
            return $res->getId();
        } else {
            return $res->getErrorMessages();
        }
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
