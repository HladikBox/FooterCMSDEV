<?php
require ROOT.'/libs/PHPExcel/Classes/PHPExcel.php';
class ExcelMgr
{
	public $objPHPExcel;
	public function __construct()
	{
		$this->objPHPExcel = new PHPExcel();
		//$this->objPHPExcel->getProperties()->setCreator("Helpfooter")
		//					 ->setLastModifiedBy("Helpfooter")
		//					 ->setTitle("数据导入模板")
		//					 ->setSubject("数据导入模板")
		//					 ->setDescription("数据导入模板")
		//					 ->setKeywords("数据导入模板")
		//					 ->setCategory("数据导入模板");

	}

	public function setTitle($title){
		$this->objPHPExcel->getProperties()->setTitle($title)
											->setSubject($title);
	}
	
	public function setResult($fields,$result,$exporttype,$flistdata){
		
		//print_r($result);
		//exit;
		
		$objPHPExcel=$this->objPHPExcel;
		$this->objPHPExcel->setActiveSheetIndex(0);
		$i=0;
		if($exporttype==0){
			$objRichText = new PHPExcel_RichText();
				//$objRichText->createText($header[$i]["name"]);
				$objPayable = $objRichText->createTextRun("主键值");
				$objPayable->getFont()->setBold(true);
				//$objPayable->getFont()->setItalic(true);
				$objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_WHITE ) );
				
				$this->objPHPExcel->getActiveSheet()->setCellValue($this->getCol($i)."1",  $objRichText);

				$this->objPHPExcel->getActiveSheet()->getStyle($this->getCol($i)."1")
				->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()->setRGB(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKBLUE ) );
				$this->objPHPExcel->getActiveSheet()->getColumnDimension($this->getCol($i))->setWidth(20);
				
				$i++;
		}
		foreach($fields as $val){
			if($exporttype==0||$val["displayinlist"]=="1"){
				$objRichText = new PHPExcel_RichText();
				//$objRichText->createText($header[$i]["name"]);
				$objPayable = $objRichText->createTextRun($val["name"]);
				$objPayable->getFont()->setBold(true);
				//$objPayable->getFont()->setItalic(true);
				$objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_WHITE ) );
				
				$this->objPHPExcel->getActiveSheet()->setCellValue($this->getCol($i)."1",  $objRichText);

				$this->objPHPExcel->getActiveSheet()->getStyle($this->getCol($i)."1")
				->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
				->getStartColor()->setRGB(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKBLUE ) );
				$this->objPHPExcel->getActiveSheet()->getColumnDimension($this->getCol($i))->setWidth(20);

			
				$i++;
			}
		}
		
		//print_r($result);
		//exit;
		
		$j=2;
		foreach($result as $r){
			$i=0;
			if($exporttype==0){
				
					$objRichText = new PHPExcel_RichText();
					$objPayable = $objRichText->createTextRun($r["id"]);
					$this->objPHPExcel->getActiveSheet()->setCellValue($this->getCol($i).$j,  $objRichText);
					$i++;
			}
			foreach($fields as $val){
				if($exporttype==0||$val["displayinlist"]=="1"){
					$key=$val["key"];
					
					$v=$r[$key];
					$type=$val["type"];
					if($type=='select'){
						//print_r($v);
						//exit;
						$v=$v["name"];
					}
					if($type=='fkey'){
						//print_r($v);
						//exit;
						$v=$v["name"];
					}
					if($type=="flist"){
						foreach($flistdata as $flist){
							if($val["key"]==$flist["key"]){
								$flistvalue=[];
								$vid=explode(",",$v);
								$flistmatch=$flist["match"];
								foreach($vid as $kid){
									$flistvalue[]=$flistmatch[$kid]["name"];
								}
								$v=join(",",$flistvalue);
								break;
							}
						}
					}
					
					$objRichText = new PHPExcel_RichText();
					$objPayable = $objRichText->createTextRun($v);
					$this->objPHPExcel->getActiveSheet()->setCellValue($this->getCol($i).$j,  $objRichText);
					$i++;
				}
			}
			//exit;
			$j++;
		}
		
	}

	public function setHeaderForModel($header){

		$objPHPExcel=$this->objPHPExcel;
		$this->objPHPExcel->setActiveSheetIndex(0);
		
		$i=0;
		
		$objRichText = new PHPExcel_RichText();
			//$objRichText->createText($header[$i]["name"]);
			$objPayable = $objRichText->createTextRun("主键值");
			$objPayable->getFont()->setBold(true);
			//$objPayable->getFont()->setItalic(true);
			$objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_WHITE ) );
			
			$this->objPHPExcel->getActiveSheet()->setCellValue($this->getCol($i)."1",  $objRichText);

			$this->objPHPExcel->getActiveSheet()->getStyle($this->getCol($i)."1")
			->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()->setRGB(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKBLUE ) );
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($this->getCol($i))->setWidth(15);

		$i=1;
		foreach($header as $val){

			if($val["type"]=="grid"){
				continue;
			}

			$objRichText = new PHPExcel_RichText();
			//$objRichText->createText($header[$i]["name"]);
			$objPayable = $objRichText->createTextRun($val["name"]);
			$objPayable->getFont()->setBold(true);
			//$objPayable->getFont()->setItalic(true);
			$objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_WHITE ) );
			
			$this->objPHPExcel->getActiveSheet()->setCellValue($this->getCol($i)."1",  $objRichText);

			$this->objPHPExcel->getActiveSheet()->getStyle($this->getCol($i)."1")
			->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
			->getStartColor()->setRGB(new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_DARKBLUE ) );
			$this->objPHPExcel->getActiveSheet()->getColumnDimension($this->getCol($i))->setWidth(15);

			$format=PHPExcel_Style_NumberFormat::FORMAT_GENERAL;
			if($val["type"]=="number"){
				$format=PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00;
				$objPHPExcel->getActiveSheet()->getStyle($this->getCol($i).'2:'.$this->getCol($i)."1000")->getNumberFormat()
				->setFormatCode($format);
			}else if($val["type"]=="datetime"){
				$format=PHPExcel_Style_NumberFormat::FORMAT_DATE_YYYYMMDD2;				
				$objPHPExcel->getActiveSheet()->getStyle($this->getCol($i).'2:'.$this->getCol($i)."1000")->getNumberFormat()
				->setFormatCode($format);
			}else if($val["type"]=="check"){
				for($k=2;$k<=1000;$k++){
					$objValidation = $objPHPExcel->getActiveSheet()->getCell($this->getCol($i)."$k")->getDataValidation();
					$objValidation -> setType(PHPExcel_Cell_DataValidation::TYPE_LIST)  
				   -> setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)  
				   -> setAllowBlank(false)  
				   -> setShowInputMessage(true)  
				   -> setShowErrorMessage(true)  
				   -> setShowDropDown(true)  
				   -> setErrorTitle('输入的值有误')  
				   -> setError('您输入的值不在下拉框列表内.')
				   -> setFormula1('"是,否"'); 
				}
			}else if($val["type"]=="select"){
				
				$select="";
				$f=true;
				foreach($val["options"]["option"] as $option){
					
					if(!$f){
						$select=$select.",";
					}
					$f=false;
					$select=$select.$option["name"];
				}
				
				for($k=2;$k<=1000;$k++){
					$objValidation = $objPHPExcel->getActiveSheet()->getCell($this->getCol($i)."$k")->getDataValidation();
					$objValidation -> setType(PHPExcel_Cell_DataValidation::TYPE_LIST)  
				   -> setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
				   -> setAllowBlank(false)  
				   -> setShowInputMessage(true)  
				   -> setShowErrorMessage(true)  
				   -> setShowDropDown(true)  
				   -> setErrorTitle('输入的值有误')  
				   -> setError('您输入的值不在下拉框列表内.')
				   -> setFormula1('"'.$select.'"'); 
				}
			}
			
			$i++;
		}
		
		$this->objPHPExcel->getActiveSheet()->setTitle('sheet 1');
	}

	public function read($filename){
		$objPHPExcel=$this->objPHPExcel;
		$objPHPExcel = PHPExcel_IOFactory::load($filename);

		$head=array();
		$ret=array();

		foreach ($objPHPExcel->getWorksheetIterator() as $worksheet) {
			//echo 'Worksheet - ' , $worksheet->getTitle() , EOL;

			foreach ($worksheet->getRowIterator() as $row) {
				$r=array();
				$rowIndex=$row->getRowIndex();
				$cellIterator = $row->getCellIterator();
				$cellIterator->setIterateOnlyExistingCells(false); // Loop all cells, even if it is not set
				$cellindex=0;
				$arrrow=array();
				$rownotnull=false;
				foreach ($cellIterator as $cell) {
					if (!is_null($cell)) {
						$cellval=$cell->getCalculatedValue();
					}
					if($cellval!=""){
						if($rowIndex==1){
							$head[$cellindex]=$cellval;
						}
					}
					if($rowIndex>1){
						if($cellval!=""){
							$rownotnull=true;
						}
						$arrrow[$head[$cellindex]]=$cellval;
					}
					$cellindex++;
				}
				if($rownotnull){
					$ret[]=$arrrow;
				}
			}
		}
		return $ret;
	}

	public function download($filename){
		ob_end_clean();
		ob_start();
		// Redirect output to a client’s web browser (Excel2007)
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="'.$filename.'.xlsx"');
		header('Cache-Control: max-age=0');
		// If you're serving to IE 9, then the following may be needed
		header('Cache-Control: max-age=1');

		// If you're serving to IE over SSL, then the following may be needed
		header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
		header ('Pragma: public'); // HTTP/1.0

		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->save('php://output');
		exit;
	}

	public function getCol($a){
		$ret="";
		if($a>=26){
			$c=$a/26;
			$ret.=chr($c+64);
			$a=$a%26;
		}
		$ret.=chr($a+64+1);
		return $ret;
		
		return $ret;
	}

}

?>