<?php
switch($_REQUEST['exec']) {
    case 'cron':
	$sVal='cron';
	break;
    case 'direct':
	$sVal='direct';
	break;
}
    exec("/usr/bin/sudo /var/www/html72/KASSA_RELEASE/25.05.2019/kassa/public/exec.sh ".$sVal, $aOut);
?>