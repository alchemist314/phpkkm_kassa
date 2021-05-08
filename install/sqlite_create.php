<?php

include "../app/Config/Config.php";

try {
    $oPDO = new PDO("sqlite:".PHPKKM_SQLITE_PATH);
    $oPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "\nНевозможно установить соединение: " . $e->getMessage()."\n";
}

$vQuery = "CREATE TABLE IF NOT EXISTS `".PHPKKM_PDO_TABLENAME."` (
`id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
`uuid` VARCHAR (36) UNIQUE NOT NULL,
`tname` VARCHAR (36) UNIQUE NOT NULL,
`tdate`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
`tbody` TEXT DEFAULT '',
`tresult` TEXT DEFAULT '',
`tflag` INTEGER NOT NULL);";

try {
    $oResult = $oPDO->query($vQuery);
    echo "\nЗапрос выполнен успешно!\n";
} catch(PDOException $e) {
    echo "\nОшибка выполнения запроса: " . $e->getMessage()."\n";
}

?>