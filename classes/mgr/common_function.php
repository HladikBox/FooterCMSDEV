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
	Global $dbmgr;
	
	$arr=array();
      $param = trim($param);
	$param = strtr($param,$arr);
	$param = mysqli_real_escape_string($dbmgr->conn,$param);
	$param=emoji_encode($param);
      if($htmlchange){
         $param = htmlspecialchars($param);
      }
	return $param;
}

/**对emoji表情转义
 * @param $nickname
 * @return string
 */
function emoji_encode($nickname)
{
    $strEncode = '';
    $length = mb_strlen($nickname, 'utf-8');
	if($length>1000){
		return $nickname;
	}
    for ($i = 0; $i < $length; $i++) {
        $_tmpStr = mb_substr($nickname, $i, 1, 'utf-8');
        if (strlen($_tmpStr) >= 4) {
            $strEncode .= '[[EMOJI:' . rawurlencode($_tmpStr) . ']]';
        } else {
            $strEncode .= $_tmpStr;
        }
    }
    return $strEncode;
}

//对emoji表情转反义
function emoji_decode($str)
{
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function ($matches) {
        return rawurldecode($matches[1]);
    }, $str);

    return $strDecode;
}
function emoji_empty($str)
{
    $strDecode = preg_replace_callback('|\[\[EMOJI:(.*?)\]\]|', function ($matches) {
        return "";
    }, $str);

    return $strDecode;
}

function filter_Emoji($str)
{
    $str = preg_replace_callback(    //执行一个正则表达式搜索并且使用一个回调进行替换
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $str);

     return $str;
 }
function ParentRedirect($url)
{
	//Header("Location: $url");
    //echo "<a href='$url'>$url</a>";
	echo "<script languate=\"javascript\">";
	echo "parent.location.href='".$url."'";
	echo "</script>";
	exit();
}
function WindowRedirect($url)
{
	//Header("Location: $url");
    //echo "<a href='$url'>$url</a>";
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

function doEmojiArray($result){
	if($result==null){
		return $result;
	}
	if(is_array($result[0])){
		//二维数组
		for($i=0;$i<count($result);$i++){
			foreach($result[$i] as $k=>$v){
				if(is_array($v)==false){
					$result[$i][$k]=emoji_decode($result[$i][$k]);
				}else{
					$result[$i][$k]=doEmojiArray($result[$i][$k]);
				}
			}
		}
	}else{
		foreach($result as $k=>$v){
			if(is_array($v)==false){
				$result[$k]=emoji_decode($result[$k]);
			}else{
				$result[$k]=doEmojiArray($result[$k]);
			}
		}
	}
	return $result;
}

function outputJson($result,$emoji=true){
	Global $CONFIG;

	logger_mgr::logInfo($_SERVER["REQUEST_URI"]." output:".json_encode($result));
	if($emoji==true){
		$result=doEmojiArray($result);
	}
	
	$str=json_encode($result);
	if($CONFIG['solution_configuration']!="release"&&MODULE=="api"){
		$length=strlen($str);
		$arr["action"]="apicalllog";
		$arr["login"]=LOGIN;
		$arr["alias"]=ALIAS;
		$arr["model"]=MODEL;
		$arr["func"]=FUNC;
		$arr["output_data_length"]="$length";
		$arr["input_data"]=json_encode($_REQUEST);
		$arr["output_data"]=json_encode($result);
		
		
		//print_r($arr);
		//$ret=request_post("http://console.app-link.org/api/cms?action=apicalllog",$arr);
		if($_REQUEST["sl"]=="Y"){
			//echo $ret;
		}
		//echo $ret;
	}
    die( $str);
}



function outResult($code,$message,$return=""){
  $arr=Array();
  $arr["code"]=$code+0;
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
	  curl_setopt($ch, CURLOPT_ENCODING,"gzip,deflate");
      $res= curl_exec($ch);
      curl_close($ch);
      //echo $res;
      return $res;
	  
	  
	  $ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	//curl_setopt($ch, CURLOPT_POST, TRUE);
	//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
    curl_close($ch);
	return $result;
}

function request_post($url,$postdata) {
	
	
	
      $ch = curl_init();
      $headers = array();
      $headers[] = 'Cache-Control: no-cache';
      $headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
      $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	  curl_setopt($ch, CURLOPT_POST, TRUE);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
      curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
      $res= curl_exec($ch);
      curl_close($ch);
      //echo $res;
      return $res;
	  
	  
}


function request_json($url,$postdata) {
	
	
	
      $ch = curl_init();
      $headers = array();
      $headers[] = 'Cache-Control: no-cache';
      $headers[] = 'Content-Type: application/json; charset=utf-8';
      $headers[] = 'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_URL,$url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	  curl_setopt($ch, CURLOPT_POST, TRUE);
	  curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
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

  function GenerateTokenSign($token,$tokenkey_id,$mdsalt){
	$url="http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	$arr=$_POST;
	ksort($arr);
	$arrpost=array();
	foreach ($arr as $key => $value) {
		$str=str_replace(array('-','_','.','!','~','*','\'','(',')'),"",$value);
		$str=rawurlencode($str); 
		$arrpost[]=$key."=".($str);
	}
	$poststr=join("&",$arrpost);
	$md5str=$url."~".$poststr."~".$token."~".$tokenkey_id;
	//echo "\r\n";
	$md5str=strtoupper($md5str);
	$mysign=md5($md5str.$mdsalt);
	//echo "$sign==$mysign";
	return $mysign;
  }
  function getImageOtherSave($orifilename,$width,$height){
    $info= pathinfo($orifilename);
    $dir=$info["dirname"];
    $filename=$info["filename"];
    $extension=$info["extension"];

    if($width>0){
        $width_f="_w$width";
    }
    if($height>0){
        $height_f="_h$height";
    }

    $filename=$filename.$width_f.$height_f;
    $desfilename=$dir."/".$filename.".".$extension;
    if(!file_exists($desfilename)){
        
        require ROOT."/classes/obj/imageresize.php";
        $imgresize=new ImageResize($orifilename,$width,$height);
        $tempfile=$imgresize->DoResize();
        copy($tempfile,$desfilename);
        unlink($tempfile);
    }
    return $desfilename;
    
  }
  
  function spid($ids){
	  
	  return formatIDSplit($ids);
  }
  function formatIDSplit($ids){
	  $ret=array();
	  $ret[]=0;
	  $ids=explode(",",$ids);
	  foreach($ids as $v){
		  $ret[]=($v+0);
	  }
	  return join(",",$ret);
  }
  
  function saveBase64ToImage($imageData,$module)
  {
	$dir = USER_ROOT."/upload/$module/";
	$fileName = date("YmdHis").floor(microtime() * 1000).'.jpg';
	
	$imageData = str_replace(" ","+",$imageData);
	$binImageData = base64_decode($imageData);
	$FP = fopen($dir.$fileName,"w"); 
	fwrite($FP,$binImageData);
	return $fileName;
  }
  
  function getSession($key){
	  $jsonfile=$jsonfile.$_SERVER["HTTP_ACCESSTOKEN"].".json";
	  $data = json_decode(file_get_contents($jsonfile),true);
	  if($data["expired_time"] < time()){
		return $data["data"][$key];
	  }else{
		return "";
	  }
	  
	  //$sessionname=$CONFIG["SessionName"];
	  //return $_SESSION[$sessionname][$key];
  }
  
  function setSession($key,$value){
	  
	  $jsonfile=$jsonfile.$_SERVER["HTTP_ACCESSTOKEN"].".json";
	  $data = json_decode(file_get_contents($jsonfile),true);
	  $data["data"][$key]=$value;
	  
	  $fp = fopen($jsonfile, "w");
      fwrite($fp, json_encode($data));
      fclose($fp);
  }
  function getCMSSession($key){
	  Global $CONFIG;
	  $sessionname=$CONFIG["SessionName"];
	  return $_SESSION[$sessionname][$key];
  }
  function getCMSUser(){
	  
	  return getCMSSession("SysUser");
  }
  function setCMSSession($key,$val){
	  
	  Global $CONFIG;
	  $sessionname=$CONFIG["SessionName"];
	  $_SESSION[$sessionname][$key]=$val;
  }
  
  function downloadFile($url){
    $d="ymdhis";
	$f=explode(".",$url);
	$f=$f[count($f)-1];
    $filename=USER_ROOT.'logs/'.md5($url).".".$f;
    if(!file_exists($filename)){
        $image = file_get_contents($url); 
        file_put_contents($filename, $image);
    }
    return $filename;
}

function downloadResource($module,$resourcename){
	global $CONFIG;
	
	$url=$CONFIG['fileupload']['upload_path']."/".USER_PATH2.$CONFIG['fileupload']['bucket_path'].$module."/".$resourcename;
	
    $ext=explode(".",$url);
    $ext=$ext[count($ext)-1];
    $ext=explode("?",$ext);
    $ext=$ext[0];
    $d="ymdhis";
    $filename=USER_ROOT.'/logs/'.md5($url).".".$ext;
    //检查检查文件是否存在
    if(!file_exists($filename)){
        $image = file_get_contents($url); 
        file_put_contents($filename, $image);
    }
    
    return $filename;
}
?>
