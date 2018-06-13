<?php
/*
 * Created on 2011-1-22
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 $osstype=$CONFIG['fileupload']['type'];
 if($osstype==""){
	 $osstype="aliyun";
 }
include_once "ossupload.$osstype.php";
 
 
 
 
 
 
?>