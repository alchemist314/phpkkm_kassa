<?php

use phpKKM\app\Common\cLogger;
use phpKKM\app\Common\cModel;
use phpKKM\app\Model\cConnectModel;
use phpKKM\app\Model\cTaskModel;

    switch(PHPKKM_STORAGE) {
        // Инициализируем БД SQLITE
        case 'SQLITE':
            try {
                cModel::$oPDO = new PDO(strtolower(PHPKKM_STORAGE).":".PHPKKM_SQLITE_PATH);
                cModel::$oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		define('PHPKKM_SQL_TABLE',  '`'.PHPKKM_PDO_TABLENAME.'`');
            } catch(PDOException $e) {
                cLogger::fWriteLog("Невозможно cоединиться с БД: ".$e->getMessage(), '', PHPKKM_LOG_CRIT);
            }
            break;
        // Инициализируем БД MYSQL
        case 'MYSQL':
            try {
                cModel::$oPDO = new PDO(strtolower(PHPKKM_STORAGE).":host=".PHPKKM_PDO_HOSTNAME, PHPKKM_PDO_LOGIN, PHPKKM_PDO_PASSWD);
                cModel::$oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		define('PHPKKM_SQL_TABLE',  '`'.PHPKKM_PDO_DBNAME.'`.`'.PHPKKM_PDO_TABLENAME.'`');
            } catch(PDOException $e) {
                cLogger::fWriteLog("Невозможно cоединиться с БД: ".$e->getMessage(), '', PHPKKM_LOG_CRIT);
            }
            break;
    }

// Инициализируем обькт контроллера соединений
$oConnectModel = new cConnectModel();

// Инициализируем обькт контроллера заданий
$oTaskModel = new cTaskModel();

// Создаем обьект текущей даты
$oDateNow = new DateTime('now');

?>