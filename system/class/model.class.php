<?php
//TODO - SaveData (salva multiplos Rows)
//TODO - Integrar com o schema (gerente de db)

		require_once( "properties.class.php" );

		abstract class Model extends Properties{
			
			//Propriedades da classe
			protected $globals;			//Valores globais do sistema
         protected $tools;
         protected $environment;
			
			private   $table;				//Tabela base do modelo
			private   $structure;		//Estrutura da tabela
			private   $sql = array();  //SQL que deve ser usado na proxima busca
			
			
			/***   Construtores ***/
			function __construct(){
				
				@session_start();
				
				$a = func_get_args();
				$i = func_num_args();
				
				if (method_exists($this,$f='__construct'.$i)) {
					call_user_func_array(array($this,$f),$a); 
				} else {
					throw( new Exception('Numero de parametros invalido') );
				}
			}
			
			 
			//Construtores do objeto
			function __construct1( $globals ){
				
				$this->globals     = $globals;
            $this->tools       = $globals->tools;
            $this->environment = $globals->environment;
				$this->table       = strtolower( substr( get_class( $this ), 0, -6) );
				$this->updateTableInfo();
				$this->resetCommand();
				
			}
			
			
			
			
			//propriedades do objeto
			//$table
			function set_table( $val ){
				if( $this->globals->conn->tableExist( $val ) ){
					
					$this->table = $val;
					$this->updateTableInfo();
					
				}
			}
			function get_table(){ return $this->table; }
			
			
			
			//Invoca o modelo correto
			static function Load( $class, $globals ){
				
				$file    = strtolower( $class ) . '.php';
				$dir     = $globals->environment->modelPath;
				$class   = ucfirst($class) . '_Model';
				
				//Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){
					
					//Instancia o objeto
					require_once( $dir . $file );
					return new $class( $globals );
					
				} else {
					
					//Model não localizado no disco
					throw( new ModelException( "Model <strong>\"" . $class . "\"</strong> not found in <strong>\"" . $dir . $file . "\"</strong>", 0x2001 ) );
					
				}
				
			}
			
		
			//Gera o comando sql da consulta
			private function makeCommand( $obj = null){
				
				if( is_null( $obj ) ) $obj = $this;
				
				//TODO - Adicionar ANTI-INJECTION na geração
				//TODO - Adicionar validação de valores como Tipo do Join, operadores do Where, campos do order by;
				
				//Parte dos campos
				if( !isset( $obj->sql[ 'field' ] ) || count( $obj->sql[ 'field' ] ) <= 0 )
               throw( new ModelException( 'No <strong>fields</strong> defined for the command', 2101 ) );
				
				
				$sql = 'SELECT ';
				//Campos base do select
				foreach( $obj->sql['field'] as $a){
					$sql .= (strlen( $sql ) > 8 ? ', ' : '') .
							  (empty( $a[2] ) ? '' : '`' . $a[2] . '`.' ) .
							  $a[0] . (empty( $a[1] ) ? '' : ' AS "' . $a[1] . '"');
				}
				
				$sql .= ' FROM ' . (isset( $obj->sql['table'] ) ? '`' . $obj->sql['table'] . '`' : '`' . $obj->table . '`') . ' as a';
				
				//parte dos joins
				if( isset( $obj->sql[ 'join' ] ) && count( $obj->sql[ 'join' ] ) > 0 ){
					$b = 98;
					foreach( $obj->sql[ 'join' ] as $a ){
						
						if( is_object( $a ) ){
						} else {
						}
						$sql .= ' ' . strtoupper($a[0]) . ' JOIN ' .
										  (is_object( $a[1] ) ? '(' . $this->makeCommand($a[1]) . ')' : '`' . $a[1] . '`') .
										  ' as ' . (empty( $a[3] ) ? chr( $b++ ) : '`' . $a[3] . '`') . ' on ' . $a[2];
					}
				}
				
				//Parte do where
				if( isset( $obj->sql['where'] ) && count( $obj->sql['where'] ) > 0 ){
					$sql .= ' WHERE ';
					foreach( $obj->sql['where'] as $a ){
						$sql .= $a[0] . ' ' . $a[1] . ' ' . $a[2] . (count( $a ) > 3 ? ' ' . $a[3] . ' ' : '');
					}
				}
				
				//Parte do group by
				if( isset( $obj->sql['group'] ) && count( $obj->sql['group'] ) > 0 ){
					$sql .= ' GROUP BY ';
					$g    = 0;
					foreach( $obj->sql['group'] as $a ){
						$sql .= ( $g++ > 0 ? ', ' : '') . $a;
					}
				}
				
            //Parte do having
				if( isset( $obj->sql['having'] ) && count( $obj->sql['having'] ) > 0 ){
					$sql .= ' HAVING ';
					foreach( $obj->sql['having'] as $a ){
						$sql .= ' ' . $a . ' ';
					}
				}
				
            
            
				//Parte do order by
				if( isset( $obj->sql['order'] ) && count( $obj->sql['order'] ) > 0 ){
					$sql .= ' ORDER BY ';
					$g    = 0;
					foreach( $obj->sql['order'] as $a ){
						$sql .= ( $g++ > 0 ? ', ' : '') . ( $a[0] > 0 || strpos( $a[0], '.' ) !== false || strpos( $a[0], '(' ) !== false ? $a[0] : '`' . $a[0] . '`') . ' ' . $a[1];
					}
				}
				
				
				//Parte do LIMIT
				if( isset( $obj->sql['limit'] ) && count( $obj->sql['limit'] ) > 0 ){
					$sql .= ' LIMIT ' . $obj->sql[ 'limit' ][0] . ($obj->sql[ 'limit' ][1] > 0 ? ', ' . $obj->sql[ 'limit' ][1] : '');
				}
            
				return $sql;
			}
			

			//Reseta o comando
			function resetCommand(){
				$this->sql = array( 'field'  => array(), 'where'  => array(), 'order'  => array(), 'group'  => array(), 'having'  => array(), 'join'   => array(), 'limit' => array());
			}

			
			//Conta quantos registros viriam no comando
			function countCommand(){

				$sql = $this->makeCommand();			
				return $this->globals->conn->count( $sql );
				
			}
			
			
			//Retorna um objeto com as variaveis para gerar um comando sql
			function getCommandObj( $lReset = true){
				
				$obj          = new StdClass();
				$obj->sql     = $this->sql;
				$obj->table   = $this->table;
				$obj->command = $this->makeCommand();
				if( $lReset ) $this->resetCommand();
				
				return $obj;
			}
			
			//Retorna o valor do comando
			function getCommandData( $lReset = true){
				
				$index = 0;
				$conn  = $this->globals->conn;
				
				try{
					$sql  = $this->makeCommand();
				} catch( ModelException $e ){
					throw( $e );
				}
				
				$ret  = new CustomData( null );
				
				if( $conn->count( $sql ) > 0 ){
					
					$query = $conn->query( $sql );
					$row   = $conn->fetch( $query );
					
					do{
						foreach( $row as $i => $a ){
							$ret[ $index ][ $i ] = $a;
						}
						$index++;
					} while( $row = $conn->fetch( $query ) );
					
				}
				
				if( $lReset ) $this->resetCommand();
				return $ret;
				
			}
			
			//Adiciona um campo na consulta SQL
			function field( $name, $alias = '', $table = '' ){ $this->sql['field'][] = array( $name, $alias, $table ); return $this; }
			
			//From - No caso de fazer um select de outra tabela, sem precisar mudar o campo TABLE
			function from( $table ){ $this->sql['table'] = $table; return $this; }

			//Comparações
			function where( $field, $compare, $value ){ $this->sql['where'][] = array($field, $compare, $value ); return $this; }
			
			//Separadores do where
			function wOr(){   $this->sql['where'][ count( $this->sql['where'] ) - 1 ][] = 'OR';   return $this; }
			function wAnd(){	$this->sql['where'][ count( $this->sql['where'] ) - 1 ][] = 'AND';  return $this; }
			
			//order by
			function order( $ord, $dir = 'ASC' ){ $this->sql['order'][] = array($ord, $dir); return $this; }
			
			//Group by
			function group( $field ){ $this->sql['group'][] = $field; return $this; }
			
         //having
         function having( $sintax ){ $this->sql['having'][] = $sintax; }
         
         //Separadores do having
         function hOr(){ $this->sql['having'][] = 'OR'; }
         function hAnd(){ $this->sql['having'][] = 'AND'; }
         
         
			//Join
			function join( $type, $table, $comp, $alias = '' ){ $this->sql[ 'join' ][] = array( $type, $table, $comp, $alias );  return $this; }
			
			//Limit de registros
			function limit( $init, $end = 0 ){ $this->sql[ 'limit' ] = array( $init, $end); return $this; }
			
			
			
			//Atualiza as informações da tabela
			function updateTableInfo(){
				
				$conn =  $this->globals->conn;
				$sql  =  'SELECT COLUMN_NAME AS `name`, ' .
							       'COLUMN_DEFAULT as `default`, ' .
							       'IS_NULLABLE = "YES" AS `null`, ' .
							       'DATA_TYPE AS `type`, ' .
							       'IF(CHARACTER_MAXIMUM_LENGTH IS NULL, NUMERIC_PRECISION, CHARACTER_MAXIMUM_LENGTH) AS `length`, ' .
							       'IF(CHARACTER_MAXIMUM_LENGTH IS NULL, NUMERIC_SCALE, 0) AS `decimal`, ' .
							       'COLUMN_KEY = "PRI" AS `primary_key`, ' .
							       'INSTR(EXTRA,"auto_increment") as `auto_increment`, ' .
							       'COLUMN_KEY = "PRI" OR COLUMN_KEY = "UNI" as `unique`, ' .
							       'NOT( COLUMN_KEY = "" OR COLUMN_KEY IS NULL) as `index` ' .
							   'FROM information_schema.COLUMNS ' .
							  			'WHERE TABLE_SCHEMA="' . $this->globals->db->name . '" ' .
										  'AND TABLE_NAME="' . $this->table . '" ORDER BY ORDINAL_POSITION';

				$query = $this->globals->conn->query( $sql );
				
				$this->structure = array();
				while( $row = $conn->fetch( $query ) ){
					
					$obj = new StdClass();
					$obj->name              = $row['name'];
					$obj->default_value     = $row['default'];
					$obj->accept_null       = $row['null'];
					$obj->field_type        = $row['type'];
					$obj->length            = $row['length'];
					$obj->decimal           = $row['decimal'];
					$obj->is_primary_key    = $row['primary_key'];
					$obj->is_auto_increment = $row['auto_increment'];
					$obj->is_unique         = $row['unique'];
					$obj->is_index          = $row['index'];
					
					$this->structure[ $row['name'] ] = $obj;
				}
			
			}
			
			
			
			//Busca um registro(ou um range de registro) da tabela atual
			function getData( $key = '', $sort = '', $limit = '' ){
				
				$this->updateTableInfo();
				
				//Busca as informações usadas no processo
				$conn    = $this->globals->conn;
				$tools   = $this->globals->tools;
				$primary = array();
				$where   = '';
				$data    = new CustomData( $this->structure );
				
				//Filtra as partes usadas
				$primary = $this->getPrimary();
				$where   = $this->getWhere( $key, $primary );
				
				$sql = 'SELECT ';
				foreach( $this->structure as $col ){
					
					$close = '';
					
					if( strlen( $sql ) > 7 ) $sql .= ', ';
					
					//Formata alguns valores
   				if( $col->field_type == 'datetime' || $col->field_type == 'timestamp' ){
						$sql   .= 'DATE_FORMAT(';
						$close  = ',"%d/%m/%Y %H:%i:%s")';
					} elseif( $col->field_type == 'date' ){
						$sql   .= 'DATE_FORMAT(';
						$close  = ',"%d/%m/%Y")';
					}
						
					$sql .= 'a.`' . $col->name . '`' . $close . ' as "' . $col->name . '"';
					
				}
				$sql .= ' FROM `' . $this->table . '` as a' . (empty( $where ) ? '' : ' WHERE ' . $where );
				
				
				//Caso precise vir em uma ordem especifica
				if( is_array( $sort ) ){
					$order = '';
					foreach( $sort as $a ) $order .= (empty( $order ) ? '' : ', ') . '`' . $a . '`';
					$sql .= ' ORDER BY ' . $order;
				} elseif( !empty( $sort ) ){
					$sql .= ' ORDER BY ' . $sort;
				}
				
				
				//Caso seja preciso passar um limit de registros
				if( is_array( $limit ) ) $limit = $limit[0] . (isset( $limit[1] ) ? ', ' . $limit[1] : '');
				if( !empty( $limit ) )   $sql .= ' LIMIT ' . $limit;
				
				$query = $conn->query( $sql );

				if( $conn->count( $sql ) > 0 ){
					while( $row = $conn->fetch( $query ) ){
					
						$pk =	'';
						foreach( $primary as $a ) $pk .= (empty( $pk ) ? '' : ',' ) . $row[ $a->name ];
						foreach( $row as $i => $a ) $data[ $pk ][ $i ] = $a;
						
					}
				}
				
				return $data;
				
			}
			
			
			//Busca a quantidade de resultados que tem na tabela, baseado nas chaves passadas
			function countData( $key ){
				
				$sql   = 'SELECT 1 FROM `' . $this->table . '`';
				$where = $this->getWhere( $key );
				
				if( !empty( $where ) ) $sql .= ' WHERE ' . $where;
				
				return $this->globals->conn->count( $sql );	
				
			}
			
			
			//Retorna um objeto row com os valores padroes dos campos
			function getEmptyRow(){
				
				$row = new CustomRow( $this->structure );
				foreach( $this->structure as $a ){
					$row[ $a->name ] = $a->default_value;
				}
				return $row;
				
			}
			
			
			//Deleta um registro ou uma serie de registros
			function delete( $key ){
				
				$sql = 'DELETE FROM `' . $this->table . '` WHERE ' . $this->getWhere( $key );
				
				if( ! $this->globals->conn->execute( $sql ) ){
					throw( new ModelException( "Error while deleting the registry", 2102 ) );
				}
				
				return true;
				
			}
			
			
			//Salva uma linha na base de dados
			function saveRow( $row, $table = '', $retCommand = false ){
				
				$table     = (empty( $table ) ? $this->table : $table);
				$sql       = '';
				$where     = '';
				
				$conn      = $this->globals->conn;
				
				$structure = $row->structure;
				$pk        = array();
				$lUpdate   = false;

				//Verifica se os dados estão ok
				foreach( $structure as $col ){
               
               $col_value = $row[ $col->name ]->value;
               
					//Campos auto_increment ou timestamp são ignorados na hora de salvar, por isso, não precisa de verificação
					if( !(($col->is_auto_increment && empty($col_value)) || $col->field_type == 'timestamp') ){
						
						//Verifica se é chave primaria e tem um valor
						if( $col->is_primary_key && empty( $col_value ) ){
							throw( new ModelException( 'Primary Key <strong>' . $col->name . '</strong> has no value and are not auto_increment', 2103 ) );
						}
						
						//Se o campo estiver nulo, mas não aceitar valores nulos
						if( $col->accept_null == false &&
							 $col->is_auto_increment == false &&
                      (is_null( $row[ $col->name ]->value ) || strtolower($row[ $col->name ]->value === 'null')) ){
							throw( new ModelException( 'Field <strong>' . $col->name . '</strong> cannot be null', 2104 ) );
						}
					}
					
					$this->formatField( $row[ $col->name ] );
					
					if( $col->is_primary_key ) $pk[ $col->name ] = $row[ $col->name ]->value;
					
				}
				

				//Monta a sentença de comparação no caso de ser um update e usa para identificar o mesmo
				foreach( $pk as $i => $a ) $where .= (empty( $where ) ? '' : ' AND ') . '`' . $i . '`="' . $a . '"';
				
				if( $conn->exist( $table, "1", 1, $where ) ){
					
					//É uma alteração
					foreach( $structure as $col ){
						if( ! ($col->is_auto_increment || $col->field_type == 'timestamp' || $col->is_primary_key) ){
							$sql .= (empty( $sql ) ? '' : ', ' );
							$sql .= '`' . $col->name . '`=' . $this->sqlField( $row[ $col->name ]);
						}
					}
					
					$sql     = 'UPDATE `' . $table . '` SET ' . $sql . ' WHERE ' . $where;
					$lUpdate = true;
					
				} else {
					
					//É uma inclusão
					$header = '';
					foreach( $structure as $col ){
						if( ! ($col->is_auto_increment || $col->field_type == 'timestamp') ){
							$header .= (empty( $sql ) ? '' : ', ') . '`' . $col->name . '`';
							$sql    .= (empty( $sql ) ? '' : ', ') . $this->sqlField( $row[ $col->name ] );
						}
					}
					$sql     = 'INSERT INTO `' . $table . '`(' . $header . ') VALUES(' . $sql . ')';
				}
            
				//Executa o comando na base de dados
				if( $conn->execute( $sql ) ){
					
   				$sql = $where = '';
					
					foreach( $pk as $i => $a ){
					    	
						if( $structure[ $i ]->is_auto_increment && (empty( $a ) || strtolower($a) == 'null') ){
							$a = $conn->lastId;
						}
						
						$where .= (empty( $sql ) ? '' : ' AND ') . '`' . $i . '`="' . $a . '"';
						$sql   .= (empty( $sql ) ? '' : ', ') . '`' . $i . '`';
						
					}
					
					$sql = 'SELECT ' . $sql . ' FROM `' . $table . '` WHERE ' . $where;
               
					$row = $conn->fetch( $conn->query( $sql, true ) );
					
				} else {
					
					//Se der erro na hora de salvar, joga um erro para o nivel de cima
					throw( new ModelException( "Error while saving - " . $sql, 2105 ) );
					
				}
				
				return $row;
				
			}

			
			
			//Busca um array com as chaves primarias da estrutura
			function getPrimary( $structure = ''){
				
				$primary = array();
            if( empty( $structure ) ) $structure = $this->structure;
				
				//Seleciona todas as chaves primarias( caso exista mais de uma )
				foreach( $structure as $col ){
					if( $col->is_primary_key ) $primary[] = $col;
				}
				
				return $primary;
				
			}
			
			
			//Retorna a parte de um where padrao, usado internamente
			function getWhere( $key, $structure = '' ){
				
            if( empty( $structure ) ) $structure = $this->structure;
				
            $primary = $this->getPrimary( $structure );
				$tools   = $this->globals->tools;
				$where   = '';
				
				//Monta a parte de comparação
            if( $key === '' ){
               $where = '1=1';
               
				}elseif( is_numeric( $key ) ){
               
               $where .= $primary[ 0 ]->name . '="' . $tools->antiInjection( $key ) . '"';
               
            } elseif( is_string( $key ) ){
               
               $where = $key;
               
				} elseif( is_array( $key ) ){
               
               $c = 0;
               foreach( $key as $a ){
                  if( $c < count( $primary ) )
                     $where .= (strlen( $where ) <= 0 ? '' : ' AND ') . $primary[ $c ]->name . '="' . $a . '"';
                  $c++;
               }
               
            }

				return $where;
				
			}
			
			
			//Formata o campo baseado no seu conteudo
			function formatField( $field ){
				
				$tools = $this->globals->tools;
				
            if( strpos( '[blog][mediumblob][bigblob][tinyblob][smallblob][text][mediumtext][bigtext][tinytext][smalltext]', $field->format->field_type ) ){
               $field->value = $tools->antiInjection( $field->value, true, true );
            } else {
               $field->value = $tools->antiInjection( $field->value );  
            }
				
				
				//Formatando as datas para o formato mysql
            if( $field->value == 'NULL' ){
               $field->value = 'NULL';
            } elseif( $field->format->field_type == 'datetime' &&
							(strlen( $field->value ) == 16 || strlen( $field->value ) == 19) ){
					$field->value = $tools->dateToSql( $field->value, true );
				} elseif( $field->format->field_type == 'date' &&
                     (strlen( $field->value ) == 10 || strlen( $field->value ) == 8) ){
               $field->value = $tools->dateToSql( $field->value );
				} elseif( $field->value == '' && strpos('[datetime][integer][decimal][double][float][enum][int][smallint][bigint][tinyint][unsigned]', $field->format->field_type) !== false ){
					$field->value = 'NULL';
            }
				
			}
			
			
			//formata o campo para o mysql
			function sqlField( $field ){
				
				$ret = ''; 			
				
            if( $field->value == 'NULL' ){
               $ret = 'NULL';
               
            } elseif( strpos( '[datetime][integer][decimal][double][float][int][smallint][bigint][tinyint][unsigned]', $field->format->field_type ) === false ){
					$ret = '"' . $field->value . '"';

            } elseif( $field->format->field_type == 'enum' ){
               if( $field->value > 0 ){
                  $ret = $field->value;
               } else {
                  $ret = '"' . $field->value . '"';
               }

				} else {
					$ret = $field->value;
				}
				
				return $ret;
				
			}
			
		}



		//Objeto de erro personalizadp
		class ModelException extends Exception{
			
			function __construct( $message, $err_cod ){
				parent::__construct( $message, $err_cod );
			}
			
		}

