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
         
         private $db;				//Base de dados em que está conectado
         private $host;				//Host atual conectado
			private $user;				//Usuário que foi usado para conectar
			private $pwd;				//Senha para a base de dados
         
         
         //Devido ao suporte fraco a constantes de objeto, vou deixa-los como propriedade
         public $FETCH_OBJECT = 0;
         public $FETCH_ASSOC  = MYSQLI_ASSOC;
         public $FETCH_NUM    = MYSQLI_NUM;
         
			
         function __construct1( $dbHost )                            { $this->connect( $dbHost, '', '' ); }
         function __construct3( $dbHost, $dbUser, $dbPass )          { $this->connect( $dbHost, $dbUser, $dbPass ); } 
         function __construct4( $dbHost, $dbUser, $dbPass, $dbName ) { $this->connect( $dbHost, $dbUser, $dbPass, $dbName ); } 
        
         //Destructor força a desconexão do banco de dados;
         function __destruct() {
            $this->disconnect();
         }
        
        
        
        
        
        
        
        
         /***   Metodos de SET e GET de valores ***/
         function get_conn() {        return $this->conn; }
         function get_connected() {   return ($this->conn ? true : false); }
         function get_msg() {         return $this->msg; }
         function get_lastCommand() { return $this->lastCommand; }
         function get_lastId() {      return mysqli_insert_id( $this->conn ); }
			function get_db()          { return $this->db; }
        
        
        
        
        
        
         /***   Metodos do Objeto ***/
        
        
         //Conecta no banco de dados
         function connect($dbHost, $dbUser, $dbPass, $dbName = ''){

				$this->host = $dbHost;
				$this->user = $dbUser;
				$this->pwd  = $dbPass;
            $this->db   = $dbName;
               
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
            return $this->conn->select_db( $dbName );
				
         }


         //Desconecta da base de dados
         function disconnect(){
            return @$this->conn->close();
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


         //Executa uma consulta no banco de dados
         function query( $sql ){
      
            $this->lastCommand = $sql;
            
            //Se não conseguir executar
            if( $query = $this->conn->query( $sql ) ){
               
               //Verifica se a conexão não caiu
					if( !$this->isConnected() ){
               
                  //Se tiver caido, tenta reconectar
						$this->reconnect();
                  
						$query = $this->conn->query( $sql );
					}
            }
            
            return $query;
         }


         //Executa um comando na base de dados
         function execute( $sql ){
				
				$ret = false;
				
            $this->lastCommand = $sql;
            
				//Se não conseguir executar
            if( !$stmt = $this->conn->prepare( $sql ) ){
               
               //Verifica se a conexão não caiu
					if( !$this->isConnected() ){
               
                  //Se tiver caido, tenta reconectar
						$this->reconnect();

						if( $stmt = $this->conn->prepare( $sql ) ){
                     return $stmt->execute();
                  }
					}
            } else {
               return $stmt->execute();
            }
            
				return false;
         }


         //Executa uma serie de comandos sql em uma transação
         function secureExec( $sql ){
            
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
            
            if( $type == $this->FETCH_OBJECT ){
               $row = $query->fetch_object();
            } else {
               $row = $query->fetch_array( $type );
            }
				
            return $row;
         }
         
         
         //Carrega todo o resultset em um array
         function fetchAll( $query, $type = 0 ){
            
            for($set = array (); $row = $this->fetch( $query, $type ); $set[] = $row);
            
            return $set;
         }


         //Seleciona o valor mais alto de uma coluna
         function max( $table, $field ){
            
            $query = $this->query( 'select max(' . $field . ') as a from ' . $table );
            $row = $this->fetch($query);
            return $row['a'];

         }


         //Seleciona o valor mais baixo
         function min( $table, $field ){
            
            $query = $this->query( 'select min(' . $field . ') as a from ' . $table );
            $row = $this->fetch($query);
            return $row['a'];

         }


         //Conta o numero de registros no resultSet
         function count( $query ){
            
            $n = false;
            
            if( is_string( $query ) ){
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
            
            $sql = '';

            if( !empty( $column  ) ) $sql .= $column . ( $compare == 'null' ? ' is null ' : '="' . $value . '" ');
            if( !empty( $compare ) && $compare != 'null' ) $sql .= (empty( $sql ) ? '' : ' and ') . $compare;
            if( !empty( $sql     ) ) $sql = 'select count( * ) as a from `' . $table . '` where ' . $sql;
            
            $query = $this->query( $sql );
            
            if( $this->count( $query ) > 0 ){
               $row = $this->fetch( $query );
               return $row['a'] > 0;
            }

            return false;
         }
			
			
			//Verifica se a tabela existe
			function tableExist( $table ){
				
				
				$sql = 'select count(*) as a from information_schema.TABLES where TABLE_SCHEMA="' . $this->db . '" and TABLE_NAME="' . $table . '"';
				$row = $this->fetch( $this->query( $sql ) );
				
				return $row['a'] > 0;
				
			}
			
			
			//Verifica se um determinado campo existe na tabela
			function fieldExist( $table, $field ){
				
				$sql = 'select count(*) as a from information_schema.COLUMNS where TABLE_SCHEMA = "' . $this->db . '" and TABLE_NAME="' . $table . '" and COLUMN_NAME="' . $field . '"';
				$row = $this->fetch( $this->query( $sql ) );
				
				return $row['a'] > 0;
				
			}
			

         //Busca um valor de um campo especifico
         function getVal( $table, $field, $compare, $max = 0 ){
         
            $ret = array();
            $sql = 'select ' . $field . ' from ' . $table . ' where ' . $compare;
            
            if( $max > 0 || is_string( $max ) ){
               $sql .= ' limit ' . $max;
            }
				
            
            //Se for trazer resultados
				if( $this->count( $sql ) ){
	            $query  = $this->query( $sql );
	            $result = $this->fetch_all( $query, true );
               foreach( $result as $a ) $ret[] = $a[0];
				}

            return $ret;

         }

         
      } 
   
   