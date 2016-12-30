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
  if(MODULE==""||MODEL==""||(MODULE=="admin"&&MODEL=="dashboard")){
    $smarty->assign("MyModule","admin");
     $smarty->assign("MyMenuId","dashboard");
     $reminderList=$businessMgr->getReminderAll($SysUser["id"]);
     $smarty->assign("ReminderList",$reminderList);
     $smarty->display(ROOT.'/templates/dashboard.html');
     

  }elseif(MODULE=="admin"&&MODEL=="about"){
    $smarty->assign("MyModule","admin");
    $smarty->assign("MyMenuId","about");
    $smarty->display(ROOT.'/templates/about.html');
  }else{
  
    $action=$_REQUEST["action"];
    $model=new XmlModel(MODEL,CURRENT_PATH);
    $model->DefaultShow($smarty,$dbmgr,$action,MODEL,$_REQUEST);
  }
   





  
?>