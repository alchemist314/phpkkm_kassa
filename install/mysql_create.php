<?php

include "../app/Config/Config.php";

try {
    $oPDO = new PDO("mysql:host=".PHPKKM_PDO_HOSTNAME, PHPKKM_PDO_LOGIN, PHPKKM_PDO_PASSWD);
    $oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "\nНевозможно установить соединение: " . $e->getMessage()."\n";
}

$vQuery = "CREATE DATABASE IF NOT EXISTS ".PHPKKM_PDO_DBNAME;
try {
    $oResult = $oPDO->query($vQuery);
    echo "\nЗапрос выполнен успешно!\n";
} catch(PDOException $e) {
    echo "\nОшибка выполнения запроса: " . $e->getMessage()."\n";
}

$vQuery = "CREATE TABLE IF NOT EXISTS `".PHPKKM_PDO_DBNAME."`.`".PHPKKM_PDO_TABLENAME."` (
`id` INT(11) NOT NULL AUTO_INCREMENT,
`uuid` VARCHAR(36) NOT NULL, 
`tname` VARCHAR(36) NOT NULL, 
`tdate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, 
`tbody` TEXT NOT NULL, 
`tresult` TEXT NOT NULL, 
`tflag` tinyint(4) NOT NULL,
`orderid` INT(11) NOT NULL,
PRIMARY KEY (`id`),
UNIQUE KEY `uuid` (`uuid`, `tname`)) ENGINE = MYISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";

try {
    $oResult = $oPDO->query($vQuery);
    echo "\nЗапрос выполнен успешно!\n";
} catch(PDOException $e) {
    echo "\nОшибка выполнения запроса: " . $e->getMessage()."\n";
}

?>