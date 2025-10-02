<?php  

Class clSession{	
	//public	$sessid;  	
	private  $deletedTime = 60 * 60 * 24 * 14; // (14 діб) життя сесії
	private  $blockTime   = 60 * 5;  // (5 хв.) довжина блокування
	private  $countProba  = 5; 		 // дозволена кількість сброб авторизації
						
	public function __construct(){	 
		//ini_set('session.use_strict_mode', 1);  
	   	$this->sessionCheck();	    		        
	}//______________________________________________________________________________________________________________________
	
	// функция запуска сессии с поддержкой управления сессиями на основе временны́х меток 
	public function sessionCheck(){	
		//session_start();
		
		if ( session_status() != PHP_SESSION_ACTIVE ) {							
	    	$this->sessionRegenerateId();
		}	
		// не разрешать слишком старый идентификатор сессии
		else if ( // !empty($_SESSION['deleted_time_session']) ||  
				  ( time() - $_SESSION['deleted_time_session'] ) > $this->deletedTime ) {				  	
			// session_destroy();
						
			// записываем данные сессии и завершаем её
		    session_write_close();
	        
	        $this->sessionRegenerateId();
	    }	        
	}//____________________________________________________________________________________________________________________
	
	// Функция обновления идентификатора сессии
	public function sessionRegenerateId(){						    	        
	    // убедимся, что PHP принимает пользовательский идентификатор сессии
	    // замечание: разработчики PHP рекомендуют включать опцию use_strict_mode, чтобы защитить приложение от уязвимостей
	    //ini_set('session.use_strict_mode', 0);

	    // устанавливаем новый пользовательский идентификатор сессии
	    $sessPref = session_create_id('s-');
	    session_id( $sessPref );
	    
	    //session_id('cli_test'); 

	    // запускаем сессию с пользовательским идентификатором
	    session_start();
	    
	    // устанавливаем временну́ю метку удаления данных текущей сессии.		    
	    $_SESSION['deleted_time_session'] = time();	    
	}//____________________________________________________________________________________________________________________	
	
	// 
	public function getState(){					
	   	return [
	   		'id' 		   => session_id(),	   		
	   		'time'  	   => (time() - $_SESSION['deleted_time_session']),	   		
	   		'isPermission' => $_SESSION['check_proba'] < $this->countProba,	   		
	   		'timeProba'    => (time() - $_SESSION['check_time']) ,	 
	   		'countProba'   => $this->countProba - $_SESSION['check_proba'],
	   	];	  
	}//____________________________________________________________________________________________________________________
	
	public function getId(){					
	   	return session_id();	  
	}//____________________________________________________________________________________________________________________
		
	// 
	public function setState(){	
		if ( empty($_SESSION['check_time']) ) {
			$_SESSION['check_time'] = time();	     	
		}
		
		if ( ( time() - $_SESSION['check_time'] ) > $this->blockTime ) {
			$_SESSION['check_proba'] = 0;
			$_SESSION['check_time']  = time();	     	
		}	
		else {
			$_SESSION['check_proba'] += 1;	    	
		}			   	
	}//____________________________________________________________________________________________________________________
				
}
?> 