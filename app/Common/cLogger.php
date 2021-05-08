<?php

namespace phpKKM\app\Common;

/**
 * Класс логгирования cLogger
 */

class cLogger {
    /**
     * Выводим сообщение об ошибке
     */
    public static function fWriteLog($vMessage, $vParam="", $vLogLevel="") {

	if (PHPKKM_LOGGER) {
	    switch($vLogLevel) {
		case 'CRIT':
		    $vMessageLevel = "Ошибка!";
		    break;
		case 'WARN':
		    $vMessageLevel = "Внимание!";
		    break;
		case 'SUCC':
		    $vMessageLevel = "Успех!";
		    break;
	    }
	    $vLogMessage=date("d.m.Y H:i:s")." ".$vParam." ".$vMessageLevel." ".$vMessage."\n";
	    if (PHPKKM_SHOWLOG==true) {
		//print "<br>".$vLogMessage;
	    }
    	    file_put_contents(PHPKKM_LOG_PATH, $vLogMessage, FILE_APPEND);
	    chmod(PHPKKM_LOG_PATH,0666);
	}
    }
}

?>