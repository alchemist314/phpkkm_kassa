<?php

/**
 * Скрипт забирает задания от платежного сервера и отправляет на ККМ
 */

use phpKKM\app\Common\cLogger;
use phpKKM\app\Common\cModel;
use phpKKM\app\Model\cAuthModel;
use phpKKM\app\Model\cKKMModel;
use phpKKM\app\Model\cConnectModel;

// Файл конфигурации
require_once "../app/Config/Config.php";

// Загружаем функции (автозагрузка классов, генерация UUID)
require_once(PHPKKM_ROOT.'/app/Functions/fFunctions.php');

// Инициализируем обьекты
require_once PHPKKM_ROOT.'/app/Includes/init.php';

// Подключаем сторонние библиотеки
require_once PHPKKM_ROOT.'/app/Vendor/uuid/src/Lootils/Uuid/Uuid.php';

// Получаем время предыдущего запуска скрипта
$vKKMTime = cKKMModel::fKKMGetTime();
if (preg_match("/[0-9]/", $vKKMTime)) {
    $oDateScriptLastRun = new \DateTime($vKKMTime);
    // Получаем разницу в минутах
    $vScriptLastRunDiffMin = $oDateNow->diff($oDateScriptLastRun)->format('%i');
    // Получаем разницу в секундах
    $vScriptLastRunDiffSec = $oDateNow->diff($oDateScriptLastRun)->format('%s');
}

// Получаем состояние кассы
$vStatus = cKKMModel::fKKMGetStatus();

// Касса не заблокирована
if (preg_match("/run/", $vStatus)) {

    // Состояние смены: Проверка каждые 40 минут или по вызову платежного сервера
    if ($vScriptLastRunDiffMin>=0) {

	// Блокируем кассу
	cKKMModel::fKKMSetStatus("lock");

	// Формируем задание "состояние смены"
	$aJSON_Data=cKKMModel::fKKMCreateTask('shift_info');
	// Отправляем задание на ККМ
	$oJSON_Result=$oConnectModel->fSendTaskToKKMServer($aJSON_Data);

    // Задание выполнено без ошибок

    if (!$oConnectModel->vTaskError) {

	if (PHPKKM_ENGINE=='ATOL_LOCAL') {
	    // Получаем время действия смены
	    $vShiftStatus = $oJSON_Result->result->shiftStatus->state;
	    // Создаем обьект даты экспирации смены
	    $oDateShiftExpired = new \DateTime($oJSON_Result->result->shiftStatus->expiredTime);
	    // Создаем обьект текущей даты
	    // Получаем разницу в часах
	    $vShiftDiff=($oDateNow->getTimestamp()-$oDateShiftExpired->getTimestamp());
	}
	if (PHPKKM_ENGINE=='ATOL_HTTP') {
	    // Получаем время действия смены
	    $vShiftStatus = $oJSON_Result->results[0]->result->shiftStatus->state;
	    // Создаем обьект даты экспирации смены
	    $oDateShiftExpired = new DateTime($oJSON_Result->results[0]->result->shiftStatus->expiredTime);
	    // Создаем обьект текущей даты
	    // Получаем разницу в часах
	    $vShiftDiff=($oDateNow->getTimestamp()-$oDateShiftExpired->getTimestamp());
	}

	if (PHPKKM_ENGINE=='KKMS_HTTP') {
	    if ($oJSON_Result->Info->SessionState==3) {
		$vShiftStatus = "expired";
	    }
	}
	// Смена >=24 часа, закрываем смену
	if (($vShiftStatus=="expired") || (($vShiftStatus=="opened") && ($vShiftDiff>=-3584))) {
	    // Формируем задание на закрытие смены
	    $aJSON_Data=cKKMModel::fKKMCreateTask('shift_close');
	    // Отправляем задание на ККМ
	    $oJSON_Result=$oConnectModel->fSendTaskToKKMServer($aJSON_Data);
	} //24 expired
    } else {
	// Касса не отвечает! Перезагружаем кассу
	cLogger::fWriteLog("shift_info не сработал!", "", PHPKKM_LOG_CRIT);
    }

	// Выставляем время
	cKKMModel::fKKMSetTime();
	// Разблокируем кассу
	cKKMModel::fKKMSetStatus("run");
    } //diff

    // Запускаем только если скрипт вызван со стороны платежного сервера
    if (($argv[1]=='cron') || ($argv[1]=='direct')) {
	// Блокируем кассу
	cKKMModel::fKKMSetStatus("lock");

    // Забираем задания с платежного сервера
	$oJSON_Data = cConnectModel::fSendActionToPayServer('get_tasks');
	if (!$oJSON_Data->result) {
	    cLogger::fWriteLog("get_tasks не сработал!","", PHPKKM_LOG_WARN);
	} else {
	// Есть задания, отправляем их на KKM
	if (count($oJSON_Data->tasks)) {
		$oConnectModel->fSendTaskToKKMServer($oJSON_Data->tasks);
	    }
	} // get task 

    cModel::$aCompleteTasks = $oTaskModel->fGetCompleteTasks();

        // Передаем исполненные задания на платежный сервер
	if (count(cModel::$aCompleteTasks)) {
	    if (!$oConnectModel->fSendActionToPayServer('clear_tasks', cModel::$aCompleteTasks)->result) {
		cLogger::fWriteLog("clear_tasks не сработал!", "", PHPKKM_LOG_WARN);
	    }
	}

	cModel::$aIncompleteTasks = $oTaskModel->fGetIncompleteTasks();

	// Передаем неисполненные задания на платежный сервер
        if (count(cModel::$aIncompleteTasks)) {
	    if (!$oConnectModel->fSendActionToPayServer('incomplete_tasks', cModel::$aIncompleteTasks)->result) {
		cLogger::fWriteLog("incomplete_tasks не сработал!", "", PHPKKM_LOG_WARN);
	    }
	}

    } //exec cron or direct
    // Разблокируем кассу
    cKKMModel::fKKMSetStatus("run");
} // status==run

?>