<?php


class XmlModel
{
  public $XmlData;
  private $PageName;

  public function __construct($name,$pagename){
      GLOBAL $CONFIG,$SysLangCode;

	  $xmlstr=$this->loadXmlFile($name);
      $this->XmlData=$this->xmlToArray($xmlstr);
	  if($CONFIG["SupportMultiLanguage"]==true){
		$this->XmlData=ResetNameWithLang($this->XmlData,$SysLangCode);
	  }
	  $this->XmlData["model"]=$name;
	  $this->PageName=$pagename;

	  if($this->XmlData["nolist"]=="1"){
	  	$_REQUEST["id"]=$this->GetNoListId();
	  	if($_REQUEST["action"]!="save"){
	  		$_REQUEST["action"]="edit";
	  	}
	  }
	  $this->fixModelData($this->XmlData);
  }


  private function xmlToArray( $xml )
  {

    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement',  LIBXML_NOBLANKS); 
    $model = json_decode(json_encode($xmlstring),true); 

    if($model["options"]["option"][0]==""&&$model["options"]["option"]["name"]!=""){
      $temp=$model["options"]["option"];
      $model["options"]["option"]=array();
      $model["options"]["option"][]=$temp;
    }
    if($model["charts"]["chart"][0]==""&&$model["charts"]["chart"]["name"]!=""){
      $temp=$model["charts"]["chart"];
      $model["charts"]["chart"]=array();
      $model["charts"]["chart"][]=$temp;
    }
    if($model["fields"]["field"][0]==""&&$model["fields"]["field"]["name"]!=""){
      $temp=$model["fields"]["field"];
      $model["fields"]["field"]=array();
      $model["fields"]["field"][]=$temp;
    }
	

    for ($i=0; $i < count($model["fields"]["field"]); $i++) { 
      $model["fields"]["field"][$i]["typename"]=$this->keytypename[$model["fields"]["field"][$i]["type"]];
      $model["fields"]["field"][$i]["json"]=json_encode($model["fields"]["field"][$i]);
      if($model["fields"]["field"][$i]["type"]=="select"){
        if($model["fields"]["field"][$i]["options"]["option"][0]==""&&$model["fields"]["field"][$i]["options"]["option"]["name"]!=""){
          $temp=$model["fields"]["field"][$i]["options"]["option"];
          $model["fields"]["field"][$i]["options"]["option"]=array();
          $model["fields"]["field"][$i]["options"]["option"][]=$temp;
        }
      }
    }

    for ($i=0; $i < count($model["options"]["option"]); $i++) { 
      $model["options"]["option"][$i]["json"]=json_encode($model["options"]["option"][$i]);
    }
    for ($i=0; $i < count($model["charts"]["chart"]); $i++) { 
      $model["charts"]["chart"][$i]["json"]=json_encode($model["charts"]["chart"][$i]);
    }
	if($model["groups"]["group"]==""){
		$model["groups"]["group"]=[];
	}
	array_unshift($model["groups"]["group"],"");
	//print_r($model);

    $model=setArrayNoNull($model);
    return $model;
  }

  public function fixModelData($xmldata){
	 $this->XmlData=$xmldata;
  }
  
  public function getModelField($key){
	  for($i=0;$i<count($this->XmlData["fields"]["field"]);$i++){
		if($this->XmlData["fields"]["field"][$i]["key"]==$key){
			return $this->XmlData["fields"]["field"][$i];
		}
	  }
	  return null;
  }
  
  public function setModelField($key,$field){
	  for($i=0;$i<count($this->XmlData["fields"]["field"]);$i++){
		if($this->XmlData["fields"]["field"][$i]["key"]==$key){
			$this->XmlData["fields"]["field"][$i]=$field;
		}
	  }
  }
  
  private function loadXmlFile($name){
    
    $path=ROOT."/model/$name.xml";
    if(!file_exists($path)){
        $path=USER_ROOT."/model/$name.xml";
        if(!file_exists($path)){
            die("500,找不到模型文件");
        }
    }
    $fp = fopen($path,"r");
    $str = fread($fp,filesize($path));
    return $str;
  }

  public function fixShowList($data){
  	return $data;
  }
  
  private function ShowList($dbMgr,$smartyMgr){
	Global $CmsStyle;
    //$searchField=$this->XmlData["fields"];
	//print_r($this->XmlData);
	$dataWithFKey=$this->loadFKeyValue($dbMgr,$this->XmlData);

	$this->GetFListData($dbMgr,$smartyMgr);
	$dataWithFKey=$this->fixShowList($dataWithFKey);
    $smartyMgr->assign("ModelData",$dataWithFKey);
    $smartyMgr->assign("PageName",$this->PageName);
    $smartyMgr->display(ROOT.'/templates/'.$CmsStyle.'/list.html');
  }

  private function loadFKeyValue($dbMgr,$XmlDataEx){

	$fields=$XmlDataEx["fields"]["field"];
	$count=count($fields);
	for($i=0;$i<$count;$i++){
		if($fields[$i]["type"]=="fkey"){
            if($fields[$i]["ajax"]!='1'){
                $options=$this->GetFKeyData($dbMgr,$fields[$i]["displayfield"],$fields[$i]["tablename"],$fields[$i]["ntbname"],$fields[$i]["condition"],$fields[$i]["fmutillang"]);
                $fields[$i]["options"]=$options;
            }else{
                $id=$fields[$i]["value"];
                if($id>0){
                    $options=$this->GetFKeyData($dbMgr,$fields[$i]["displayfield"],$fields[$i]["tablename"],$fields[$i]["ntbname"]," id=$id ",$fields[$i]["fmutillang"]);
                    $fields[$i]["datavalue"]=$options[0];
                }
                
            }
		}
	}
	$XmlDataEx["fields"]["field"]=$fields;
	//print_r($XmlDataEx);
	return $XmlDataEx;
  }

  private function GetFKeyData($dbMgr,$displayfield,$tablename,$tablerename,$condition,$ismutillang,$q=""){
    Global $CONFIG;
    if($q!=""){
        $q=parameter_filter($q);
		$f=explode(",",$displayfield);
		$f=$f[0];
        $q=" and `$f` like '%$q%' ";
    }
	if($ismutillang=="1"){
		$subsql=$this->GetLangTableSql($tablename,$tablerename);
		$sql="select oid id, trim($displayfield) as name from $subsql where ".(empty($condition)?"1=1":$condition)." $q";
	}else{
		$displayfield=explode(",",$displayfield);
		$displayfield=$displayfield[0];
		$sql="select id,REPLACE(REPLACE(trim($displayfield), CHAR(10), ''), CHAR(13),'') as name from $tablename as `$tablerename`  where ".(empty($condition)?"1=1":$condition)." $q";
	}
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array_all($query); 

	return $result;
  }
  

  private function GetLangTableSql($tablename,$tablenickname){
	Global $CONFIG;
	$subsql="  (select * from $tablename ".$tablenickname."_a 
							left join ".$tablename."_lang ".$tablenickname."_b 
							on ".$tablenickname."_a.id=".$tablenickname."_b.oid and ".$tablenickname."_b.lang='".$CONFIG["lang"]."'  ) $tablenickname ";
	return $subsql;
  }

  public function GetSearchSql($request){
	$sql=$this->GetSearchSqlField($request);
	$sql.=$this->GetSearchSqlCondition($request);
	//echo $sql;
	//exit;
	return $sql;
  }
  public function GetSelectTotalCount($request){
	 
	Global $CONFIG;
	//echo "a";
	//print_r($request);
	$selectfield=parameter_filter($request["selectfield"]);
	$sql="select count(1) totalrownum ";
	
	$fields=$this->XmlData["fields"]["field"];
	
	//$sql=$sql." from ".$this->XmlData["tablename"]." as r_main ";
	if($this->XmlData["ismutillang"]=="1"){
		$subsql=$this->GetLangTableSql($this->XmlData["tablename"],"r_main");
		$sql=$sql." from $subsql ";
	}else{
		$sql=$sql." from ".$this->XmlData["tablename"]." as r_main ";
	}

	foreach ($fields as $value){
		if($value["displayinlist"]=="1"){
			if($value["type"]=="fkey"){
				if($value["fmutillang"]=="1"){
					$subsql=$this->GetLangTableSql($value["tablename"],$value["ntbname"]);
					$sql=$sql." left join $subsql on r_main.".$value["key"]."=".$value["ntbname"].".id ";
				}else{
				
					$sql=$sql." left join ".$value["tablename"]." `".$value["ntbname"]."` on r_main.".$value["key"]."=".$value["ntbname"].".id ";
				}
			}
		}
	}
	//$sql;
	//exit;
	return $sql; 
  }
  public function GetSearchSqlField($request,$allfieldshow=false){
	  
	Global $CONFIG;
	//echo "a";
	//print_r($request);
	$selectfield=parameter_filter($request["selectfield"]);
	$sql="select r_main.id ,r_main.updated_date,r_main.created_date $selectfield ";
	$fields=$this->XmlData["fields"]["field"];
	foreach ($fields as $value){
		if($value["displayinlist"]=="1"||$allfieldshow){
			if($value["type"]=="flist"&&$value["relatetable"]!=""){
				$table=$value["relatetable"];
				
				$sql=$sql." ,'' ".$value["key"];
			}else if($value["type"]=="select"){

				$sql=$sql." ,case   r_main.".$value["key"]." ";
				foreach ($value["options"]["option"] as $option){
					$sql=$sql." when '".$option["value"]."' then '".$option["name"]."'";
				}
				$sql=$sql." else 'unknow' ";
				$sql=$sql." end as ".$value["key"]."_name";
				$sql=$sql." ,r_main.".$value["key"];

			}else if($value["type"]=="check"){
			
				$sql=$sql." ,case   r_main.".$value["key"]." when 'Y' then '".$value["yvalue"]."' else '".$value["nvalue"]."' ";
				$sql=$sql." end as ".$value["key"];
				$sql=$sql." , r_main.".$value["key"]." as ".$value["key"]."_value";

			}else if($value["type"]=="fkey"){
			
				$sql=$sql." ,r_main.".$value["key"];
				$fkeys=explode(",",$value["displayfield"]);
				$sql=$sql." ,".$value["ntbname"].".".$fkeys[0]." ".$value["key"]."_name";
				foreach($fkeys as $key){
					$sql=$sql." ,".$value["ntbname"].".".$key." ".$value["ntbname"]."_".$key;
				}

			}else if($value["type"]=="datetime"){
			
				if($value["onlydate"]=="1"){
					$sql=$sql." ,DATE_FORMAT(r_main.".$value["key"] .",'%Y-%m-%d') as ".$value["key"];
				}else{
				
					$sql=$sql." ,r_main.".$value["key"];
				}
				if($value["onlydate"]=="1"){
					$sql=$sql." ,DATE_FORMAT(r_main.".$value["key"] .",'%Y-%m-%d') as ".$value["key"]."_formatting";
				}else{
				
					$sql=$sql." ,DATE_FORMAT(r_main.".$value["key"] .",'%Y-%m-%d %H:%i') as ".$value["key"]."_formatting";
				}

				$sql=$sql." ,UNIX_TIMESTAMP(r_main.".$value["key"].") as ".$value["key"]."_timespan";
					$sql=$sql." ,DATE_FORMAT(r_main.".$value["key"] .",'%Y-%m-%d') as ".$value["key"]."_dateformat";
			}else if($value["type"]=="grid"){
			}else{

				$sql=$sql." ,r_main.".$value["key"];

			}
		}
	}
	
	//$sql=$sql." from ".$this->XmlData["tablename"]." as r_main ";
	if($this->XmlData["ismutillang"]=="1"){
		$subsql=$this->GetLangTableSql($this->XmlData["tablename"],"r_main");
		$sql=$sql." from $subsql ";
	}else{
		$sql=$sql." from ".$this->XmlData["tablename"]." as r_main ";
	}

	foreach ($fields as $value){
		if($value["displayinlist"]=="1"||$allfieldshow){
			if($value["type"]=="fkey"){
				if($value["fmutillang"]=="1"){
					$subsql=$this->GetLangTableSql($value["tablename"],$value["ntbname"]);
					$sql=$sql." left join $subsql on r_main.".$value["key"]."=".$value["ntbname"].".id ";
				}else{
				
					$sql=$sql." left join ".$value["tablename"]." `".$value["ntbname"]."` on r_main.".$value["key"]."=".$value["ntbname"].".id ";
				}
			}
		}
	}
	//echo $sql;
	//exit;
	return $sql;
  }
  public function GetSearchSqlCondition($request,$istototal=false){
	  $fields=$this->XmlData["fields"]["field"];
	$sql="  where  ".(empty($this->XmlData["searchcondition"])?" 1=1 ":$this->XmlData["searchcondition"]);
	foreach ($fields as $value){
		
		if($value["search"]=="1"){

			if($value["type"]=="datetime"){

				if($request[$value["key"]."_from"]!=""){

					$sql=$sql." and r_main.".$value["key"].">='".parameter_filter($request[$value["key"]."_from"])."'";

				}

				if($request[$value["key"]."_to"]!=""){

					$sql=$sql." and r_main.".$value["key"]."<='".parameter_filter($request[$value["key"]."_to"])."'";

				}
				if($request[$value["key"]]!=""){

					$sql=$sql." and r_main.".$value["key"]."='".parameter_filter($request[$value["key"]])."'";

				}

			}else if($value["type"]=="fkey"){
				$csr=explode(",",$request[$value["key"]]);
				if(is_array($request[$value["key"]])){
					$sql=$sql." and r_main.".$value["key"]." in (".parameter_filter(join(",",$request[$value["key"]])).")";
						
				}elseif(count($csr)>1){
					
					$sql=$sql." and r_main.".$value["key"]." in (".parameter_filter(join(",",$csr)).")";
					
				}else{
					if($request[$value["key"]]!="0"&&$request[$value["key"]]!=""){
						$sql=$sql." and r_main.".$value["key"]."=".(parameter_filter($request[$value["key"]])+0)."";
					}
				}
				if($request[$value["key"]."_name"]!=""){
					$sql=$sql." and ".$value["ntbname"].".".$value["displayfield"]." like '%".parameter_filter($request[$value["key"]."_name"])."%'";
				}


			}else if($value["type"]=="select"){
				$csr=explode(",",$request[$value["key"]]);
				if(is_array($request[$value["key"]])){
					$crr=array();
					foreach($request[$value["key"]] as $v){
						$crr[]="'".parameter_filter($v)."'";
					}
					$sql=$sql." and r_main.".$value["key"]." in (".join(",",$crr).")";
						
				}elseif(count($csr)>1){
					$crr=array();
					foreach($csr as $v){
						$crr[]="'".parameter_filter($v)."'";
					}
					$sql=$sql." and r_main.".$value["key"]." in (".join(",",$crr).")";
					
				}else{
					if($request[$value["key"]]!=""
					&&$request[$value["key"]]!="no-value"){
						$sql=$sql." and r_main.".$value["key"]."='".parameter_filter($request[$value["key"]])."'";
					}
				}


			}else{
				if($request[$value["key"]]!=""
				&&$request[$value["key"]]!="no-value"){
					if($value["strict"]=="1"){
						$sql=$sql." and r_main.".$value["key"]." = '".parameter_filter($request[$value["key"]])."'";
					}else{
						$sql=$sql." and r_main.".$value["key"]." like '%".parameter_filter($request[$value["key"]])."%'";
					}
					
					
				}
			}

		}

	}
	if($istototal==true){
		return $sql;
	}
	$lastupdatecalltime=parameter_filter($request["lastupdatecalltime"]);
	if($lastupdatecalltime!=""){
		$sql=$sql." and r_main.updated_date > '$lastupdatecalltime' ";
	}

	$groupby=parameter_filter($request["groupby"]);
	if($groupby!=""){
		$groupby=" group by $groupby";	
	}
	

	$orderby=parameter_filter($request["orderby"]);
	if($orderby==""){
		//echo "a";
		//exit();
		$orderby="r_main.updated_date desc";	
	}
	//print_r($request);
	$limit=parameter_filter($request["limit"]);
	if(MODULE!="api"&&$limit==""){
		$limit=" limit $limit 0,65535";
	}else{
		if($limit!=""){
			$limit=" $groupby limit $limit";
		}
	}
	  
	 $sql=$sql." $groupby order by $orderby  
	$limit ";
	//die();
	  
	return $sql;
  }

  private function GetFListData($dbMgr,$smartyMgr,$key=""){
	Global $CONFIG;

	$Array=Array();
	$fields=$this->XmlData["fields"]["field"];
	foreach ($fields as $value){
		if($value["type"]=="flist"){
			//ismutillang
			
			if($key!=""){
				if($key!=$value["key"]){
					continue;
				}
			}else{
				if($value["async"]=="1"){
					continue;
				}
			}
			
			$tablename=$value["tablename"];
			$tablerename=$value["ntbname"];
			$displayfield=$value["displayfield"];
			$condition=$value["condition"];
			$ismutillang=$value["fmutillang"];

			$arrayvalue=$this->GetFKeyData($dbMgr,$displayfield,$tablename,$tablerename,$condition,$ismutillang);
			
			$Arr=Array();
			$Arr["key"]=$value["key"];
			$Arr["value"]=$arrayvalue;
			
			$match=[];
			foreach($arrayvalue as $kv){
				$match[$kv["id"]]=$kv;
				
			}
			$Arr["match"]=$match;
			
			
			$Array[]=$Arr;
		}
	}
	if($smartyMgr!=null){
		
		$smartyMgr->assign("FListArr",$Array);
	}
	return $Array;
  }
  
  
  public function GetFListDataValue($dbMgr,$id=0,$key=""){
	Global $CONFIG;

	$Array=Array();
	$fields=$this->XmlData["fields"]["field"];
	foreach ($fields as $value){
		if($value["type"]=="flist"){
			//ismutillang
			if($key!=""){
				if($key!=$value["key"]){
					continue;
				}
			}
			$tablename=$value["tablename"];
			$tablerename=$value["ntbname"];
			$relatetable=$value["relatetable"];
			$displayfield=$value["displayfield"];
			$condition=$value["condition"];
			if($id>0){
				$condition="  id in (Select fid from ".$relatetable." where pid=$id ) ";
			}
			$ismutillang=$value["fmutillang"];

			$arrayvalue=$this->GetFKeyData($dbMgr,$displayfield,$tablename,$tablerename,$condition,$ismutillang);
			
			$Arr=Array();
			$Arr["key"]=$value["key"];
			$Arr["value"]=$arrayvalue;
			$Array[]=$Arr;
		}
	}
	
	return $Array;
	
    //$smartyMgr->assign("FListArr",$Array);
  }

  public function fixListSearchSql($sql)
  {
  	return $sql;
  }
  public function fixListSearchResult($result)
  {
  	return $result;
  }

  private function ShowSearchResult($dbMgr,$smartyMgr,$request){
	$sql=$this->GetSelectTotalCount($request);
	$sql.=$this->GetSearchSqlCondition($request,true);
	$sql=$this->fixListSearchSql($sql);
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array($query);
	
	$totalrownum=$result["totalrownum"];
	$listpagenum=$request["listpagenum"]+0;
	$listpagecount=50;
	if($totalrownum>1000){
		$request["limit"]=($listpagenum*$listpagecount).",".(($listpagenum+1)*$listpagecount);
		$smartyMgr->assign("listpagenum",$listpagenum);
		$smartyMgr->assign("totalrownum",$totalrownum);
		$smartyMgr->assign("totalpagenum",intval( $totalrownum/$listpagecount));
		$smartyMgr->assign("needpagesplit","Y");
		$smartyMgr->assign("listpagecount",$listpagecount);
	}
	
	
	$sql=$this->GetSearchSql($request);
	
	$sql=$this->fixListSearchSql($sql);
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array_all($query);
	$result=$this->ClearData($result);

	$result=$this->ReloadFListData($dbMgr,$result);
	$result=$this->ReloadFKeyData($dbMgr,$result);
	$result=$this->ReloadSelectData($dbMgr,$result);
	//print_r($result);
	//exit;

	$result=$this->fixListSearchResult($result);

    $smartyMgr->assign("ModelData",$this->XmlData);
    $smartyMgr->assign("PageName",$this->PageName);
    $smartyMgr->assign("result",$result);
	
  }
  
  public function SearchResultExport($dbMgr,$smartyMgr,$request){
	
	$sql=$this->GetSearchSqlField($request,$request["exporttype"]==0);
	$sql.=$this->GetSearchSqlCondition($request);
	 $sql=$this->fixListSearchSql($sql);
	
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array_all($query);
	
	//print_r($result);
	//exit;
	$result=$this->ClearData($result);

	$result=$this->ReloadFListData($dbMgr,$result);
	$result=$this->ReloadFKeyData($dbMgr,$result);
	$result=$this->ReloadSelectData($dbMgr,$result);
	
	$flistdata=$this->GetFListData($dbMgr,$smartyMgr);

	$result=$this->fixListSearchResult($result);
	
	$data=$this->XmlData;
	
	$mgr=new ExcelMgr();
	mkdir (USER_ROOT."logs/",0777,true);
	//print_r($result);
	//exit;
	if($this->XmlData["exportfile"]==''||$this->XmlData["exportfile"]=='csv'){
	  $mgr->exportToCSV(USER_ROOT."logs/".$data["name"]."数据导出-".date("YmdHi").".csv",$data["fields"]["field"],$result,$request["exporttype"]+0,$flistdata);
	}else{
		$mgr->setTitle($data["name"]."数据");
		$mgr->setResult($data["fields"]["field"],$result,$request["exporttype"]+0,$flistdata);
		$mgr->download($data["name"]."数据导出-".date("YmdHi"));
	}
	  //echo $mgr->getCol(27);
	  //exit;
	  //$mgr->setTitle($data["name"]."数据");
	 // print_r($resu);
	  //$mgr->setResult($data["fields"]["field"],$result,$request["exporttype"]+0,$flistdata);
	
	  //$mgr->download($data["name"]."数据导出-".date("YmdHi"));
	exit;
  }

  private function ReloadFListData($dbMgr,$result){
	$fields=$this->XmlData["fields"]["field"];
	foreach ($fields as $value){
		if($value["type"]=="flist"){
			$rtable=$value["relatetable"];
			if($rtable!=""){
				for($i=0;$i<count($result);$i++){
					$sql="select pid,fid from $rtable where pid=".$result[$i]["id"];
					$query = $dbMgr->query($sql);
					$rs = $dbMgr->fetch_array_all($query);

					$isfirst=1;
					$str="";
					foreach($rs as $v){
						if($isfirst==0){
							$str.=",";
						}
						$isfirst=0;
						$str.=$v["fid"];
					}
					$result[$i][$value["key"]]=$str;
				}
			}
		}
	}
	return $result;
  }

  private function ReloadFKeyData($dbMgr,$result){
	$fields=$this->XmlData["fields"]["field"];
	foreach ($fields as $value){
		if($value["type"]=="fkey"){
			for($i=0;$i<count($result);$i++){
				$col=array();
				$col["name"]=$result[$i][$value["key"]."_name"];
				$col["value"]=$result[$i][$value["key"]];
				$result[$i][$value["key"]]=$col;
			}
		}
	}
	return $result;
  }
  private function ReloadSelectData($dbMgr,$result){
	$fields=$this->XmlData["fields"]["field"];
	foreach ($fields as $value){
		if($value["type"]=="select"){
			for($i=0;$i<count($result);$i++){
				$col=array();
				$col["name"]=$result[$i][$value["key"]."_name"];
				$col["value"]=$result[$i][$value["key"]];
				$result[$i][$value["key"]]=$col;
			}
		}
	}
	return $result;
  }
  private function ClearData($result){
	$count=count($result);
	for($i=0;$i<$count;$i++){
		for($j=0;$j<count($result[$i]);$j++){
			$value=$result[$i][$j];
			if($value instanceof DateTime){
				$result[$i][$j]= $value->format('Y-m-d H:i:s');
			}
		}
	}
	return $result;
  }

  public function fixGridSearchSql($sql)
  {
  	return $sql;
  }
  public function fixGridSearchResult($result)
  {
  	return $result;
  }
  private function ShowGridResult($dbMgr,$smartyMgr,$request,$parenturl){
	  
	Global $CmsStyle;
	$sql=$this->GetSearchSql($request);
	$sql=$this->fixGridSearchSql($sql);

	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array_all($query); 
	$result=$this->ClearData($result);

	$result=$this->ReloadFListData($dbMgr,$result);
	$result=$this->ReloadFKeyData($dbMgr,$result);
	$result=$this->ReloadSelectData($dbMgr,$result);
	$result=$this->fixGridSearchResult($result);
	
	$this->GetFListData($dbMgr,$smartyMgr);
    $smartyMgr->assign("ModelData",$this->XmlData);
    $smartyMgr->assign("PageName",$this->PageName);
    $smartyMgr->assign("parenturl",$parenturl);
    $smartyMgr->assign("result",$result);
    $smartyMgr->display(ROOT.'/templates/'.$CmsStyle.'/grid.html');

  }

  
  public function Add($dbMgr,$smartyMgr,$request){
	 
	Global $CmsStyle;
	$copyid=$request["copyid"]+0;
	
	$sql="select * from ".$this->XmlData["tablename"]." r_main where id=$copyid";
	$sql=$this->fixEditSql($sql);
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array_all($query);

	$result=$this->ClearData($result);
	$result=$this->ReloadFListData($dbMgr,$result);

	$result=$result[0];
	$copyid=$result["id"]+0;
	if($copyid>0){
		$XmlDataWithInfo=$this->assignWithInfo($this->XmlData,$result,$langresult);
		$dataWithFKey=$this->loadFKeyValue($dbMgr,$XmlDataWithInfo);
		$smartyMgr->assign("incopy",1);
	}else{
		
		$dataWithFKey=$this->loadFKeyValue($dbMgr,$this->XmlData);
	}
	
	$this->GetFListData($dbMgr,$smartyMgr);
	  

	$smartyMgr->assign("ParentKey",$request["key"]);
	$smartyMgr->assign("ParentId",$request["id"]);
	
	$dataWithFKey=$this->fixEditData($dataWithFKey);
    $smartyMgr->assign("ModelData",$dataWithFKey);
    $smartyMgr->assign("PageName",$this->PageName);
    $smartyMgr->assign("action","add");
	
    $smartyMgr->display(ROOT.'/templates/'.$CmsStyle.'/detail.html');
	
	
	
	
  }

  public function fixEditId($id){
  	return $id;
  }
  public function fixEditSql($sql){
  	return $sql;
  }
  public function fixEditData($result){
  	return $result;
  }
  
  public function Edit($dbMgr,$smartyMgr,$id,$logdata=null){

	Global $CmsStyle;
    if($logdata!=null){
		//print_r($logdata);
		//exit;
        $result=$logdata;
    }else{
        $id=$this->fixEditId($id);
        
        $sql="select * from ".$this->XmlData["tablename"]." r_main where id=$id";
        
        $sql=$this->fixEditSql($sql);
        $query = $dbMgr->query($sql);
        $result = $dbMgr->fetch_array_all($query); 

    }
	
	
    $result=$this->ClearData($result);
    $result=$this->ReloadFListData($dbMgr,$result);

    $result=$result[0];

	if($this->XmlData["ismutillang"]=="1"){
	$sql="select * from ".$this->XmlData["tablename"]."_lang where oid=$id";
	$query = $dbMgr->query($sql);
	$langresult = $dbMgr->fetch_array_all($query); 
	}

	$XmlDataWithInfo=$this->assignWithInfo($this->XmlData,$result,$langresult);
    $dataWithFKey=$this->loadFKeyValue($dbMgr,$XmlDataWithInfo);
	//print_r($dataWithFKey);
	//exit;
	$this->GetFListData($dbMgr,$smartyMgr);
	$dataWithFKey=$this->fixEditData($dataWithFKey);
    $smartyMgr->assign("ModelData",$dataWithFKey);
    $smartyMgr->assign("PageName",$this->PageName);

    if($this->XmlData["recording"]=="1"){

        $sql="select *,a.user_name created_user_name,b.user_name updated_user_name from ".$this->XmlData["tablename"]." r_main
        left join tb_user a on r_main.created_user=a.id
        left join tb_user b on r_main.updated_user=b.id
        where r_main.id=$id";
        $query = $dbMgr->query($sql);
        $user = $dbMgr->fetch_array($query); 
        $smartyMgr->assign("operator",$user);

        if(!$dbMgr->checkHave("information_schema.TABLES"," TABLE_NAME='applink_operation_log' ")){
            $sql="CREATE TABLE `applink_operation_log` (`id`  int auto_increment,`module` varchar(20),`pid` int,user_id  int,op_time datetime,kdesc varchar(200),kdata longtext,
            primary key (id) ) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
            $dbMgr->query($sql);
        }

        $name=$this->XmlData["tablename"];

        $query = $dbMgr->query("select a.*,b.user_name user_name from applink_operation_log a
        left join tb_user b on a.user_id=b.id
        where a.`module`='$name' and a.`pid`=$id order by op_time desc ");
        $oplogs = $dbMgr->fetch_array_all($query); 
        $smartyMgr->assign("oplogs",$oplogs);

    }

    $smartyMgr->assign("id",$id);
    $smartyMgr->assign("action","edit");
    $smartyMgr->display(ROOT.'/templates/'.$CmsStyle.'/detail.html');
  }

  private function assignWithInfo($XmlDataEx,$info,$langresult){
	
	$fields=$XmlDataEx["fields"]["field"];
	$count=count($fields);
	for($i=0;$i<$count;$i++){
		if($fields[$i]["ismutillang"]=="1"){
			$valarray=Array();
			foreach ($langresult as $rs){
				$arr=Array();
				$arr["code"]=$rs["lang"];
				$arr["value"]=$rs[$fields[$i]["key"]];
				$valarray[]=$arr;
			}
			$fields[$i]["value"]=$valarray;
		}else{
			$fields[$i]["value"]=$info[$fields[$i]["key"]];
			$fields[$i]["default"]=$info[$fields[$i]["key"]];
			if($fields[$i]["type"]=="number"){
				$fields[$i]["default"]=$info[$fields[$i]["key"]];
			}
		}
	}
	$XmlDataEx["fields"]["field"]=$fields;
	//print_r($XmlDataEx);
	return $XmlDataEx;
  }

  public function saveValidate($dbMgr,$request){
  	Global $SysLang,$Config;
  	$error="";
	
	
    if(MODULE!="api"&&$this->XmlData["nosave"]=="1"){
    	return "no save permission";
    }
	
  	$fields=$this->XmlData["fields"]["field"];
    $unionunique_sql="";
    $unionunique_keyname=Array();
    foreach ($fields as $value){
        
		if($value["type"]=="text"&&$value["unique"]=="1"){
			//$sql="".$this->XmlData["tablename"];
			$condition=$value["key"]."='".($request[$value["key"]])."' and id<>".($request["primary_id"]+0).(!empty($this->XmlData["searchcondition"])?" and ".$this->XmlData["searchcondition"]:"");
			if($dbMgr->checkHave($this->XmlData["tablename"]." r_main",$condition) ){
				$error.= $value["name"].$SysLang["model"]["keyunique"]."\r\n";
			}
		}
		if($value["type"]=="text"&&!empty($value["format"])){
			if(preg_match($value["format"],($request[$value["key"]]))==false){
				$error.= $value["name"].$SysLang["model"]["formatincorrect"]."\r\n";
			}
		}

		if($value["type"]=="text"&&$value["unionunique"]=="1"){
			//$sql="".$this->XmlData["tablename"];
			$unionunique_sql.=" and ".$value["key"]."='".($request[$value["key"]])."' ";
			$unionunique_keyname[]=$value["name"];
		}
		if($value["notnull"]=="1"
            &&(($value["type"]=="fkey"||$value["type"]=="number")&&($request[$value["key"]])=="0"
                ||($request[$value["key"]])=="")){ 
			$error.= $SysLang["model"]["pleaseenter"].$value["name"]."\r\n";
		}
	}
	if($unionunique_sql!=""){
		 $unionunique_sql="id<>".($request["primary_id"]+0).(empty($this->XmlData["searchcondition"])==false?" and ".$this->XmlData["searchcondition"]:"").$unionunique_sql;
		if($dbMgr->checkHave($this->XmlData["tablename"]." r_main",$unionunique_sql) ){
				$error.= join(", ",$unionunique_keyname).$SysLang["model"]["keyunionunique"]."\r\n";
		}
	}
    return $error;
  }
  public function fixInsertSql($sql){
  	return $sql;
  }
  public function fixUpdateSql($sql){
  	return $sql;
  }
  public function afterSave($dbmgr,$id){
  	
  }

  public function resetRequestData($dbMgr,$request){
  	return $request;
  }
  
  public function beforeSaveDataFix($request){
	$fields=$this->XmlData["fields"]["field"];
	foreach ($request as $key => $value) {
		$request[$key]=parameter_filter($value);
	}
	foreach ($fields as $value){
        if($value["type"]=="fkey"||$value["type"]=="number"){
            $request[$value["key"]]=$request[$value["key"]]+0;
        }
    }
	return $request;
  }
	public function editone($dbMgr,$request,$sysuser=-1){
		$key=$request["key"];
		$val=parameter_filter( $request["val"]);
		$primary_id=parameter_filter( $request["primary_id"]);
		
		 $sql="update ".$this->XmlData["tablename"]." set `$key`='$val' where id=$primary_id ";
		$dbMgr->query($sql);
		return "";
	}
  public function Save($dbMgr,$request,$sysuser=-1){
	Global $SysLang,$Config;

	$fields=$this->XmlData["fields"]["field"];
   
	//print_r($request);
	
    $sql="";

	$dbMgr->begin_trans();
	$haveMutilLang=false;

	if($this->XmlData["nolist"]=="1"){
		if($dbMgr->checkHave($this->XmlData["tablename"]," id=".$this->GetNoListId()) ){
			$request["primary_id"]="".$this->GetNoListId();
		}else{
			$request["primary_id"]="";
		}
	}

	
	if($request["primary_id"]==""){
	
		

		$haveMutilLang=false;
        
		$sql="insert into ".$this->XmlData["tablename"]." (id";
		$fields=$this->XmlData["fields"]["field"];
		foreach ($fields as $value){
			if($value["ismutillang"]=="1"){
				$haveMutilLang=true;
				continue;
			}
			if($value["type"]=="grid"){
				continue;
			}
			if($value["type"]=="flist"&&$value["relatetable"]!=""){
				continue;
			}
			if($value["nosave"]=="1"){
				continue;
			}
			$sql=$sql.",`".$value["key"]."`";
		}
		$sql=$sql.",created_date,created_user,updated_date,updated_user ) values ( ";
		$sql=$sql." absolutexxidxx ";
		foreach ($fields as $value){
			
			
			if($value["type"]=="grid"
			||$value["ismutillang"]){
				continue;
			}
			if($value["type"]=="flist"&&$value["relatetable"]!=""){
				continue;
			}
			if($value["nosave"]=="1"){
				continue;
			}

			if($value["type"]=="password"){
				$sql=$sql.",'".md5($request[$value["key"]])."'";
			}elseif($value["type"]=="url"){
				$sql=$sql.",'".str_replace("PRIMARY_ID","absolutexxidxx",$request[$value["key"]])."'";
			}elseif($value["type"]=="number"){
				$sql=$sql.",'".($request[$value["key"]]+0)."'";
			}elseif($value["type"]=="datetime"
 				&&(strtolower($request[$value["key"]])=="null"||strtolower($request[$value["key"]])=="")){
 				$sql=$sql.",null";
			}else{
				$sql=$sql.",'".($request[$value["key"]])."'";
			}
			if($value["type"]=="check"&&$value["unique"]=="1"&&($request[$value["key"]])=="Y"){
				$dbMgr->query("update ".$this->XmlData["tablename"]." set ".$value["key"]."='N'");
			}
		}
		$sql=$sql.",".$dbMgr->getDate().",$sysuser,".$dbMgr->getDate().",$sysuser )";
		//print_r($request);
		 $sql=$this->fixInsertSql($sql);
		if($this->XmlData["nolist"]=="1"){
			 $id=$this->GetNoListId();
			
		}else{
			//$id="ifnull(max(id),0)+1";
			$id=$dbMgr->getNewId($this->XmlData["tablename"]);
		}
		$sql=str_replace("absolutexxidxx",$id,$sql);
		$query = $dbMgr->query($sql);
		if($this->XmlData["nolist"]!="1"){
			
			//$sql="select last_insert_id() lastid";
			//$query = $dbMgr->query($sql);
			//$csr=$dbMgr->fetch_array($query);
			//print_r($csr);
			//$id=$csr["lastid"]+0;
		}
	}else{
		$haveMutilLang=false;
		$id=$request["primary_id"];
		$sql="update `".$this->XmlData["tablename"]."` set updated_date=".$dbMgr->getDate().",updated_user=$sysuser";
		$fields=$this->XmlData["fields"]["field"];
		foreach ($fields as $value){
			if($value["ismutillang"]=="1"){
				$haveMutilLang=true;
				continue;
			}
			if($value["type"]=="grid"
			||$value["type"]=="password"){
				continue;
			}
			if($value["type"]=="flist"&&$value["relatetable"]!=""){
				continue;
			}
			if($value["nosave"]=="1"){
				continue;
			}
			if($value["type"]=="check"&&$value["unique"]=="1"&&($request[$value["key"]])=="Y"){
				$dbMgr->query("update ".$this->XmlData["tablename"]." set ".$value["key"]."='N'");
			}
			if($value["type"]=="number"){
				$request[$value["key"]]=$request[$value["key"]]+0;
			}
			
			if($value["type"]=="url"){
				$request[$value["key"]]=str_replace("PRIMARY_ID","$id",$request[$value["key"]]);
			}

			

			if($value["type"]=="datetime"
 				&&(strtolower($request[$value["key"]])=="null"||strtolower($request[$value["key"]])=="")){
 				$sql=$sql.", `".$value["key"]."`= null ";
 			}else{
 			

 				$sql=$sql.", `".$value["key"]."`='".($request[$value["key"]])."'";
 			}
			

		}
		$sql=$sql." where id=$id";
		$query = $dbMgr->query($sql);

	}
	//print_r($request);
	$tempid=$request["tempid"]+0;

		foreach ($fields as $value){
			if($value["type"]=="password"&&$value["nosave"]!='1'){
				$sql="update `".$this->XmlData["tablename"]."` set ";
				$sql=$sql." `".$value["key"]."`='".md5($request[$value["key"]])."'";
				$sql=$sql." where id=$id and `".$value["key"]."`<>'".($request[$value["key"]])."'";
				//echo $sql;
				$query = $dbMgr->query($sql);
			}
			if($value["type"]=="flist"&&$value["relatetable"]!=""){
				$relatetable=$value["relatetable"];
				$sql="delete from $relatetable where pid=$id";
				$query = $dbMgr->query($sql);
				$arr=explode(",",$request[$value["key"]]);
				if($request[$value["key"]]!=""&&count($arr)>0){
					$sql="insert into $relatetable (pid,fid) values";
					$isfirst=1;
					foreach($arr as $v){
						if($isfirst==0){
							$sql.=",";
						}
						$isfirst=0;
						$sql.=" ($id,$v)";
					}
					$query = $dbMgr->query($sql);
				}
			}
			if($tempid>0&&$value["type"]=="grid"){
				//print_r($value);
				//exit;
				if($request["primary_id"]==""){
					
					$model=$value["model"];
					$key=$value["modelkey"];
					$xmlstr=$this->loadXmlFile($model);
					$modelinfo=$this->xmlToArray($xmlstr);
					 $tablename=$modelinfo["tablename"];
					 
					 $sql="update $tablename set $key=$id where $key=$tempid ";
					
					$dbMgr->query($sql);
				}
			}
		}
		
		if($haveMutilLang){
			foreach ($SysLangConfig["langs"]["lang"] as $lang){
				$sql="update ".$this->XmlData["tablename"]."_lang set lang='".$lang["code"]."'";
				foreach ($fields as $value){
					if($value["ismutillang"]=="1"){
						$sql=$sql.", ".$value["key"]."='".($request[$value["key"]."_".$lang["code"]])."'";
					}
				}
				$sql=$sql." where oid=$id and lang='".$lang["code"]."'";
				$query = $dbMgr->query($sql);
			}
		}

	if($haveMutilLang){
			$sql="delete from ".$this->XmlData["tablename"]."_lang where oid=$id ";
				$query = $dbMgr->query($sql);
			foreach ($SysLangConfig["langs"]["lang"] as $lang){
				$sql="insert into ".$this->XmlData["tablename"]."_lang (oid,lang";
				$fields=$this->XmlData["fields"]["field"];
				foreach ($fields as $value){
					if($value["ismutillang"]=="1"){
					$sql=$sql.",".$value["key"]."";
					}
				}
				$sql=$sql." ) values ( $id ,'".$lang["code"]."' ";
				foreach ($fields as $value){
					if($value["ismutillang"]=="1"){
						$sql=$sql.",'".($request[$value["key"]."_".$lang["code"]])."'";
					}
				}
				$sql=$sql." )";
				$query = $dbMgr->query($sql);
			}
		}

	$this->afterSave($dbMgr,$id);

    $dbMgr->commit_trans();
    
    if($this->XmlData["recording"]=="1"){

        if($request["primary_id"]==""){
            $op="新增";
        }else{
            $op="修改";
        }

        if(!$dbMgr->checkHave("information_schema.TABLES"," TABLE_NAME='applink_operation_log'")){
            $sql="CREATE TABLE `applink_operation_log` (`id`  int auto_increment,`module` varchar(20),`pid` int,user_id  int,op_time datetime,kdesc varchar(200),kdata longtext ,
            primary key (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;";
            $dbMgr->query($sql);
        }

        $name=$this->XmlData["tablename"];
        
        $sql="select * from ".$this->XmlData["tablename"]." r_main where id=$id";
        $query = $dbMgr->query($sql);
        $result = $dbMgr->fetch_array($query); 

        $rt=json_encode($result);
		//exit;
		 $rt=str_replace("\\","\\\\",$rt);
        $query = $dbMgr->query("insert into applink_operation_log 
        (`module`,`pid`,`user_id`,`op_time`,`kdesc`,`kdata`) values 
        ('$name',$id, $sysuser , now(),'$op','$rt') ");
        //$oplogs = $dbMgr->fetch_array_all($query); 
        //$smartyMgr->assign("oplogs",$oplogs);

    }
    



	return "right".$id;
  }


  public function GetNoListId(){
  	return 0;
  }

  private function Import($dbMgr,$smartyMgr,$request,$sysuser){
	Global $CmsStyle;
	  set_time_limit(3000);
	$file=$_FILES["file_import"];
	if($file["error"]!="0"){
		return "UPLOADERROR";
	}
	//echo $file["type"];
	if ($file["type"] != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"){
		//return "FILETYPEERROR";
	}
	$excelMgr=new ExcelMgr();
	$excelarr=$excelMgr->read($file["tmp_name"]);
	//print_r($excelarr);
	//exit;
	$importData=$this->ImportDataCheck($excelarr,$dbMgr);
	$importData=$this->fixImportDataCheck($importData,$dbMgr);
    $smartyMgr->assign("ModelData",$this->XmlData);
    $smartyMgr->assign("PageName",$this->PageName);
    $smartyMgr->assign("ImportData",$importData);
    $smartyMgr->display(ROOT.'/templates/'.$CmsStyle.'/import.html');

  }

  public function fixImportDataCheck($dataarr,$dbMgr){
  	return $dataarr;
  }

  private function ImportDataCheck($dataarr,$dbMgr){
	$fields=$this->XmlData["fields"]["field"];
	
	$ret=array();
	foreach($dataarr as $row){
		$r=array();
		
		$r["primary_id"]=$row["主键值"];
		foreach($fields as $fk=>$field){
			foreach($row as $key=>$col){
				if($key==$field["name"]){
					$field["value"]=htmlspecialchars( $col);
					$field["display"]=$col;
				}
			}
			$field["value"]=trim($field["value"]);
			
			$field["error"]="0";
			if($field["notnull"]==1&&$field["value"]==""){
					$field["error"]="1";
					$field["display"]="不为空";
			}
			if($field["type"]=="select"){
				$options=$field["options"]["option"];
				$opval="";
				foreach($options as $option){
					if($field["value"]==$option["name"]){
						$opval=$option["value"];
					}
				}
				if($opval==""){
					$field["error"]="1";
					$field["display"]=$field["display"]." 错误值";
					$field["value"]=$opval;
				}else{
					//$field["display"]=$opval;
					$field["value"]=$opval;
				}
			}
			if($field["type"]=="fkey"){
				if(trim($field["value"])==""){
					
							$field["display"]="-";
							$field["value"]=0;
				}else{
					$tablename=$field["tablename"];
					$tname=$field["ntbname"];
					$condition=$field["condition"];
					if($condition==""){
						$condition=" 1=1 ";
					}
					$searchfield=explode(",",$field["displayfield"]);
					$searchfield=$searchfield[0];
					
					 $sql=" select id from $tablename as $tname where $condition and `$searchfield` like'".trim($field["value"])."' ";
					//exit;
					$query = $dbMgr->query($sql);
					$result = $dbMgr->fetch_array($query);
					
					if(($result["id"]+0)==0){
						$sql=" select id from $tablename as $tname where $condition and `$searchfield` like'%".trim($field["value"])."%' ";
						$query = $dbMgr->query($sql);
						$result = $dbMgr->fetch_array($query); 
					}
					if(($result["id"]+0)==0){
						if($field["notnull"]==1){
							$field["error"]="1";
							$field["display"]=$field["display"]." 没有值";
							$field["value"]=0;
						}else{
							$field["display"]="-";
							$field["value"]=0;
						}
					}else{
						$field["value"]=$result["id"];
					}
				}
				
			}
			if($field["type"]=="flist"){
				$tablename=$field["tablename"];
				$tname=$field["ntbname"];
				$condition=$field["condition"];
				if($condition==""){
					$condition=" 1=1 ";
				}
				$searchfield=explode(",",$field["displayfield"]);
				$searchfield=$searchfield[0];
				
				$flistvalues=explode(",",$field["value"]);
				
				$vs=[];
				foreach($flistvalues as $v){
					$v=parameter_filter($v);
					 $sql=" select id from $tablename as $tname where $condition and `$searchfield` like'%".$v."%' ";
					$query = $dbMgr->query($sql);
					$result = $dbMgr->fetch_array($query); 
					if(($result["id"]+0)>0){
						$vs[]=$result["id"]+0;
					}
				}
				//print_r($vs);
				$field["value"]=join(",",$vs);
			}
			
			
			if($field["type"]=="check"){
				$field["value"]=$field["value"]=="是"?"Y":"N";
			}
			if($field["type"]=="number"){
				$field["value"]=round($field["value"],2)+0;
			}
			
			$r[$field["key"]]=$field;
		}
		$ret[]=$r;
	}
	//print_r($ret);
	//exit;
	return $ret;
  }

  public function deleteId($id_array){
  	return $id_array;
  }
  public function deleteVaild($id_array,$dbMgr){
  	return "";
  }
  public function afterDelete($id_array,$dbMgr){
  	
  }

  public function Delete($dbMgr,$idlist,$sysuser=-1){
    if(empty($idlist)){
        $idlist="-1";
    }
    $idlist=explode(",",$idlist);
    for($i=0;$i<count($idlist);$i++){
        $idlist[$i]=$idlist[$i]+0;
    }
    $idlist=$this->deleteId($idlist);

    $error=$this->deleteVaild($idlist,$dbMgr);
    if($error!=""){
    	return $error;
    }

    $idlist=join(",",$idlist);
    $dbMgr->begin_trans();

	$sql="update ".$this->XmlData["tablename"]." set status='D',updated_user=$sysuser,updated_date=".$dbMgr->getDate()." where id in ($idlist)";
	$query = $dbMgr->query($sql);

	$this->afterDelete($idlist,$dbMgr);
    $dbMgr->commit_trans();

	return "success";
  }

  public function DefaultShow($smarty,$dbmgr,$action,$menuId,$request){
	Global $SysUser;
	Global $CmsStyle;
	
	  $request=$this->resetRequestData($dbmgr,$request);
	
	  if($action==""){
		$smarty->assign("MyMenuId",$menuId."_list");
		$this->ShowList($dbmgr,$smarty);

	  }else if($action=="search"){
		$this->ShowSearchResult($dbmgr,$smarty,$request);
		$smarty->display(ROOT.'/templates/'.$CmsStyle.'/result.html');
	  }else if($action=="export"){
		$this->SearchResultExport($dbmgr,$smarty,$request);
		
	  }else if($action=="print"){
		$this->XmlData["nosave"]="1";
		$this->XmlData["nolist"]="1";
		
		$fields=$this->XmlData["fields"]["field"];    
		for($i=0;$i<count($this->XmlData["fields"]["field"]);$i++){
			$this->XmlData["fields"]["field"][$i]["displayinlist"]=$this->XmlData["fields"]["field"][$i]["hidden"]=="1"?"0":"1";
		}
		$smarty->assign("viewonly","Y");
		$this->ShowSearchResult($dbmgr,$smarty,$request);
		$smarty->display(ROOT.'/templates/'.$CmsStyle.'/print.html');
	  }else if($action=="getgrid"){
		
		$smarty->assign("candelete",$request["candelete"]);
		$this->ShowGridResult($dbmgr,$smarty,$request,$request["parenturl"]);

	  }else if($action=="add"){

		$smarty->assign("MyMenuId",$menuId."_add");
		$tempid=-1*time();
		if($request["tempid"]!=""){
			$tempid=$request["tempid"];
		}
		$smarty->assign("tempid",$tempid);
		$this->Add($dbmgr,$smarty,$request);

	  }else if($action=="edit"){
		$smarty->assign("MyMenuId",$menuId."_add");
		$this->Edit($dbmgr,$smarty,$request["id"]+0);
	  }else if($action=="view"){
		$this->XmlData["nosave"]="1";
		$this->XmlData["nolist"]="1";
		
		$fields=$this->XmlData["fields"]["field"];    
		for($i=0;$i<count($this->XmlData["fields"]["field"]);$i++){
			$this->XmlData["fields"]["field"][$i]["disableindetail"]="1";
		}
		$smarty->assign("viewonly","Y");
		$this->Edit($dbmgr,$smarty,$request["id"]+0);
	  }else if($action=="viewlog"){
		$this->XmlData["nosave"]="1";
		$this->XmlData["nolist"]="1";
		
		$fields=$this->XmlData["fields"]["field"];    
		for($i=0;$i<count($this->XmlData["fields"]["field"]);$i++){
			$this->XmlData["fields"]["field"][$i]["disableindetail"]="1";
		}
        $smarty->assign("viewonly","Y");
        
		$id=$request["logid"]+0;
        $query = $dbmgr->query("select a.*,b.user_name user_name from applink_operation_log a
        left join tb_user b on a.user_id=b.id 
        where a.id=$id ");
        $log = $dbmgr->fetch_array($query);  
		//echo $log["kdata"];
        $log=json_decode($log["kdata"],true);
        //print_r($log);
		//exit;
		$r=[];
		$r[]=$log;
		$this->Edit($dbmgr,$smarty,0,$r);
	  }else if($action=="save"){
		  
		$request=$this->beforeSaveDataFix($request);
		$error=$this->saveValidate($dbmgr,$request);
		if($error!=""){
			echo $error;
		}else{
			
			$result=$this->Save($dbmgr,$request,$SysUser["id"]);
			echo $result;
		}
		  
	  }else if($action=="delete"){
		$result=$this->Delete($dbmgr,$request["idlist"],$SysUser["id"]);
		echo $result;
	  }else if($action=="import"){
		$result=$this->Import($dbmgr,$smarty,$request,$SysUser["id"]);
		echo $result;
	  }else if($action=="editone"){
		$result=$this->editone($dbmgr,$request,$SysUser["id"]);
		echo $result;
	  }else if($action=="flist"){
		$result=$this->GetFListData($dbmgr,$smarty,$request["key"]);
		$smarty->assign("key",$request["key"]);
		$smarty->display(ROOT.'/templates/'.$CmsStyle.'/flist.html');
		exit;
	  }else if($action=="fkeysearch"){
        $key=$request["key"];
        $q=$request["q"];
        $field=$this->getModelField($key);
        $result=$this->GetFKeyData($dbmgr,$field["displayfield"],$field["tablename"],$field["ntbname"],$field["condition"],$field["fmutillang"],$q);
        
		outputJSON($result);
	  }
 }
 
 

 public function fixApiListRequest($dbMgr,$request){
 	return $request;
 }

 public function fixApiListSql($sql){
 	return $sql;
 }

 public function fixApiListResult($result){
 	return $result;
 }

 public function setDisplayInList($isshow,$arr){
	 
    $arr=explode(",",$arr);
    foreach($arr as $a){
        $field=$this->getModelField($a);
        $field["displayinlist"]=$isshow?"1":"0";
		$this->setModelField($a,$field);
    }
 }
 
  private function ShowAPIList($dbMgr,$request){
  	$request=$this->fixApiListRequest($dbMgr,$request);
    $sql=$this->GetSearchSql($request);
    $sql=$this->fixApiListSql($sql);
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array_all($query); 
	$result=$this->fixApiListResult($result);
	outputJSON($result);
  }

  public function fixApiGetId($id){
  	return $id;
  }
 
  public function fixApiGetSql($sql){
  	return $sql;
  }
  
  public function fixApiGetData($result){
  	return $result;
  }

  private function DetailApi($dbMgr,$id,$lang){
  	$id=$id+0;
  	$id=$this->fixApiGetId($id);

	if($this->XmlData["ismutillang"]=="1"){
		$sql="select * from ".$this->XmlData["tablename"]." m
		inner join ".$this->XmlData["tablename"]."_lang ml on m.id=ml.oid and code='$lang' where id=$id";
	}else{
		$sql="select * from ".$this->XmlData["tablename"]." where id=$id";
	}
	$sql=$this->fixApiGetSql($sql);
	$query = $dbMgr->query($sql);
	$result = $dbMgr->fetch_array($query);
	$result=$this->fixApiGetData($result);
	outputJSON($result);
  }

  private function SaveApi($dbmgr,$request){
	  
	$request=$this->beforeSaveDataFix($request);
	$error=$this->saveValidate($dbmgr,$request);
	if($error!=""){
		outputJSON(outResult(-1,"Save fail",$error));
	}
	$result=$this->Save($dbmgr,$request,-1);
    if(substr($result,0,5)=="right"){
        $result=outResult(0,"Save Success",substr($result,5));
    }else{
        $result=outResult(-1,"Save fail",$result);
    }
	outputJSON($result);
  }

  private function DeleteApi($dbmgr,$request){
	$result=$this->Delete($dbmgr,$request["idlist"],-1);
    if(substr($result,0,7)=="success"){
        $result=outResult(0,"Delete Success");
    }else{
        $result=outResult(-1,"Delete fail",$result);
    }
	outputJSON($result);
  }
	  
  public function DefaultShowAPI($dbmgr,$action,$request){
	  
	  $request=$this->resetRequestData($dbmgr,$request);
	  
	  if($action==""){
		$this->ShowAPIList($dbmgr,$request);
	  }if($action=="detail"){
		$this->DetailApi($dbmgr,$request["id"],$request["lang"]);
	  }else if($action=="save"){
		$result=$this->SaveApi($dbmgr,$request);
		echo $result;
	  }else if($action=="delete"){
		$result=$this->DeleteApi($dbmgr,$request["idlist"]);
		echo $result;
	  }
  }

}

?>