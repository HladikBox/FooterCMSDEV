<?php

    


  if(MODULE=="login"){
      if($_REQUEST["action"]=="login"){
	    if(trim($_REQUEST["login_id"])==""){
		    $smarty->assign("ErrorMsg",$SysLang["index"]["logincannotbenull"]);
	    }else{
		    $smarty->assign("login_id",$_REQUEST["login_id"]);
	    }
	    if(trim($_REQUEST["password"])==""){
		    $smarty->assign("ErrorMsg",$SysLang["index"]["pswcannotbenull"]);
	    }else{
		    $smarty->assign("password",$_REQUEST["password"]);
	    }
	    require ROOT.'/classes/datamgr/user.cls.php';
	    $login_id=$_REQUEST["login_id"];
	    $password=$_REQUEST["password"];
	    $userRows=$userMgr->getUserByName($login_id);
	
	    if(count($userRows)==0||$userRows[0]["status"]!="A"){
		    $smarty->assign("ErrorMsg",$SysLang["index"]["invaliduser"]);
	    }else{
		    $user=$userRows[0];
		    if(md5($password)!=$user["password"]){
			    $smarty->assign("ErrorMsg",$SysLang["index"]["incorrectpsw"]);
		    }else{
			    $_SESSION[SESSIONNAME]["SysUser"]=$user;
			    if($_REQUEST["language"]!=""){
				    $_SESSION[SESSIONNAME]["LangCode"]=$_REQUEST["language"];
			    }

                $folder=USER_ROOT."\\model\\";
                $filesnames = scandir($folder);
                $ret=array();
                for($i=2;$i<count($filesnames);$i++){

                    $filenamearr=explode(".", $filesnames[$i]);
                    $model["modelname"]=$filenamearr[0];
                    $path=$folder.$filenamearr[0].".xml";
                    $fp = fopen($path,"r");
                    $str = fread($fp,filesize($path));
                    $model=xmlToArray($str);
                    $model["modelname"]=$filenamearr[0];
                    $ret[$filenamearr[0]]=$model;
                }
                $_SESSION[SESSIONNAME]["modellist"]=$ret;
                //print_r($_SESSION[SESSIONNAME]["modellist"]);
                //exit;
			    WindowRedirect(USER_PATH."Admin/dashboard");
			    exit();
		    }
	    }
      }
  
      $smarty->display(ROOT.'/templates/index.html');
      exit();
  }elseif(MODULE=="logout"){

      $_SESSION[SESSIONNAME]=null;
        empty($_SESSION[SESSIONNAME]);
        unset($_SESSION[SESSIONNAME]);
        WindowRedirect(USER_PATH);


      }
?>