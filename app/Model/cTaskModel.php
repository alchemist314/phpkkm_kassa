<?php

namespace phpKKM\app\Model;

use phpKKM\app\Common\cLogger;
use phpKKM\app\Common\cModel;

/**
 * Модель cTaskModel
 */

class cTaskModel extends cModel {

    /**
    * Конструктор
    */
    public function __construct() {
	// Подготавливаем список выполненных заданий
	cModel::$aCompleteTasks  = $this->fGetCompleteTasks();
	// Подготавливаем список ожидающих исполнения заданий
	cModel::$aWaitingTasks  = $this->fGetWaitingTasks();
	// Подготавливаем список не выполненных заданий
	cModel::$aIncompleteTasks  = $this->fGetIncompleteTasks();
    }

    /**
     * Создаем массив выполненных заданий
     *
     * @return string array
     */
    public function fGetCompleteTasks() {

	// Создаем обьект текущей даты
        $oDateStart = new \DateTime('now');
	// Отнимаем указанное количество дней
	$oDateStart->modify('-'.PHPKKM_DAY_DELTA.' day');
	$vDateStart=$oDateStart->format('YmdHis');


        if (PHPKKM_STORAGE=='DIR') {
	    // Забираем завершенные задания из директории
	    $aYearsDir = scandir(PHPKKM_TASKS_DIR_COMPLETE);
	    // Пробегаем по всем годам в директории
	    for($l=2; $l<count($aYearsDir); $l++) {
		// Директория с годом
		$vYear = PHPKKM_TASKS_DIR_COMPLETE.'/'.$aYearsDir[$l];
		if (is_dir($vYear)) {
		    $aMonthsDir = scandir($vYear);
		    // Пробегаем по всем месяцам в директории
		    for($m=2; $m<count($aMonthsDir); $m++) {
			// Директория с месяцем
    			$vMonth = $vYear.'/'.$aMonthsDir[$m];
			if (is_dir($vMonth)) {
			    // Директория с заданиями месяца
			    $aTasksDir = scandir($vMonth);
			    // Пробегаем по всем заданиям в месяце
			    for($t=2; $t<count($aTasksDir); $t++) {
				if (is_file($vMonth.'/'.$aTasksDir[$t])) {
				    // Формируем общий массив с заданиями
				    $vTaskDate=substr($aTasksDir[$t], 0, 14);
				    // Если дата задание >= текущая дата минус дельта, добавляем его в массив
				    if ($vTaskDate>=$vDateStart) {
					// Если задание не сервисное
					if (!preg_match("/service/", $aTasksDir[$t])) {
					    $aTaskCompleteArray[]=$aTasksDir[$t];
					}
				    }
				}
			    }
			}
		    }
		}
	    }
        }

    if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {

	//Забираем завершенные задания из БД
	try {
	    $oResults = cModel::$oPDO->query("SELECT tname FROM ".PHPKKM_SQL_TABLE." WHERE tflag=-1 AND substr(tname, 1, 14)>=".$vDateStart." AND tname NOT LIKE '%service%'");
	    foreach ($oResults->fetchAll() as $aRow) {
		//$vTaskDate=substr($aTasksDir[$t], 0, 14);
		$aTaskCompleteArray[]=$aRow[tname];
	    }
	} catch(PDOException $e) {
	    cLogger::fWriteLog("Невозможно выгрузить из БД: ".$e->getMessage(), __FUNCTION__, PHPKKM_LOG_CRIT);
	}

    }


	return $aTaskCompleteArray;
    }

    /**
     * Задание выполнено
     */
    public function fSetTaskComplete ($vTaskName, $vTaskBody, $aJSON_Result_Object) {

        if (PHPKKM_STORAGE=='DIR') {
	    $vYear = substr($vTaskName, 0,4);
	    // Если нет директории с текущим годом, создаем ее
	    if (!file_exists(PHPKKM_TASKS_DIR_COMPLETE.'/'.$vYear)) {
		mkdir(PHPKKM_TASKS_DIR_COMPLETE.'/'.$vYear, 0755, true);
	    }
	    $vMonth = substr($vTaskName, 4,2);
	    // Если нет директории с текущим месяцем, создаем ее
	    if (!file_exists(PHPKKM_TASKS_DIR_COMPLETE.'/'.$vYear.'/'.$vMonth)) {
		mkdir(PHPKKM_TASKS_DIR_COMPLETE.'/'.$vYear.'/'.$vMonth, 0755, true);
	    }
	    // Сохраняем задание в директорию PHPKKM_TASKS_DIR_COMPLETE / год / месяц / задача
	    $vFilePath=PHPKKM_TASKS_DIR_COMPLETE.'/'.$vYear.'/'.$vMonth.'/'.$vTaskName;
	    if (!file_put_contents($vFilePath, $vTaskBody."\n".print_r($aJSON_Result_Object,true))) {
		cLogger::fWriteLog("Невозможно записать файл: ".$vFilePath, $vTaskName, PHPKKM_LOG_CRIT);
	    } else {
		cLogger::fWriteLog(" перенесено в архив.", $vTaskName, "");
	    }
	    // Проверяем не находится ли задание в ожидащих исполнения заданиях
	    if (is_array(cModel::$aWaitingTasks) && in_array($vTaskName,cModel::$aWaitingTasks)) {
		// Удаляем ожидающие завершения задания из папки PHPKKM_TASKS_DIR_WAIT
		if (!unlink(PHPKKM_TASKS_DIR_WAIT.'/'.$vTaskName)) {
		    cLogger::fWriteLog("Невозможно удалить файл: ".PHPKKM_TASKS_DIR_WAIT.'/'.$vTaskName, $vTaskName, PHPKKM_LOG_CRIT);
		} else {
		    cLogger::fWriteLog(" удалено из папки: ".PHPKKM_TASKS_DIR_WAIT, $vTaskName, "");
		}
	    }
        }
	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    // Обновляем статус задания в БД
	    try {

		cModel::$oPDO->query("UPDATE ".PHPKKM_SQL_TABLE." SET tflag=-1, tresult='".json_encode($aJSON_Result_Object,JSON_UNESCAPED_UNICODE)."' WHERE tname='".$vTaskName."'");
		cLogger::fWriteLog("заданию присвоен статус: выполнено", $vTaskName, "");
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно обновить БД: ".$e->getMessage(), $vTaskName, PHPKKM_LOG_CRIT);
	    }
	}
    }

    /**
     * Задание не выполнено
     */
    public function fSetTaskIncomplete ($vTaskName, $vTaskBody, $jResult) {

	if (PHPKKM_STORAGE=='DIR') {
	    // Сохраняем задание в директорию PHPKKM_TASKS_DIR_INCOMPLETE
	    if (!file_put_contents(PHPKKM_TASKS_DIR_INCOMPLETE.'/'.$vTaskName, $vTaskBody."\n".$jResult)) {
		cLogger::fWriteLog("Невозможно записать файл в папку: ", $vTaskName, PHPKKM_LOG_CRIT);
	    } else {
		cLogger::fWriteLog(" перенесено в папку: ".PHPKKM_TASKS_DIR_INCOMPLETE, $vTaskName, "");
	    }
        }
	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    // Обновляем статус задания в БД
	    try {
		cModel::$oPDO->query("UPDATE ".PHPKKM_SQL_TABLE." SET tflag=3, tresult='".$jResult."' WHERE tname='".$vTaskName."'");
		cLogger::fWriteLog("заданию присвоен статус: не выполнено", $vTaskName, "");
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно обновить БД: ".$e->getMessage(), $vTaskName, PHPKKM_LOG_CRIT);
	    }
        }
    }

    /**
     * Задание в процессе исполнения
     */
    public function fSetTaskWait($vTaskName) {

	if (PHPKKM_STORAGE=='DIR') {
	    // Сохраняем задание в директорию PHPKKM_TASKS_DIR_WAIT
	    if (!file_put_contents(PHPKKM_TASKS_DIR_WAIT.'/'.$vTaskName, "")) {
		cLogger::fWriteLog("Невозможно записать файл в папку: ", $vTaskName, PHPKKM_LOG_CRIT);
	    } else {
		cLogger::fWriteLog(" перенесено в папку: ".PHPKKM_TASKS_DIR_WAIT, $vTaskName, "");
	    }
	}

	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    // Обновляем статус задания в БД
	    try {
		cModel::$oPDO->query("UPDATE ".PHPKKM_SQL_TABLE." SET tflag=2 WHERE tname='".$vTaskName."'");
		cLogger::fWriteLog("заданию присвоен статус: в ожидании","", "");
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно обновить БД: ".$e->getMessage(), $vTaskName, PHPKKM_LOG_CRIT);
	    }
        }
    }

    /**
     * Возвращает задания с ошибками
     *
     * @return string array
     */
    public function fGetIncompleteTasks() {

	if (PHPKKM_STORAGE=='DIR') {
	    //Забираем незавершенные задания из директории
	    $aIncompleteTaskName = scandir(PHPKKM_TASKS_DIR_INCOMPLETE);
	    // Пробегаем по всем файлам в директории
	    for($l=0; $l<count($aIncompleteTaskName); $l++) {
		$vTaskFilePath = PHPKKM_TASKS_DIR_INCOMPLETE.'/'.$aIncompleteTaskName[$l];
		if (is_file($vTaskFilePath)) {
		    $aReadTasksBody=file($vTaskFilePath);
		    strlen($aReadTasksBody[1]) > 1 ? '' : $aReadTasksBody[1]="no data";
		    // Если задание не сервисное
		    if (!preg_match("/service/", $aIncompleteTaskName[$l])) {
			$aIncompleteTasks[$aIncompleteTaskName[$l]]=$aReadTasksBody[1];
			//$aIncompleteTasks[$aIncompleteTaskName[$l]]=file_get_contents($vTaskFilePath);
		    }
		}
	    }
	}
	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    //Забираем незавершенные задания из БД
	    try {
		$oResults = cModel::$oPDO->query("SELECT tname,tbody,tresult FROM ".PHPKKM_SQL_TABLE." WHERE tflag=3 AND tname NOT LIKE '%service%'");
		foreach ($oResults->fetchAll() as $aRow) {
		    $aIncompleteTasks[$aRow[tname]]=$aRow[tbody]."\n".$aRow[tresult];
		}
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно выгрузить из БД: ".$e->getMessage(), __FUNCTION__, PHPKKM_LOG_CRIT);
	    }
        }

	return $aIncompleteTasks;
    }

    /**
     * Возвращает ожидающие исполнения задания
     *
     * @return string array
     */

    public function fGetWaitingTasks() {

	if (PHPKKM_STORAGE=='DIR') {
	    //Забираем ожидающие исполнения задания из директории
	    $aWaitTaskName = scandir(PHPKKM_TASKS_DIR_WAIT);
	    // Пробегаем по всем файлам в директории
	    for($l=0; $l<count($aWaitTaskName); $l++) {
		$vTaskFilePath = PHPKKM_TASKS_DIR_WAIT.'/'.$aWaitTaskName[$l];
		if (is_file($vTaskFilePath)) {
		    // Если задание не сервисное
		    if (!preg_match("/service/", $aWaitTaskName[$l])) {
			$aWaitTasks[]=$aWaitTaskName[$l];
		    }
		}
	    }
	}
	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    //Забираем ожидающие исполнения задания из БД
	    try {
		$oResults = cModel::$oPDO->query("SELECT tname,tbody FROM ".PHPKKM_SQL_TABLE." WHERE tflag=2 AND tname NOT LIKE '%service%'");
		foreach ($oResults->fetchAll() as $aRow) {
		    $aWaitTasks[]=$aRow[tname];
		}
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно выгрузить из БД: ".$e->getMessage(), __FUNCTION__, PHPKKM_LOG_CRIT);
	    }
        }

  	return $aWaitTasks;
    }


    /**
     * Сохраняем текущее задание в БД
     */
    public function fPutTaskToDB($vTaskName, $jTaskBody, $vUUID) {

	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    $vQuery="
		    INSERT INTO 
				".PHPKKM_SQL_TABLE." (
				    uuid,
				    tname,
				    tbody,
				    tresult,
				    tflag)
		    VALUES  (
				    '".$vUUID."',
				    '".$vTaskName."',
				    '".$jTaskBody."',
				    '',
				    0
			    )";
	    try {
		cModel::$oPDO->query($vQuery);
		cLogger::fWriteLog("------------------------------------------", $vTaskName, "");
		cLogger::fWriteLog("новое задание", $vTaskName, "");
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно сохранить в БД: ".$e->getMessage(), $vTaskName, PHPKKM_LOG_CRIT);
	    }
        }

    }

    /**
     * Записываем результат ATOL_LOCAL задания
     *
     * @return json array
     */
    public function fSetResultLocalTask($vTaskName, $jResult) {
	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    //Записываем результат задания в БД
	    try {
		$oResults = cModel::$oPDO->query("UPDATE ".PHPKKM_SQL_TABLE." SET tresult='".$jResult."' WHERE tname='".$vTaskName."'");
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно выгрузить из БД: ".$e->getMessage(), __FUNCTION__, PHPKKM_LOG_CRIT);
	    }

	}
	return $jResult;
    }
    /**
     * Забираем результат ATOL_LOCAL задания
     *
     * @return json array
     */
    public function fGetResultLocalTask($vTaskName) {
	if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
	    //Забираем результат задания из БД
	    try {
		$oResults = cModel::$oPDO->query("SELECT tresult FROM ".PHPKKM_SQL_TABLE." WHERE tname='".$vTaskName."'");
		foreach ($oResults->fetchAll() as $aRow) {
		    $jResult=$aRow[tresult];
		}
	    } catch(PDOException $e) {
		cLogger::fWriteLog("Невозможно выгрузить из БД: ".$e->getMessage(), __FUNCTION__, PHPKKM_LOG_CRIT);
	    }

	}
	return $jResult;
    }
}

?>
