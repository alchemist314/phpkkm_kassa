<?php

namespace phpKKM\app\Classes;

/** Example: 
  * fSanitize($vString, 10) - cut string length 10, disallow all
  * fSanitize($vString, 20, "email")  - cut 20, allow  email only
  * fSanitize($vString, 10, array("mysql", "email")) - cut 10, allow email only. add slashes \' (for mysql query)
  * fSanitize($vString, 10, ".")  - cut 10, allow dot only
  * fSanitize($vString, 10, array(".", ","))  - cut 10, allow ".", ","
  * fSanitize($vString, 10, "all") - cut 10, allow all
  * fSanitize($vString, 10, array("all", "mysql")) - cut 10, allow all. add slashes \' (for password fieled)
  * fSanitize($vString, 10, "mysql") - cut 10, add slashes \' (for mysql query)
**/

class cSanitizer {

  /**
   * Очищает и обрезает строку
   *
   * @param string $vString- строка, integer $vLength - допустимая длина строки
   *
   * @return string обработанная строка
   */
    public static function fSanitize ($vString, $vLength="", $aParam="") {

	$vSQLFlag = false;
	$aExc = array();
        $aHTML = array("&", ";");
	$aEmail = array("@", ".", "-", "_");
        $aDisallow = array("<", ">", "?", "%", ";", "+", "-", "=", "(",
            	    ")", "*", "&", "#", "@", "`", "\"", "'", "|",
            	    ",", ".", "{", "}", "/", "^", "\\", "_", ":",
            	    "[", "]", "!", "$", "~");

	$vString = trim($vString);
	$vString = strip_tags($vString);
	(int)$vLength > 0 ? $vString = substr($vString, 0, $vLength) : "";
	if (is_array($aParam))  {
	    if (in_array("email", $aParam)) {
    		$aExc = $aEmail;
		$aParam = array_diff($aParam, array("email"));
	    }
	    if (in_array("all", $aParam)) {
		$aDisallow = array();
		$aParam = array_diff($aParam, array("all"));
	    }
	    if (in_array("html", $aParam)) {
    		$aExc = array_merge($aExc, $aHTML);
	        $vString = htmlentities($vString);
		$aParam = array_diff($aParam, array("html"));
	    }
	    if (in_array("mysql", $aParam)) {
		$vSQLFlag = true;
		$aParam = array_diff($aParam, array("mysql"));
	    }
	} else {
	    if (strlen($aParam)>0) {
		$aParam=="all" ? $aDisallow = array() : "";
		$aParam=="email" ? $aExc = $aEmail : "";
		$aParam=="html" ? $aExc = $aHTML : "";
		$aParam=="mysql" ? $vSQLFlag = true : "";
	    }
	    $aParam = array($aParam);
	}
	$aExc = array_merge($aExc, $aParam);
	$aDisallow = array_diff($aDisallow, $aExc);
	foreach($aDisallow as $vVal) {
    	    $vString = str_replace($vVal, "", $vString);
	}
	if ($vSQLFlag) {
    	    $vString = mysql_real_escape_string($vString);
	}
	return $vString;
    }
}

?>