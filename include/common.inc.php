<?php
/*
 * Created on 2010-5-6
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
//set include path and config


define('ROOT', str_replace("\\", '/', substr(dirname(__FILE__), 0, -8)));	// -9 = 0-strlen('includes')-1;
require ROOT.'/classes/mgr/common_function.php';
$redurecturl=explode("?",$_SERVER["REQUEST_URI"]);
$redurecturl=$redurecturl[0];
if(!file_exists(ROOT.'/Users/config.inc.php')){
$urlparam=explode("/",$redurecturl);
//print_r($urlparam);
define("LOGIN",$urlparam[1]);
define("ALIAS",$urlparam[2]);
define("MODULE",strtolower($urlparam[3]));
define("MODEL",strtolower($urlparam[4]));
define("FUNC",strtolower($urlparam[5]));
$login=$urlparam[1];
$alias=$urlparam[2];
$module=$urlparam[3];
$model=$urlparam[4];
$func=$urlparam[5];
define("USER_ROOT", ROOT."/Users/$login/$alias/");
define("USER_PATH", "/$login/$alias/");
define("USER_PATH2", "$login/$alias/");
define("USER_PATH3", "/$login/$alias");
define("CURRENT_PATH", "/$login/$alias/$module/$model");
//echo USER_ROOT;
if(!file_exists(USER_ROOT.'config.inc.php')){die("500错误,你的应用还没有进行初始化");}


//$appinfo=json_decode(request_get("http://console.app-link.org/api/cms?action=appinfo&login=$login&alias=$alias"),true);
//if($appinfo["return"]["run_status"]!="P"){
//	die("404错误，服务已经停止，请联系管理员");
//}

}else{
	$urlparam=explode("/",$redurecturl);
	//print_r($urlparam);
	define("LOGIN","");
	define("ALIAS","");
	define("MODULE",strtolower($urlparam[1]));
	define("MODEL",strtolower($urlparam[2]));
	define("FUNC",strtolower($urlparam[3]));
	$login="";
	$alias="";
	$module=$urlparam[1];
	$model=$urlparam[2];
	$func=$urlparam[3];
	define("USER_ROOT", ROOT."/Users/");
	define("USER_PATH", "/");
	define("USER_PATH2", "");
	define("USER_PATH3", "/");
	define("CURRENT_PATH", "/$module/$model");
}

require USER_ROOT.'config.inc.php';

define('PEAR_HOME',ROOT."/libs/PEAR/");
define('SESSIONNAME',$CONFIG["SessionName"]);


//~ set php global variable to NULL, for security
unset($HTTP_ENV_VARS, $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_POST_FILES, $HTTP_COOKIE_VARS);



//~ session start
session_start();

header("Content-type:text/html;charset=utf-8");

//log start
require ROOT.'/classes/mgr/logger_mgr.cls.php';
define('LOGGER_INFO_FILE', USER_ROOT."/logs/info/log_%y%m%d.txt");
define('LOGGER_ERROR_FILE', USER_ROOT."/logs/error/log_%y%m%d.txt");
define('LOGGER_DEBUG_FILE', USER_ROOT."/logs/debug/log_%y%m%d.txt");
define('LOGGER_IS_DEBUG', $CONFIG['solution_configuration']=="debug"?true:false);
set_error_handler('error_handler');//,$CONFIG['error_handler']

//image upload cofig
//define('MUILTI_FILE_UPLOAD','10');// define max upload file
define('MAX_SIZE_FILE_UPLOAD','2000000');//define max file size
define('ACTIVITY_IMAGE_UPLOAD_DIR','/images/activity/');//define upload path
define('HOTEL_IMAGE_UPLOAD_DIR','/images/hotel/');//define upload path
define('VENUE_IMAGE_UPLOAD_DIR','/images/venue/');//define upload path
define('HOUSE_MAINTAINCE_UPLOAD_DIR','/images/house/');//define upload path
$image_extention_arr=array('jpg','png','gif');

  
function error_handler($errno,$errmsg,$file,$line,$vars)
{
$errortype=array(1=>"Error",2=>"Warning",4=>"Parsing Error",8=>"Notice",
		16=>"Core Error",32=>"Core Warning",
		64=>"Compile Error",128=>"Compile Warning",
		256=>"User Error",512=>"User Warning",
		1024=>"User Notice",2048=>"Strict Notice");
		if($errno==4||$errno==2)
		{
			//logger_mgr::logInfo("[".$errortype[$errno]."]".$errmsg ."in file ".$file ." line ".$line);
		}
}





include ROOT.'/classes/mgr/'.$CONFIG['database']['provider'].'.cls.php';

include ROOT.'/classes/mgr/smarty.cls.php';

include ROOT.'/classes/mgr/excel.cls.php';

include ROOT.'/classes/modelmgr/XmlModel.cls.php';

include ROOT.'/include/lang.inc.php';


include ROOT.'/include/template.inc.php';

include ROOT.'/include/api.inc.php';

include ROOT.'/include/upload.inc.php';


include ROOT.'/include/login.inc.php';

include ROOT.'/include/init.inc.php';

?>