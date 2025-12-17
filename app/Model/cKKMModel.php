<?php

namespace phpKKM\app\Model;

use phpKKM\app\Common\cLogger;
use phpKKM\app\Common\cModel;

/**
 * Модель cKKMModel
 */

class cKKMModel extends cModel {

    /**
     * Находим и загружаем исходник JSON задачи
     *
     * @param string $vTask название задачи
     *
     * @return string array массив с запросом
     */

    public function fKKMRouter($vTask) {
	$vDirPath= PHPKKM_ROOT.'/app/Config/KKM_action/'.PHPKKM_ENGINE;
        $aDir = scandir($vDirPath);
        for($l=0; $l<count($aDir); $l++) {
            $vFilePath=$vDirPath.'/'.$aDir[$l];
            if (is_file($vFilePath)) {
                $vRoutes=str_replace(".php", "", $aDir[$l]);
		if ($vRoutes==$vTask) {
			// Загружаем шаблон задания
			$sTemplateCode=file_get_contents($vFilePath);
			// Ищем и подставляем в fGuid() сгенерированный UUID
			$sTemplateCode=preg_replace_callback('/fGuid\s*\(\s*\)/',
				function($matches) {
					return "'". fGuid() ."'";
				}, $sTemplateCode);
			$aTemplateData = eval("?>$sTemplateCode<?php ");
		    // Загружаем исходник JSON задачи
		    $aTaskBody=json_encode($aTemplateData, JSON_UNESCAPED_UNICODE);
		}
            }
        }
	return $aTaskBody;
    }

    /**
     * Создаем задачу
     *
     * @param string $vTask название задачи
     *
     * @return string array массив с запросом
     */

    public function fKKMCreateTask($vTask) {
	$vServiceTaskName = date("YmdHis")."_service";
	$vServiceTaskBody = self::fKKMRouter($vTask);
	$aJSON_Data[$vServiceTaskName]=$vServiceTaskBody;
	return $aJSON_Data;
    }

    /**
     * Выставляем статус ККМ
     *
     */
    public static function fKKMSetStatus($vStatus) {
	file_put_contents(PHPKKM_KKM_STATUS, $vStatus);
    }

    /**
     * Получаем статус ККМ
     *
     * @return string
     */
    public static function fKKMGetStatus() {
	if (file_exists(PHPKKM_KKM_STATUS)) { 
	    $vReturn = file_get_contents(PHPKKM_KKM_STATUS); 
	} else { 
	    $vReturn ="run";
	    file_put_contents(PHPKKM_KKM_STATUS, $vReturn);
	}
	return $vReturn;
    }

    /**
     * Выставляем время предыдущего запуска срипта
     *
     */
    public function fKKMSetTime() {
	file_put_contents(PHPKKM_KKM_TIME, date("d.m.Y H:i:s"));
    }

    /**
     * Получаем время предыдущего запуска ККМ
     *
     * @return datetime
     */
    public static function fKKMGetTime() {
	if (file_exists(PHPKKM_KKM_TIME)) { 
	    $vReturn = file_get_contents(PHPKKM_KKM_TIME); 
	} else {
	    $vReturn = date("d.m.Y H:i:s");
	    file_put_contents(PHPKKM_KKM_TIME, $vReturn);
	}
	return $vReturn;
    }

}


