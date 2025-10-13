<?php   	
	include_once "PHPExcel.php";
    include_once "PHPExcel/Writer/Excel5.php"; 	
	include_once "PHPExcel/Reader/Excel5.php"; 	
			
Class clReport{	  		
	private $xlsReader 	 = null; 
	private $xlsWriter 	 = null; 						
	private $xls  	   	 = null; 
	
	// имя шаблона
	private $namePattern = 'ModelReport/patern_report_1C.xlsx'; 
	// имя отчета
	private $nameReport  = 'Report/patern_report_1C.xlsx';	 
	
    // конструктор класса с начальной инициализацией
	public function __construct($mysql, $param ){
		$this->mysqli = $mysql;
	   	$this->_PARAM = $param;
	   		
		$this->xlsReader = new PHPExcel_Reader_Excel2007();				
	}//______________________________________________________________________________________________________________________
	
	// деструктор класса
	function __destruct(){
		$this->mysqli->close();
	}//______________________________________________________________________________________________________________________
			
	// функция преобразования номера столбца в его имя 
	public function nameColumn($a){
		$b = 26;	
		$ostatok = $a % $b;
		$int 	 = ($a - $ostatok) / $b; 
	  	
	  	if ($int==0) return chr($a + 65);
	  	else return chr( $int + 64) . chr( $ostatok + 65);
	}//____________________________________________________________________________________________________________________	
	
	// функция 
	public function checkFilters($str){
		$needles = ["Лом_тяжелый", "Лом цветных металлов", "Вторсырье"];		
		
		foreach ($needles as $word) {
		    if (mb_stripos($str, $word) !== false) {
		        return $word;
		    }
		}
		    
		return '';		
	}//____________________________________________________________________________________________________________________	
		
	// функция для поиска двух дат
	private function regDate($str){		
		preg_match_all('/\d{2}\.\d{2}\.\d{4}/', $str, $matches);

		$dates = array_map(function($d) {
		    return DateTime::createFromFormat('d.m.Y', $d)->format('Y-m-d');
		}, $matches[0]);

		return $dates;
	}//____________________________________________________________________________________________________________________	
	
	// функция для поиска даты
	private function parseDate($str){		
		$str = trim($str);

	    // убираем "г." и слово "Период:" если есть
	    $str = preg_replace('/г\.?/iu', '', $str);
	    $str = preg_replace('/^Период:\s*/iu', '', $str);

	    // 1) формат дд.мм.гггг
	    if (preg_match('/(\d{1,2})\.(\d{1,2})\.(\d{4})/', $str, $m)) {
	        return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
	    }

	    // 2) формат гггг-мм-дд
	    if (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $str, $m)) {
	        return sprintf('%04d-%02d-%02d', $m[1], $m[2], $m[3]);
	    }

	    // 3) формат "1 октября 2025"
	    $months = [
	        'января' => 1, 'февраля' => 2, 'марта' => 3, 'апреля' => 4,
	        'мая' => 5, 'июня' => 6, 'июля' => 7, 'августа' => 8,
	        'сентября' => 9, 'октября' => 10, 'ноября' => 11, 'декабря' => 12,
	    ];

	    if (preg_match('/(\d{1,2})\s+([а-яё]+)\s+(\d{4})/iu', $str, $m)) {
	        $day   = (int)$m[1];
	        $month = $months[mb_strtolower($m[2], 'UTF-8')] ?? null;
	        $year  = (int)$m[3];
	        if ($month) {
	            return sprintf('%04d-%02d-%02d', $year, $month, $day);
	        }
	    }

	    // если не получилось распарсить
	    return null;
	}//____________________________________________________________________________________________________________________	
	
	// функция для поиска материала
	private function regMat($str){		
		preg_match_all('/\((.*?)\)/u', $str, $matches);

		return $matches[1];
	}//____________________________________________________________________________________________________________________	
	
	// массив с направлениями операций
	private function dirOper(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT operVid.* FROM operVid"			
		));									
		while(  $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
			$this->operVid[ $rec[id_V] ] = $rec[dir];
		};				
	}//_________________________________________________________________________
	
	// редактирование остатков из транзакции
	private function setBits( $arOper ){						
		$arValues = [];	
		$id_agent = 0;
		$this->dirOper();
		$dir = -1;		
				
		foreach( $arOper as $key => $oper ){									
			$this->mysqli->query( sprintf(				
			   "UPDATE tempBitsPunkt SET count = count + (%3\$f)
				WHERE id_P = %1\$d AND id_M = %2\$d",							
				/*1*/	$oper[id_P],				
				/*2*/	$oper[id_M],			
				/*3*/	( in_array( $oper[id_V], [4, 5] )
					?   ( $oper[new_count] * $oper[new_price] - 
						 $oper[old_count] * $oper[old_price] ) * $this->operVid[ $oper[id_V] ] * $dir
					:	$oper[d_count] * $this->operVid[ $oper[id_V] ] * $dir
				)				
			));									
		}		
		
	}//_________________________________________________________________________
	
	// получение ОТСТАТКОВ
	public function getBits($date, $matKategories){									
		$arPunkt = [];					
				
		$this->mysqli->query( sprintf(				
			"CREATE TEMPORARY TABLE tempBitsPunkt
			SELECT bits.id_M, bits.id_P, bits.count, 				
			 	material.name as name_M, material.unit,			 	
			 	matKategories.id_K as id_K, matKategories.name as name_K,
			 	matKategories.id_G,
			 	punkt.name as name_P
			FROM bits 				
				LEFT JOIN material ON bits.id_M = material.id_M
				LEFT JOIN matKategories ON material.id_K = matKategories.id_K
				LEFT JOIN punkt ON bits.id_P = punkt.id_P
			WHERE id_G != 1 AND matKategories.name_1C = '%1\$s'	
			ORDER BY id_G, id_K, id_M",			
			/*1*/	$matKategories								
		));
		
		// убираем из учета операции после заданной даты
		if( !empty($date) ){
			$cursorOper = $this->mysqli->query( sprintf(				
				"SELECT transaction.*, 
					operation.id_O, operation.count, operation.price, operation.id_M,  
					operation.id_V, operation.isMoveKassa							 	
				FROM transaction 
					LEFT JOIN operation ON transaction.id_T = operation.id_T									
				WHERE transaction.date > '%1\$s' ",
				/*1*/	date('Y-n-d', strtotime($date))															
			));		
			$arOpers = [];
			while( $rec = $cursorOper->fetch_array( MYSQLI_ASSOC ) ){			
				$arOpers[] = [
					id_M 	 	=> $rec[id_M],						
					id_V 	 	=> $rec[id_V],
					id_P 	 	=> $rec[id_P],							
					d_count 	=> $rec[count],						
					d_price 	=> $rec[price],
					old_count   => 0,
	            	old_price   => 0,
	            	new_count   => $rec[count],
	            	new_price   => $rec[price],	
	            	is_move_kassa => $rec[isMoveKassa],					
				];								
			}
			$this->setBits( $arOpers );
		}
		
		$cursor = $this->mysqli->query(
			"SELECT name_P, sum(count) AS count 
			FROM tempBitsPunkt 
			GROUP BY name_P"
		);
				
		if ( $cursor->num_rows > 0) {
			while(  $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
				$arPunkt[ trim($rec['name_P']) ] = $rec['count'];
			};		
		};		
		
		return [
			'arPunkt' => $arPunkt,
		];								
	}//____________________________________________________________________________________________________________________	
								
	// получение отчета "Щоденний звіт по ВЛАСНИМ ПУНКТАМ" 
	public function getReportEconomist(){		
//		$this->xls->setActiveSheetIndex(0);		
//		$sheet = $this->xls->getActiveSheet();
//		$arrKontrAgent = [
//			"1" => "Украина",
//			"3" => "Покупатель делового лома",
//		];		
		
		$this->xls = $this->xlsReader->load($this->namePattern); 				
		$this->xlsWriter = new PHPExcel_Writer_Excel2007($this->xls);
							
		$this->mysqli->query( sprintf("				
			UPDATE transaction SET isEdit = 0, isDel = 0, isLoadReport = 1
			WHERE transaction.id_T IN (%1\$s)",
			/*1*/	implode(", ", $this->_PARAM[_arTransaction])
		));
										
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT punkt.id_U AS idUser, users.PIB AS pib, punkt.name AS name_P, 
			     	transaction.id_T, transaction.date, transaction.time, transaction.comment,  
			     	operation.id_O, operation.count, operation.price, operation.id_Bu, operation.id_cm,
			      	operVid.id_V, operVid.name as name_V,
			      	material.name AS name_M,
			      	buyer.name AS name_B,
			      	CM.name AS name_CM,
			      	matKategories.vid_T	as vid_T 		      	
			FROM transaction 
			  LEFT JOIN operation ON transaction.id_T = operation.id_T
			  LEFT JOIN operVid ON operation.id_V = operVid.id_V
			  LEFT JOIN material ON operation.id_M = material.id_M
			  LEFT JOIN buyer ON operation.id_Bu = buyer.id_Bu
			  LEFT JOIN punkt AS CM ON operation.id_CM = CM.id_P			
			  LEFT JOIN punkt ON transaction.id_P = punkt.id_P
			  LEFT JOIN users ON punkt.id_U = users.id
			  LEFT JOIN matKategories ON material.id_K = matKategories.id_K
			WHERE transaction.id_T IN (%1\$s) %2\$s
			ORDER BY idUser ASC, punkt.id_P ASC, id_T ASC, id_O, date DESC, time DESC",			
			/*1*/	implode(", ", $this->_PARAM[_arTransaction]),
			/*2*/	( in_array($this->_PARAM[_checkOperation], [21, 22]) 
						? "AND operVid.id_V = 2 AND operation.id_" . ( $this->_PARAM[_checkOperation] == 21 ? 'CM' : 'Bu' ) . " > 0 "
						: ( $this->_PARAM[_checkOperation] == 6
							? "AND operVid.id_V IN (6, 7)"
							: ( $this->_PARAM[_checkOperation] > 0
								? "AND operVid.id_V = {$this->_PARAM[_checkOperation]}"
								: ""
							)
						)
					)								
		));
				
		if ( $cursor->num_rows > 0) {						
			$old = [id_T => 0];	
			$curSheet = $i = 0;
			$row = 1;
			$minDate = '01.01.2100';
			$maxDate = '01.01.1970';							
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_T => 0]);
																
				if( ($old[id_T] <> $rec[id_T]) && $old[id_T] > 0 ){
					$sheet = $this->xls->getSheet($curSheet);
					$num = $curSheet + 1;
					$sheet->setTitle("$num");
					
					$copySheet = $sheet->copy();		
					$copySheet->setTitle("пустая");
					$this->xls->addSheet($copySheet);
					//$this->xls->addSheet($copySheet, $this->xls->getIndex($sheet) + 1);
					
					$sheet = $this->xls->getSheet($curSheet);
					$sheet->fromArray($arrCell, ' ', 'A6');
					
					$sheet->setCellValue("C1", join("  ", [ date('d.m.Y', strtotime($old[date])), $old[time] ] )
					); 
					$sheet->setCellValue("F1", $old[name_P] );
					$sheet->setCellValue("B4", $old[comment] );
					
					switch ($this->_PARAM[_checkOperation]){
						case 1: {
							$sheet->setCellValue("B3",  "Контрагент");
							$sheet->setCellValue("C3",  "Украина"	);
							$sheet->setCellValue("F21", "для создания РКО");
							break;
						}
						case 21:{							
							$sheet->setCellValue("E1",  "Склад отправитель");
							$sheet->setCellValue("E2",  "Склад получатель");
							$sheet->setCellValue("F2",  $old[name_CM]);
							$sheet->setCellValue("F21", "");
							$sheet->setCellValue("E21", 0);
							break;
						}
						case 22:{
							$sheet->setCellValue("B3",  "Контрагент");
							$sheet->setCellValue("C3",  $old[name_B]	);
							$sheet->setCellValue("F21", "");
							$sheet->setCellValue("E21", 0);
							$sheet->setCellValue("E4",  "Вид транспорта");
							$sheet->setCellValue("F4",  $old[vid_T]);
							break;
						}
						case 3:{
							$sheet->setCellValue("B3",  "Контрагент");
							$sheet->setCellValue("C3",  "Покупатель делового лома"	);
							$sheet->setCellValue("F21", "для создания ПКО");
							$sheet->setCellValue("E4",  "Вид транспорта");
							$sheet->setCellValue("F4",  $old[vid_T]);
							break;	
						}
						case 5: {
							$sheet->setCellValue("B3",  "");
							$sheet->setCellValue("C3",  "");
							$sheet->setCellValue("F21", "для создания РКО");
							break;
						}						
						default:{						
						}
					}
										
					$arrCell = [];				
					$curSheet++;
					$row = 1;
					$minDate = (strtotime($minDate) > strtotime($old[date]) ? date('d.m.Y', strtotime($old[date])) : $minDate);
					$maxDate = (strtotime($maxDate) < strtotime($old[date]) ? date('d.m.Y', strtotime($old[date])) : $maxDate);												
				}
				
				if( !in_array($this->_PARAM[_checkOperation], [6, 7] ) ){
					$arrCell[] = [
						$row,				
						$rec[name_M],
						$rec[count],				
						$rec[price]																	
					];
				}
				else {
					$arrCell[] = [
						$row,				
						$rec[name_M],
						$rec[count],				
						$rec[price],
						'',
						( $rec[id_V] == 6
							? "комплектующие"
							: ( $rec[id_V] == 7
								? "комплект"
								: ""
							)
						)												
					];
				}			
				
				$old = $rec;
				$row++;	
				$i++;						
			};											
		};
		
//		$cursor->data_seek(0);
//		$rec = $cursor->fetch_array(MYSQLI_ASSOC);			
										
		$this->xlsWriter->save($this->nameReport);		
		$this->xls->disconnectWorksheets();  
		unset($this->xlsReader);
		unset($this->xlsWriter); 
		unset($this->xls);
		
		//$date = date("d.m.Y");
		$period = ($minDate == $maxDate ? $minDate : "{$minDate} - {$maxDate}");
		$pack = [	
			isSuccesfull => true,
			fileName 	 => "{$this->_PARAM[_nameOperation]} {$period}.xlsx", 
			//fileName 	 => basename($this->nameReport),
			mime	     => mime_content_type($this->nameReport),					
			content		 => base64_encode( file_get_contents($this->nameReport) ),						
		];		
		unlink($this->nameReport);
		
		return $pack;			
	}//____________________________________________________________________________________________________________________	
	
	// получение отчета "АНАЛІТИКА ПО НОМЕНКЛАТУРІ НА ВЛАСНИХ ПУНКТАХ"
	public function getReportAnalitikaResurs(){		
		
		$uploadDir 		= __DIR__ . '/Report/';
		$file_name  	= $_FILES['_file']["name"];	  			
		$file       	= $_FILES['_file']["tmp_name"]; //имя файла во временной папке на сервере
		$file_dir 		= "{$uploadDir}{$file_name}";
		
		$info = explode('.', $file_name); //pathinfo($file_name);
		$basename   = $info[0]; 		  //$info['filename'];   // имя файла без расширения
		$extension  = $info[1];           //$info['extension'];  // расширение файла
		
		$isExistFile	= false;
		$arPunkt		= [];
		$count_Punkt = $count_Error = 0;
								  	  
		if ( move_uploaded_file( $file, $file_dir ) ){  
			$isExistFile = true;	  
		    $this->xls = $this->xlsReader->load($file_dir); 				
			$this->xlsWriter = new PHPExcel_Writer_Excel2007($this->xls);
			
			$sheet = $this->xls->getSheet(0);
			
//			$arDate = $this->regDate( $sheet->getCell( "B2")->getValue());
//			$arMat = $this->regMat( $sheet->getCell( "B5")->getValue() );			
			
			$date = $this->parseDate( $sheet->getCell( "B2")->getValue());
			$dateUkr = date('d.m.Y', strtotime($date));		
			$matKategories = $this->checkFilters( $sheet->getCell( "B5")->getValue() );
						
			$sheet->setCellValue("E9", "{$matKategories} на {$dateUkr}" );
		
			$dataBits = $this->getBits($date, $matKategories);
			
			for ($i = 11; $i <= 150; $i++){
				$punkt = trim($sheet->getCell( "B{$i}")->getValue());
				$count_1C = trim($sheet->getCell( "C{$i}")->getValue());	
				
				if( strlen($punkt) > 0 ){
					$count_Punkt++;
					$count_WM = $dataBits['arPunkt'][$punkt];
					
					$sheet->setCellValue("E{$i}", $count_WM );
					
					if( $count_1C != $count_WM && $count_WM > 0){
						// красный цвет 
						$sheet->getStyle("E{$i}")->getFill()->applyFromArray(['type' => PHPExcel_Style_Fill::FILL_SOLID,  'startcolor' => ['rgb' => 'FF0000'] ]);
						$count_Error++;
						
						$sheet->setCellValue("F{$i}", ($count_WM - $count_1C) );
					}					 
				}				
				
				if($punkt == 'Итог') break;
			}				
			
			$this->xlsWriter->save($file_dir);		
			$this->xls->disconnectWorksheets();  
			unset($this->xlsReader);
			unset($this->xlsWriter); 			
		}
		unset($this->xls);
				
		$pack = [	
			isSuccesfull => $isExistFile,
			fileName 	 => "{$basename} (проаналізовано).{$extension}", 
			mime	     => mime_content_type($file_dir),					
			content		 => base64_encode( file_get_contents($file_dir) ),
			message		 => implode('<br>',	[								
								"<div style='font-size:13pt; font-weight: normal;'>Проаналізовано категорію <b>{$matKategories}</b> по {$dateUkr}." .
								" Загальна кількість пунктів {$count_Punkt}.",
								( $count_Error != 0 ? "Кількість з помилковими залишками <span style='color:#FF0000; font-weight: bold;'>{$count_Error}.</span>" : ""),
								"<BR>Файл '{$basename} (проаналізовано).{$extension}' збережено в директорії для завантаження.</div>"								
							]),										
		];
		unlink($file_dir);
		
		return $pack;			
	}//____________________________________________________________________________________________________________________	
	
	// получение отчета "АНАЛІТИКА ПО РУХУ ГРОШОВИХ КОШТІВ ВЛАСНИХ ПУНКТІВ"
	public function getReportAnalitikaMoney(){		
			
	}//____________________________________________________________________________________________________________________	
}?>