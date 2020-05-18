<?php

 if(MODULE=="fileupload"){
	 
	 
    header('Access-Control-Allow-Credentials:true');  
    header('Access-Control-Allow-Origin:'.$_SERVER['HTTP_ORIGIN']);  
    header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE');  
    header('Access-Control-Allow-Headers:x-requested-with,content-type,TokenKey,Sign,Fmd5str,lang,accesstoken,unicode,UT,TOKEN');  
    if(strtolower($_SERVER["REQUEST_METHOD"])=="options"){
		echo "OK";
		exit();
	}

 $field=$_REQUEST["field"];
 $module=$_REQUEST["module"];
 if($module==""){
 $module="default";
 $field="upload";
 }
 //print_r($_FILES);
 
 if(isset($_REQUEST["base64"])){
	$file=base64_image_content($_REQUEST["base64"],USER_ROOT."logs");
	$file=pathinfo($file);
	$file["name"]=$file["basename"];
	$file["tmp_name"]=USER_ROOT."logs/".$file["basename"];
	//print_r($file);
 }else{
	$file=$_FILES[$field];
 }
 if($file==""){
	//echo "Fils is empty";
	//exit;
 }
 //print_r($_FILES);
 if($_REQUEST["notrename"]!="Y"){
	 $filename=md5($file["name"].rand())."_".date('ymdHis')."_".rand().".".substr($file["name"], strrpos($file["name"], '.')+1); //$file["name"];
 }else{
	 $filename=$_FILES[$field]["name"];
 }
 
 $refilename=$_REQUEST["refilename"];
 if($refilename!=""){
	 $filename=$refilename;
 }
 //echo $filename;
 if($CONFIG['fileupload']['oss']==true){
	$type=$CONFIG['fileupload']['type'];
	if($type==""){
		$type="aliyun";
	}
	//echo USER_PATH2.$CONFIG['fileupload']['bucket_path']."$module/";
	 require ROOT.'/classes/obj/ossupload.'.$type.'.php';
	 $file=new OssUpload($file,$filename,USER_PATH2.$CONFIG['fileupload']['bucket_path']."$module/",false);
	 
	 
 }else{
	 
	require ROOT.'/classes/obj/upload.php';
	 $folder=USER_ROOT.$CONFIG['fileupload']['upload_path'];
	 if(!file_exists($folder)){
		mkdir($folder,0777);
	 }


	 $folder=USER_ROOT.$CONFIG['fileupload']['upload_path']."/$module/";
	 if(!file_exists($folder)){
		mkdir($folder,0777);
	 }
	 $file=new Upload($file,$filename,$folder,true);
 }
	if($_REQUEST["rettype"]=="json"){
		outputJSON(outResult($file->safetyUpload(),$filename));
	}else{
		
	 echo $file->safetyUpload();
	 echo "|~~|".$filename;
	 exit;
		
	}
}

if(MODULE=="upload"){
	$filename=ROOT."/Users".$redurecturl;
	if(!file_exists($filename)){
		die("File cannot be found");
	}
	ob_clean();

    $info = pathinfo($filename);
	if( in_array(strtolower($info['extension']),array("gif","jpeg","png","bmp","jpg"))){
        $width=$_REQUEST["width"]+0;
        $height=$_REQUEST["height"]+0;
        if($width>0||$height>0){
            $filename=getImageOtherSave($filename,$width,$height);
            //echo $filename;
        }
    }
	$folder=explode("upload/".MODEL,dirname($filename));
	$folder=$folder[1];
	header('Location:'."/Users".USER_PATH."upload/".MODEL.$folder."/".basename($filename));
	exit;
    //exit;
	$fp=fopen($filename,"r");
	$length=filesize($filename);
	$encoded_filename = rawurlencode($filename);
    


	$ua = $_SERVER["HTTP_USER_AGENT"];
	header("Cache-Control: public");
	header("Pragma: cache");
	$offset = 12*30*60*60*24; // cache 1 year
	$ExpStr = "Expires: ".gmdate("D, d M Y H:i:s", time() + $offset)." GMT";
	header($ExpStr);
	header('Accept-Ranges: bytes');
	header('Accept-Length: ' . $length);
	if( in_array(strtolower($info['extension']),array("gif","jpeg","png","bmp","jpg"))){
		header('Content-type: image/jpeg');
	}else{
		header('Content-Type: application/octet-stream');
	}
	header('Content-Encoding: none');
	header("Content-Transfer-Encoding: binary" );
	header("Content-Length: ".$length);

	if (preg_match("/MSIE/", $ua)) {
		//header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
	} else if (preg_match("/Firefox/", $ua)) {
		//header('Content-Disposition: attachment; filename*="utf8\'\'' . $encoded_filename . '"');
	} else {
		//header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
	}
	$buffer=1024;
	$buffer_count=0;

	while(!feof($fp)&&$length-$buffer_count>0){
		$data=fread($fp,$buffer);
		$buffer_count+=$buffer;
		echo $data;
	}
	
	fclose($fp);

	exit;
}

function base64_image_content($base64_image_content,$path){
    //匹配出图片的格式
    if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
		
        $type = $result[2];
        $new_file = $path."/".date('Ymd',time());
        if(!file_exists($new_file)){
            //检查是否有该文件夹，如果没有就创建，并给予最高权限
            mkdir($new_file, 0700);
        }
        $new_file = $new_file.time().".png";
        if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
            return '/'.$new_file;
        }else{
			
            return false;
        }
    }else{
		
        return false;
    }
}
?>