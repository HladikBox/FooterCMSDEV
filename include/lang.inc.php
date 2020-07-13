<?php


//lang config init




if($smarty!=null){

	$SysLangCode=$CONFIG['lang'];
	if(isset($_REQUEST["lang"])){
		$SysLangCode=$_REQUEST["lang"];
		$_SESSION[SESSIONNAME]["LangCode"]=$SysLangCode;
	}
	if(isset($_SESSION[SESSIONNAME]["LangCode"])){
		$SysLangCode=$_SESSION[SESSIONNAME]["LangCode"];
	}
	//$SysLangCode;

	//lang init
	$path=ROOT."/lang/$SysLangCode.xml";
	$fp = fopen($path,"r");
	$str = fread($fp,filesize($path));
	$SysLang=json_decode(json_encode((array) simplexml_load_string($str)), true);
	//$_SESSION[SESSIONNAME]["Lang"]=$SysLang;

	$smarty->assign("SysLang",$SysLang);
	$smarty->assign("SysLangConfig",$SysLangConfig);
	$smarty->assign('SysLangCode',$SysLangCode);

}

?>