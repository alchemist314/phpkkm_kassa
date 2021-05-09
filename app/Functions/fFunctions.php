<?php

/**
 * Функция fAutoload для автоматического подключения классов
 */

function fAutoload($vClassName) {

    $vPath = PHPKKM_ROOT."/";
    $vPath .= str_replace("\\", "/", substr($vClassName, 6)) . ".php";
    // Если такой файл существует, подключаем его
    if (file_exists($vPath)) {
        require $vPath;
    }

}

/**
 * Функция fGuid генерирует UUID
 */
/*
function fGuid() {
	return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
}
*/
spl_autoload_register("fAutoload");
