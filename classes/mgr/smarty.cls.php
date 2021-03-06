<?php

	require ROOT.'/libs/smarty/Smarty.class.php';

$smarty = new Smarty;

$smarty->compile_check = true;
$smarty->debugging = false;
$smarty->caching=false;
$smarty->cache_lifetime=3600;
$smarty->compile_dir=ROOT."/templates_c";
$smarty->cache_dir=ROOT."/cache";
$smarty->left_delimiter="{{";
$smarty->right_delimiter="}}";



 $smarty->assign('rootpath',"https://cmsdev.oss-cn-hangzhou.aliyuncs.com/");
 $smarty->assign('userpath',USER_PATH);
 $smarty->assign('smarty_root',ROOT."/templates");
 $smarty->assign('file_url',$_SERVER["PHP_SELF"]);
 $rep=array(USER_PATH);
 $smarty->assign('file_url_parameter',strtr($_SERVER["QUERY_STRING"],$rep));
 $smarty->assign('script_path',strtr($_SERVER["PHP_SELF"],$rep));
 $smarty->assign('charset',$CONFIG['charset']);
 $smarty->assign('Title',$CONFIG['Title']);
 $smarty->assign('Url',$CONFIG['URL']);
 $smarty->assign('SupportEnglish',$CONFIG['SupportEnglish']);
 if($CONFIG['fileupload']['oss']==true){
	 $smarty->assign('uploadpath',$CONFIG['fileupload']['upload_path'].USER_PATH3);
 }else{
	 
	$smarty->assign('uploadpath',USER_PATH.$CONFIG['fileupload']['upload_path']);
 }
 $curl=$_SERVER["REQUEST_URI"];
 if($_REQUEST["tempid"]==''){
	 $curl.="&tempid=-".time();
 }
 $smarty->assign('request_url_encode',base64_encode($curl));
 $smarty->assign('parenturl',base64_decode($_REQUEST["parenturl"]));
 $smarty->assign('support_multilang',$CONFIG["SupportMultiLanguage"]?"1":"0");
 if($CONFIG["CmsStyle"]==""){
 	$CONFIG["CmsStyle"]="AdminLTE";
 }
 $CmsStyle=$CONFIG["CmsStyle"];
 $smarty->assign('CmsStyle',$CONFIG["CmsStyle"]);
 $smarty->assign('now',date("Y-m-d H:i:s"));

?>