<?php

use phpKKM\app\Model\cKKMModel;

// Файл конфигурации
require_once "../app/Config/Config.php";
// Загружаем функции (автозагрузка классов, генерация UUID)
require_once(PHPKKM_ROOT.'/app/Functions/fFunctions.php');

// Инициализируем обьекты
require_once PHPKKM_ROOT.'/app/Includes/init.php';

// Подключаем сторонние библиотеки
require_once PHPKKM_ROOT.'/app/Vendor/uuid/src/Lootils/Uuid/Uuid.php';

// Получаем состояние кассы
$vStatus = cKKMModel::fKKMGetStatus();

// Касса не заблокирована ($vStatus == 'run')
if (preg_match("/run/", $vStatus)) {
        // Блокируем кассу
        cKKMModel::fKKMSetStatus("lock");
        // Формируем задание "информаця о кассе" (загружаем шаблон из папки app/Config/KKM_action/ATOL_LOCAL/kkm_info.php)
        $aJSON_Data=cKKMModel::fKKMCreateTask('kkm_info');
        // Отправляем задание на ККМ
        $oJSON_Result=$oConnectModel->fSendTaskToKKMServer($aJSON_Data);

    // Задание выполнено без ошибок ($oConnectModel->vTaskError==true)
    if (!$oConnectModel->vTaskError) {
        print "kkm_info успешно отработал!\n";
    } else {
        // Касса не отвечает!
        print "kkm_info не сработал!\n";
        print "error: ".$oConnectModel->vTaskCurrentError."\n";
    }
    // Разблокируем кассу
    cKKMModel::fKKMSetStatus("run");
}
