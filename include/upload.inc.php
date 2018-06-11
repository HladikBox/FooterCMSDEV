<?php

 if(MODULE=="fileupload"){

 $field=$_REQUEST["field"];
 $module=$_REQUEST["module"];
 if($module==""){
 $module="default";
 $field="upload";
 }
 $file=$_FILES[$field];
 if($file==""){
	//echo "Fils is empty";
	//exit;
 }
 $filename=md5($file["name"])."_".date('ymdHIs').".".substr($file["name"], strrpos($file["name"], '.')+1); //$file["name"];
 if($CONFIG['fileupload']['oss']==true){
	$type=$CONFIG['fileupload']['type'];
	if($type==""){
		$type="aliyun";
	}
	
	 require ROOT.'/classes/obj/ossupload.'.$type.'.php';
	 $file=new OssUpload($file,$filename,USER_PATH2."$module/",false);
	 
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

 echo $file->safetyUpload();
 echo "|~~|".$filename;
 exit;
}

if(MODULE=="upload"){
	$filename=ROOT."/Users".$_SERVER["REQUEST_URI"];
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

?>