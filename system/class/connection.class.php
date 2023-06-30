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
define( 'DB_CONNECTION_NO_HOST', "Não foi informado um host para conexão com a base de dados" );
define( 'DB_CONNECTION_ERROR', "Erro ao conectar na base de dados: \n%s\n" );
define( 'DB_CONNECTION_INVALID_SQL', 'Comando sql inválido fornecido para consulta');
define( 'DB_CONNECTION_INVALID_QUERY', 'Objeto de consulta inválido');
define( 'DB_CONNECTION_INVALID_TABLE', 'Nome da tabela inválido');
define( 'DB_CONNECTION_INVALID_PARAMETER', 'Parametos inválidos para função %s');


      class Connection extends GojiraCore{
    
         protected static $connection;    //Objeto de conexão
         protected static $lastCommand;   //Ultimo comando executado
         
         protected static $database;		//Base de dados em que está conectado
         protected static $dbHost;			   //Host atual conectado
			protected static $dbUser;			   //Usuário que foi usado para conectar
			protected static $dbPassword;		//Senha para a base de dados
         

         //As propriedades estão ai para manter compatibilidade com sistemas antigos
         const FETCH_OBJECT = 0;
         const FETCH_ASSOC  = MYSQLI_ASSOC;
         const FETCH_NUM    = MYSQLI_NUM;
         public $FETCH_OBJECT = 0;
         public $FETCH_ASSOC  = MYSQLI_ASSOC;
         public $FETCH_NUM    = MYSQLI_NUM;
         
			
         function __construct1( $dbHost )                            { $this->configure( $dbHost, '', '' ); }
         function __construct3( $dbHost, $dbUser, $dbPass )          { $this->configure( $dbHost, $dbUser, $dbPass ); } 
         function __construct4( $dbHost, $dbUser, $dbPass, $dbName ) { $this->configure( $dbHost, $dbUser, $dbPass, $dbName ); } 
        
         //Destructor força a desconexão do banco de dados;
         //Desativado porque tava dando problema: Como o model extende connection,
         //Esse destruct desconectava no final do model, dando problema se tentasse usar a conexão
         //Em outros pontos apos o termino do model.
         //TODO - Deixar isso no engine.php quando ele for integrado ao system
         /*function __destruct() {
            $this->disconnect();
         }*/
        
        
        
        
         /***   Metodos de SET e GET de valores ***/
         function get_connection()  { return self::$connection; }
         function get_connected()   { return $this->isConnected(); }
         function get_lastCommand() { return self::$lastCommand; }
         function get_lastId()      { return mysqli_insert_id( $this->connection ); }
			function get_database()    { return self::$database; }
         function get_host()        { return self::$dbHost; }
        
        
        
         /***   Metodos do Objeto ***/
         function configure($dbHost, $dbUser, $dbPass, $dbName = ''){

            self::$dbHost     = $dbHost;
            self::$dbUser     = $dbUser;
            self::$dbPassword = $dbPass;
            self::$database   = $dbName;

         }
        

         //Conecta no banco de dados
         function connect(){

            //Quando não existe o host, identifica como uma sessão sem conexão com o banco
            if( empty( self::$dbHost ) ){
               throw(new Exception( DB_CONNECTION_NO_HOST ) );
               exit();
            }
            
            $connection = new mysqli(self::$dbHost, self::$dbUser, self::$dbPassword, self::$database );
            
            //Verifica se conectou corretamente
            if(mysqli_connect_errno()){
               throw(new Exception( sprintf(DB_CONNECTION_ERROR, mysqli_connect_error()) ) );
               exit();
            }
            
            self::$connection = $connection;
            
            $this->execute("SET NAMES 'utf8'");
            $this->execute('SET character_set_connection=utf8');
            $this->execute('SET character_set_client=utf8');
            $this->execute('SET character_set_results=utf8');
            $this->execute('SET lc_time_names=pt_BR');

            return $this;
         }


         //Seleciona a base de dados ativa da coexão
         function selectDatabase( $dbName ){
				
            self::$database = $dbName;
            $ret            = false;

            if( $this->connected ){
               $ret = self::$connection->select_db( self::$database );
            } 

            return $ret;
				
         }


         //Desconecta da base de dados
         function disconnect(){
            
            $ret = false;

            if( $this->connected ){
               $ret = @self::$connection->close();
            }

            return $ret;
         }


			//Reconecta a base de dados
			function reconnect(){
				
				$this->disconnect(); 
				return $this->connect();
				
			}


			//Verifica se o servidor está connectado
			function isConnected(){
				
				//Se não for objeto é por que não está conectado
				if( ! is_object( self::$connection ) ) return false;
				
				//Se for objeto, verifica se a conexão está ativa
				return self::$connection->ping();
				
			}


         //Checa se está conectado e gera um erro caso não esteja
         private function checkConnection(){

            if( !$this->isConnected() ){
               $this->reconnect();
            }

            return true;
         }


         //Executa uma consulta no banco de dados
         function query( $sql ){
            
            $this->checkConnection();

            self::$lastCommand = $sql;
            
            $query = self::$connection->query( $sql );
            
            if( !is_object($query) && $query === false ){
               throw( new Exception(DB_CONNECTION_INVALID_SQL) );
            }

            return $query;
         }


         //Executa um comando na base de dados
         function execute( $sql ){
            
            $this->checkConnection();
				
            $ret = false;
				
            self::$lastCommand = $sql;
                        
				//Executa o comando
            if( $stmt = self::$connection->prepare( $sql ) ){
               return $stmt->execute();
            }
            
				return false;
         }


         //Executa uma serie de comandos sql em uma transação
         function secureExec( $sql ){
            
            $this->checkConnection();
            
            if( ! is_array( $sql ) ) $sql = array( $sql );
            
            self::$connection->autocommit( false );
            self::$connection->begin_transaction( MYSQLI_TRANS_START_WITH_CONSISTENT_SNAPSHOT );
               
            foreach( $sql as $a ){
               if( ! $this->execute( $a ) ){
                  self::$connection->rollback(); 
                  self::$connection->autocommit( true );
                  return false;
               }
            }
            
            self::$connection->commit();
            self::$connection->autocommit( true );
            
            return true;
         }


         //Carrega uma linha do resultSet
         function fetch( $query, $type = MYSQLI_ASSOC){
            
            if( ! is_object( $query ) ){
               throw( new Exception(DB_CONNECTION_INVALID_QUERY));
            }
            
            if( $type == $this->FETCH_OBJECT ){
               $row = $query->fetch_object();
            } else {
               $row = $query->fetch_array( $type );
            }
				
            return $row;
         }
         
         
         //Carrega todo o resultset em um array
         function fetchAll( $query, $type = MYSQLI_ASSOC, $map_function = null ){
            
            if( is_object( $map_function ) ){
               for($set = []; $row = $this->fetch( $query, $type ); $set[] = $map_function($row));               
            } else {
               for($set = []; $row = $this->fetch( $query, $type ); $set[] = $row);
            }
            
            return $set;
         }


         //Seleciona o valor mais alto de uma coluna
         function max( $table, $field ){
            
            if( empty( $table ) || empty( $field ) ){
               throw( new Exception( sprintf(DB_CONNECTION_INVALID_PARAMETER, 'MAX') ) );
            }

            $this->checkConnection();

            $query = $this->query( 'select max(`' . $field . '`) as a from `' . $table . '`' );
            $row = $this->fetch($query);
            return $row['a'];

         }


         //Seleciona o valor mais baixo
         function min( $table, $field ){
            
            if( empty( $table ) || empty( $field ) ){
               throw( new Exception(sprintf(DB_CONNECTION_INVALID_PARAMETER, 'MIN')) );
            }

            $this->checkConnection();

            $query = $this->query( 'select min(`' . $field . '`) as a from `' . $table . '`' );
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
               throw( new Exception(sprintf(DB_CONNECTION_INVALID_PARAMETER, 'EXIST')) );
            }

            $this->checkConnection();

            $sql = '';

            if( !empty( $column  ) ) $sql .= '`' . $column . '`' . ( strtolower($value ?? '') == 'null' || is_null($value) ? ' IS NULL ' : '="' . $value . '" ');
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
               throw( new Exception(DB_CONNECTION_INVALID_TABLE) );
            }

            $this->checkConnection();

				$sql = 'SELECT COUNT(*) AS a FROM information_schema.TABLES WHERE TABLE_SCHEMA="' . $this->database . '" and TABLE_NAME="' . $table . '"';
  				$row = $this->fetch( $this->query( $sql ) );

				return $row['a'] > 0;
				
			}
			
			
			//Verifica se um determinado campo existe na tabela
			function fieldExist( $table, $field ){
            
            if( empty( $table ) || empty( $field ) ){
               throw( new Exception(sprintf(DB_CONNECTION_INVALID_PARAMETER, 'FIELDEXIST')) );
            }

            $this->checkConnection();

				$sql = 'select count(*) as a from information_schema.COLUMNS where TABLE_SCHEMA = "' . $this->database . '" and TABLE_NAME="' . $table . '" and COLUMN_NAME="' . $field . '"';
				$row = $this->fetch( $this->query( $sql ) );
				
				return $row['a'] > 0;
				
			}
			

         //Busca um valor de um campo especifico
         function getValue( $table, $field, $compare, $max = 0 ){
            
            if( empty( $table ) || empty( $field ) || empty( $compare ) ){
               throw( new Exception(sprintf(DB_CONNECTION_INVALID_PARAMETER, 'GETVALUE')) );
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
   
   