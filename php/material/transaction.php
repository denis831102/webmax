<?php

if ( $curQuery && $countInsert_T == 1){								
	$bits = $this->setBits( $this->_PARAM[_opers], $this->_PARAM[_idPunkt], $id_T, 1);
				
	$this->mysqli->query( sprintf(				
		"INSERT INTO operation (id_V, id_M, id_T, count, price, id_Bu, id_cm, isMoveKassa, token)
		 VALUES %1\$s",
		/*1*/ implode(", ", $bits['arValues'])
	));
	$countInsert = $this->mysqli->affected_rows;
	$countInsert = ( $countInsert == -1 ? 0 : $countInsert );			
	
//			if ( $countOper == $countInsert){
		
		$this->res->data = [
			isSuccesfull => 1,	
			idT			 => $id_T,				
			message 	 => "Транзакція на {$countInsert} операцій пройшла вдало",									
		];		
//			}else {
//				$this->res->data = [
//					isSuccesfull => 0,									
//					message 	 => "З {$countOper} операцій пройшло вдало {$countInsert}",					
//				];
//			}			
} else {
	$this->res->data = [
		isSuccesfull => 0,									
		message => ( $countInsert_T == -1
			? "Транзакція не створена при відсутності операцій"
			: "Помилка створення транзакції"
		),				
	];
}	


// --------------------------------------------------------------------------------------------

if ( $curQuery && $countInsert_T == 1){	
	// 1. Старт транзакції
    $this->mysqli->begin_transaction();
	
	try {	
		
		// 2. UPDATE залишків						
		$bits = $this->setBits( $this->_PARAM[_opers], $this->_PARAM[_idPunkt], $id_T, 1);
		
		if ($bits === false) {
            throw new Exception("Помилка в setBits()");
        }
		
		// 3. INSERT операцій					
		$query =  sprintf(				
			"INSERT INTO operation (id_V, id_M, id_T, count, price, id_Bu, id_cm, isMoveKassa, token)
			 VALUES %1\$s",
			/*1*/ implode(", ", $bits['arValues'])
		);
		if (!$this->mysqli->query($query)) {
            throw new Exception("INSERT error: " . $this->mysqli->error);
        }
		$countInsert = $this->mysqli->affected_rows;
		$countInsert = ( $countInsert == -1 ? 0 : $countInsert );			
		
		// 4. Якщо все добре — коміт
		$this->mysqli->commit();
		
//			if ( $countOper == $countInsert){
			
		$this->res->data = [
			isSuccesfull => 1,	
			idT			 => $id_T,				
			message 	 => "Транзакція на {$countInsert} операцій пройшла вдало",									
		];		
//			}else {
//				$this->res->data = [
//					isSuccesfull => 0,									
//					message 	 => "З {$countOper} операцій пройшло вдало {$countInsert}",					
//				];
//			}			
	} catch (Exception $e) {
        
        // 5. Якщо щось впало — rollback
        $this->mysqli->rollback();

        $this->res->data = [
            isSuccesfull => 0,
            message => "Помилка транзакції: " . $e->getMessage(),
        ];
    } 			
} else {
	$this->res->data = [
		isSuccesfull => 0,									
		message => ( $countInsert_T == -1
			? "Транзакція не створена при відсутності операцій"
			: "Помилка створення транзакції"
		),				
	];
}	



?>