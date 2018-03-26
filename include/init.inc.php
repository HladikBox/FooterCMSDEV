<?php
if(MODULE=="myjs"){
	if(file_exists(USER_ROOT."js/".MODEL)){
		$content = @file_get_contents(USER_ROOT."js/".MODEL);
		echo $content;
		exit;
	}
}



//login redirect
//print_r($_SESSION);
if(!isset($_SESSION[SESSIONNAME]["SysUser"])&&MODULE!="login")
{
	if($CONFIG["nologincheck"]!=true){
		
		$_SESSION[SESSIONNAME]["url_request"]="http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		WindowRedirect(USER_PATH."login");
		exit();
	}
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
    if($MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][0]==""){
        $temp=$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"];
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]=array();
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][]=$temp;
    }
}




if(1==1||$_SESSION[SESSIONNAME]["modellist"]==null){
	
	for($i=0;$i<count($MenuArray["mainmenus"]["mainmenu"]);$i++){
	    for($j=0;$j<count($MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]);$j++){
	    	
		 	$filename=$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["model"];
		    $model["modelname"]=$filename;
		    $path=USER_ROOT."model/".$filename.".xml";
		    $fp = fopen($path,"r");
		    $str = fread($fp,filesize($path));
		    $model=xmlToArray($str);
		    $model["modelname"]=$filename;
		    $ret[$filename]=$model;
		}
	}
	$_SESSION[SESSIONNAME]["modellist"]=$ret;
}








$modellist=$_SESSION[SESSIONNAME]["modellist"];

for($i=0;$i<count($MenuArray["mainmenus"]["mainmenu"]);$i++){
	
    for($j=0;$j<count($MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]);$j++){
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["name"]=$modellist[$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["model"]]["name"];
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["nolist"]=$modellist[$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["model"]]["nolist"];
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["nosave"]=$modellist[$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["model"]]["nosave"];
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["ndh"]=$modellist[$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["model"]];
        $MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["right"]=1;
    }
	$MenuArray["mainmenus"]["mainmenu"][$i]["submenucount"]=count($MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]);
}
if($SysUser["is_admin"]!="Y"){
	include_once ROOT."/classes/datamgr/user.cls.php";
	$right=$userMgr->getAccessRight($SysUser["role_id"]);
	$inaccessright=false;
	for($i=0;$i<count($MenuArray["mainmenus"]["mainmenu"]);$i++){
		$subcount=0;	
		for($j=0;$j<count($MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"]);$j++){
			$val=$MenuArray["mainmenus"]["mainmenu"][$i]["module"]."_".$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["model"];
			if(in_array($val,$right)){
				$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["right"]=1;
				$subcount++;
				if(MODULE."_".MODEL==$val){
					$inaccessright=true;
				}
			}else{
				$MenuArray["mainmenus"]["mainmenu"][$i]["submenus"]["submenu"][$j]["right"]=0;
			}
		}
		$MenuArray["mainmenus"]["mainmenu"][$i]["submenucount"]=$subcount;
	}
	if($inaccessright==false){
		if((MODULE!="admin"&&MODEL!="dashboard")||(MODULE!="admin"&&MODEL!="about")){
			WindowRedirect(USER_PATH."Admin/About");
			die("hack");
			exit;
		}
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