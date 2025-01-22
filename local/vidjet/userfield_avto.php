<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
    "lars:avto",
    "",
    array(
        "LIST_SINGLE" => "2"
    )
);

?>
