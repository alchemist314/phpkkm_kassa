<?php

namespace phpKKM\app\Model;

use phpKKM\app\Common\cLogger;
use phpKKM\app\Common\cModel;
use phpKKM\app\Model\cTaskModel;

/**
 * Модель cConnectModel
 */

class cConnectModel extends cModel {

    /**
     * Хранит UUID текущего задания
     * 
     * @var string
     */
    private $vTaskCurrentUUID="";

    /**
     * Хранит название текущего задания
     * 
     * @var string
     */
    private $vTaskCurrentName="";

    /**
     * Хранит 'тело' текущего задания
     * 
     * @var string
     */
    private $vTaskCurrentBody="";

    /**
     * Хранит результат текущего задания
     * 
     * @var string
     */
    private $vTaskCurrentResult="";

    /**
     * Хранит состояние ошибки
     * 
     * @var bool
     */
    public $vTaskError=false;

    /**
     * Хранит обьект класса cTaskModel
     * 
     * @var object
     */
    public $oTaskModel;
    
    /**
    * Конструктор
    */
    public function __construct() {
	$this->oTaskModel = new cTaskModel();
    }

    /**
     * Отсылаем управляющую команду платежному серверу
     */
    public function fSendActionToPayServer($vAction, $aData="") {

	$vHTTP = [
	    'method' => 'POST',
	    'timeout'=> PHPKKM_SERVER_TIMEOUT,
	    'header'=>  "Content-Type: application/json\r\n" .
                "Accept: application/json\r\n",
	    'content' => urlencode(json_encode([
                'login'=>PHPKKM_LOGIN,
                'passwd'=>PHPKKM_PASSWD,
                'action'=>$vAction,
		'data'=>$aData
	    ],JSON_UNESCAPED_UNICODE))
	];
	$vSSL= [
		'verify_peer'=>false,
		'verify_peer_name'=>false
	];


	$vStream = stream_context_create(['http'=>$vHTTP, 'ssl'=>$vSSL]);
	if (!$vResult = file_get_contents(PHPKKM_URL_TASKS,false,$vStream)) {
	    cLogger::fWriteLog("Невозможно соединиться с сервером: ".PHPKKM_URL_TASKS, __FUNCTION__, PHPKKM_LOG_CRIT);
	}

   //LOG
    return json_decode($vResult);

    }

    /**
     * Подготавливаем задания и отправляем на ККМ сервер
     */
    public function fSendTaskToKKMServer($aJSON_Data) {

	// Сбрасываем переменные текущего задания
	unset(
	    $this->vTaskCurrentUUID, 
	    $this->vTaskCurrentName,
	    $this->vTaskCurrentBody,
	    $this->vTaskCurrentResult,
	    $this->vTaskError
	);

	// Пробегаемся по массиву заданий
	foreach($aJSON_Data as $vTaskName=>$jTaskBody) {
	    $this->vTaskCurrentName = $vTaskName;

	    // Проверяем не было ли ранее успешно выполнено это задание
	    if (in_array($vTaskName,cModel::$aCompleteTasks)) {
		cLogger::fWriteLog("Задание уже было успешно исполнено, проверьте доступ на удаление задания с сервера оплат.", $this->vTaskCurrentName, PHPKKM_LOG_WARN);
	    } else {

		// Проверяем не находится ли задание в ожидающих исполнения заданиях
		if (in_array($vTaskName,cModel::$aWaitingTasks)) {
		    //Задание находится в папке ожидающих исполнения, обновляем его статус
		    cLogger::fWriteLog("Задание находится в статусе ожидающих исполнения", $this->vTaskCurrentName, PHPKKM_LOG_WARN);
		    $oJSON_Result=self::fKKM_GetResult($this->vTaskCurrentUUID);
		} else {

		    // Проверяем не находится ли задание в неисполненных заданиях
		    foreach(cModel::$aIncompleteTasks as $vIncompleteTaskName=>$vIncompleteTaskBody) {
			$aIncompleteTasksNames[]=$vIncompleteTaskName;
		    }
		    if (in_array($vTaskName,$aIncompleteTasksNames)) {
			cLogger::fWriteLog("Такое задание уже было! Для повторного исполнения измените uuid и tname", $this->vTaskCurrentName, PHPKKM_LOG_WARN);
		    } else {

			// Задание поступило впервые
    			$this->vTaskCurrentBody = $jTaskBody;
			// Получаем уникальный номер задания из JSON запроса
			if ((PHPKKM_ENGINE=='ATOL_HTTP') || (PHPKKM_ENGINE=='ATOL_LOCAL')) {
			    $this->vTaskCurrentUUID=json_decode($jTaskBody)->uuid;
			}
			if (PHPKKM_ENGINE=='KKMS_HTTP') {
			    $this->vTaskCurrentUUID=json_decode($jTaskBody)->IdCommand;
	    		}

			if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
			    // Записываем текущее задание в БД
			    $this->oTaskModel->fPutTaskToDB($this->vTaskCurrentName, $this->vTaskCurrentBody, $this->vTaskCurrentUUID);
			}
			// Отправляем задание на ККМ сервер
			self::fKKM_Connector($jTaskBody);
			// Ожидаем
			sleep(PHPKKM_TASK_DELAY);
    			// Получаем результат
			$oJSON_Result=self::fKKM_GetResult($this->vTaskCurrentUUID);
		    } // first time task
		} // waiting
	    } // already
	}
	return $oJSON_Result;
    }

    /**
     * Отправляем задание на ККМ сервер
     */
    public function fKKM_Connector($jKKMData) {

	if (PHPKKM_ENGINE=='KKMS_HTTP') {
	    $vAddHeader='Authorization: Basic '.base64_encode("User:")."\r\n";
	    $vURLParam='Execute';
	}
	if (PHPKKM_ENGINE=='ATOL_HTTP') {
	    $vAddHeader='';
	    $vURLParam='requests';
	}

	if (PHPKKM_ENGINE=='ATOL_LOCAL') {
	    $oKKMData=json_decode($jKKMData);
	    $jTaskBody=json_encode($oKKMData->request,JSON_UNESCAPED_UNICODE);
	    $jTaskBody=str_replace("\"", "\\\"", $jTaskBody);
	    chdir(PHPKKM_JSONDRV_DIRPATH);
	    $sStr="/usr/bin/java -classpath .:".PHPKKM_JSONDRV_LIBFPTRPATH." proxydrv.proxydrv \"".str_replace(array("\r", "\n"),"", $jTaskBody)."\"";
	    exec($sStr, $aOut);
	    $jResult=$this->vTaskCurrentResult=$aOut[0];
	    // Записываем результат задания
	    $this->oTaskModel->fSetResultLocalTask($this->vTaskCurrentName, $jResult);
	} else {
	    $vHTTP = [
		'method'=>'POST',
		'timeout'=>PHPKKM_SERVER_TIMEOUT,
		'header'=>'Content-Type: application/json'."\r\n".
		$vAddHeader,
		'content'=>$jKKMData
	    ];
	}


	if ((PHPKKM_ENGINE=='ATOL_HTTP') || (PHPKKM_ENGINE=='KKMS_HTTP')) {
	    $vStream = stream_context_create(['http'=>$vHTTP]);
	}

	if (PHPKKM_ENGINE=='ATOL_HTTP') {
	    $oJSON_Result=file_get_contents(PHPKKM_URL_KKM."/".$vURLParam,false,$vStream);
	    $vResponseStr=$http_response_header[0];

	    if (preg_match("/201/", $vResponseStr) && (preg_match("/Created/", $vResponseStr))) {
		cLogger::fWriteLog("Задание принято ".PHPKKM_ENGINE." сервером!", $this->vTaskCurrentName, PHPKKM_LOG_SUCC);
	    } else {
		cLogger::fWriteLog("Задание не принято ".PHPKKM_ENGINE." сервером! result: ".$vResponseStr[0], $this->vTaskCurrentName, PHPKKM_LOG_WARN);
	    }
	}

	if (PHPKKM_ENGINE=='KKMS_HTTP') {
    	    if (!$jResult = file_get_contents(PHPKKM_URL_KKM."/".$vURLParam,false,$vStream)) {
		cLogger::fWriteLog("Невозможно соединиться с сервером: ".PHPKKM_URL_KKM." result: ".$jResult, "", PHPKKM_LOG_CRIT);
	    }
	}

    $oJSON_Result = json_decode($jResult);

	return $oJSON_Result;
    }

    /**
     * Получаем результат по указанному ID
     */
    public function fKKM_GetResult($vUUID) {

	    if (PHPKKM_ENGINE=='ATOL_LOCAL') {
		if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
		    $jResult=$this->oTaskModel->fGetResultLocalTask($this->vTaskCurrentName);
		}
		if (PHPKKM_STORAGE=='DIR') {
		    $jResult=$this->vTaskCurrentResult;
		}
		$oJSON_Result=json_decode($jResult);

		if ($oJSON_Result->error<0) {
			// Запрос успешно исполнен
			// Помечаем задание как выполненное
			cLogger::fWriteLog("Задание исполнено.", $this->vTaskCurrentName, PHPKKM_LOG_SUCC);
			// Переносим в архив
			//if (!preg_match("/service/", $this->vTaskCurrentName)) {
			    $this->oTaskModel->fSetTaskComplete($this->vTaskCurrentName, $this->vTaskCurrentBody, $oJSON_Result);
			//}
		} else {
			// Задание не исполнено
			$this->vTaskError=true;
			cLogger::fWriteLog("Задание не исполнено: ".$oJSON_Result->result, $this->vTaskCurrentName ,PHPKKM_LOG_WARN);
			// Переносим в папку ошибок
			//if (!preg_match("/service/", $this->vTaskCurrentName)) {
			    $this->oTaskModel->fSetTaskIncomplete($this->vTaskCurrentName, $this->vTaskCurrentBody, $jResult);
			//}
		}
	    }

	    if (PHPKKM_ENGINE=='ATOL_HTTP') {
		$jResult=file_get_contents(PHPKKM_URL_KKM."/requests/".trim($vUUID));
		$vResponseStr = $http_response_header[0];
		if (preg_match("/200/", $vResponseStr) && (preg_match("/OK/", $vResponseStr))) {
		    cLogger::fWriteLog("Запрос результата по ID прошел!", $this->vTaskCurrentName, PHPKKM_LOG_SUCC);
		} else {
		    $this->vTaskError=true;
		    cLogger::fWriteLog("Запрос презультата по ID не прошел! result: ".$vResponseStr[0], $this->vTaskCurrentName, PHPKKM_LOG_WARN);
		}
		$oJSON_Result=json_decode($jResult);

		if ($oJSON_Result->results[0]->status=="ready") {
			// Запрос успешно исполнен
			// Помечаем задание как выполненное
			cLogger::fWriteLog("Задание исполнено.", $this->vTaskCurrentName, PHPKKM_LOG_SUCC);
			// Переносим в архив, если только задание не сервисное
			if (!preg_match("/service/", $this->vTaskCurrentName)) {
			    $this->oTaskModel->fSetTaskComplete($this->vTaskCurrentName, $this->vTaskCurrentBody, $oJSON_Result);
			}
		} elseif ($oJSON_Result->results[0]->status=="wait") {
			// Запрос исполняется
			// Помечаем задание как ожидающее исполнения
			cLogger::fWriteLog("Задание ожидает исполнения.", $this->vTaskCurrentName, PHPKKM_LOG_WARN);
			// Переносим в архив ожидающих исполнения заданий, только если оно не сервисное
			if (!preg_match("/service/", $this->vTaskCurrentName)) {
			    $this->oTaskModel->fSetTaskWait($this->vTaskCurrentName);
			}
		} else {
			// Задание не исполнено
			$this->vTaskError=true;
			cLogger::fWriteLog("Задание не исполнено: ".$oJSON_Result->results->errorDescription, $this->vTaskCurrentName ,PHPKKM_LOG_WARN);
			// Переносим в папку ошибок, если только задание не сервисное
			if (!preg_match("/service/", $this->vTaskCurrentName)) {
			    $this->oTaskModel->fSetTaskIncomplete($this->vTaskCurrentName, $this->vTaskCurrentBody, $jResult);
			}
		}
	    
	    }

	    if (PHPKKM_ENGINE=='KKMS_HTTP') {
		    // Отправляем запрос на ККМ сервер
		    //$jKKMData = "{\"Command\": \"GetRezult\",\"IdCommand\":\"".$vUUID."\"}";
		    $aKKMData = array(
            		"Command"=>"GetRezult",
            		"idCommand"=>$vUUID
		    );
		    $jKKMData = json_encode($aKKMData,JSON_UNESCAPED_UNICODE);
                    $oJSON_Result = self::fKKM_Connector($jKKMData);

		    if (((int)$oJSON_Result->Status===0) && empty($oJSON_Result->Error) && !empty($oJSON_Result)) {
			// Запрос успешно исполнен
			// Помечаем задание как выполненное
			cLogger::fWriteLog("Задание исполнено.", $this->vTaskCurrentName, PHPKKM_LOG_SUCC);
			// Переносим в архив
			$this->oTaskModel->fSetTaskComplete($this->vTaskCurrentName, $this->vTaskCurrentBody, $oJSON_Result);
		    } else {
			// Задание не исполнено
			$this->vTaskError=true;
			cLogger::fWriteLog("Задание не исполнено: ".$oJSON_Result->Error, $this->vTaskCurrentName ,PHPKKM_LOG_WARN);
			// Переносим в папку ошибок
			$this->oTaskModel->fSetTaskIncomplete($this->vTaskCurrentName, $this->vTaskCurrentBody, $jResult);
		    }
	    }

	return $oJSON_Result;
    }
}

?>
