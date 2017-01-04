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



 $smarty->assign('rootpath',"/");
 $smarty->assign('userpath',USER_PATH);
 $smarty->assign('smarty_root',ROOT."/templates");
 $smarty->assign('file_url',$_SERVER["PHP_SELF"]);
 $rep=array(USER_PATH);
 $smarty->assign('file_url_parameter',strtr($_SERVER["QUERY_STRING"],$rep));
 $smarty->assign('script_path',strtr($_SERVER["PHP_SELF"],$rep));
 $smarty->assign('charset',$CONFIG['charset']);
 $smarty->assign('Title',$CONFIG['Title']);
 $smarty->assign('Url',$CONFIG['URL']);
 $smarty->assign('uploadpath',USER_PATH.$CONFIG['fileupload']['upload_path']);
 $smarty->assign('request_url_encode',base64_encode($_SERVER["REQUEST_URI"]));
 $smarty->assign('parenturl',base64_decode($_REQUEST["parenturl"]));
 $smarty->assign('frontendurl',$CONFIG['frontendurl']);
 $smarty->assign('support_multilang',$CONFIG["SupportMultiLanguage"]?"1":"0");

?>