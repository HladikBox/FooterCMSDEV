<?php
/*
 * Created on 2011-1-22
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
define('OSSACCESSKEYID',$CONFIG['fileupload']['accesskeyid']);
define('OSSACCESSKEYSECRET',$CONFIG['fileupload']['accesskeysecret']);
define('OSSENDPOINT',$CONFIG['fileupload']['endpoint']);
define('OSSBUCKET',$CONFIG['fileupload']['bucket']);

 require_once ROOT."/libs/ALIOSS/OssClient.php";
 require_once ROOT."/libs/ALIOSS/Core/OssUtil.php";
 require_once ROOT."/libs/ALIOSS/Core/OssException.php";
 require_once ROOT."/libs/ALIOSS/Core/MimeTypes.php";
 require_once ROOT."/libs/ALIOSS/Http/RequestCore.php";
 require_once ROOT."/libs/ALIOSS/Http/ResponseCore.php";
 require_once ROOT."/libs/ALIOSS/Result/Result.php";
 require_once ROOT."/libs/ALIOSS/Result/PutSetDeleteResult.php";
 
 use OSS\OssClient;
 use OSS\Core\OssUtil;
 use OSS\Core\OssException;
 use OSS\Core\MimeTypes;
 use OSS\Http\RequestCore;
 use OSS\Http\ResponseCore;
 use OSS\Result\PutSetDeleteResult;
 use OSS\Result\Result;
 
 class OssUpload{
 	private $file=null;
 	private $name=null;
 	private $folder=null;
 	function __construct($fi,$rename,$folder,$is_full_path)
 	{
 		$this->file=$fi;
 		if($rename=="")
 		{
 			$this->name=$fi["name"];
 		}
 		else
 		{
 			$this->name=$rename;
 		}
 		
 		if($is_full_path==true)
 		{
 			$this->folder=$folder;
 		}
 		else
 		{
 			$this->folder=$folder;
 		}
 		
 		
 	}
 	
	public function getType()
	{
		return $this->file["type"];
	}
	
	public function getSize()
	{
		return $this->file["size"];
	}
	public function getFullName()
	{
		return $this->folder.$this->name;
	}
	
	public function upload()
	{
		//echo $this->folder.$this->name;
		//echo $this->file["tmp_name"];
		$oss=new OssClient(OSSACCESSKEYID, OSSACCESSKEYSECRET, OSSENDPOINT);
		try{
			//$oss->deleteObject(OSSBUCKET, $this->folder.$this->name);
		}catch(Exception $ex){
		}
		$arr= $oss->uploadFile(OSSBUCKET, $this->folder.$this->name, $this->file["tmp_name"]);//
		//print_r($arr);
		return $arr;
	}
	public function unlink()
	{
		return unlink($this->folder.$this->name);
	}
	
	public function safetyUpload()
	{
		$ret=$this->upload();
		if(count($ret)>0){
			return "success";
		}else{
			return "fail";
		}
		
	}
	
 }
 
 
 
 
 
 
?>