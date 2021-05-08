<?php

namespace phpKKM\app\Common;

/**
 * Абстрактный класс cModel содержит общую логику для всех моделей
 */

abstract class cModel {
    /**
    * Содержит массив выполненных заданий
    *
    * @string array
    */
    public static $aCompleteTasks;

    /**
    * Содержит массив не выполненных заданий
    *
    * @string array
    */
    public static $aIncompleteTasks;

    /**
    * Содержит массив ожидающих исполнения заданий
    *
    * @string array
    */
    public static $aWaitingTasks;

    /**
    * Содержит обьект базы данных PDO
    *
    * @object
    */
    public static $oPDO;
}

?>