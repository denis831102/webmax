<?php   	
	
	header('Access-Control-Allow-Origin: *');	
	header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Token, Ecp');	
	header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
//	header("Content-Type: application/json; charset=utf-8");
				
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
		
	private $ecp 			= 'c3875d07f44c422f3b3bc019c23e16ae';  
	private $key 			= 'denis';  
	private $headers		= [];
	
	private	$mysqli;
			
	// конструктор класса с начальной инициализацией
	public function __construct(){		
		$this->mysqli = new mysqli("localhost", $this->login, $this->password, $this->DB);
		$this->headers = getallheaders();
						   	
	   	switch( $_SERVER['REQUEST_METHOD'] ){
	   		case 'POST':	   			
	   			$this->_PARAM = json_decode( file_get_contents('php://input'), true); 
	   		break;
			case 'GET':
				$this->_PARAM = $_GET; 
	   		break;
	   		default:
	   			$this->_PARAM = file_get_contents('php://input');  	
		}	   			
	}//_____________________________________________________________________
	
	// деструктор класса
	function __destruct(){
		$this->mysqli->close(); 
	}//_____________________________________________________________________________
	
	// ERROR 
	public function error( $mes = '' ){					
	    $this->res = [
	    	'message' => ( $mes == ''
		    	? "Error in name function '{$this->_PARAM['method']}' "	  		  	
		    	: $mes
	    	),
	    	'status' => 'error', 
	    ];	
		return json_encode($this->res);		
	}//___________________________________________________________________________
	
	// проверка ecp
	public function checkECP(){								
		if ( $this->headers['ecp']){			
			return $this->headers['ecp'] == $this->ecp;			
		} else {
			return false;
		}				
	}//_____________________________________________________________________________
	
	//  
	public function proba(){
		$cursor = $this->mysqli->query( sprintf(				
			"SELECT *
			FROM users
			WHERE id = %1\$d",
			/*1*/	$this->_PARAM['_id_U']					 	
		));
		
		$rec = $cursor->fetch_array( MYSQLI_ASSOC );
							
	    $this->res = [
	    	'status' => 'success', 
	    	'id'     => $this->_PARAM['_id_U'],
	    	'fio'    => $rec['PIB'],
		];	
		return json_encode($this->res, JSON_UNESCAPED_UNICODE);		
	}//___________________________________________________________________________

	
}?>