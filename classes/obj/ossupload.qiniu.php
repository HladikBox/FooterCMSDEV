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

 require_once ROOT."/libs/Qiniu/Config.php";
 require_once ROOT."/libs/Qiniu/Auth.php";
 require_once ROOT."/libs/Qiniu/Storage/FormUploader.php";
 require_once ROOT."/libs/Qiniu/Storage/UploadManager.php";
 require_once ROOT."/libs/Qiniu/functions.php";
 require_once ROOT."/libs/Qiniu/Zone.php";
 require_once ROOT."/libs/Qiniu/Http/Request.php";
 require_once ROOT."/libs/Qiniu/Http/Response.php";
 require_once ROOT."/libs/Qiniu/Http/Client.php";
 
 use Qiniu\Auth;
 use Qiniu\Storage\UploadManager;
 use Qiniu\Storage\FormUploader;
 
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
		//$oss=new OssClient(OSSACCESSKEYID, OSSACCESSKEYSECRET, OSSENDPOINT);
		$auth = new Auth(OSSACCESSKEYID, OSSACCESSKEYSECRET);
		$token = $auth->uploadToken(OSSBUCKET);
		$uploadMgr = new UploadManager();
		try{
			//$oss->deleteObject(OSSBUCKET, $this->folder.$this->name);
		}catch(Exception $ex){
		}
		//$arr= $oss->uploadFile(OSSBUCKET, $this->folder.$this->name, $this->file["tmp_name"]);//
		$ret = $uploadMgr->putFile($token, $this->folder.$this->name, $this->file["tmp_name"]);
		//print_r($arr);
		return $ret;
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