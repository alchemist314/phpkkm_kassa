<?php

/**
 * Скрипт очищает базу данных от сервисных заданий
 */

require_once "../../app/Config/Config.php";
require_once "../../app/Common/cLogger.php";

// Если указан флаг SQLITE или MYSQL, определяем вариант хранения заданий
if ((PHPKKM_STORAGE=='SQLITE') || (PHPKKM_STORAGE=='MYSQL')) {
    switch(PHPKKM_STORAGE) {
	// Инициализируем БД SQLITE
	case 'SQLITE':
    	    try {
    		$oPDO = new PDO(strtolower(PHPKKM_STORAGE).":".PHPKKM_SQLITE_PATH);
        	$oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		define('PHPKKM_SQL_TABLE',  '`'.PHPKKM_PDO_TABLENAME.'`');
    	    } catch(PDOException $e) {
    		cLogger::fWriteLog("Невозможно cоединиться с БД: ".$e->getMessage(), '', PHPKKM_LOG_CRIT);
    	    }
    	    break;
	// Инициализируем БД MYSQL
	case 'MYSQL':
    	    try {
        	$oPDO = new PDO(strtolower(PHPKKM_STORAGE).":host=".PHPKKM_PDO_HOSTNAME, PHPKKM_PDO_LOGIN, PHPKKM_PDO_PASSWD);
        	$oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		define('PHPKKM_SQL_TABLE',  '`'.PHPKKM_PDO_DBNAME.'`.`'.PHPKKM_PDO_TABLENAME.'`');
    	    } catch(PDOException $e) {
        	cLogger::fWriteLog("Невозможно cоединиться с БД: ".$e->getMessage(), '', PHPKKM_LOG_CRIT);
    	    }
    	    break;
    }

    $vQuery="DELETE FROM ".PHPKKM_SQL_TABLE." WHERE tname like '%service%' OR tflag=3";
    try {
	$oPDO->query($vQuery);
    	cLogger::fWriteLog(PHPKKM_STORAGE." Очистка базы ", "cron", PHPKKM_LOG_SUCC);
    } catch(PDOException $e) {
    	cLogger::fWriteLog(PHPKKM_STORAGE." Невозможно очистить базу: ".$e->getMessage(), __FILE__, PHPKKM_LOG_CRIT);
    }
}

?>