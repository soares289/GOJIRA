<?php
/* Connection.class.php - Classe de conexão padrão do sistema
   Contem metodos para se trabalhar com o banco de dados de forma simples
   com funções que facilitam na interação.
   
   por Carlson A. Soares - 2011-02-17 09:06
   
   Ultimo Update:
      Count aceita comandos sql
*/

//CONSTANTES DA CLASSE

//Defaults
define( 'DB_NEW_LINK', true );
define( 'DB_DEF_USR' , 'user' );
define( 'DB_DEF_PASS', 'user$' );

//Error msgs
define( 'DB_CONNECTION_ERROR', 'Erro ao conectar na base de dados' );



      require_once( 'properties.class.php' );  //Propriedades
    
    
      class Connection extends Properties{
    
         private $conn;          //Objeto de conexão
         private $msg;           //Mensagens de aviso ou erro
         private $lastCommand;   //Ultimo comando executado
         private $db;				//Base de dados em que está conectado
         private $host;				//Host atual conectado
			private $user;				//Usuário que foi usado para conectar
			private $pwd;				//Senha para a base de dados
			
			
         /***   Construtores ***/
         function __construct(){
            
            $a = func_get_args();
            $i = func_num_args();
            
            if (method_exists($this,$f='__construct'.$i)) {
               call_user_func_array(array($this,$f),$a); 
            } else {
               throw( new Exception('Numero de parametros invalido') );
            }
         }
             
         function __construct1( $dbHost )                            { $this->connect( $dbHost, DB_DEF_USR, DB_DEF_PASS ); }
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
         function get_lastId() {      return mysql_insert_id( $this->conn ); }
			function get_db()          { return $this->db; }
        
        
        
        
        
        
         /***   Metodos do Objeto ***/
        
        
         //Conecta no banco de dados
         function connect($dbHost, $dbUser, $dbPass, $dbName = ''){

				$this->host = $dbHost;
				$this->user = $dbUser;
				$this->pwd  = $dbPass;
				
            $conn = @mysql_connect($dbHost, $dbUser, $dbPass, DB_NEW_LINK )
                                    or die ( DB_CONNECTION_ERROR );
            
            mysql_query("SET NAMES 'utf8'", $conn);
            mysql_query('SET character_set_connection=utf8', $conn);
            mysql_query('SET character_set_client=utf8', $conn);
            mysql_query('SET character_set_results=utf8', $conn);
            
            $this->conn = $conn;
            
            if( !empty( $dbName ) )  $this->selectDb( $dbName );

            return $conn;
         }


         //Seleciona a base de dados ativa da coexão
         function selectDb( $dbName ){
				
				$this->db = $dbName;
            return mysql_select_db( $dbName, $this->conn );
				
         }


         //Desconecta da base de dados
         function disconnect(){
            return @mysql_close( $this->conn );
         }



			//Reconecta a base de dados
			function reconnect(){
				
				$this->disconnect();
				$this->connect( $this->host, $this->user, $this->pwd, $this->db );
				
			}


			//Verifica se o servidor está connectado
			function isConnected(){
				
				//Se não for resource [ex. null, int, array, etc] é por que não está conectado
				if( ! is_resource( $this->conn ) ) return false;
				
				//Se for resource e tiver dado mensagem de que o server caiu, é por que caiu e está desconectado
				if( strpos( mysql_stat( $this->conn ), 'server has gone away' ) !== false ) return false;
				
				return true;
				
			}


         //Executa uma consulta no banco de dados
         function query( $sql ){
      
            $this->lastCommand = $sql;
            
				if( ! $query = mysql_query( $sql, $this->conn ) ){

					//Verifica se a conexão não caiu
					if( !$this->isConnected() ){	
						$this->reconnect();
						$query = mysql_query( $sql, $this->conn );
					}
					
				}
				
            return $query;
         }


         //Executa um comando na base de dados
         function execute( $query ){
				
				$ret = false;
				
            $this->lastCommand = $query;
				
            if( ! $ret = mysql_query( $query, $this->conn) ){
					
					//Verifica se a conexão não caiu
					if( !$this->isConnected() ){				
						$this->reconnect();
						$ret = mysql_query( $query, $this->conn);
					}
					
				}
				
				return $ret;
         }



         //Executa uma serie de comandos sql em uma transação
         function secureExec( $sql ){
            
            $this->execute('SET AUTOCOMMIT=0');
            $this->execute('START TRANSACTION');
            
            if( is_array( $sql ) ){
               
               foreach( $sql as $a ){
                  
                  if( ! $this->execute( $a ) ){
                     $this->execute('ROLLBACK'); 
                     $this->execute('SET AUTOCOMMIT=1');
                     return false;
                  }
               }
               
            } else {
               
               if( ! $this->execute( $sql ) ){
                  
                  $this->execute('ROLLBACK'); 
                  $this->execute('SET AUTOCOMMIT=1');
                  return false;
               }
               
            }
            
            $this->execute('COMMIT');
            $this->execute('SET AUTOCOMMIT=1');
            return true;
         }


         //Carrega uma linha do resultSet
         function fetch( $query, $lArray = false ){

            $lArray = ( $lArray ? MYSQL_NUM : MYSQL_ASSOC );
   			$row = mysql_fetch_array( $query, $lArray );
				
            return $row;
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
            
            if( is_string( $query ) ){
               $sql = 'select count(*) as a from (' . $query . ') as a';
               $ret = $this->query( $sql );
               $row = $this->fetch( $ret );
               $n   = $row['a'];
            } else {
               $n = @mysql_num_rows( $query );
               if( !is_numeric( $n ) ) $n = 0;
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
				
				if( ! $this->execute( $sql ) ){
					return array();
				}
				
				if( $this->count( $sql ) ){
	            $query = $this->query( $sql );
	            while( $row = $this->fetch( $query, true ) ){
	               $ret[] = $row[0];
	            }
				}

            return $ret;

         }

         
         
      } 
   
   