<?php

 if(MODULE=="api"){
    
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
            
            if($api["type"]=="model"){

            }else{
                include USER_ROOT."api/".$api["model"]."/".$api["func"].".php";
            }
            break;
        }
    }
    outputJSON(null);

}

?>