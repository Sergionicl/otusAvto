<?php

namespace Lars\Agent;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class RequestAvtoManager
{
    /**
     * @param int $itemId
     * @param int $productId
     * @return bool|string
     * @throws LoaderException
     */
    static function createProc(int $itemId, int $productId): bool|string
    {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");
        $arWorkflowParameters = [];
        \CBPDocument::StartWorkflow(
            6, //ID робота, смотреть через таблицу b_bp_workflow_template
            array("crm", "Bitrix\Crm\Integration\BizProc\Document\Dynamic", "DYNAMIC_1034_" . $itemId),
            array_merge($arWorkflowParameters, array("id_prodoct" => $productId)),
            $arErrorsTmp
        );

        if (count($arErrorsTmp) > 0) {
            foreach ($arErrorsTmp as $e)
                $errorMessage .= "[" . $e["code"] . "] " . $e["message"] . " ";
            return $errorMessage;
        }
        return true;
    }


    /**
     * @param int $n
     * @return mixed
     */
    static function getRandomArr(int $n)
    {
        $message = json_encode(
            array(
                'jsonrpc' => '2.0',
                'id' => 1,
                'method' => 'generateIntegers',
                'params' => array(
                    "apiKey" => "40a53dc3-235c-434e-a532-195eb4b74cee",
                    "n" => $n,
                    "min" => 0,
                    "max" => 10,
                    "replacement" => true
                )
            )
        );
        $requestHeaders = [
            'Content-type: application/json'
        ];

        $ch = curl_init('https://api.random.org/json-rpc/4/invoke');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
        $json = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($json, true);
        return $res['result']['random']['data'];
    }
}