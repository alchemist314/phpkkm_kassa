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

function fGuid() {
    return \Lootils\Uuid\Uuid::createV4();
}

spl_autoload_register("fAutoload");
