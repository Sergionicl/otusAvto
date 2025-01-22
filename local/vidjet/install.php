<?php
require_once (__DIR__.'/crest.php');


$install_result = CRest::installApp();

$handlerAutoList = ($_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] === '443' ? 'https' : 'http') . '://'
	. $_SERVER['SERVER_NAME']
	. (in_array($_SERVER['SERVER_PORT'],	['80', '443'], true) ? '' : ':' . $_SERVER['SERVER_PORT'])
	. str_replace($_SERVER['DOCUMENT_ROOT'], '',__DIR__)
	. '/auto_list.php';

$handlerUserfieldAvto = ($_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] === '443' ? 'https' : 'http') . '://'
    . $_SERVER['SERVER_NAME']
    . (in_array($_SERVER['SERVER_PORT'],	['80', '443'], true) ? '' : ':' . $_SERVER['SERVER_PORT'])
    . str_replace($_SERVER['DOCUMENT_ROOT'], '',__DIR__)
    . '/userfield_avto.php';


$result = CRest::call(
	'placement.bind',
	[
		'PLACEMENT' => 'CRM_CONTACT_DETAIL_TAB',
		'HANDLER' => $handlerAutoList,
		'TITLE' => "ГАРАЖ"
	]
);

CRest::setLog(['CRM_CONTACT_DETAIL_TAB' => $result], 'installation');

$result = CRest::call(
    'userfieldtype.add',
    [
        'USER_TYPE_ID'=> 'cource_field',
        'HANDLER'=> $handlerUserfieldAvto,
        'TITLE'=> 'Информация по авто',
        'DESCRIPTION'=> 'Информация по авто'
    ]
);



CRest::setLog(['COURCE_FIELD' => $result], 'installation');


if($install_result['rest_only'] === false):?>
<head>
	<script src="//api.bitrix24.com/api/v1/"></script>
	<?if($install_result['install'] == true):?>
	<script>
		BX24.init(function(){
			BX24.installFinish();
		});
	</script>
	<?endif;?>
</head>
<body>
	<?if($install_result['install'] == true):?>
		installation has been finished
	<?else:?>
		installation error
	<?endif;?>
</body>
<?endif;