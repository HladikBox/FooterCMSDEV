<?php
/*
 * Created on 2012-6-30
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
  require 'include/common.inc.php';

  
  $smarty->assign("MyModule",MODULE);
  $smarty->assign("MyMenuId",MODEL);
  
  $smarty->assign("LOGIN",LOGIN);
  $smarty->assign("ALIAS",ALIAS);

  if(MODULE==""||MODEL==""||(MODULE=="admin"&&MODEL=="dashboard")){
     $smarty->assign("MyModule","admin");
     $smarty->assign("MyMenuId","dashboard");
     $reminderList=$businessMgr->getReminderAll($SysUser["id"]);
     $smarty->assign("ReminderList",$reminderList);
	 
	 $model=null;
if(file_exists(USER_ROOT."common/cms.inc.php")){
	include USER_ROOT."common/cms.inc.php";
}
	 
     $smarty->display(ROOT.'/templates/dashboard.html');
     

  }elseif(strtolower(MODULE)=="admin"&&strtolower(MODEL)=="about"){
    $smarty->assign("MyModule","admin");
    $smarty->assign("MyMenuId","about");
    $smarty->display(ROOT.'/templates/about.html');
  }else{
    $modelmgrpath=USER_ROOT."modelmgr/".MODEL.".model.php";
     
    if(file_exists($modelmgrpath)){
      $class=MODEL."XmlModel";
      include_once $modelmgrpath;
      $model=new $class(MODEL,CURRENT_PATH);
    }else{
      $model=new XmlModel(MODEL,CURRENT_PATH);
    }
	
	
if($_REQUEST["action"]=="search"){
	//$insts=$model->getModelField("inst_id");
	$seq=$model->getModelField("seq");
	if($seq!=null){
		$_REQUEST["orderby"]=" r_main.seq,r_main.updated_date desc ";
	}
}
    
if(file_exists(USER_ROOT."common/cms.inc.php")){
	include USER_ROOT."common/cms.inc.php";
}
    $action=$_REQUEST["action"];
    $model->DefaultShow($smarty,$dbmgr,$action,MODEL,$_REQUEST);
  }
   





  
?>