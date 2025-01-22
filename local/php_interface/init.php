<?php

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Catalog\Product\QuantityControl;
use Bitrix\Crm\Service\Factory;
use Lars\Agent\RequestAvtoManager;

if (file_exists(__DIR__ . '/classes/autoload.php')) {
    require_once __DIR__ . '/classes/autoload.php';
}
// require_once(Application::getDocumentRoot().'/local/php_interface/include/hlblock/events.php');

// вывод данных
function pr($var, $type = false)
{
    echo '<pre style="font-size:10px; border:1px solid #000; background:#FFF; text-align:left; color:#000;">';
    if ($type) {
        var_dump($var);
    } else {
        print_r($var);
    }

    echo '</pre>';
}

//создание заглушки ()
function expArrInLog($array): void
{
    //$log = date('Y-m-d H:i:s') . ' ' . print_r($array, true);
    $log = date('Y-m-d H:i:s') . ' ' . var_export($array, true);
    file_put_contents(__DIR__ . '/log.txt', $log . PHP_EOL, FILE_APPEND);
}

require_once Application::getDocumentRoot() . '/local/php_interface/include/crm/deal/IblockDeal/Handler.php';
require_once Application::getDocumentRoot() . '/local/php_interface/include/iblock/events.php';
require_once Application::getDocumentRoot() . '/local/php_interface/include/rest/events.php';
require_once Application::getDocumentRoot() . '/local/php_interface/include/agent/RequestAvtoManager.php';
require_once Application::getDocumentRoot() . '/local/php_interface/include/crm/deal/dealAvto/Handler.php';

Loader::registerAutoLoadClasses(null, [
    'Lars\\Deal\\Handler' => '/local/php_interface/include/crm/deal/IblockDeal/Handler.php',
    'Lars\\IBlock\\Event' => '/local/php_interface/include/iblock/events.php',
    'Lars\\Rest\\Event' => '/local/php_interface/include/rest/events.php',
    'Lars\\Agent\\RequestAvtoManager' => '/local/php_interface/include/agent/RequestAvtoManager.php',
    'Lars\\Deal\\Avto\\Handlder' => '/local/php_interface/include/crm/deal/dealAvto/Handler.php',
//    'Otus\Orm\Events' => '/local/php_interface/include/orm/events.php',
]);


/****************BEGIN Запуск ежедневно БП вставка остатка***************/
function bpPuchaseSpareParts()
{
    Loader::includeModule('catalog');
    Loader::includeModule('crm');
//Берем список id продукции
    $result = \Bitrix\Catalog\ProductTable::getList(
        [
            'select' => ['ID'],
            'filter' => ['QUANTITY_TRACE' => 'N'],
            'order' => ['ID' => 'ASC'],
        ]
    )->fetchAll();
    $arProd = array_map(function ($item) {
        return $item['ID'];
    }, $result);
//Количество продукции
    $n = count($arProd);
//Получаем случайные сисла
    $arrRandomNum = RequestAvtoManager::getRandomArr($n);

//Обновляем количество в вариациях
    foreach ($arProd as $key => $prodID) {
        $arSKU = \CCatalogSKU::getOffersList($prodID);
        $skuFirstID = array_key_first($arSKU[$prodID]);

        $res = \Bitrix\Catalog\ProductTable::update($skuFirstID, ['QUANTITY' => $arrRandomNum[$key]]);
        //если количество 0, то создаем заявку

        if ($arrRandomNum[$key] == 0) {
            $entityTypeId = 1034;
            $newAssigned = 1;
            $userId = 1;
            $factory = \Bitrix\Crm\Service\Container::getInstance()->getFactory($entityTypeId);
            $item = $factory->createItem();
            $item->setAssignedById($newAssigned);
            $context = new \Bitrix\Crm\Service\Context();
            $context->setUserId($userId);
            // операция производится от пользователя $userId с выполнением всех проверок
            $operation = $factory->getAddOperation($item, $context);
            $res = $operation->launch();
            if ($res->isSuccess()) {
                $itemId = $res->getId();
                RequestAvtoManager::createProc($itemId, $prodID);
            }

        }
    }
    return 'bpPuchaseSpareParts();';
}

/****************END Запуск ежедневно БП вставка остатка***************/

