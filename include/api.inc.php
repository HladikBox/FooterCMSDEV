<?php

 if(MODULE=="api"){
    header('Access-Control-Allow-Origin:*');  
    header('Access-Control-Allow-Methods:POST');  
    header('Access-Control-Allow-Headers:x-requested-with,content-type,TokenKey,Sign,Fmd5str,lang');  

    if(strtolower($_SERVER["REQUEST_METHOD"])=="options"){
		exit();
	}
    $path=USER_ROOT."api.xml";
    $fp = fopen($path,"r");
    $str = fread($fp,filesize($path));
    $apilist=xmlToArray($str);
    if($apilist["apis"]["api"][0]==""){
        $temp=$apilist["apis"]["api"];
        $apilist["apis"]["api"]=array();
        $apilist["apis"]["api"][]=$temp;
    }
					
    $apilist=$apilist["apis"]["api"];

    foreach($apilist as $api){

        if($api["model"]==MODEL&&$api["func"]==FUNC){

            if($api["type"]!=""&&$api["active"]!="1"){

                outputJSON(outResult("401","Api not active"));    

            }
            try
            {
				$initphp=USER_ROOT."common/init.inc.php";
				 if(file_exists($initphp)){
					include $initphp;
				 }


                define(LANG, $_SERVER["HTTP_LANG"]);

                $fmd5str=$_SERVER["HTTP_FMD5STR"];
                logger_mgr::logInfo($_SERVER["REQUEST_URI"]."Lang: ".LANG." sign: ".$_SERVER["HTTP_SIGN"]."  $fmd5str tokenkey: ".$_SERVER["HTTP_TOKENKEY"]." input:".json_encode($_REQUEST));
                if($api["type"]=="model"){
                    $modelmgrpath=USER_ROOT."modelmgr/".MODEL.".model.php";
     
                    if(file_exists($modelmgrpath)){
                      $class=MODEL."XmlModel";
                      include_once $modelmgrpath;
                      $model=new $class(MODEL,CURRENT_PATH);
                    }else{
                      $model=new XmlModel(MODEL,CURRENT_PATH);
                    }
                    
                    if(FUNC=="list"){
                        $_REQUEST["action"]="";
                    }elseif(FUNC=="get"){
                        $_REQUEST["action"]="detail";
                    }elseif(FUNC=="update"){
                        $_REQUEST["action"]="save";
                    }elseif(FUNC=="delete"){
                        $_REQUEST["action"]="delete";
                    }
                    $action=$_REQUEST["action"];
                    $model->DefaultShowAPI($dbmgr,$action,$_REQUEST);
                }else{
                    include USER_ROOT."api/".$api["model"]."/".$api["func"].".php";
                }
            }catch(Exception $ex){
                outputJSON(outResult("500",$ex->getMessage()));
            }
            break;
        }
    }
    outputJSON(outResult("404","Api not found"));

}

?>