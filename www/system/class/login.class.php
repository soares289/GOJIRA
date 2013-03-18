<?php

define( 'LGN_LOGIN_INVALID', "Invalid Login");
define( 'LGN_INATIVE'      , "User account not active!");
define( 'LGN_PASS_INVALID' , "Wrong password");

define( 'LGN_DEFAULT_TABLE' , 'user' );
define( 'LGN_DEFAULT_PREFIX', 'usr' );
define( 'LGN_USE_EMAIL'     , true );


	require_once( "properties.class.php" );
	require_once( "log.class.php" );

		class Login extends Properties{
			
			//Propriedades da classe
			private $conn;
			private $tools;
			private $id;
			
			private $table    = LGN_DEFAULT_TABLE;
			private $prefix   = LGN_DEFAULT_PREFIX;
			private $useEmail = LGN_USE_EMAIL;
			
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
			
			//Constructor com 2 parametros	 
			function __construct2( $conn, $tools ){
				$this->conn = $conn;
				$this->tools = $tools;
			}
			
			//Constructor com 4 parametros
			function __construct4( $conn, $tools, $table, $prefix ){
				
				$this->conn   = $conn;
				$this->tools  = $tools;
				
				$this->table  = $table;
				$this->prefix = $prefix;
				
			}

			
			
			function get_table(){ return $this->table; }
			function get_prefix(){ return $this->prefix; }
			function get_useEmail(){ return $this->useEmail; }

			function config($table, $prefix){

				$this->table = $table;
				$this->prefix = $prefix; 

			}
			
			
			//Executa o login
			function login( $login, $pwd, $type ){
				
				$login = $this->tools->antiInjection( $login );
				$pwd   = $this->tools->antiInjection( $pwd );
				$type  = $this->tools->antiInjection( $type );
				$type  = $this->getType( $type );

				//Verifica se o usuario é valido
				if( !$this->conn->exist( $this->table, $this->prefix . "login", $login, "typecod in " . $type ) ){
					if( !$this->useEmail || !$this->conn->exist( $this->table, $this->prefix . "email", $login, "typecod in " . $type ) )
								throw( new Exception( LGN_LOGIN_INVALID, 9001 ) );
				}
				
				
				//Monta a sentença SQL para verificar se o usuário existe
				$sql   = 'SELECT b.typesmall as type, ' . 
									 'a.' . $this->prefix . 'cod as code, ' .
				   				 'a.' . $this->prefix . 'pwd as pwd, ' .
									 'a.' . $this->prefix . 'login as login, ' .
									 'a.' . $this->prefix . 'email as email, ' .
									 'a.' . $this->prefix . 'active as active, ' .
							($this->conn->fieldExist( $this->table, $this->prefix . 'name' ) ? 
									 'a.' . $this->prefix . 'name as name ' : '"" as name ') .
							' FROM ' . 
								$this->table . ' as a inner join user_type as b on a.typecod = b.typecod ' .
										'where (a.' . $this->prefix . 'login = "' . $login . '" ' . 
				  ($this->useEmail ? ' or a.' . $this->prefix . 'email = "' . $login . '"' : '') .
				  					') and a.' . 'typecod in ' . $type . ' limit 1';
				

				$query = $this->conn->query( $sql );
				$row   = $this->conn->fetch( $query );
				$pwd   = $this->toPassword( $pwd );

				//Verifica se o usuário está ativo
				if( ! $row[ 'active' ] ){
					throw( new Exception( LGN_INATIVE, 9002 ) );
				}
				
				//Verifica se o password está correto
				if( $pwd != $row[ 'pwd' ] ){
					throw( new Exception( LGN_PASS_INVALID, 9003 ) );
				}
				
				
				//Salva a sessão
				$_SESSION[ $row['type'] . "Logged"] = true;
				$_SESSION[ $row['type'] . "Cod"]    = $row['code'];
				$_SESSION[ $row['type'] . "Login"]  = $row['login'];
				$_SESSION[ $row['type'] . "Email"]  = $row['email'];
				$_SESSION[ $row['type'] . "Name"]   = $row['name'];
				
				//Loga o login
				$log = new Log( $this->conn, $this->tools );
				$log->add("LOGIN","LOGON","user",$row['code'],$row['code'],'Logado com sucesso');
				
				return $row['code'];
				
			}
			
			
			
			//Remove a sessão do usuario logado
			function logoff( $type ){

				if( $this->isLogged( $type ) ){
					
					//Loga a saida do sistema
					$usr = $this->getLogged( 'Cod', $type );
					$log = new Log( $this->conn, $this->tools );
					$log->add("LOGIN","LOGOFF","user",$usr,$usr,'Saindo do sistema');
					
					$type = $this->getType( $type, true );
					$type = explode( ',', strtoupper( $type ) );
					
					//Desloga todos os tipos de usuários informados
					foreach( $type as $a ){
						
						unset( $_SESSION[ $a . 'Logged'] );
						unset( $_SESSION[ $a . 'Cod'] );
						unset( $_SESSION[ $a . 'Name'] );
						unset( $_SESSION[ $a . 'Login'] );
						unset( $_SESSION[ $a . 'Email'] );
					}
					
				} else {
					
					return false;
					
				}
				
				return false;
				
			}
			
			
			//Busca qual o tipo de usuário logado.
			//Retorno pode ser 1 = TypeSmall, 2 - TypeCod, 3 - TypeName
			function loggedType( $type, $return = 1 ){
				
				//Converte o tipo para o formato certo ou busca ele da db caso não seja informado
				if( empty( $types ) ){
					
					$query = $this->conn->query( 'select typesmall from user_type' );
					$type = array();
					while( $row = $this->conn->fetch( $query ) ){
						$type[] = $row['typesmall'];
					}
					
				} else {
					
					$type = $this->getType( $type, true );
					
				}
				
				//Verifica qual dos tipos estão conectado, o primeiro a ser localizado será o usado
				foreach( $type as $a ){
					if( $this->isLogged( $a ) ){
						if( $return == 1 ){
							return strtoupper( $a );
						} else {
							$sql = 'select ' . ($return == 2 ? 'typecod as a' : 'typename as a' ) . ' from user_type where typesmall = "' . $a . '"';
							$row = $this->conn->fetch( $this->conn->query( $sql ) );
							return $row[ 'a' ];
						}
					}
				}
				
				return false;
			
			}
			
			
			//Retorna um campo especifico, independente de quem estiver logado
			function getLogged( $field, $type = ''){
				
				if( empty( $type ) ){
					
					$query = $this->conn->query( 'select typesmall from user_type' );
					$type = array();
					while( $row = $this->conn->fetch( $query ) ){
						$type[] = $row['typesmall'];
					}
					
				} else {
					$type = explode( ',', $type );
				}
				
				$field    = strtolower( $field );
				$field[0] = strtoupper( $field[0] );
				
				foreach( $type as $a ){
					if( isset( $_SESSION[ strtoupper( $a ) . $field ] ) &&
						!empty( $_SESSION[ strtoupper( $a ) . $field ] ) ){
						return $_SESSION[ strtoupper( $a ) . $field ];
					}
				}
				
				return '';
			}
			
			//Verifica se o usuario está logado
		   function isLogged( $type ){
				
				$type = $this->getType( $type, true );

				$type = explode( ',', strtoupper( $type ) );
				$n    = 0;
				
				foreach( $type as $a ){

					if( isset( $_SESSION[ $a . 'Logged'] ) &&
						 isset( $_SESSION[ $a . 'Cod'] ) &&
						 isset( $_SESSION[ $a . 'Name'] ) &&
						 isset( $_SESSION[ $a . 'Login'] ) &&
						 isset( $_SESSION[ $a . 'Email'] ) ){



						if( $_SESSION[ $a . 'Logged'] == true &&
							!empty( $_SESSION[ $a . 'Cod'] ) &&
							!empty( $_SESSION[ $a . 'Login'] ) &&
							!empty( $_SESSION[ $a . 'Email'] ) ){
								
							$typeCod = $this->getType( $a );

							if( $this->conn->exist( $this->table, $this->prefix . 'cod', $_SESSION[ $a . 'Cod'], "typecod in " . $typeCod . ' and ' . $this->prefix . 'active' ) ){
								$n++;
							}
						}
					}
				}
				
		      return $n > 0;
		
		   }
		
			
			
			//Criptografa a senha
		   function toPassword( $val ){
				
		      $n = 3;
		
		      for( $c = 0; $c < $n; $c++ ){
					$val = hash('sha512'   , $val, false );
					$val = hash('whirlpool', $val, false );
		      }
				
		      return $val;
		
		   }
			
			
			
			//Gera uma senha aleatória com letras maiusculas, minusculas, numeros e caracteres especiais
		   function getPassword( $n ){
		
		      $pwd     = '';
		      $special = '@#$%&+';
		      $order   = '';
		
		      //Define quantos caracteres vão ter na senha
		      if( $n > 4 ){
					
		         $ma = (int)($n * 0.25);
		         $mi = (int)($n * 0.25);
		         $es = (int)($n * 0.25);
		         $nu = (int)($n * 0.25);
		
		         if( ($ma + $mi + $es + $nu) < $n){
		            $mi += ($n - ($ma + $mi + $es + $nu));
		         }
					
		      } else {
					
		         $ma = 0;
		         $mi = $n;
		         $es = 0;
		         $nu = 0;
					
		      }
				
				
		      //Define a ordem que cada parte da senha vira
		      while( strlen( $order ) < 4 ){
		         $c = rand( 1, 4 );
		         if( strpos( '  ' . $order, $c . '' ) <= 0 ) $order .= $c;
		      }
		
		      for( $i = 0; $i < 4; $i++ ){
		
		         switch( substr( $order, $i, 1 ) ){
		            case '1': 	//Numeros
		               for( $c = 0; $c < $nu ; $c++ ){
		                  $pwd .= chr(rand( 48, 57 ));
		               }
		               break;
		
		            case '2':	//Caracteres minusculos
		               for( $c = 0; $c < $mi ; $c++ ){
		                  $pwd .= chr(rand( 97, 122 ));
		               }
		               break;
		
		            case '3':	//Caracteres maiusculos
		               for( $c = 0; $c < $ma ; $c++ ){
		                  $pwd .= chr(rand( 65, 90 ));
		               }
		               break;
		
		            case '4':	//Caracteres especiais
		               for( $c = 0; $c < $es ; $c++ ){
		                  $pwd .= substr( $special, rand( 0, mb_strlen( $special, 'UTF-8' )-1 ), 1 );
		               }
		               break;
		         }
		      }
		      return $pwd;
		   }
			
			
			
			//Tenta converter o nome do tipo para o código do tipo
			function getType( $type, $useSmall = false ){
				
				$ret   = '';
				$table = 'user_type';
				$where = '';
				
				if( $this->conn->tableExist( $table ) ){
					
					$type  = explode(',',$type);
					foreach( $type as $a ){
						$where .= (empty( $where ) ? '' : ',') . '"' . $a . '"'; 
					}
					
					$sql   = 'select typecod as cod, typesmall as small from ' . $table .
									' where typecod in (' . $where . ') or typesmall in (' . $where . ') or typename in (' . $where . ')';
					
					$query = $this->conn->query( $sql );
					while( $row = $this->conn->fetch( $query ) ){
						
						$ret .= (empty( $ret ) ? '' : ',');
						
						if( $useSmall ){
							$ret .= trim($row["small"]);
						} else {
							$ret .= '"' . $row["cod"] . '"';
						}
						
					}
					
					if( ! $useSmall ) $ret = '(' . $ret . ')';
					
				} else {
					
					if( $useSmall ){
						$ret = $type;
					} else {
						$ret = '("' . $type . '")';
					}
				}
				
				return $ret;
			}
			
			
			//Verifica se o usuário existe
			function userExists( $type , $user ){
				
				$type = $this->getType( $type );
				if(is_numeric($user)){
					$sql 	= "SELECT " . $this->prefix . "email AS email," . $this->prefix . "cod AS cod,COUNT(*) AS numUser FROM " . $this->table . " WHERE " . $this->prefix . "cod = '" . $user . "' and typecod in " . $type . ";";
				}else{
					$sql 	= "SELECT " . $this->prefix . "email AS email," . $this->prefix . "cod AS cod,COUNT(*) AS numUser FROM " . $this->table . " WHERE " . $this->prefix . "login = '" . $user . "' OR " . $this->prefix . "email = '" . $user . "' and typecod in " . $type . ";";
				}
				$ret 	= $this->conn->fetch($this->conn->query($sql));
				return(array(
				
								"exists" => $ret['numUser'] ? 1 : 0,
								"cod"		=> $ret['cod'],
								"email" 	=> $ret['email']
							
							));
				
			}
			
			
			//Altera a senha do usuário
			function changePass( $userId , $pass ){
				
				$cryptPass 	= $this->toPassword($pass);
				$sql  = "UPDATE " . $this->table . " SET " . $this->prefix . "pwd = '" . $cryptPass . "' WHERE " . $this->prefix . "cod = " . $userId . " LIMIT 1";
				return($this->conn->query($sql));
				
			}
			

		
		}

