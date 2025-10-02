<?php   	
//	header('Access-Control-Allow-Origin: *');	
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Token, Ecp');	
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
					
	$objServer = new clServer();
		
	if ($objServer->checkECP()){		
		$method = [ $objServer, $objServer->_PARAM['_method'] ];					
		echo is_callable( $method ) ? $method() : $objServer->error();		
	} else {		
		echo $objServer->error("Error! Access is denied");
	}	
				
Class clServer{	  
	private $DB 			= "ch015015b1_db_webmax";
	private $login 			= "ch015015b1_wmax";  
	private $password 		= "KZVxpDrq0I";  
	private $table_Schema 	= 'ch015015b1_db_webmax';  	
	private $countAccess 	= 11;  
	
	//private $ecp 			= 'c3875d07f44c422f3b3bc019c23e16ae';  
	private $key 			= 'denis';  
	private $headers		= [];
	private $countMinute	= 1;   
	
	private	$mysqli;
	
	private $deletedTime = 60 * 60 * 24 * 14; // (14 діб) життя сесії
	private $blockTime   = 60 * 10;  // (5 хв.) довжина блокування
	private $countProba  = 3; 		 // дозволена кількість сброб авторизації
	
	// cписок дозволених доменів
	private	$allowedOrigins = [
	    'https://webmax.lond.lg.ua1',
	    'http://localhost:8080',
	    'http://localhost:8081',
	];
	public  $arWord  = [ 
		1 => 'а', 2 => 'и', 3 => 'и', 4 => 'и', 5 => '', 6 => '', 7 => '', 8 => '', 9 => '',
		10 => '', 11 => '', 12 => '', 13 => '', 14 => '', 15 => ''
	];
	
	private $nameAccess  = [ 		
		[ key => "v1",  label => "доступ до користувачів" ],
        [ key => "v2",  label => "доступ до статусів" ],
        [ key => "v3",  label => "аналітики по ВП" ],
        [ key => "v4",  label => "щодений звіт" ],
        [ key => "v5",  label => "зміна транзакцій" ],
        [ key => "v6",  label => "видалення транзакцій" ],
        [ key => "v7",  label => "аналітика по коштам" ],
        [ key => "v8",  label => "перегляд операцій інших менеджерів" ],
        [ key => "v9",  label => "редагування операцій інших менеджерів" ],
        [ key => "v10", label => "редагування операцій без залежності від дати" ],
        [ key => "v11", label => "імпорт аналітики" ],        
	];
		
	// конструктор класса с начальной инициализацией
	public function __construct(){							
	   	$this->mysqli = new mysqli("localhost", $this->login, $this->password, $this->DB);	   	
	   	$this->headers = getallheaders();
	   		   	
	   	switch( $_SERVER['REQUEST_METHOD'] ){
	   		case 'POST':
	   			//$this->_PARAM = $_POST; 
	   			//$this->_PARAM = json_decode($_POST); 
	   			//$this->_PARAM = json_decode( file_get_contents('php://input'), true);	   			
	   			if (isset($_FILES) && count($_FILES)) {
		            // загрузка файла через FormData
		            $this->_PARAM = $_POST;
		        } else {
		            // загрузка JSON
		            $this->_PARAM = json_decode(file_get_contents('php://input'), true);
		        }
	   		break;
			case 'GET':
				$this->_PARAM = $_GET; 
	   		break;
	   		default:
	   			$this->_PARAM = file_get_contents('php://input');  	
		}	   		
	   	/*if ( isset( $_GET['_method'] ) ){		   	 
			$this->_PARAM = $_GET; //array_merge( $this->_PARAM, $_POST ); 
		}
		else {
			$this->_PARAM = json_decode( file_get_contents('php://input'), true);  	
		}*/
		
		$this->checkOrigin();		
	}//_____________________________________________________________________
	
	// деструктор класса
	function __destruct(){
		$this->mysqli->close(); 
	}//_____________________________________________________________________________
	
	// ERROR 
	public function error( $mes = '' ){					
	    $this->res->data = ( $mes == ''
	    	? "Error in name function '{$this->_PARAM['method']}' "	  		  	
	    	: $mes
	    );	
		return json_encode($this->res);		
	}//___________________________________________________________________________

	// проверка ecp
	public function checkECP(){			
		//$time = date("H:i:s");  
		//$hach = md5("{$time}-{$this->key}");
		$hach = [];		
					
		if ( $this->headers['ecp']){			
			//return $this->headers['ecp'] == $this->ecp;
			//return $this->headers['ecp'] == $hach;
			
			for ( $num = 0; $num <= $this->countMinute; $num++ ){
				$time   = date("H:i", strtotime("+{$num} minute"));
				$hach[] = md5("{$time}-{$this->key}");
				//$hach[] = "{$time}-{$this->key}";
			}
			
			return in_array($this->headers['ecp'], $hach);
		} else {
			return false;
		}				
	}//_____________________________________________________________________________
	
	// проверка ORIGIN
	public function checkOrigin(){	
		// проверяем, есть ли Origin в запросе
		if (isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $this->allowedOrigins)) {
		    header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
		    header("Access-Control-Allow-Credentials: true"); // Разрешаем куки/сессии
		    header("Vary: Origin"); // Чтобы кэш не мешал разным доменам
		}			
	}//_____________________________________________________________________________
	
	// проверка токена
	public function checkToken(){		
		if ($this->headers['token']){
			$token =  explode(':', base64_decode($this->headers['token']));					
						
			$cursor = $this->mysqli->query( sprintf(
				"SELECT users.id FROM users
				WHERE login = \"%1\$s\" AND password = \"%2\$s\" ",
				/*1*/	$this->mysqli->real_escape_string( $token[0] ),	
				/*2*/	$this->mysqli->real_escape_string( $token[1] )									
			));	
			
			return ($cursor->num_rows == 1);
		} else {
			return false;
		}						
	}//_____________________________________________________________________________
	
	public function generateToken($length = 12) {
	    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	    $password = '';
	    for ($i = 0; $i < $length; $i++) {
	        $password .= $chars[random_int(0, strlen($chars) - 1)];
	    }
	    return $password;
	}
	
	// 1.1 запрос к базе на получение данных таблицы
	private function queryCursor( $tabl ){											
		$textQuery = sprintf("				
			SELECT * FROM %1\$s",
			/*1*/	$tabl									
		);			
		$cursor = $this->mysqli->query( $textQuery );
		
		return array( 'cursor' 	 => $cursor, 
					  'num_rows' => $cursor->num_rows,  					
		);				 				
	}//_____________________________________________________________________________
	
	// 2.1 проверка авторизации ПОЛЬЗОВАТЕЛЯ 
	public function checkAuthenticated(){		
		if( isset($this->_PARAM[_login]) ){
			$cursor = $this->mysqli->query( sprintf(
				"SELECT users.id as idUser, users.PIB, users.status,  status.* 
				FROM users LEFT JOIN status ON users.status = status.id
				WHERE login = \"%1\$s\" AND password = \"%2\$s\" ",
				/*1*/	$this->mysqli->real_escape_string( $this->_PARAM[_login] ),	
				/*2*/	$this->mysqli->real_escape_string( $this->_PARAM[_password] )									
			));	
		}
		
		if ( isset($this->_PARAM[_login]) && $cursor->num_rows == 1 ) {
			$rec = $cursor->fetch_array( MYSQLI_ASSOC );	
			
			$listAccess = [];
			for($i = 1; $i <= $this->countAccess; $i++){
				$listAccess[$i] = $rec["v{$i}"];
			}
			
			$chDate = $this->changeDateUser($rec[idUser], $rec[status]);					
			
			$this->res->data = [
				isSuccesfull => $chDate[count],
				id 	 	 	 => $rec[idUser],
				idStatus 	 => $rec[status],
				nameStatus	 => $rec[name],
				PIB 	 	 => $rec[PIB],				
				listAccess 	 => $listAccess,								
			];			
		}
		else {						
			$this->sessionStart();
			$this->setState();
			$state = $this->getState();
									
			$this->res->data = [
				'isSuccesfull' => 0,
				//'PHPSESSID'    => $state['id'],
				'answer'	   => [
					'message'  => implode( '<br>', [						
						"Упс, логін чи пароль не вірний.", 
						"Вхід заборонено!",
						( $state['isPermission']
							? "Для входу в кабінет залишилось {$state['countProba']} спроб{$this->arWord[$state['countProba']]}"
							: 'Спроби авторизації вичерпані.'
						),						
					]),
					'status'	=> $state['isPermission'],								
				],																
			];
		}
		
		return json_encode( $this->res->data, JSON_UNESCAPED_UNICODE);		
	}//______________________________________________________________
	
	// 2.2 запуск сессии с поддержкой управления сессиями на основе временны́х меток 
	public function sessionStart(){	
		ini_set('session.use_strict_mode', 1);
		session_start();
		
		if ( session_status() != PHP_SESSION_ACTIVE ) {							
	    	$this->sessionRegenerateId();
		}	
		// не разрешать слишком старый идентификатор сессии
		else if ( //!isset($_SESSION['deleted_time_session']) ||  
				  ( time() - $_SESSION['deleted_time_session'] ) > $this->deletedTime ) {				  	
			session_destroy();
						
			// записываем данные сессии и завершаем её
		    session_write_close();
	        
	        $this->sessionRegenerateId();
	    }	        
	}//____________________________________________________________________________________________________________________
	
	// 2.3 обновление идентификатора сессии
	public function sessionRegenerateId(){						    	        
	    // убедимся, что PHP принимает пользовательский идентификатор сессии
	    // замечание: разработчики PHP рекомендуют включать опцию use_strict_mode, чтобы защитить приложение от уязвимостей
	    ini_set('session.use_strict_mode', 0);

	    // устанавливаем новый пользовательский идентификатор сессии
	    $sessPref = session_create_id('s-');
	    session_id( $sessPref );
	    
	    // запускаем сессию с пользовательским идентификатором
	    session_start();
	    
	    // устанавливаем временну́ю метку удаления данных текущей сессии.		    
	    $_SESSION['deleted_time_session'] = time();	    
	}//____________________________________________________________________________________________________________________	
	
	// 2.4 отримати параметрів сесії
	public function getState(){					
	   	return [
	   		'id' 		   => session_id(),	   		
	   		'time'  	   => (time() - $_SESSION['deleted_time_session']),	   		
	   		'isPermission' => $_SESSION['check_proba'] < $this->countProba,	   		
	   		'timeProba'    => (time() - $_SESSION['check_time']) ,	 
	   		'countProba'   => $this->countProba - $_SESSION['check_proba'],
	   	];	  
	}//____________________________________________________________________________________________________________________
		
	// 2.5 зміна параметрів сесії
	public function setState(){			
		if ( !isset($_SESSION['check_time']) ||
			 ( time() - $_SESSION['check_time'] ) > $this->blockTime 
		){
			$_SESSION['check_proba'] = 0;
			$_SESSION['check_time']  = time();	     	
		}	
		else {
			$_SESSION['check_proba'] += 1;	    	
		}			   	
	}//____________________________________________________________________________________________________________________
		
	// 3.1 получение данных таблицы ПОЛЬЗОВАТЕЛЕЙ
	public function getUsers(){							
		//$queryUser  = $this->queryCursor( 'users' );															
		// запрос к базе -  получение данных 
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT users.*, 
					status.name, status.id as idStatus, 
					punkt.name as namePunkt, punkt.adres
			FROM users 
				LEFT JOIN status ON users.status = status.id
				LEFT JOIN punkt ON users.id = punkt.id_U
			ORDER BY users.PIB"
		));
		
		$this->res->data 	= [];					
		
		if ( $cursor->num_rows > 0) {
			$i = 0;			
			$old = [id => 0];
			$listPunkt = [];
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id => 0]);
				
				if($old[id] <> $rec[id] && $old[id] > 0 ){
					$this->res->data[] = [
						id 	 	 	=> $old[id],
						PIB 	 	=> $old[PIB],
						login 	 	=> $old[login],
						password 	=> $old[password],
						nameStatus 	=> $old[name],
						idStatus 	=> $old[idStatus],
						date 	 	=> date('d.m.Y', strtotime($old[date])),
						time 	 	=> $old[time],
						listPunkt 	=> count($listPunkt) > 0 ? $listPunkt : [],
					];
					$listPunkt = [];					
				}
				if (strlen( $rec[namePunkt] ) > 0 ){
					$listPunkt[] = [
						namePunkt  => $rec[namePunkt],						
						adres 	   => $rec[adres],						
					];	
				}				
				$old = $rec;
				$i++;
			};	
		};		
		
		return json_encode( $this->res->data );								
	}//***********************************  3 ПОЛЬЗОВАТЕЛИ ********************************
		
	// 3.2 запрос на изменение данных ПОЛЬЗОВАТЕЛЯ
	public function changeDateUser( $id = 0, $status = 0 ){											
		$textQuery = sprintf("				
			UPDATE users SET %2\$s %3\$s %4\$s
			WHERE id = %1\$d",
			/*1*/	$this->_PARAM[_id] ? $this->_PARAM[_id] : $id,				
			/*2*/	$this->_PARAM[_date]  
						? " date = '{$this->_PARAM[_date]}', time = '{$this->_PARAM[_time]}' " 
						: '',
			/*3*/	$id == 0 
						? ($this->_PARAM[_date] ? ',' : ' ') .
						  " pib = '{$this->_PARAM[_pib]}', login = '{$this->_PARAM[_login]}', password = '{$this->_PARAM[_password]}'" 
						: '',
			/*4*/	$this->_PARAM[_idStatus] 
						? ", status = {$this->_PARAM[_idStatus]}" 
						: ", status = {$status}" 
		);			
		$this->mysqli->query( $textQuery );
		$countUpdate = $this->mysqli->affected_rows;
				
		if ($id == 0){		
			$this->res->data = [
				isSuccesfull => ($countUpdate == 1),								
			];
			return json_encode( $this->res->data );	
		}
		else{			
			return [
				count 		=> ($countUpdate ? 1 : 0),
				//textQuery	=> $textQuery	
			];
		}	
	}//______________________________________________
	
	// 3.3 запрос на добавление ПОЛЬЗОВАТЕЛЯ
	public function addUser( $id = 0 ){											
		$textQuery = sprintf("				
			INSERT INTO users (PIB, login, password, status)
			VALUE (\"%1\$s\", \"%2\$s\", \"%3\$s\", %4\$d )",
			/*1*/	$this->_PARAM[_pib],
			/*2*/	$this->_PARAM[_login],
			/*3*/	$this->_PARAM[_password],
			/*4*/	$this->_PARAM[_idStatus]			
		);			
		$this->mysqli->query( $textQuery );
		$countInsert = $this->mysqli->affected_rows;
		$id = $this->mysqli->insert_id;
		
		if ( $countInsert == 1){
			$this->res->data = [
				isSuccesfull => 1,	
				user => [
					id 			=> $id,
					PIB 		=> $this->_PARAM[_pib],
					login 		=> $this->_PARAM[_login],
					password 	=> $this->_PARAM[_password],
					nameStatus 	=> $this->_PARAM[_nameStatus],
					idStatus 	=> $this->_PARAM[_idStatus],
					listPunkt   => [],
					date		=> '',
					time		=> ''					
				]							
			];	
		} else {
			$this->res->data = [
				isSuccesfull => 0,									
			];
		}		
		
		return json_encode( $this->res->data );			
	}//__________________________________________________________________
	
	// 3.4 запрос на удаление ПОЛЬЗОВАТЕЛЯ
	public function delUser( $id = 0 ){											
		$textQuery = sprintf("				
			DELETE FROM users 
			WHERE id = %1\$d",
			/*1*/	$this->_PARAM[_id]
		);			
		$this->mysqli->query( $textQuery );
		$countDelete = $this->mysqli->affected_rows;
		
		if ($id == 0){		
			$this->res->data = [
				isSuccesfull => ($countDelete == 1),					
			];
			return json_encode( $this->res->data );	
		}
		else{
			return ($countDelete ? 1 : 0);
		}	
	}//__________________________________________________________________
	
	// 4.1 получение данных таблицы СТАТУСОВ
	public function getStatuses(){						
		//$queryTable  = $this->queryCursor( 'status' );	
		
		// запрос к базе -  получение данных 		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT status.*, users.PIB, users.date, users.time 
			FROM status 
				LEFT JOIN users ON users.status = status.id
			ORDER BY status.id"
		));
			
		$this->res->arStatus = [];					
		
		if ( $cursor->num_rows > 0) {
			$i = 0;			
			$old = [id => 0];
			$listUsers = [];
			while( $i <= $cursor->num_rows ){								
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id => 0]);
								
				if($old['id'] <> $rec['id'] && $old['id'] > 0 ){
					
					$listAccess = [];
					for($j = 1; $j <= $this->countAccess; $j++){
						$listAccess["v{$j}"] = $old["v{$j}"];
					}
					
					$this->res->arStatus[] = [
						id 	  	 	=> $old['id'],
						nameStatus 	=> $old['name'],
						listUser 	=> count($listUsers) > 0 ? $listUsers : [],
						listAccess	=> $listAccess,						
					];	
					
					$listUsers = [];					
				}
				if (strlen( $rec['PIB'] ) > 0 ){
					$listUsers[] = [
						pib  => $rec['PIB'],
						date => date('d.m.Y', strtotime($rec['date'])),
						time => $rec['time']
					];	
				}				
				$old = $rec;
				$i++;
			};			
		};	
		
		$this->res->nameAccess = $this->nameAccess;	
		
		return json_encode( $this->res );								
	}//*********************************  4 СТАТУСЫ ****************************************
	
	// 4.2 изменение данных СТАТУСА
	public function setStatus(){					
		$textQuery = sprintf("				
			UPDATE status SET %2\$s %3\$s
			WHERE id = %1\$d",
			/*1*/	$this->_PARAM[_id],				
			/*2*/	$this->_PARAM[_name]  ? " name = '{$this->_PARAM[_name]}' " : '',			
			/*3*/	$this->_PARAM[_atr]  ? " {$this->_PARAM[_atr]} = {$this->_PARAM[_val]} " : ''
		);			
		$this->mysqli->query( $textQuery );
		$countUpdate = $this->mysqli->affected_rows;
		
		$this->res->data = [];					
		
		$this->res->data = [
			isSuccesfull => ($countUpdate == 1),					
//			textQuery => $textQuery,					
			countUpdate => $countUpdate,
		];
		
		return json_encode( $this->res->data );							
	}//________________________________________________________________________
	
	// 4.3 запрос на добавление CТАТУСА
	public function addStatus( $id = 0 ){											
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT status.* FROM status
			 WHERE status.name = \"%1\$s\"",
			/*1*/	$this->_PARAM[_name]
		));		
		
		if ( $cursor->num_rows == 0 ){
			$textQuery = sprintf("				
				INSERT INTO status (name)
				VALUE (\"%1\$s\")",
				/*1*/	$this->_PARAM[_name]
			);			
			$this->mysqli->query( $textQuery );
			$countInsert = $this->mysqli->affected_rows;
			$id = $this->mysqli->insert_id;				
					
		} else {
			$countInsert = 0;			
		}	
		
		if ( $countInsert == 1){
			$this->res->data = [
				isSuccesfull => 1,					
				id 			 => $id,
				name 		 => $this->_PARAM[_name],				
				message		 => 'Новий cтатус додано',
			];	
		} else {
			$this->res->data = [
				isSuccesfull => 0,
				message		 => 'Cтатус не додано',
			];
		}	
		
		return json_encode( $this->res->data );			
	}//_______________________________________________________________
	
	// 4.4 запрос на удаление CТАТУСА
	public function delStatus( $id = 0 ){		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT users.*	FROM users
			 WHERE users.status = %1\$d",
			/*1*/	$this->_PARAM[_id]
		));		
				
		if ( $cursor->num_rows == 0 ){
			$textQuery = sprintf(				
				"DELETE FROM status WHERE id = %1\$d",
				/*1*/	$this->_PARAM[_id]
			);			
			$this->mysqli->query( $textQuery );
			$countDelete = $this->mysqli->affected_rows;	
			$message = $countDelete == 1 ? "Cтатус успішно видалено" : "Помилка видалення статусу";
		} else {
			$countDelete = 0;
			$num_rows = $cursor->num_rows;
			$end1 = ($num_rows == 1 ? 'го' : ($num_rows > 1 && $num_rows < 5 ? 'х' : 'и'));
			$end2 = ($num_rows == 1 ? 'а' : 'ів');
			$message = "Заборонено видаляти статус визначений у {$cursor->num_rows}-{$end1} користувач{$end2}";
		}		
				
		if ($id == 0){		
			$this->res->data = [
				isSuccesfull => ($countDelete == 1),					
				message 	 => $message,
			];
			return json_encode( $this->res->data );	
		}
		else{
			return ($countDelete ? 1 : 0);
		}	
	}//_______________________________________________________________
	
	// 5.1 получение данных таблицы ПУНКТ
	public function getPunkt(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT punkt.*, users.PIB
			FROM punkt LEFT JOIN users ON users.id = punkt.id_U
			ORDER BY punkt.id_P"
		));
			
		$this->res->data = [];					
		
		if ( $cursor->num_rows > 0) {		
			while(  $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
				$this->res->data[] = [
					id 		=> $rec[id_P],
					name	=> $rec[name],						
					adres	=> $rec[adres],						
					pib		=> $rec[PIB],
					typeP	=> $rec[typeP]						
				];								
			};			
		};				
		return json_encode( $this->res->data );								
	}//*************************************  5 ПУНКТ **************************************
	
	// 5.2 получение данных ПУНКТОВ текущего пользователя
	public function getPunktCur(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT punkt.*
			FROM punkt
			WHERE punkt.id_U = %1\$d
			ORDER BY punkt.id_P",
			/*1*/	$this->_PARAM[_id_U]
		));
			
		$this->res->data = [];					
		
		if ( $cursor->num_rows > 0) {		
			while(  $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
				$this->res->data[] = [
					id 		=> $rec[id_P],
					name	=> $rec[name],						
					adres	=> $rec[adres],											
				];								
			};			
		};				
		return json_encode( $this->res->data );								
	}//_________________________________________________________________________	
	
	// 5.3 изменение данных ПУНКТ
	public function setPunkt(){							
		$this->mysqli->query( sprintf(
			"UPDATE punkt SET id_U = %2\$d, name = \"%3\$s\", adres = \"%4\$s\", typeP = \"%5\$s\"
			WHERE id_P = %1\$d",
			/*1*/	$this->_PARAM[_id],				
			/*2*/	$this->_PARAM[_idU],  
			/*3*/	$this->_PARAM[_name],
			/*4*/	$this->_PARAM[_adres],
			/*5*/	$this->_PARAM[_typeP]
		));
		$countUpdate = $this->mysqli->affected_rows;
		
		// для теста
		//$countAddBits = $this->addBitsNewPunkr($this->_PARAM[_id]);
		
		$this->res->data = [];							
		$this->res->data = [
			isSuccesfull => ($countUpdate == 1),								
			countUpdate  => $countUpdate						
		];
		
		return json_encode( $this->res->data );							
	}//_________________________________________________________________________	
	
	// 5.4 запрос на добавление ПУНКТ
	public function addPunkt( $id = 0 ){											
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT punkt.* FROM punkt
			 WHERE punkt.name = \"%1\$s\"",
			/*1*/	$this->_PARAM[_name]
		));		
		
		if ( $cursor->num_rows == 0 ){
			$textQuery = sprintf("				
				INSERT INTO punkt (id_U, name, adres, typeP)
				VALUE (%1\$d, \"%2\$s\", \"%3\$s\", \"%4\$s\")",
				/*1*/	$this->_PARAM[_idU],
				/*2*/	$this->_PARAM[_name],
				/*3*/	$this->_PARAM[_adres],
				/*4*/	$this->_PARAM[_typeP]
			);			
			$this->mysqli->query( $textQuery );
			$countInsert = $this->mysqli->affected_rows;
			$id = $this->mysqli->insert_id;				
					
		} else {
			$countInsert = 0;			
		}	
		
		if ( $countInsert == 1){
			$countAddBits = $this->addBitsNewPunkr($id);
			
			$this->res->data = [
				isSuccesfull => 1,					
				idP			 => $id,
				name 		 => $this->_PARAM[_name],				
				adres 		 => $this->_PARAM[_adres],				
				message		 => 'Новий пункт додано',
			];	
		} else {
			$this->res->data = [
				isSuccesfull => 0,
				message		 => 'Пункт не додано',
			];
		}	
		
		return json_encode( $this->res->data );			
	}//________________________________________________________________
	
	// 5.5 запрос на удаление  ПУНКТ
	public function delPunkt( $id = 0 ){		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT bits.*	FROM bits
			 WHERE bits.id_P = %1\$d AND bits.count != 0",
			/*1*/	$this->_PARAM[_id]
		));		
				
		if ( $cursor->num_rows == 0 ){
			$textQuery = sprintf(				
				"DELETE punkt, bits FROM punkt 
				 LEFT JOIN bits ON bits.id_P = punkt.id_P
				 WHERE bits.id_P = %1\$d",
				/*1*/	$this->_PARAM[_id]
			);			
			$this->mysqli->query( $textQuery );
			$countDelete = $this->mysqli->affected_rows;	
			$message = $countDelete >= 1 ? "Пункт успішно видалено" : "Помилка видалення пункту";			
		} else {
			$countDelete = 0;
			$num_rows = $cursor->num_rows;			
			$message = "Заборонено видаляти пункт з залишками на складі";
		}		
				
		if ($id == 0){		
			$this->res->data = [
				isSuccesfull => ($countDelete >= 1),					
				message 	 => $message,
			];
			return json_encode( $this->res->data );	
		}
		else{
			return ($countDelete ? 1 : 0);
		}	
	}//________________________________________________________________
	
	// 6.1 получение данных таблицы МАТЕРИАЛ
	public function getMaterial(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT material.id_M as id_M, material.name as name_M, material.unit, material.isLessening, 
					matKategories.id_K as id_K, matKategories.name as name_K 
					%2\$s
			FROM material LEFT JOIN matKategories ON material.id_K = matKategories.id_K 
			    %1\$s
			ORDER BY material.id_M, matKategories.id_K",
			/*1*/	isset($this->_PARAM[_idPunkt])
				? " LEFT JOIN bits ON  material.id_M = bits.id_M AND bits.id_P = {$this->_PARAM[_idPunkt]} "
				: '',
			/*2*/	isset($this->_PARAM[_idPunkt])
				? ' , bits.count'
				: ''	 	
		));
			
		$this->res->data = [];					
		
		if ( $cursor->num_rows > 0) {		
			while( $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
				$this->res->data[] = [
					id_K	=> $rec[id_K],
					name_K	=> $rec[name_K],						
					id_M	=> $rec[id_M],
					name_M	=> $rec[name_M],											
					unit	=> $rec[unit],
					isLessening	=> $rec[isLessening],
					count	=> isset($this->_PARAM[_idPunkt]) ? $rec[count] : 0
				];								
			};			
		};				
		return json_encode( $this->res->data );								
	}//*********************************  6 МАТЕРИАЛ  **************************************
	
	// 6.2 изменение данных  МАТЕРИАЛ
	public function setMaterial(){									
		$this->mysqli->query( sprintf("				
			UPDATE material SET name = \"%2\$s\", unit = \"%3\$s\", isLessening = %4\$d
			WHERE id_M = %1\$d",
			/*1*/	$this->_PARAM[_id_M],							
			/*2*/	$this->_PARAM[_name_M],
			/*3*/	$this->_PARAM[_unit],
			/*4*/	$this->_PARAM[_isLessening]
		));
		$countUpdate = $this->mysqli->affected_rows;
		
		$this->res->data = [];					
		
		// для теста
		//$countAddBits = $this->addBitsNewMaterial($this->_PARAM[_id_M]);
		
		$this->res->data = [
			isSuccesfull => ($countUpdate == 1),							
			countUpdate => $countUpdate,
		];
		
		return json_encode( $this->res->data );							
	}//_________________________________________________________________________	
	
	// 6.3 запрос на добавление  МАТЕРИАЛ
	public function addMaterial( $id = 0 ){											
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT material.* FROM material
			 WHERE material.name = \"%1\$s\"",
			/*1*/	$this->_PARAM[_name_M]
		));		
		
		if ( $cursor->num_rows == 0 ){
			$textQuery = sprintf("				
				INSERT INTO material (id_K, name, unit, isLessening)
				VALUE (%1\$d, \"%2\$s\", \"%3\$s\", %4\$d )",				
				/*1*/	$this->_PARAM[_id_K],
				/*2*/	$this->_PARAM[_name_M],				
				/*3*/	$this->_PARAM[_unit],
				/*4*/	$this->_PARAM[_isLessening]
			);			
			$this->mysqli->query( $textQuery );
			$countInsert = $this->mysqli->affected_rows;
			$id = $this->mysqli->insert_id;				
					
		} else {
			$countInsert = 0;			
		}	
		
		if ( $countInsert == 1){
			$countAddBits = $this->addBitsNewMaterial($id);
			
			$this->res->data = [
				isSuccesfull => 1,					
				id_M		 => $id,
				name 		 => $this->_PARAM[_name],								
				message		 => 'Новий матеріал додано',
			];	
		} else {
			$this->res->data = [
				isSuccesfull => 0,
				message		 => 'Матеріал не додано',
			];
		}	
		
		return json_encode( $this->res->data );			
	}//________________________________________________________________
	
	// 6.4 запрос на удаление  МАТЕРИАЛ
	public function delMaterial( $id = 0 ){		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT bits.* FROM bits
			 WHERE bits.id_M = %1\$d AND bits.count != 0",
			/*1*/	$this->_PARAM[_id_M]
		));		
				
		if ( $cursor->num_rows == 0 ){
			$this->mysqli->query( sprintf(				
				"DELETE bits, material FROM material 
				LEFT JOIN bits ON bits.id_M = material.id_M
				WHERE material.id_M = %1\$d",
				/*1*/	$this->_PARAM[_id_M]
			));
			$countDelete = $this->mysqli->affected_rows;	
			$message = $countDelete > 0 ? "Матеріал успішно видалено" : "Помилка видалення матеріалу";
		} else {
			$countDelete = 0;						
			$message = "Заборонено видаляти матеріал з залишками на складі ({$cursor->num_rows})";
		}		
				
		if ($id == 0){		
			$this->res->data = [
				isSuccesfull => $countDelete > 0,					
				message 	 => $message,
			];
			return json_encode( $this->res->data );	
		}
		else{
			return ($countDelete ? 1 : 0);
		}	
	}//________________________________________________________________
	
	// 6.6 получение данных таблицы КАТЕГОРИЙ МАТЕРИАЛ
	public function getKategories(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT matKategories.id_K, matKategories.name as name_K, 
					material.id_M, material.name as name_M, material.unit
			FROM matKategories 
				LEFT JOIN material ON matKategories.id_K = material.id_K
			ORDER BY matKategories.id_K, material.id_M"
		));
			
		$this->res->data = [];					
		
		if ( $cursor->num_rows > 0) {
			$i = 0;			
			$old = [id_K => 0];
			$listMaterial = [];
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_K => 0]);
				
				if($old[id_K] <> $rec[id_K] && $old[id_K] > 0 ){
					$this->res->data[] = [
						id_K	=> $old[id_K],
						name_K	=> $old[name_K],						
						listMaterial=> count($listMaterial) > 0 ? $listMaterial : [],
					];
					$listMaterial = [];					
				}
				if (strlen( $rec[name_M] ) > 0 ){
					$listMaterial[] = [
						id_M    => $rec[id_M],						
						name_M  => $rec[name_M],
						unit 	=> $rec[unit],
					];	
				}				
				$old = $rec;
				$i++;
			};	
		};		
					
		return json_encode( $this->res->data );								
	}//_________________________________________________________________________	
	
	// 7.1 получение списка ОПЕРАЦИЙ
	public function getOperation(){											
		// запрос к базе -  получение данных 
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT operVid.id_V as idOper,   	    operVid.name as nameOper, 	operVid.dir, 
					matKategories.id_K as idKateg, 	matKategories.name as nameKateg, 										
					material.id_M as idMater,   	material.name as nameMater, material.unit,
					bits.count  
			FROM operVid 
				LEFT JOIN okRelation ON operVid.id_V = okRelation.id_V
				LEFT JOIN matKategories ON okRelation.id_K = matKategories.id_K
				LEFT JOIN material ON matKategories.id_K = material.id_K
				LEFT JOIN bits ON  material.id_M = bits.id_M
			WHERE bits.id_P = %1\$d AND !(material.id_M IN(40, 80))	
			ORDER BY operVid.id_V, matKategories.id_K, material.id_M",
			/*1*/	$this->_PARAM[_id_P]							
		));
		
		$this->res->data 	= [];					
		
		if ( $cursor->num_rows > 0) {
			$i = $num_K = $num_M = $num_O = 0;
			$old = [idOper => 0];
			$listKateg = [];
			$listMaterial = [];
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [idOper => 0]);
								
				if( ($old[idKateg] <> $rec[idKateg] || ( $old[idOper] <> $rec[idOper] && $old[idOper] > 0) ) 
					&& strlen( $old[nameKateg] ) > 0 
				){
					$listKateg[] = [
						label 	=> $old[nameKateg],
						value 	=> [	
							id  => $old[idKateg],
							num => $num_K++,
						],	
						children => count($listMaterial) > 0 ? $listMaterial : [],
					];
					$listMaterial = [];	 
					$num_M = 0;			
				}	
				
				if (strlen( $rec[nameMater] ) > 0 ){
					$listMaterial[] = [
						label   => $rec[nameMater],												
						value   => [	
							id  => $rec[idMater],
							num => $num_M++,							
							count => round( $rec[count], 3),
							unit  => $rec[unit],
							dir   => $rec[dir],							
						],	
						check => ($rec[idMater] == 5 ? true : false ),
					];	
				}	
				
				if( ($old[idOper] <> $rec[idOper]) && $old[idOper] > 0 ){
					$this->res->data[] = [
						label 	=> $old[nameOper],						
						value 	=> [	
							id  => $old[idOper],
							num => $num_O++,
						],	
						children => count($listKateg) > 0 ? $listKateg : [],
					];
					$listKateg = [];	
					$num_K = 0;												
				}			
				
				$old = $rec;
				$i++;
			};	
		};		
		
		return json_encode( $this->res->data );								
	}//*********************************  7 ОПЕРАЦИИ ***************************************
	
	// 7.2 удаление списка ОПЕРАЦИЙ (при редактировании транзакции)
	public function delOperation( $listDel, $id_P, $id_T ){
		
		$listDelFilter = $arDel = [];
		foreach( $listDel as $key => $oper ){
			if( $oper[is_move_kassa] != -1 ){
				$listDelFilter[] = $oper;
			}
			$arDel[] = $oper[id_O];						
		}
		
		$this->setBits( $listDelFilter, $id_P, $id_T, -1);
								
		$this->mysqli->query( sprintf(				
			"DELETE operation
			 FROM operation
			 WHERE id_O IN (%1\$s)",
			/*1*/	implode(', ', $arDel)
		));
		$countDelete = $this->mysqli->affected_rows;	
		$message = $countDelete == count($listDel) ? "Операції ({$countDelete}) успішно видалено" : "Помилка видалення операцій";
		
		return $message;		
	}//________________________________________________________________
	
	// 7.3 добавление списка ОПЕРАЦИЙ (при редактировании транзакции)
	public function addOperation( $listAdd, $id_P, $id_T ){											
									
		$bits = $this->setBits( $listAdd, $id_P, $id_T, 1);
							
		$this->mysqli->query( sprintf(				
			"INSERT INTO operation (id_V, id_M, id_T, count, price, id_Bu, id_cm, isMoveKassa, token)
			 VALUES %1\$s",
			/*1*/ implode(", ", $bits[arValues])
		));
		$countInsert_O = $this->mysqli->affected_rows;
		
		$message = "Додавання {$countInsert_O} операцій пройшло вдало";	
		
		return $message;
	}//_________________________________________________________________________
	
	// 7.4 изменение списка ОПЕРАЦИЙ (при редактировании транзакции)
	public function changeOperation( $listChn, $id_P, $id_T ){											
									
		$this->setBits( $listChn, $id_P, $id_T, 1);
		
		$countInsert_O = 0;		
		forEach( $listChn as $key => $oper ){
			$this->mysqli->query( sprintf(				
				"UPDATE operation SET count = count + %2\$f, price = price + %3\$f %4\$s
				 WHERE id_O = %1\$s",
				/*1*/ $oper[id_O],
				/*2*/ $oper[d_count],
				/*3*/ $oper[d_price],
				/*4*/ ( $oper[mode_otg] == 'ca'
					? ", id_Bu = {$oper[id_agent]}, id_cm = 0"
					: ( $oper[mode_otg] == 'cm'
						?  ", id_cm = {$oper[id_agent]}, id_Bu = 0"
						: ""
					)
				)
			));			
			$countInsert_O += $this->mysqli->affected_rows;
		}					
		$message = "Змінено {$countInsert_O} операцій";	
		
		return $message;
	}//_________________________________________________________________________

	// 7.5 получение списка Видов ОПЕРАЦИЙ
	public function getVidOperation(){											
		// запрос к базе -  получение данных 
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT operVid.id_V as idOper, operVid.name as nameOper, operVid.dir
			FROM operVid			
			ORDER BY operVid.id_V"							
		));
		
		$this->res->data = [];					
		
		//if ( $cursor->num_rows > 0) {
			while( $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ) {												
				$this->res->data[] = [
					idV 	=> $rec[idOper],	
					name 	=> $rec[nameOper]					
				];				
			};	
		//};		
		
		return json_encode( $this->res->data );								
	}//_________________________________________________________________________
	
	// 8.1 получение ТРАНЗАКЦИЙ с ОПЕРАЦИЯМИ
	public function getTransaction(){
				
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT transaction.*, 
				operation.id_O, operation.count, operation.price, operation.id_Bu, 
				operation.id_cm, operation.id_cm, operation.isMoveKassa, operation.token,
			 	material.id_M, material.name as name_M, material.unit,
			 	operVid.id_V, operVid.name as name_V, operVid.dir as dir,
			 	matKategories.id_K, matKategories.name as name_K,
			 	bits.count as countBits,
			 	users.PIB,
			 	operChild.id_O as id_O_child
			FROM transaction 
				LEFT JOIN operation ON transaction.id_T = operation.id_T
				LEFT JOIN operVid ON operation.id_V = operVid.id_V
				LEFT JOIN material ON operation.id_M = material.id_M
				LEFT JOIN matKategories ON material.id_K = matKategories.id_K
				LEFT JOIN bits ON bits.id_M = material.id_M AND bits.id_P = transaction.id_P
				LEFT JOIN users ON transaction.id_U = users.id
				LEFT JOIN operation AS operChild ON operation.id_T != operChild.id_T AND BINARY operation.token = operChild.token
			WHERE -- transaction.id_U = %1\$d AND 
				  transaction.id_P = %2\$d %3\$s %4\$s
			ORDER BY date DESC, time DESC, id_O",
			/*1*/	$this->_PARAM[_id_U],
			/*2*/	$this->_PARAM[_id_P],
			/*3*/	( mb_strlen($this->_PARAM[_search], 'utf-8') > 0
						? implode("", ['AND comment LIKE "%', $this->_PARAM[_search], '%" '])
						: ""
					),
			/*4*/	( $this->_PARAM[_isPeriod]  				
						? "AND (transaction.date BETWEEN '{$this->_PARAM[_date_l]}' AND '{$this->_PARAM[_date_r]}' )"
						: ""
					)								
		));
		
		$ar_data = [];		
		$i = $total = $suma = $kassa = 0;
				
		if ( $cursor->num_rows > 0) {						
			$old = [id_T => 0];
			$listOper = [];			
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_T => 0]);
				
				if($old[id_T] <> $rec[id_T] && $old[id_T] > 0 ){
					$ar_data[] = [
						id_T 	 	=> $old[id_T],	
						id_T_child 	=> $old[id_T_child],	
						dateCreate 	=> date('d.m.Y', strtotime($old[dateCreate])),				
						date 	 	=> date('d.m.Y', strtotime($old[date])),
						time 	 	=> $old[time],
						comment		=> $old[comment],
						PIB			=> $old[PIB],						
						suma		=> round($suma, 3),
						listOper 	=> count($listOper) > 0 ? $listOper : [],
						//groupOperation =>  $old[groupOperation],
						id_Bu 		=> $id_Bu,
						id_cm 		=> $id_cm,
						isEdit 		=> $old[isEdit],
						isDel 		=> $old[isDel],	
						isLoadReport=> $old[isLoadReport] ? 1 : 0,						
					];
					$listOper = [];	
					$total++;
					$suma = 0;
					$id_cm = 0;	
					$id_Bu = 0;								
				}
				if (strlen( $rec[name_M] ) > 0 ){
					$koef = (in_array($rec[id_V], [2, 3, 4])) ? 1 : -1;										
					$listOper[] = [
						id_O  	=> $rec[id_O],						
						count  	=> round($rec[count], 3),						
						countBits => round($rec[countBits], 3),
						price 	=> round($rec[price], 3),
						suma 	=> round($rec[price] * $rec[count], 3) * $koef,						
						name_M 	=> $rec[name_M],
						name_V 	=> $rec[name_V],
						name_K 	=> $rec[name_K],
						id_M 	=> $rec[id_M],
						id_V 	=> $rec[id_V],
						id_K 	=> $rec[id_K],
						unit 	=> $rec[unit],
						dir 	=> $rec[dir],
						isMoveKassa => $rec[isMoveKassa],
						token   => $rec[token],
						id_O_child  => $rec[id_O_child],						
					];	
					$suma += $rec[price] * $rec[count] * $koef;	
					$id_Bu = $rec[id_V] == 2 ? $rec[id_Bu] : 0;
					$id_cm = $rec[id_V] == 2 ? $rec[id_cm] : 0;				
					//$kassa += (in_array($rec[id_K], [1, 6] && $old[id_M] <> $rec[id_M]) ? $rec[countBits] : 0);
				}				
				$old = $rec;
				$i++;
			};
			
			$ar_data = array_slice( $ar_data, 
				($this->_PARAM[_currentPage] - 1) * $this->_PARAM[_sizePage], 
				$this->_PARAM[_sizePage]
			);					
		};		
				
		$kassa    = $this->getKassa( $this->_PARAM[_id_P] );
		$oldKassa = $this->getKassaOnDay( $this->_PARAM[_id_P] );		
		$percent  = ( $kassa > 0 && $oldKassa > 0
			? round( ( $kassa - $oldKassa ) / $oldKassa * 100, 1)
			: 100
		);	
		//$percent  = round( $kassa  / $oldKassa * 100, 1);
							
		return json_encode([ 
			ar_data  => $ar_data,
			total 	 => $total,						
			kassa 	 => $kassa,	
			oldkassa => $oldKassa,	
			percent  => $percent,
			//text	 => $text,			
		]);								
	}//****************************  8 ТРАНЗАКЦИИ ****************************************
	
	// 8.2 получение ТРАНЗАКЦИЙ для экономиста
	public function getETransaction(){	
		$strCheckManeger = implode(', ', $this->_PARAM[_checkManeger]);
																			
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT punkt.id_U AS idUser, users.PIB AS pib, punkt.name AS namePunkt, 
			      	transaction.*, 
			      	operation.id_O, operVid.id_V, operVid.name as name_V, operation.count, 
			      	operation.price, operation.id_Bu, operation.id_cm, operation.id_M
			      	-- IF (ISNULL(T2.id_T), 0, T2.id_T) AS id_parent
			FROM transaction 
				LEFT JOIN operation ON transaction.id_T = operation.id_T
				LEFT JOIN operVid ON operation.id_V = operVid.id_V			
		        LEFT JOIN punkt ON transaction.id_P = punkt.id_P
		        LEFT JOIN users ON punkt.id_U = users.id
		        LEFT JOIN transaction AS T2 ON transaction.id_T = T2.id_T_Child
			WHERE 1=1 %1\$s %2\$s %3\$s AND ISNULL(T2.id_T) AND operation.id_M != 40 
			ORDER BY users.PIB ASC, punkt.id_P ASC, id_T ASC, id_O, date DESC, time DESC",
			/*1*/	( count($this->_PARAM[_checkManeger]) > 0 
						? "AND punkt.id_U IN ({$strCheckManeger})"  
						: "AND punkt.id_U = 0"
					),
			/*2*/	( in_array($this->_PARAM[_checkOperation], [21, 22]) 
						? "AND operVid.id_V = 2 AND operation.id_" . ( $this->_PARAM[_checkOperation] == 21 ? 'CM' : 'Bu' ) . " > 0 "
						: ( $this->_PARAM[_checkOperation] > 0
							? "AND operVid.id_V = {$this->_PARAM[_checkOperation]}"
							: ""
						)
					),			
			/*3*/	( $this->_PARAM[_isPeriod] == 1  				
						? "AND (transaction.date BETWEEN '{$this->_PARAM[_date_l]}' AND '{$this->_PARAM[_date_r]}' )"
						: ""
					)								
		));
		
		$ar_data = [];		
		$i = $total = $suma = $kassa = 0;
				
		if ( $cursor->num_rows > 0) {						
			$old = [id_U => 0];
			$listPunkt = $listTransaction = [];					
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_U => 0]);
				
				if( ($old[id_T] <> $rec[id_T]) && $old[id_T] > 0 ){
					$listTransaction[] = [
						id_U 	=> $old[idUser],	
						id_P	=> $old[id_P],	
						id_T	=> $old[id_T],	
						id_M	=> $old[id_M],					
						comment => $old[comment],	
						suma	=> round($sumaT, 3),
						date 	=> date('d.m.Y', strtotime($old[date])),
						isLoad 	=> !$old[isLoadReport],	
						isLoadReport => $old[isLoadReport],
					];
					$sumaT = 0;	
								
				}	
														
				if( ($old[id_P] <> $rec[id_P]) && $old[id_P] > 0 ){
					$listPunkt[] = [
						id_P			 => $old[id_P],						
						namePunkt 		 => $old[namePunkt],	
						listTransaction  => count($listTransaction) > 0 ? $listTransaction : [],
					];
					$listTransaction = [];																						
				}
								
				if( ($old[idUser] <> $rec[idUser]) && $old[idUser] > 0 ){
					$ar_data[] = [						
						id_U 		=> $old[idUser],					
						pib 	 	=> $old[pib],
						name		=> $old[name_V],
						id_V		=> $old[id_V],							
						listPunkt 	=> count($listPunkt) > 0 ? $listPunkt : [],					
					];
					$listPunkt = [];																		
				}				
				
				$sumaT += $rec[count] * $rec[price];
				$old = $rec;
				$i++;
			};											
		};		
						
		return json_encode([ 
			ar_data  => $ar_data,								
		]);								
	}//_________________________________________________________________________	
	
	// 8.3 добавление ТРАНЗАКЦИИ с ОПЕРАЦИЯМИ
	public function addTransaction(){											
		$countOper = count($this->_PARAM[_opers]);			
		
		if ( $countOper > 0){	
			$date = new DateTime(); // Получаем текущую дату и время
    												
			$this->mysqli->query(sprintf(
				"INSERT INTO transaction (id_U, id_P, date, time, comment, id_T_child, isEdit, isDel, dateCreate)
				VALUE (%1\$d, %2\$d, '%3\$s', '%4\$s', '%5\$s', %6\$d, %7\$d, %8\$d, '%9\$s') ",
				/*1*/	$this->_PARAM[_idUser],
				/*2*/	$this->_PARAM[_idPunkt],
				/*3*/	$this->_PARAM[_date],
				/*4*/	$this->_PARAM[_time],			
				/*5*/	$this->_PARAM[_comment],
				/*6*/	$this->_PARAM[_idTChild],
				/*7*/	$this->_PARAM[_isEdit],
				/*8*/	$this->_PARAM[_isDel],
				/*9*/	$date->format('Y-m-d'), // Форматируем в нужный вид	// date('Y-m-d')		
			));
			$countInsert_T = $this->mysqli->affected_rows;
			$id_T = $this->mysqli->insert_id;
		} else {
			$countInsert_T = -1;
		}						
		
		$this->res->data = [];							
		if ( $countInsert_T == 1){								
			$bits = $this->setBits( $this->_PARAM[_opers], $this->_PARAM[_idPunkt], $id_T, 1);
						
			$this->mysqli->query( sprintf(				
				"INSERT INTO operation (id_V, id_M, id_T, count, price, id_Bu, id_cm, isMoveKassa, token)
				 VALUES %1\$s",
				/*1*/ implode(", ", $bits[arValues])
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
		
		return json_encode( $this->res->data );							
	}//_________________________________________________________________________	
	
	// 8.4 удаление ТРАНЗАКЦИИ с ОПЕРАЦИЯМИ
	public function delTransaction(){
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT transaction.*, 
				operation.id_O, operation.count, operation.price, operation.id_M, 
				operation.id_V, operation.isMoveKassa			 	
			FROM transaction 
				LEFT JOIN operation ON transaction.id_T = operation.id_T				
			WHERE transaction.id_T = %1\$d AND operation.isMoveKassa != -1 ",
			/*1*/	$this->_PARAM[_id_T]						
		));		
		$arOpers = [];
		while( $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){			
			$arOpers[] = [
				id_M 	 	=> $rec[id_M],						
				id_V 	 	=> $rec[id_V],						
				d_count 	=> $rec[count],						
				d_price 	=> $rec[price],
				old_count   => 0,
            	old_price   => 0,
            	new_count   => $rec[count],
            	new_price   => $rec[price],	
            	is_move_kassa => $rec[isMoveKassa],					
			];					
			$id_P = $rec[id_P];
		}	
				
		$this->setBits( $arOpers, $id_P, $this->_PARAM[_id_T], -1);
								
		$this->mysqli->query( sprintf(				
			"DELETE transaction, operation
			 FROM transaction LEFT JOIN operation ON transaction.id_T = operation.id_T
			 WHERE transaction.id_T = %1\$d",
			/*1*/	$this->_PARAM[_id_T]
		));
		$countDelete = $this->mysqli->affected_rows;	
		$message = $countDelete >= 1 ? "Тразакцію успішно видалено" : "Помилка видалення транзакції";
		
		$kassa    = $this->getKassa( $id_P );
		$oldKassa = $this->getKassaOnDay( $id_P );
		$percent  = round( 100 - ( $kassa - $oldKassa ) / $oldKassa * 100, 1);
		
		$this->res->data = [
			isSuccesfull => ($countDelete >= 1),					
			message 	 => $message,			
			kassa 	 	 => $kassa,	
			oldkassa 	 => $oldKassa,	
			percent  	 => $percent,					
		];
		return json_encode( $this->res->data );		
	}//________________________________________________________________
	
	// 8.5 массив с направлениями операций
	private function dirOper(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT operVid.* FROM operVid"			
		));									
		while(  $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
			$this->operVid[ $rec[id_V] ] = $rec[dir];
		};				
	}//_________________________________________________________________________	
		
	// 8.6 редактирование параметров ТРАНЗАКЦИИ
	public function changeTransaction(){						
		$this->res->data 	= [
			isSuccesfull => 1,		
			message	 	 => "Зміна параметрів транзакції. \n",																		
		];
		
		// редактирование комментария
		$this->mysqli->query( sprintf(				
			"UPDATE transaction SET comment = \"%2\$s\", date = \"%3\$s\", time = \"%4\$s\" 
			WHERE id_T = %1\$d",
			/*1*/	$this->_PARAM[_id_T],				
			/*2*/	$this->_PARAM[_comment],			
			/*3*/	$this->_PARAM[_date],
			/*4*/	$this->_PARAM[_time]
		));
				
		if ( count( $this->_PARAM[_opersAdd] ) > 0 ) {
			$this->res->data[message] .= 
				$this->addOperation( $this->_PARAM[_opersAdd],  $this->_PARAM[_idPunkt],  $this->_PARAM[_id_T] ) . "\n";
		}
		
		if ( count( $this->_PARAM[_opersChn] ) > 0 ) {
			$this->res->data[message] .= 
				$this->changeOperation( $this->_PARAM[_opersChn], $this->_PARAM[_idPunkt], $this->_PARAM[_id_T]	);
		}
		
		if ( count( $this->_PARAM[_opersDel] ) > 0 ) {			
			$this->res->data[message] .= 
				$this->delOperation( $this->_PARAM[_opersDel],  $this->_PARAM[_idPunkt],  $this->_PARAM[_id_T]	) . "\n";
		}
		
		return json_encode( $this->res->data );				
	}//_________________________________________________________________________	
	
	// 8.7 изменение доступа к ТРАНЗАКЦТЯМ (edit, del)
	public function setAccessTransaction(){								
		$this->mysqli->query( sprintf("				
			UPDATE transaction SET %2\$s
			WHERE id_T = %1\$d",
			/*1*/	$this->_PARAM[_id_T],							
			/*2*/	$this->_PARAM[_atr]  ? " {$this->_PARAM[_atr]} = {$this->_PARAM[_val]} " : ''
		) );
		$countUpdate = $this->mysqli->affected_rows;
		
		$this->res->data = [];					
		
		$this->res->data = [
			isSuccesfull => ($countUpdate == 1),					
			textQuery => $textQuery,					
			countUpdate => $countUpdate,
		];
		
		return json_encode( $this->res->data );							
	}//________________________________________________________________________
		
	// 9.1 получение ОТСТАТКОВ
	public function getBits(){									
		$this->res->data = [];					
		
		$this->mysqli->query( sprintf(				
			"CREATE TEMPORARY TABLE tempBitsPunkt
			SELECT bits.id_M, bits.id_P, bits.count, 				
			 	material.name as name_M, material.unit,			 	
			 	matKategories.id_K as id_K, matKategories.name as name_K,
			 	matKategories.id_G
			FROM bits 				
				LEFT JOIN material ON bits.id_M = material.id_M
				LEFT JOIN matKategories ON material.id_K = matKategories.id_K
			WHERE bits.id_P = %1\$d	
			ORDER BY id_G, id_K, id_M",			
			/*1*/	$this->_PARAM[_id_P]						
		));
		
		// убираем из учета операции после заданной даты
		if( !empty($this->_PARAM[_date]) ){
			$cursorOper = $this->mysqli->query( sprintf(				
				"SELECT transaction.*, 
					operation.id_O, operation.count, operation.price, operation.id_M,  
					operation.id_V, operation.isMoveKassa			 	
				FROM transaction 
					LEFT JOIN operation ON transaction.id_T = operation.id_T				
				WHERE transaction.date >= '%1\$s' AND id_P = %2\$d",
				/*1*/	date('Y-n-d', strtotime($this->_PARAM[_date]) ),
				/*2*/	$this->_PARAM[_id_P]												
			));		
			$arOpers = [];
			while( $rec = $cursorOper->fetch_array( MYSQLI_ASSOC ) ){			
				$arOpers[] = [
					id_M 	 	=> $rec[id_M],						
					id_V 	 	=> $rec[id_V],						
					d_count 	=> $rec[count],						
					d_price 	=> $rec[price],
					old_count   => 0,
	            	old_price   => 0,
	            	new_count   => $rec[count],
	            	new_price   => $rec[price],	
	            	is_move_kassa => $rec[isMoveKassa],					
				];								
			}
			$this->setBits( $arOpers, $this->_PARAM[_id_P], 0, -1, 'tempBitsPunkt');
		}
		
		$cursor = $this->mysqli->query( "SELECT * FROM tempBitsPunkt");
				
		if ( $cursor->num_rows > 0) {
			$i = 0;			
			$old = [id_G => 0];
			$listMater = [];
			$summa = 0;
			while( $i <= $cursor->num_rows ){
				$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_G => 0]);
				
				if($old[id_G] <> $rec[id_G] && $old[id_G] > 0 ){
					$this->res->data[] = [
						id_K 	 	=> $old[id_K],												
						id_G 	 	=> $old[id_G],
						name_K 	 	=> mb_strtoupper(str_replace(['-', '+'], '', $old[name_K]), "utf-8"),						
						summa_K  	=> round($summa, 3),	
						unit 		=> $old[unit],
						listMater 	=> count($listMater) > 0 ? $listMater : [],
					];
					$listMater = [];					
					$summa = 0;
				}
				if (strlen( $rec[name_M] ) > 0 ){
					$listMater[] = [
						id_M  	=> $rec[id_M],						
						name_M  => $rec[name_M],						
						count  	=> round($rec[count], 3),						
						unit 	=> $rec[unit],						
					];	
					$summa += $rec[count];
				}				
				$old = $rec;
				$i++;
			};	
		};		
		
		return json_encode( $this->res->data );								
	}//***********************************  9 ОТСТАТКИ **********************************

	// 9.2 получение ОТСТАТКОВ (касса)
	private function getKassa( $id_P, $tabl = 'bits'){											
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT sum(%1\$s.count) as sumKasa
			FROM %1\$s  
				LEFT JOIN material ON %1\$s.id_M = material.id_M
				LEFT JOIN matKategories ON material.id_K = matKategories.id_K  
			WHERE %1\$s.id_P = %2\$d AND material.id_K in (1, 6)",			
			/*1*/	$tabl,
			/*2*/	$id_P
		));
		$rec = $cursor->fetch_array( MYSQLI_ASSOC );
		
		return round( $rec[sumKasa], 3);								
	}//_________________________________________________________________________
	
	// 9.3 получение ОТСТАТКОВ (касса) на заданный день 
	public function getKassaOnDay( $id_P ){		 
		$date = date('Y-n-d', strtotime( date('Y-n-d') . '-1 day'));	
				
		$this->mysqli->query( sprintf(				
			"CREATE TEMPORARY TABLE tempBits
			SELECT bits.*
			FROM bits 				
			WHERE id_P = %1\$d",			
			/*1*/ $id_P												
		));	
		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT transaction.*, 
				operation.id_O, operation.count, operation.price, operation.id_M,  
				operation.id_V, operation.isMoveKassa			 	
			FROM transaction 
				LEFT JOIN operation ON transaction.id_T = operation.id_T				
			WHERE transaction.date >= '%1\$s' AND id_P = %2\$d",
			/*1*/	$date,
			/*2*/	$id_P												
		));		
		$arOpers = [];
		while( $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){			
			$arOpers[] = [
				id_M 	 	=> $rec[id_M],						
				id_V 	 	=> $rec[id_V],						
				d_count 	=> $rec[count],						
				d_price 	=> $rec[price],
				old_count   => 0,
            	old_price   => 0,
            	new_count   => $rec[count],
            	new_price   => $rec[price],		
            	is_move_kassa => $rec[isMoveKassa],					
			];								
		}	
				
		$this->setBits( $arOpers, $id_P, 0, -1, 'tempBits');		
		
		return $this->getKassa( $id_P, 'tempBits' );					
	}//________________________________________________________________
	
	// 9.4 запрос на добавление BITS для нового пункта
	public function addBitsNewPunkr( $id_P ){											
		$countInsert = 0;			
		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT bits.* FROM bits
			 WHERE id_P = %1\$d",
			/*1*/ $id_P
		));		
		
		if ( $cursor->num_rows == 0 ){
			$curMat = $this->mysqli->query( "SELECT material.* FROM material ORDER BY id_M" );	
			
			$arValues = [];						
			while( $rec = $curMat->fetch_array( MYSQLI_ASSOC ) ){		
				$arValues[] = "({$id_P}, {$rec[id_M]}, 0)";
			}
			
			$this->mysqli->query( sprintf(				
				"INSERT INTO bits (id_P, id_M, count)
				 VALUES %1\$s",
				/*1*/ implode(", ", $arValues)
			));
			$countInsert = $this->mysqli->affected_rows;					
		}
				
		return $countInsert;			
	}//________________________________________________________________
	
	// 9.5 запрос на добавление BITS для нового материала
	public function addBitsNewMaterial( $id_M ){											
		$countInsert = 0;			
		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT bits.* FROM bits
			 WHERE id_M = %1\$d",
			/*1*/ $id_M
		));		
		
		if ( $cursor->num_rows == 0 ){
			$curPunkt = $this->mysqli->query( "SELECT punkt.* FROM punkt ORDER BY id_P" );	
			
			$arValues = [];						
			while( $rec = $curPunkt->fetch_array( MYSQLI_ASSOC ) ){		
				$arValues[] = "( {$rec[id_P]}, {$id_M},  0)";
			}
			
			$this->mysqli->query( sprintf(				
				"INSERT INTO bits (id_P, id_M, count)
				 VALUES %1\$s",
				/*1*/ implode(", ", $arValues)
			));
			$countInsert = $this->mysqli->affected_rows;					
		}
				
		return $countInsert;			
	}//________________________________________________________________
	
	// 9.6 редактирование остатков из транзакции
	private function setBits( $arOper, $id_P, $id_T, $dir, $tabl = 'bits' ){						
		$arValues = [];	
		$id_agent = 0;
		$this->dirOper();
		$countUpdate = 0;	
				
		foreach( $arOper as $key => $oper ){
			//$token = $this->generateToken(4);
			$token = $oper[token];
			
			$id_Bu = ( $oper[id_V] == 2 && $oper[mode_otg] == 'ca' 
				? $oper[id_agent] 
				: ( $oper[id_V] == 3 ? 1 : 0 ) 
			);				
			$id_cm = ( $oper[id_V] == 2 && $oper[mode_otg] == 'cm' ? $oper[id_agent] : 0 );						
			$arValues[] = "({$oper[id_V]}, {$oper[id_M]}, {$id_T}, {$oper[d_count]}, {$oper[d_price]}, {$id_Bu}, {$id_cm}, {$oper[is_move_kassa]}, '{$token}' )";			
									
			$this->mysqli->query( sprintf(				
			   "UPDATE %1\$s SET count = count + (%4\$f)
				WHERE id_P = %2\$d AND id_M = %3\$d",
				/*1*/	$tabl,				
				/*2*/	($id_P > 0 ? $id_P : $oper[id_P]),				
				/*3*/	$oper[id_M],			
				/*4*/	( in_array( $oper[id_V], [4, 5] )
					?   ( $oper[new_count] * $oper[new_price] - 
						 $oper[old_count] * $oper[old_price] ) * $this->operVid[ $oper[id_V] ] * $dir
					:	$oper[d_count] * $this->operVid[ $oper[id_V] ] * $dir
				)				
			));
			$countUpdate += $this->mysqli->affected_rows;
			
			// дополнительное движение по кассе
			if ( in_array( $oper[id_V], [1, 3]) && $oper[is_move_kassa] == 1 ){
				$id_V 	 = ($oper[id_V] == 1 ? 5 : 4 );
				$id_M 	 = ($oper[id_V] == 1 ? 40 : 80 );
				$d_count = ( $oper[new_count] * $oper[new_price] - 
							 $oper[old_count] * $oper[old_price] ) * 
							 $this->operVid[ $id_V ] * $dir;
				$d_price = 0;
												
				$this->mysqli->query( sprintf(				
					"UPDATE %1\$s SET count = count + (%4\$f)
					 WHERE id_P = %2\$d AND id_M = %3\$d",
					/*1*/	$tabl,
					/*2*/	($id_P > 0 ? $id_P : $oper[id_P]),					
					/*3*/	$id_M,					
					/*4*/   $d_count					
				));	
				$countUpdate += $this->mysqli->affected_rows;			
								
				$arValues[] = "({$id_V}, {$id_M}, {$id_T}, {$d_count}, {$d_price}, 0, 0, -1, '{$token}' )";							
			}						
		}
		
		return [ 
			arValues    => $arValues,
			countUpdate => $countUpdate 
		];
	}//_________________________________________________________________________	
		
	// 10.1 получение данных таблицы АНАЛИТИКА ПО МАТЕРИАЛАМ
	public function getMonitoring( $isResponse = true){	
		$arIdMat = [];
		foreach($this->_PARAM[_checkMaterial] as $material){
			$arIdMat[] = $material[1];
		}
						
		$cursor = $this->mysqli->query(sprintf(				
			"CREATE TEMPORARY TABLE tempMonitoring
			SELECT users.id as id_U, users.PIB,	
			 		punkt.id_P as id_P, punkt.name as name_P,				
					matKategories.id_G as id_G, matKategories.id_K as id_K, matKategories.name as name_K,
  					material.id_M as id_M, material.name as name_M, material.unit,
					-- sum(bits.count) as sumCount 
					bits.count as count
			FROM users 
				LEFT JOIN status ON users.status = status.id
				LEFT JOIN punkt ON users.id = punkt.id_U
				LEFT JOIN bits ON punkt.id_P = bits.id_P
				LEFT JOIN material ON bits.id_M = material.id_M
				LEFT JOIN matKategories ON material.id_K = matKategories.id_K
			WHERE users.id IN (%1\$s) AND matKategories.id_G != 1					
			-- GROUP BY id_U, id_K, id_M
			ORDER BY users.PIB, id_K, id_M, bits.count DESC",
			/*1*/ 	implode(',', $this->_PARAM[_checkManeger])			
		));
		
		// убираем из учета операции после заданной даты
		if( !empty($this->_PARAM[_date]) ){			
			$cursorOper = $this->mysqli->query(sprintf(				
				"SELECT transaction.*, 
					operation.id_O, operation.count, operation.price, operation.id_M,  operation.id_V,			 	
					punkt.id_P AS idPunkt
				FROM transaction 
					LEFT JOIN operation ON transaction.id_T = operation.id_T
					LEFT JOIN punkt ON transaction.id_P = punkt.id_P
					LEFT JOIN users ON punkt.id_U = users.id				
				WHERE transaction.date >= '%1\$s' AND users.id IN (%2\$s)",
				/*1*/	date('Y-n-d', strtotime($this->_PARAM[_date]) ),
				/*2*/	implode(',', $this->_PARAM[_checkManeger])													
			));		
			$arOpers = [];
			while( $rec = $cursorOper->fetch_array( MYSQLI_ASSOC ) ){			
				$arOpers[] = [
					id_M 	 	=> $rec[id_M],						
					id_V 	 	=> $rec[id_V],						
					d_count 	=> $rec[count],						
					d_price 	=> $rec[price],
					old_count   => 0,
	            	old_price   => 0,
	            	new_count   => $rec[count],
	            	new_price   => $rec[price],
	            	id_P		=> $rec[idPunkt],						
				];								
			}
			$this->setBits( $arOpers, 0, 0, -1, 'tempMonitoring');
		}
		
		$cursor = $this->mysqli->query( "SELECT * FROM tempMonitoring");
					
		$ar_data = [];				
		$count = count($this->_PARAM[_checkManeger]);
			
		if( $isResponse ){
			if ( $cursor->num_rows > 0) {
				$i = $summa  = 0;			
				$old = [id_U => 0];
				$listKateg = [];
				$listMaterial = [];
				
				while( $i <= $cursor->num_rows ){
					$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_K => 0]);
									
					if( ($old[id_K] <> $rec[id_K] || ( $old[id_U] <> $rec[id_U] && $old[id_U] > 0) ) 
						&& strlen( $old[name_K] ) > 0 					
					){
						if( ( count($arIdMat) > 0 && count($listMaterial) > 0 || count($arIdMat) == 0) ){
							
							if ($summa > 0){ foreach($listMaterial as &$material){						
								$material[percent] = round( $material[count] / $summa * 100, 1);	
								
								foreach($material[listPunkt] as &$materialP){	
									$materialP[percent]	 = $material[count] > 0 
										? round( $materialP[count] / $material[count] * 100, 1)
										: 0;
								}
							}}						
							
							$listKateg[] = [
								name_K 		 => $old[name_K],
								id_K 		 => $old[id_K],	
								summa_K  	 => round($summa, 3), 
								unit  	 	 => $old[unit],	
								listMaterial => count($listMaterial) > 0 ? $listMaterial : [],
							];	
							$listMaterial = [];
						}											 					
						$summa = 0;				
					}
					
					if ( strlen( $rec[name_M] ) > 0	){
						if( ( count($arIdMat) > 0 && in_array($rec[id_M], $arIdMat) || count($arIdMat) == 0) ){
							
							if ( $old[id_M] <> $rec[id_M] ){					
								$listMaterial[] = [
									id_M  	 => $rec[id_M],						
									name_M   => $rec[name_M],						
									count  	 => round($rec[count], 3),						
									unit 	 => $rec[unit],								
								];								
								$num = count($listMaterial) - 1;							
							}	
							else{
								$num = count($listMaterial) - 1;							
								$listMaterial[$num][count] += round($rec[count], 3);													
							}						
							
							$listMaterial[$num][listPunkt][] = [
								name_P 	 => $rec[name_P],
								count 	 => $rec[count],
								unit 	 => $rec[unit],							
							];
						}
						$summa += $rec[count];						
					}								
					
					if($old[id_U] <> $rec[id_U] && $old[id_U] > 0 ){
						$ar_data[] = [
							id_U 	   => $old[id_U],	
							pib 	   => $old[PIB],											
							listKateg  => count($listKateg) > 0 ? $listKateg : [],											
						];
						$listKateg = [];						
					}
					
					$old = $rec;
					$i++;
				};	
				
				/*foreach($ar_data as &$user){
					foreach($user[listKateg] as &$kategories){								
						foreach($kategories[listMaterial] as &$material){
							// $material[percent] = round($material[count] / $kategories[summa_K] * 100, 3);						
							$material[percent] = round( $material[count] / $kategories[summa_KP] * 100, 3);											
							//$material[percent] = $kategories[summa_K]; 
						}	
					}				
				}*/
					
			};
			
			return json_encode([ 
				ar_data  => $ar_data,
				count 	 => $count,				
			]);	
		}									
	}//********  10  АНАЛИТИКА  ****************************************
	
	// 10.2 получение списка менеджеров
	public function getManeger(){											
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT *
			 FROM users
			 ORDER BY PIB"			
		));
		
		while( $rec = $cursor->fetch_array( MYSQLI_ASSOC )  ){									
			$listManeger[] = [
				label  => $rec[PIB],						
				value  => $rec[id],
			];				
		};
		
		return json_encode([ 
			ar_data  => $listManeger,			
		]);								
	}//_________________________________________________________________________
	
	// 10.3 получение данных таблицы АНАЛИТИКА ПО КАССЕ
	public function getMoney( $isResponse = true){
													
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT users.id as id_U, users.PIB,	
					punkt.id_P as id_P, punkt.name as name_P,				
					matKategories.id_G as id_G, matKategories.id_K as id_K,
					IF (matKategories.name = 'каса +', 'надходження коштів', 'витрати коштів') as name_K, 
					-- matKategories.name as name_K,
			  		material.id_M as id_M, material.name as name_M, material.unit,
			  		IF (matKategories.name = 'каса +', 
			  			 ABS( IF (operation.count = 1, operation.price, operation.count) ), 
			  			-ABS( IF (operation.count = 1, operation.price, operation.count) ) 
			  		) as summa, 
			        -- ABS(operation.count) AS summa,
			        transaction.date				
			FROM transaction 				
			     LEFT JOIN punkt ON transaction.id_P = punkt.id_P
				 LEFT JOIN users ON punkt.id_U = users.id
			     LEFT JOIN operation ON transaction.id_T = operation.id_T
			     LEFT JOIN material ON operation.id_M = material.id_M
			     LEFT JOIN matKategories ON material.id_K = matKategories.id_K
			WHERE users.id IN (%1\$s) AND matKategories.id_G = 1 %2\$s
			ORDER BY users.PIB, id_P, id_V, id_M",
			/*1*/ 	implode(',', $this->_PARAM[_checkManeger]),
			/*2*/	( $this->_PARAM[_isPeriod]  				
						? "AND (transaction.date BETWEEN '{$this->_PARAM[_date_l]}' AND '{$this->_PARAM[_date_r]}' )"
						: ""
					)
		));
						
		$ar_data = [];				
		//$count = count($this->_PARAM[_checkManeger]);
			
		if( $isResponse ){
			if ( $cursor->num_rows > 0) {
				$i = $sumVidKasa = $sumInKasa = $sumaU = $sumaP  = 0;
				$old = [id_U => 0, id_P => 0, id_K => 0, id_M => 0];
				$listPunkt = $listVidKasa = $listInKassa = [];	
						
				while( $i <= $cursor->num_rows ){
					$rec = ( $i < $cursor->num_rows ? $cursor->fetch_array( MYSQLI_ASSOC ) : [id_U => 0] );
					
					if( ($old[id_M] <> $rec[id_M] && $old[id_M] > 0 ) || 
						($old[id_K] <> $rec[id_K] && $old[id_K] > 0 ) || 
						($old[id_P] <> $rec[id_P] && $old[id_P]) 
					){
						$listInKassa[] = [
							id_M	=> $old[id_M],	
							nameN	=> $old[name_M],
							suma	=> $sumInKasa,
							unit 	=> $old[unit],							
						];
						$sumInKasa = 0;											
					}
					
					if( ($old[id_K] <> $rec[id_K] && $old[id_K] > 0)  || 
						($old[id_P] <> $rec[id_P]  && $old[id_P] > 0 ) 
					){
						$listVidKasa[] = [
							id_K 		  => $old[id_K],	
							nameVidKassa  => $old[name_K],	
							sumVidKassa   => $sumVidKasa,
							listInKassa   => count($listInKassa) > 0 ? $listInKassa : [],
							unit 		  => $old[unit],	
						];
						$listInKassa = [];
						$sumVidKasa = 0;					
					}	
															
					if( $old[id_P] <> $rec[id_P] && $old[id_P] > 0 ){
						$listPunkt[] = [
							id_P		 => $old[id_P],						
							namePunkt 	 => $old[name_P],	
							sumaP		 => $sumaP,
							unit 	 	 => $old[unit],
							listVidKasa  => count($listVidKasa) > 0 ? $listVidKasa : [],
						];
						$listVidKasa = [];	
						$sumaP = 0;																
					}
									
					if( ($old[id_U] <> $rec[id_U]) && $old[id_U] > 0 ){
						$ar_data[] = [						
							id_U 		=> $old[id_U],					
							pib 	 	=> $old[PIB],
							sumaU		=> $sumaU,	
							unit 		=> $old[unit],													
							listPunkt 	=> count($listPunkt) > 0 ? $listPunkt : [],					
						];
						$listPunkt = [];
						$sumaU = 0;													
					}				
					
					$sumaU 		+= $rec[summa];
					$sumaP 		+= $rec[summa];					
					$sumVidKasa += $rec[summa];
					$sumInKasa  += $rec[summa];
					$old = $rec;
					$i++;
				};					
			};
			
			return json_encode([ 
				ar_data  => $ar_data,																
			]);	
		}									
	}////_________________________________________________________________________	
	
	// 11.1 получение данных таблицы ПОКУПАТЕЛЬ
	public function getBuyer(){						
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT buyer.*
			FROM buyer 
			ORDER BY buyer.id_Bu"
		));
			
		$this->res->data = [];					
		
		if ( $cursor->num_rows > 0) {		
			while(  $rec = $cursor->fetch_array( MYSQLI_ASSOC ) ){																				
				$this->res->data[] = [	
					id 		=> $rec[id_Bu],					
					name 	=> $rec[name],				
					cod 	=> $rec[cod],				
					commit 	=> $rec[commit],
					typeM 	=> $rec[typeM],						
				];								
			};			
		};				
		return json_encode( $this->res->data );								
	}//********************************  11 ПОКУПАТЕЛЬ **************************************
			
	// 11.2 изменение данных ПОКУПАТЕЛЬ
	public function setBuyer(){							
		$this->mysqli->query( sprintf(
			"UPDATE buyer SET name = \"%2\$s\", cod = \"%3\$s\", commit = \"%4\$s\", typeM = \"%5\$s\"
			WHERE id_Bu = %1\$d",
			/*1*/	$this->_PARAM[_id],
			/*2*/	$this->_PARAM[_name],
			/*3*/	$this->_PARAM[_cod],
			/*4*/	$this->_PARAM[_commit],
			/*5*/	$this->_PARAM[_typeM]
		));
		$countUpdate = $this->mysqli->affected_rows;
		
		// для теста
		//$countAddBits = $this->addBitsNewPunkr($this->_PARAM[_id]);
		
		$this->res->data = [];							
		$this->res->data = [
			isSuccesfull => ($countUpdate == 1),								
			countUpdate  => $countUpdate						
		];
		
		return json_encode( $this->res->data );							
	}//_________________________________________________________________________	
	
	// 11.3 запрос на добавление ПОКУПАТЕЛЬ
	public function addBuyer(){											
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT buyer.* FROM buyer
			 WHERE buyer.name = \"%1\$s\"",
			/*1*/	$this->_PARAM[_name]
		));		
		
		if ( $cursor->num_rows == 0 ){					
			$this->mysqli->query( sprintf("				
				INSERT INTO buyer (name, cod, commit, typeM)
				VALUE (\"%1\$s\", \"%2\$s\", \"%3\$s\", \"%4\$s\")",				
				/*1*/	$this->_PARAM[_name],
				/*2*/	$this->_PARAM[_cod],
				/*3*/	$this->_PARAM[_commit],
				/*4*/	$this->_PARAM[_typeM]
			));
			$countInsert = $this->mysqli->affected_rows;
			$id = $this->mysqli->insert_id;				
					
		} else {
			$countInsert = 0;			
		}	
		
		if ( $countInsert == 1){
			$countAddBits = $this->addBitsNewPunkr($id);
			
			$this->res->data = [
				isSuccesfull => 1,					
				idP			 => $id,
				name 		 => $this->_PARAM[_name],				
				cod 		 => $this->_PARAM[_cod],				
				commit 		 => $this->_PARAM[_commit],
				typeM 		 => $this->_PARAM[_typeM],
				message		 => 'Нового покупця додано',
			];	
		} else {
			$this->res->data = [
				isSuccesfull => 0,
				message		 => 'Покупця не додано',
			];
		}	
		
		return json_encode( $this->res->data );			
	}//________________________________________________________________
	
	// 11.4 запрос на удаление ПОКУПАТЕЛЬ
	public function delBuyer(){		
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT operation.*	FROM operation
			 WHERE operation.id_Bu = %1\$d ",
			/*1*/	$this->_PARAM[_id]
		));		
				
		if ( $cursor->num_rows == 0 ){
			$textQuery = sprintf(				
				"DELETE FROM buyer				 
				 WHERE buyer.id_Bu = %1\$d",
				/*1*/	$this->_PARAM[_id]
			);			
			$this->mysqli->query( $textQuery );
			$countDelete = $this->mysqli->affected_rows;	
			$message = $countDelete >= 1 ? "Покупця успішно видалено" : "Помилка видалення покупця";			
		} else {
			$countDelete = 0;
			$num_rows = $cursor->num_rows;			
			$message = "Заборонено видаляти покупця, присутнього в операції";
		}		
					
		$this->res->data = [
			isSuccesfull => ($countDelete >= 1),					
			message 	 => $message,
		];
		return json_encode( $this->res->data );				
	}//________________________________________________________________	

	// 12.1 экспорт в ECXEL 
	public function loadReport(){			
		include_once "clReport.php";		
		$report = new clReport($this->mysqli, $this->_PARAM);
				
		switch ($this->_PARAM[_nameReport]){
			// ежедневный отчет экономиста (для экспорта в 1С)
			case "economist": {
				$resultReport = $report->getReportEconomist();	
				break;
			}
			case "analitikaResurs": {							
				$resultReport = $report->getReportAnalitikaResurs();
				break;
			}
			case "analitikaMoney": {							
				$resultReport = $report->getReportAnalitikaMoney();
				break;
			}									
			default:{						
			}
		}
		
		header('Content-Type: application/json');				
		return json_encode( $resultReport );							
	}//*****************************  12  EXCEL отчеты  ****************************************
}?>