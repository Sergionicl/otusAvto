<?
//use Bitrix\Intranet\Integration\Wizards\Portal\Ids;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

//echo "Привет";
$APPLICATION->IncludeComponent(
	"lars:avto",
	"",
	Array(
		"LIST_SINGLE" => "1"
	)
);

//require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
?>
