<?php

switch($_REQUEST['exec']) {
    case 'cron':
	$sVal='cron';
	break;
    case 'direct':
	$sVal='direct';
	break;
}
exec("/usr/bin/sudo /var/www/html/kassa/public/exec.sh ".$sVal, $aOut);

?>