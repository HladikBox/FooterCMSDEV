<?php
/*
 * Created on 2010-5-11
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function encode($str)
{
	return mb_convert_encoding($str,'UTF-8');
}
function parameter_filter($param,$htmlchange=true)
{
	$arr=array("'"=>"''");
      $param = trim($param);
	$param = strtr($param,$arr);
	$param = mysql_escape_string($param);
      if($htmlchange){
         $param = htmlspecialchars($param);
      }
	return $param;
}
function ParentRedirect($url)
{
	//Header("Location: $url");
    echo "<a href='$url'>$url</a>";
	echo "<script languate=\"javascript\">";
	echo "parent.location.href='".$url."'";
	echo "</script>";
	exit();
}
function WindowRedirect($url)
{
	//Header("Location: $url");
    echo "<a href='$url'>$url</a>";
	echo "<script languate=\"javascript\">";
	echo "window.location.href='".$url."'";
	echo "</script>";
	exit();
}

/*
 function name：remote_file_exists
 function：valid remote file is exists
 params： $url_file - remote file URL
 return：exists return true，else return false
 */
function remote_file_exists($url_file){
	if(@fclose(@fopen($url_file,"r")))
	{
		return true;
	}
	else
	{
		return false;
	}
}
function xmlToArray($xml){ 
 
 //禁止引用外部xml实体 
 
libxml_disable_entity_loader(true); 
 
$xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA); 
 
$val = json_decode(json_encode($xmlstring),true); 
 
return $val; 
 
} 

function getMenuJson($menu){
	
	
$item["current"]=true;
$item["title"]="管理工具";
$item["link"]="#";
foreach ($menu as $val){
	
	$sm=$val["sub_function"];
	$subitemcontent=null;
	foreach ($sm as $vc){
		$url=null;
		$url["name"]=$vc["function_name"];
		$url["urlPathinfo"]=$vc["function_link"];
		$subitemcontent[$vc["function_link"]]=$url;
	}
	$list[$val["function_name"]]=$subitemcontent;
	
	
}
$item["list"]=$list;

return json_encode($item);
}

function ResetNameWithLang($arr,$lang){
	
	if(isset($arr["name"])&&isset($arr["name_".$lang])){
		$arr["name"]=$arr["name_".$lang];
	}

	foreach ($arr as $key => $value){
		if(is_array($arr[$key])){
			$arr[$key]=ResetNameWithLang($arr[$key],$lang);
		}
	}
	return $arr;

}

function outputJson($result){
	$str=json_encode($result);
	if(MODULE=="api"){
		$length=strlen($str);
		request_get("http://console.app-link.org/api/cms?action=apicalllog&login=".LOGIN."&alias=".ALIAS."&model=".MODEL."&func=".FUNC."&output_data_length=".$length);
	}
    die( $str);
}



function outResult($code,$message,$return=""){
  $arr=Array();
  $arr["code"]=$code;
  $arr["result"]=$message;
  $arr["return"]=$return;
  return $arr;
}

function request_get($url) {

      $ch = curl_init();

      $headers = array();
      $headers[] = 'Cache-Control: no-cache';
      $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
      $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';

      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
      $res= curl_exec($ch);
      curl_close($ch);
      //echo $res;
      return $res;
}

   function setArrayNoNull($arr){
    foreach($arr as $key=>$value){
        if(is_array($value)){
            if(count($value)==0){
                $arr[$key]="";
            }else{
                $arr[$key]=setArrayNoNull($value);
            }
        }
    }
    return $arr;
  }
?>
