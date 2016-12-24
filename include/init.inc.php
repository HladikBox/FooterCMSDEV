<?php

//login redirect
//print_r($_SESSION);
if(!isset($_SESSION[SESSIONNAME]["SysUser"])&&MODULE!="login")
{
	$_SESSION[SESSIONNAME]["url_request"]="http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	WindowRedirect(USER_PATH."login");
	exit();
}

if(isset($_SESSION[SESSIONNAME]["url_request"]))
{
	$url_request=$_SESSION[SESSIONNAME]["url_request"];
	unset($_SESSION[SESSIONNAME]["url_request"]);
	WindowRedirect($url_request);
	exit();
}

$SysUser=$_SESSION[SESSIONNAME]["SysUser"];

//Menu init
if(1==2&&isset($_SESSION[SESSIONNAME]["SystemMenu"])){
	$MenuArray=$_SESSION[SESSIONNAME]["SystemMenu"];
}else{

$path=USER_ROOT."menu.xml";
$fp = fopen($path,"r");
$str = fread($fp,filesize($path));
$MenuArray=xmlToArray($str);
if($MenuArray["mainmenus"]["mainmenu"][0]==""){
    $temp=$MenuArray["mainmenus"]["mainmenu"];
    $MenuArray["mainmenus"]["mainmenu"]=array();
    $MenuArray["mainmenus"]["mainmenu"][]=$temp;
}

for($i=0;$i<count($MenuArray["mainmenus"]["mainmenu"]);$i++){
    if($MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]==""){
        $temp=$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"];
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]=array();
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][]=$temp;
    }
}

 if($CONFIG["SupportMultiLanguage"]==true){
		$MenuArray=ResetNameWithLang($MenuArray,$SysLangCode);
	  }
$_SESSION[SESSIONNAME]["SystemMenu"]=$MenuArray;
}

if($smarty!=null){
	$smarty->assign("SystemMenu",$MenuArray);
	$smarty->assign("SysUser",$SysUser);
}



include ROOT.'/classes/datamgr/business.cls.php';
$SysReminder=$businessMgr->getReminderCount($SysUser["id"]);
$smarty->assign("SysReminder",$SysReminder);

?>