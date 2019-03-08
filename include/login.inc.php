<?php

    if(MODULE=="changepassword"){
		
	    require ROOT.'/classes/datamgr/user.cls.php';
		$oldpassword=$_REQUEST["oldpassword"];
		$newpassword=$_REQUEST["newpassword"];
		$user=$_SESSION[SESSIONNAME]["SysUser"];
		echo $userMgr->changsePassword($user["login_id"],$oldpassword,$newpassword);
		exit;
	}


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


      }elseif(MODULE=="downloadlogo"){
			$filename=$_REQUEST["filename"];
			if($filename==""){
				exit;
			}
			
			$logofile=$CONFIG['fileupload']['upload_path']."/".USER_PATH2."logo/".$filename;
			$filepath=downloadFile($logofile);
			$size=filesize($filepath);
			if($size>0){
				 $dd=USER_ROOT."js/CMSGlobalLogo.png";
				unlink($dd);
				copy($filepath,$dd);
				outputJSON(outResult(0,"SUCCESS"));
			}
			
			exit;
      }
?>