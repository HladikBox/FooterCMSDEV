<?php

 if(MODULE=="api"){
    
    header('Access-Control-Allow-Origin:*');  
    header('Access-Control-Allow-Methods:POST');  
    header('Access-Control-Allow-Headers:x-requested-with,content-type');  

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

            if($api["active"]!="1"){

                outputJSON(outResult("401","Api not active"));    

            }
            try
            {
                if($api["type"]=="model"){
                    $model=new XmlModel(MODEL,CURRENT_PATH);
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