<?php
//TODO - SaveData (salva multiplos Rows)
//TODO - Integrar com o schema (gerente de db)

		abstract class Model extends Connection{

			//Propriedades da classe
			protected $globals;			//Valores globais do sistema

			private   $table;				//Tabela base do modelo
			private   $structure;		//Estrutura da tabela
			private   $sql = array();  //SQL que deve ser usado na proxima busca

         private $mysqlFuncRegex = '';
         private $mysqlLiteralRegex = '/\b(NULL|CURRENT_TIMESTAMP)\b/ui';      //Palavras reservadas ou nomes de váriaveis que não devem ser escapados
         private $mysqlFunctions = ['CURRENT_TIMESTAMP','CURDATE','NOW','CURTIME','ADDDATE','ADDTIME','WEEKDAY','WEEKOFYEAR','YEAR','MONTH','DAY','HOUR','MINUTE','SECOND','SEC_TO_TIME','TIME_TO_SEC','STR_TO_DATE','DATEDIFF','TIMEDIFF','DATE_SUB','DAYNAME','DAYOFMONTH', 'DAYOFWEEK','DAYOFYEAR','FROM_DAYS','LAST_DAY', 'MICROSECOND', 'DATE_FORMAT',
                                    'UPPER', 'LOWER', 'TRIM', 'SUBSTR', 'STRCMP', 'SPACE', 'SOUNDS_LIKE', 'SOUNDEX', 'RTRIM', 'RPAD', 'RIGHT', 'LTRIM', 'LPAD', 'LEFT', 'REVERSE', 'REPLACE', 'REGEX', 'QUOTE', 'MID', 'MATCH',
                                    'MIN','MAX','AVG','COUNT','GROUP_CONCAT', 'SUM',
                                    'DEFAULT', 'RAND', 'UUID', 'ANY_VALUE', 'FORMAT',
                                    'COMPRESS', 'DECODE', 'ENCODE', 'MD5', 'SHA1', 'SHA2', 'UNCOMPRESS', 'VALIDATE_PASSWORD_STRENGTH',
                                    'ABS', 'ACOS', 'ASIN', 'ATAN', 'CEIL', 'CEILING', 'CONV', 'COS', 'COT', 'DEGREES', 'EXP', 'FLOOR', 'LN', 'LOG', 'LOG10', 'LOG2', 'MOD', 'PI', 'POW', 'POWER', 'RADIANS', 'ROUND', 'SIGN', 'SIN', 'SQRT', 'TAN', 'TRUNCATE'];


			//Construtores do objeto
			function __construct1( $globals ){

            $reserved      = ['Model','AppModel','ComponentModel'];
            $class         = get_class( $this );

				$this->globals    = $globals;
            $this->connection = $this->globals->connection->connection;
            $this->database   = $this->globals->connection->database;
            $this->host       = $this->globals->connection->host;
            
            //Limpa o nome da classe para tentar buscar o nome da tabela
            $table       = explode('_', $class);
            $this->table = strtolower( substr( $class, 0, strlen( $class) - strlen(end($table)) -1 ) );

            
            if( !in_array( $class, $reserved ) ){
               $this->updateTableInfo();
               $this->resetCommand();
            }

            $this->mysqlFuncRegex = '/(' . implode('|',$this->mysqlFunctions) . ')\(.*\)/ui';

			}




			//propriedades do objeto
			//$table
			function set_table( $val ){

            if( $this->connected ){
               if( $this->tableExist( $val ) ){
                  $this->table = $val;
                  $this->updateTableInfo();
               } else {
                  throw( new ModelException( "Invalid table: <strong>\"" . $val . "\"</strong>", 0x2003 ) );
               }
            } else {
               $this->table = $val;
            }
			}
			function get_table(){ return $this->table; }



			//Invoca o modelo correto
			static function Load( $class, $globals = ''){

            if( $globals == '' ){
               $globals = $GLOBALS['globals'];
            }

				$file    = strtolower( $class ) . '.php';
				$dir     = $globals->environment->modelPath;
				$class   = ucfirst($class) . '_Model';

				//Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){

					//Instancia o objeto
               require_once( $dir . $file );
               try{
                  $objModel = new $class( $globals );
                  return $objModel;
               } catch( Exception $e ){
                  throw( new ModelException( "Error in instantiation of Model <strong>\"" . $class . "\"</strong> MSG: " . $e->getMessage(), 0x2002 ) );
               }
               
				} else {

					//Model não localizado no disco
					throw( new ModelException( "Model <strong>\"" . $class . "\"</strong> not found in <strong>\"" . $dir . $file . "\"</strong>", 0x2001 ) );

				}

			}
         
         
         //Verifica se um Model existe
         static function Exists( $class ){
            
            $ret     = false;
            $globals = $GLOBALS['globals'];
            $file    = strtolower($class) . '.php';
            $class   = ucwords(strtolower($class), "_0123456789") . '_Model';
				$dir     = $globals->environment->modelPath;
            
            //Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){

               require_once( $dir . $file );
               
               if( class_exists( $class ) ){
                  $ret = true;
               }
            }
            
            return $ret;
         }


			//Adiciona um campo na consulta SQL
			protected function field( $name, $alias = '', $table = '' ){ $this->sql['field'][] = array( $name, $alias, $table ); return $this; }

			//From - No caso de fazer um select de outra tabela, sem precisar mudar o campo TABLE
			protected function from( $table ){ $this->sql['table'] = $table; return $this; }

			//Comparações
			protected function where( $field, $compare, $value ){ $this->sql['where'][] = array($field, $compare, $value ); return $this; }

			//Separadores do where
			protected function wOr(){   $this->sql['where'][ count( $this->sql['where'] ) - 1 ][] = 'OR';   return $this; }
			protected function wAnd(){	$this->sql['where'][ count( $this->sql['where'] ) - 1 ][] = 'AND';  return $this; }

			//order by
			protected function order( $ord, $dir = 'ASC' ){ $this->sql['order'][] = array($ord, $dir); return $this; }

			//Group by
			protected function group( $field ){ $this->sql['group'][] = $field; return $this; }

         //having
         protected function having( $sintax ){ $this->sql['having'][] = $sintax; return $this; }

         //Separadores do having
         protected function hOr(){ $this->sql['having'][] = 'OR'; return $this; }
         protected function hAnd(){ $this->sql['having'][] = 'AND'; return $this; }

			//Join
			protected function join( $type, $table, $comp, $alias = '' ){ $this->sql[ 'join' ][] = array( $type, $table, $comp, $alias );  return $this; }

			//Limit de registros
			protected function limit( $init, $end = 0 ){ $this->sql[ 'limit' ] = array( $init, $end); return $this; }

			//Gera o comando sql da consulta
			private function makeCommand( $obj = null){

				if( is_null( $obj ) ) $obj = $this;

				//TODO - Adicionar ANTI-INJECTION na geração
				//TODO - Adicionar validação de valores como Tipo do Join, operadores do Where, campos do order by;

				//Parte dos campos
				if( !isset( $obj->sql[ 'field' ] ) || count( $obj->sql[ 'field' ] ) <= 0 )
               throw( new ModelException( 'No <strong>fields</strong> defined for the command', 0x2011 ) );


				$sql = 'SELECT ';
				//Campos base do select
				foreach( $obj->sql['field'] as $a){
					$sql .= (strlen( $sql ) > 8 ? ', ' : '') .
							  (empty( $a[2] ) ? '' : '`' . $a[2] . '`.' ) .
							  $a[0] . (empty( $a[1] ) ? '' : ' AS "' . $a[1] . '"');
				}

            $table = (isset( $obj->sql['table'] ) ? $obj->sql['table'] : $obj->table);
				$sql .= ' FROM ' . (is_object( $table ) ? '(' . $this->makeCommand($table) . ')' : '`' . $table . '`') . ' as a';

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
                  $sql .= $a[0] . ' ' . $a[1] . ' ' . (is_object($a[2]) ? '(' . $this->makeCommand($a[2]) . ')' : $a[2]) .
                              (count( $a ) > 3 ? ' ' . $a[3] . ' ' : '');
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
				$this->sql = [ 'field' => [], 'where' => [], 'order' => [], 'group' => [], 'having' => [], 'join' => [], 'limit' => []];
			}


			//Conta quantos registros viriam no comando
			function countCommand(){

				$sql = $this->makeCommand();
				return $this->count( $sql );

			}


			//Retorna um objeto com as variaveis para gerar um comando sql
			function getCommandObject( $lReset = true ){

				$obj          = new StdClass();
				$obj->sql     = $this->sql;
				$obj->table   = $this->table;
				$obj->command = $this->makeCommand();
				if( $lReset ) $this->resetCommand();

				return $obj;
			}

			//Retorna o valor do comando
			function getCommandData( $lReset = true, $lArray = false ){

				try{
					$sql  = $this->makeCommand();
				} catch( ModelException $e ){
					throw( $e );
				}

				$ret = [];

				if( $this->count( $sql ) > 0 ){

					$query = $this->query( $sql );
               $ret   = $this->makeDataObject( $query, $lArray );

				}

				if( $lReset ) $this->resetCommand();
				return $ret;

			}


         protected function makeDataObject( $query, $lArray = false, $primary = null ){

            $ret   = [];
            $index = -1;

            while( $row = $this->fetch( $query ) ){

               //Monta o indice usando as chaves primarias, caso não tenha as chaves, usa um indice numerico
               if( is_array( $primary )){
                  $index = [];
                  foreach( $primary as $a ) $index[] = $row[ $a ];
                  $index = implode( ',', $index );

               } else {
                  $index++;
               }

               if( $lArray ){
                  $ret[ $index ] = [];
                  foreach( $row as $i => $a ){
                     $ret[ $index ][$i] = $a;
                  }

               } else {
                  $ret[ $index ] = new StdClass();
                  foreach( $row as $i => $a ){
                     $ret[ $index ]->$i = $a;
                  }
               }
            }

            return $ret;
         }



			//Atualiza as informações da tabela
			function updateTableInfo(){

            if( $this->isConnected() ){
               $sql  =  'SELECT COLUMN_NAME AS `name`, ' .
                              'COLUMN_DEFAULT as `default`, ' .
                              'COLUMN_DEFAULT IS NULL as `default_is_null`, ' .
                              'IS_NULLABLE = "YES" AS `null`, ' .
                              'DATA_TYPE AS `type`, ' .
                              'IF(CHARACTER_MAXIMUM_LENGTH IS NULL, NUMERIC_PRECISION, CHARACTER_MAXIMUM_LENGTH) AS `length`, ' .
                              'IF(CHARACTER_MAXIMUM_LENGTH IS NULL, NUMERIC_SCALE, 0) AS `decimal`, ' .
                              'COLUMN_KEY = "PRI" AS `primary_key`, ' .
                              'INSTR(EXTRA,"auto_increment") as `auto_increment`, ' .
                              'COLUMN_KEY = "PRI" OR COLUMN_KEY = "UNI" as `unique`, ' .
                              'NOT( COLUMN_KEY = "" OR COLUMN_KEY IS NULL) as `index` ' .
                           'FROM information_schema.COLUMNS ' .
                                 'WHERE TABLE_SCHEMA="' . $this->globals->database->name . '" ' .
                                 'AND TABLE_NAME="' . $this->table . '" ORDER BY ORDINAL_POSITION';

               
               $query = $this->query( $sql );

               $this->structure = array();
               while( $row = $this->fetch( $query ) ){

                  $obj = new StdClass();
                  $obj->name              = $row['name'];
                  $obj->default_value     = $row['default'];
                  $obj->default_is_null   = $row['default_is_null'];
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
			}



			//Busca um registro(ou um range de registro) da tabela atual
			function getData( $key = '', $sort = '', $limit = '', $usePrimary = false ){

				$this->updateTableInfo();

				//Busca as informações usadas no processo
				$tools   = $this->globals->tools;
				$primary = [];
				$where   = '';
				$data    = [];


				//Filtra as partes usadas
				$primary = $this->getPrimary();
				$where   = $this->getWhere( $key );

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

				if( $this->count( $sql ) > 0 ){
               $query = $this->query( $sql );
               $data  = $this->makeDataObject( $query, false, ($usePrimary ? $primary : null) );
				}

				return $data;

			}


			//Busca a quantidade de resultados que tem na tabela, baseado nas chaves passadas
			function countData( $key = '' ){

				$sql   = 'SELECT 1 FROM `' . $this->table . '`';
				$where = $this->getWhere( $key );

				if( !empty( $where ) ) $sql .= ' WHERE ' . $where;

				return $this->count( $sql );

			}


			//Retorna um objeto row com os valores padroes dos campos
			function getEmptyRow( $lArray = false ){

            if( $lArray ){
               $row = [];
            } else {
               $row = new StdClass();
            }

				foreach( $this->structure as $a ){
               if( $lArray ){
                  $row[ $a->name ] = ($a->default_is_null ? NULL : $a->default_value);
               } else {
                  $key       = ($a->name);
                  $row->$key = ($a->default_is_null ? NULL : $a->default_value);
               }
				}

				return $row;

			}


			//Deleta um registro ou uma serie de registros
			function delete( $key ){

				$sql = 'DELETE FROM `' . $this->table . '` WHERE ' . $this->getWhere( $key );

				if( ! $this->execute( $sql ) ){
					throw( new ModelException( "Error while deleting the registry", 0x2012 ) );
				}

				return true;

			}


			//Salva uma linha na base de dados
			function saveRow( $row, $retCommand = false ){

				$table     = $this->table;
				$sql       = '';
				$where     = '';
				$structure = $this->structure;
				$pk        = [];
				$lUpdate   = false;

            //Padronizando para facilitar o acesso aos dados daqui para frente
            if( !is_array( $row ) ){
               $tmp = [];
               foreach( $row as $i => $a ) $tmp[ $i ] = $a;
               $row = $tmp;
            }

				//Verifica se os dados estão ok
				foreach( $structure as $col ){

               if( isset( $row[ $col->name ] ) ){
                  $col_value = $row[ $col->name ];
               } else {
                  $col_value = null;
               }

					//Campos auto_increment ou timestamp são ignorados na hora de salvar, por isso, não precisa de verificação
					if( !(($col->is_auto_increment && empty($col_value)) || $col->field_type == 'timestamp') ){

						//Verifica se é chave primaria e tem um valor
						if( $col->is_primary_key && empty( $col_value ) ){
							throw( new ModelException( 'Primary Key <strong>' . $col->name . '</strong> has no value and are not auto_increment', 0x2013 ) );
						}

						//Se o campo estiver nulo, mas não aceitar valores nulos
						if( $col->accept_null == false &&
							 $col->is_auto_increment == false &&
                      (is_null( $col_value ) || strtolower( $col_value === 'null')) ){
							throw( new ModelException( 'Field <strong>' . $col->name . '</strong> cannot be null', 0x2014 ) );
						}
					}

               if( $col->is_primary_key ) $pk[ $col->name ] = $col_value;

					$row[ $col->name ] = $this->formatField( $col, $col_value );

				}


				//Monta a sentença de comparação no caso de ser um update e usa para identificar o mesmo
				foreach( $pk as $i => $a ) $where .= (empty( $where ) ? '' : ' AND ') . '`' . $i . '`="' . $a . '"';

				if( $this->exist( $table, '', '', $where ) ){

					//É uma alteração
					foreach( $structure as $col ){
						if( ! ( ($col->is_auto_increment || $col->is_primary_key) && $this->sqlField( $col, $row[ $col->name ] ) == 'NULL' ) ){
							$sql .= (empty( $sql ) ? '' : ', ' );
							$sql .= '`' . $col->name . '`=' . $this->sqlField( $col, $row[ $col->name ]);
						}
					}

					$sql     = 'UPDATE `' . $table . '` SET ' . $sql . ' WHERE ' . $where;
					$lUpdate = true;

				} else {

					//É uma inclusão
					$header = '';
					foreach( $structure as $col ){
						if( ! ($col->is_auto_increment && $this->sqlField( $col, $row[ $col->name ] ) == 'NULL' ) ){
							$header .= (empty( $sql ) ? '' : ', ') . '`' . $col->name . '`';
							$sql    .= (empty( $sql ) ? '' : ', ') . $this->sqlField( $col, $row[ $col->name ] );
						}
					}
					$sql     = 'INSERT INTO `' . $table . '`(' . $header . ') VALUES(' . $sql . ')';
				}
            

            //Não executa o insert, retorna o sql
            if( $retCommand ){
               return $sql;
               
            } else {

               //Executa o comando na base de dados
               if( $this->execute( $sql ) ){
                  
                  $sql = $where = '';
                  
                  foreach( $pk as $i => $a ){
                     
                     if( $structure[ $i ]->is_auto_increment && (empty( $a ) || strtolower($a) == 'null') ){
                        $a = $this->lastId;
                     }
                     
                     $where .= (empty( $sql ) ? '' : ' AND ') . '`' . $i . '`="' . $a . '"';
                     $sql   .= (empty( $sql ) ? '' : ', ') . '`' . $i . '`';
                     
                  }
                  
                  $sql = 'SELECT ' . $sql . ' FROM `' . $table . '` WHERE ' . $where;
                  
                  $query = $this->query( $sql, true );
                  $row   = $this->fetch( $query );
                  
               } else {
                  
                  //Se der erro na hora de salvar, joga um erro para o nivel de cima
                  throw( new ModelException( "Error while saving - " . $sql, 0x2015 ) );
                  
               }
            }

				return $row;

			}



			//Busca um array com as chaves primarias da estrutura
			function getPrimary( $structure = ''){

				$primary = [];
            if( empty( $structure ) ) $structure = $this->structure;

				//Seleciona todas as chaves primarias( caso exista mais de uma )
				foreach( $structure as $col ){
					if( $col->is_primary_key ) $primary[] = $col->name;
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

               $where .= $primary[ 0 ] . '="' . $key . '"';

            } elseif( is_string( $key ) ){

               $where = $key;

				} elseif( is_array( $key ) ){

               $c   = 0;
               $tmp = '';
               foreach( $key as $i => $a ){

                  if( is_string( $i ) ){
                     $tmp .=  '`' . $i . '` = "' . $a . '"';

                  } elseif( isset( $primary[ $i ] ) ) {
                     $tmp .= '`' . $primary[ $i ] . '` = "' . $a . '"';

                  } elseif( $c < count( $primary ) ){
                     $tmp .= $primary[ $c ] . '="' . $a . '"';
                  }

                  if( !empty( $tmp ) ){
                     $where .= (empty( $where ) ? '' : ' AND ') . $tmp;
                  }

                  $c++;
               }

            }

				return $where;

			}


			//Formata o campo baseado no seu conteudo
			function formatField( $format, $value ){

				$tools = $this->globals->tools;

            if( is_null( $value ) ) return $value;

            if( strpos( '[char][varchar][enum][blog][mediumblob][bigblob][tinyblob][smallblob][text][mediumtext][bigtext][tinytext][smalltext]', $format->field_type ) ){
               $value = $tools->antiInjection( $value, true, true );
            } else {
               $value = $tools->antiInjection( $value );
            }


				//Formatando as datas para o formato mysql
            if( $value != 'NULL'  && ! preg_match( $this->mysqlFuncRegex, $value ) ){

               if( ($format->field_type == 'datetime' || $format->field_type == 'timestamp') && (strlen( $value ) == 16 || strlen( $value ) == 19) ){
                  $value = $tools->dateToSql( $value, true );
               } elseif( $format->field_type == 'date' && (strlen( $value ) == 10 || strlen( $value ) == 8) ){
                  $value = $tools->dateToSql( $value );
               }
            }

            return $value;

			}


			//formata o campo para o mysql
			function sqlField( $format, $value ){

            $ret = '';
            
            if( is_null( $value ) ){
               $ret = 'NULL';

            } elseif( preg_match( $this->mysqlFuncRegex, $value ) || preg_match( $this->mysqlLiteralRegex, $value ) ){
               $ret = $value;

            } elseif( strpos( '[timestamp][time][datetime][date][year]', $format->field_type ) !== false ){
               if( empty( $value ) && $format->accept_null ){
                  $ret = 'NULL';
               } else {
                  $ret = '"' .  $value . '"';
               }

            } elseif( strpos( '[integer][int][bigint][mediumint][smallint][tinyint][decimal][double][float][real][unsigned]', $format->field_type ) !== false ){
               if( $value === '' ){
                  $ret = ($format->accept_null ? 'NULL' : 0);
               } else {
                  $ret = $value;
               }

            } elseif( $format->field_type == 'enum' ){
               if( is_int($value) ){
                  if( $value > 0 ){
                     $ret = $value;
                  } else {
                     $ret = ($format->accept_null ? 'NULL' : 0);
                  }
               } else {
                  $ret = '"' . $value . '"';
               }

            } else {
               $ret = '"' . addslashes(stripslashes($value)) . '"';
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
