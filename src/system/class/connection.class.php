<?php
/* Connection.class.php - Classe de conexão padrão do sistema
   Contem metodos para se trabalhar com o banco de dados de forma simples
   com funções que facilitam na interação.
   
   por Carlson A. Soares - 2011-02-17 09:06
   
   Ultimo Update:
      Count aceita comandos sql
*/

//CONSTANTES DA CLASSE

//Error msgs
define( 'DB_CONNECTION_ERROR', "Erro ao conectar na base de dados: \n%s\n" );

    
      class Connection extends GojiraCore{
    
         protected $conn;          //Objeto de conexão
         protected $msg;           //Mensagens de aviso ou erro
         protected $lastCommand;   //Ultimo comando executado
         
         protected $db;				//Base de dados em que está conectado
         protected $host;			//Host atual conectado
			private $user;				//Usuário que foi usado para conectar
			private $pwd;				//Senha para a base de dados
         

         //As propriedades estão ai para manter compatibilidade com sistemas antigos
         const FETCH_OBJECT = 0;
         const FETCH_ASSOC  = MYSQLI_ASSOC;
         const FETCH_NUM    = MYSQLI_NUM;
         public $FETCH_OBJECT = 0;
         public $FETCH_ASSOC  = MYSQLI_ASSOC;
         public $FETCH_NUM    = MYSQLI_NUM;
         
			
         function __construct1( $dbHost )                            { $this->connect( $dbHost, '', '' ); }
         function __construct3( $dbHost, $dbUser, $dbPass )          { $this->connect( $dbHost, $dbUser, $dbPass ); } 
         function __construct4( $dbHost, $dbUser, $dbPass, $dbName ) { $this->connect( $dbHost, $dbUser, $dbPass, $dbName ); } 
        
         //Destructor força a desconexão do banco de dados;
         //Desativado porque tava dando problema: Como o model extende connection,
         //Esse destruct desconectava no final do model, dando problema se tentasse usar a conexão
         //Em outros pontos apos o termino do model.
         //TODO - Deixar isso no engine.php quando ele for integrado ao system
         /*function __destruct() {
            $this->disconnect();
         }*/
        
        
        
        
        
        
        
        
         /***   Metodos de SET e GET de valores ***/
         function get_conn() {        return $this->conn; }
         function get_connected() {   return $this->isConnected(); }
         function get_msg() {         return $this->msg; }
         function get_lastCommand() { return $this->lastCommand; }
         function get_lastId() {      return mysqli_insert_id( $this->conn ); }
			function get_db()          { return $this->db; }
         function get_host()        { return $this->host; }
        
        
        
        
        
        
         /***   Metodos do Objeto ***/
        
        
         //Conecta no banco de dados
         function connect($dbHost, $dbUser, $dbPass, $dbName = ''){

				$this->host = $dbHost;
				$this->user = $dbUser;
				$this->pwd  = $dbPass;
            $this->db   = $dbName;
            
            //Quando não existe o host, identifica como uma sessão sem conexão com o banco
            if( empty( $dbHost ) ) return false;
            
            $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName );
            
            //Verifica se conectou corretamente
            if(mysqli_connect_errno()){
               printf(DB_CONNECTION_ERROR, mysqli_connect_error());
               exit();
            }
            
            $this->conn = $conn;
            
            $this->execute("SET NAMES 'utf8'");
            $this->execute('SET character_set_connection=utf8');
            $this->execute('SET character_set_client=utf8');
            $this->execute('SET character_set_results=utf8');
            
            return $this;
         }


         //Seleciona a base de dados ativa da coexão
         function selectDb( $dbName ){
				
            $this->db = $dbName;
            $ret      = false;

            if( $this->connected ){
               $ret = $this->conn->select_db( $dbName );
            } 

            return $ret;
				
         }


         //Desconecta da base de dados
         function disconnect(){
            
            $ret = false;

            if( $this->connected ){
               $ret = @$this->conn->close();
            }

            return $ret;
         }


			//Reconecta a base de dados
			function reconnect(){
				
				$this->disconnect(); 
				$this->connect( $this->host, $this->user, $this->pwd, $this->db );
				
			}


			//Verifica se o servidor está connectado
			function isConnected(){
				
				//Se não for objeto é por que não está conectado
				if( ! is_object( $this->conn ) ) return false;
				
				//Se for objeto, verifica se a conexão está ativa
				return $this->conn->ping();
				
			}


         //Checa se está conectado e gera um erro caso não esteja
         private function checkConnection(){

            if( !$this->isConnected() ){
               $this->reconnect();
               if( !$this->isConnected() ){
                  throw( new Exception('Cannot connect to the server') );
               }
            }

            return true;
         }


         //Executa uma consulta no banco de dados
         function query( $sql ){
            
            $this->checkConnection();

            $this->lastCommand = $sql;
            
            $query = $this->conn->query( $sql );
            
            if( !is_object($query) && $query === false ){
               throw( new Exception('Invalid sql statement supplied for connection query') );
            }

            return $query;
         }


         //Executa um comando na base de dados
         function execute( $sql ){
            
            $this->checkConnection();
				
            $ret = false;
				
            $this->lastCommand = $sql;
                        
				//Executa o comando
            if( $stmt = $this->conn->prepare( $sql ) ){
               return $stmt->execute();
            }
            
				return false;
         }


         //Executa uma serie de comandos sql em uma transação
         function secureExec( $sql ){
            
            $this->checkConnection();
            
            if( ! is_array( $sql ) ) $sql = array( $sql );
            
            $this->conn->autocommit( false );
            $this->conn->begin_transaction( MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT );
               
            foreach( $sql as $a ){
               if( ! $this->execute( $a ) ){
                  $this->conn->rollback(); 
                  $this->conn->autocommit( true );
                  return false;
               }
            }
            
            $this->conn->commit();
            $this->conn->autocommit( true );
            
            return true;
         }


         //Carrega uma linha do resultSet
         function fetch( $query, $type = MYSQLI_ASSOC){
            
            if( ! is_object( $query ) ){
               throw( new Exception('Invalid query object'));
            }
            
            if( $type == $this->FETCH_OBJECT ){
               $row = $query->fetch_object();
            } else {
               $row = $query->fetch_array( $type );
            }
				
            return $row;
         }
         
         
         //Carrega todo o resultset em um array
         function fetchAll( $query, $type = MYSQLI_ASSOC ){
            
            for($set = array (); $row = $this->fetch( $query, $type ); $set[] = $row);
            
            return $set;
         }


         //Seleciona o valor mais alto de uma coluna
         function max( $table, $field ){
            
            if( empty( $table ) || empty( $field ) ){
               throw( new Exception('Invalid parameters for MAX function') );
            }

            $this->checkConnection();

            $query = $this->query( 'select max(' . $field . ') as a from ' . $table );
            $row = $this->fetch($query);
            return $row['a'];

         }


         //Seleciona o valor mais baixo
         function min( $table, $field ){
            
            if( empty( $table ) || empty( $field ) ){
               throw( new Exception('Invalid parameters for MIN function') );
            }

            $this->checkConnection();

            $query = $this->query( 'select min(' . $field . ') as a from ' . $table );
            $row = $this->fetch($query);
            return $row['a'];

         }


         //Conta o numero de registros no resultSet
         function count( $query ){
            
            $n = false;
            
            if( is_string( $query ) ){
               
               $this->checkConnection();

               $sql = 'select count(*) as a from (' . $query . ') as a';
               $ret = $this->query( $sql );
               $row = $this->fetch( $ret );
               $n   = $row['a'];
            } elseif( is_object( $query ) && get_class( $query ) == 'mysqli_result' ) {
               $n = $query->num_rows;
            }
            
            return $n;
         }


         //Verifica se um valor existe em um campo da tabela
         function exist( $table, $column = '', $value = '', $compare = ''){
            
            if( empty( $table ) ){
               throw( new Exception('Invalid parameters for EXIST function') );
            }

            $this->checkConnection();

            $sql = '';

            if( !empty( $column  ) ) $sql .= '`' . $column . '`' . ( strtolower($value) == 'null' || is_null($value) ? ' IS NULL ' : '="' . $value . '" ');
            if( !empty( $compare ) ) $sql .= (empty( $sql ) ? '' : ' AND ') . $compare;
            if( !empty( $sql     ) ) $sql = 'SELECT COUNT(*) AS a FROM `' . $table . '` WHERE ' . $sql;

            $query = $this->query( $sql );
            
            if( $this->count( $query ) > 0 ){
               $row = $this->fetch( $query );
               return $row['a'] > 0;
            }

            return false;
         }
			
			
			//Verifica se a tabela existe
			function tableExist( $table ){
            
            if( empty( $table ) ){
               throw( new Exception('Invalid table name') );
            }

            $this->checkConnection();

				$sql = 'SELECT COUNT(*) AS a FROM information_schema.TABLES WHERE TABLE_SCHEMA="' . $this->db . '" and TABLE_NAME="' . $table . '"';
  				$row = $this->fetch( $this->query( $sql ) );

				return $row['a'] > 0;
				
			}
			
			
			//Verifica se um determinado campo existe na tabela
			function fieldExist( $table, $field ){
            
            if( empty( $table ) || empty( $field ) ){
               throw( new Exception('Invalid parameters for FIELDEXIST function') );
            }

            $this->checkConnection();

				$sql = 'select count(*) as a from information_schema.COLUMNS where TABLE_SCHEMA = "' . $this->db . '" and TABLE_NAME="' . $table . '" and COLUMN_NAME="' . $field . '"';
				$row = $this->fetch( $this->query( $sql ) );
				
				return $row['a'] > 0;
				
			}
			

         //Busca um valor de um campo especifico
         function getValue( $table, $field, $compare, $max = 0 ){
            
            if( empty( $table ) || empty( $field ) || empty( $compare ) ){
               throw( new Exception('Invalid parameters for GETVALUE function') );
            }

            $this->checkConnection();

            $ret = array();
            $sql = 'select ' . $field . ' AS a from ' . $table . ' where ' . $compare;
            
            if( $max > 0 || is_string( $max ) ){
               $sql .= ' limit ' . $max;
            }
				
            
            //Se for trazer resultados
				if( $this->count( $sql ) ){
	            $query  = $this->query( $sql );
	            $result = $this->fetchAll( $query );
               foreach( $result as $a ) $ret[] = $a['a'];
				}

            return $ret;

         }

         
      } 
   
   