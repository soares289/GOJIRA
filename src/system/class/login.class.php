<?php

define( 'LGN_LOGIN_INVALID', "Usuário inválido");
define( 'LGN_PENDING'      , "Conta com ativação pendente");
define( 'LGN_INATIVE'      , "Conta desativada");
define( 'LGN_PASS_INVALID' , "Senha inválida");

define( 'LGN_DEFAULT_USER_TABLE' , 'user' );
define( 'LGN_DEFAULT_TYPE_TABLE' , 'user_type' );
define( 'LGN_USE_EMAIL'          , true );
define( 'LGN_COOKIE_EXPIRE_TIME' , 1800);

//TODO - Permitir USAR ou NÃO USAR USER_TYPE
//TODO - Permitir configurar a tabela para o USER_TYPE
//TODO - Permitir configurar os campos da tabela

		class Login extends GojiraCore{
			
			//Propriedades da classe
			private $connection;
			private $tools;
			private $id;
         
         private $configured  = false;
         private $userTable   = LGN_DEFAULT_USER_TABLE;
         private $typeTable   = LGN_DEFAULT_TYPE_TABLE;
			private $useEmail    = LGN_USE_EMAIL;
         private $statusList  = ['PENDING' => 0, 'INACTIVE' => 1, 'ACTIVE' => 2];
         private $statusField = 'status';
         private $useCookie   = false;
         private $cookieTime  = LGN_COOKIE_EXPIRE_TIME;
         
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
			function __construct2( $connection, $tools ){
				$this->connection = $connection;
            $this->tools      = $tools;
			}
			
			//Constructor com 4 parametros
			function __construct4( $connection, $tools, $userTable, $typeTable ){
				
				$this->connection = $connection;
				$this->tools      = $tools;
            
            $this->configure( $userTable, $typeTable);
			}

			
			
         function get_userTable(){ return $this->userTable; }
         function get_typeTable(){ return $this->typeTable; }
			function get_useEmail(){ return $this->useEmail; }

			function configure($userTable, $typeTable, $cookieTime = ''){

				$this->userTable = $userTable;
				$this->typeTable = $typeTable;

            //Se existe a coluna status
            if( !$this->connection->fieldExist( $userTable, $this->statusField ) ){
               $this->statusField = 'active';
               $this->statusList  = ['PENDING' => -1, 'INACTIVE' => 0, 'ACTIVE' => 1];
            }

            $this->cookieTime = (is_numeric( $cookieTime ) ? $cookieTime : $this->cookieTime);

            $this->configured = true;

			}
			
			
			//Executa o login
			function login( $login, $pwd, $type ){
            
            if( !$this->configured ) $this->configure( $this->userTable, $this->typeTable);

				$login = $this->tools->antiInjection( $login );
				$pwd   = $this->tools->antiInjection( $pwd );
				$type  = $this->tools->antiInjection( $type );
				$type  = $this->getType( $type );

				//Verifica se o usuario é valido (pelo campo login e, se useEmail, pelo campo email)
				if( !$this->connection->exist( $this->userTable, "login", $login, "type in " . $type ) ){
					if( !$this->useEmail || !$this->connection->exist( $this->userTable, "email", $login, "type in " . $type ) )
								throw( new Exception( LGN_LOGIN_INVALID, 0x9001 ) );
				}
				
				
				//Monta a sentença SQL para verificar se o usuário existe
				$sql   = 'SELECT b.shortcode AS type, ' . 
                            'b.id AS type_cod, ' . 
									 'a.id AS code, ' .
				   				 'a.password AS pwd, ' .
									 'a.login AS login, ' .
									 'a.email AS email, ' .
									 'a.' . $this->statusField . ' AS status, ' .
							($this->connection->fieldExist( $this->userTable, 'name' ) ? 
									 'a.name AS name ' : '"" AS name ') .
                     ' FROM ' . $this->userTable . ' AS a ' . 
                        'INNER JOIN ' . $this->typeTable . ' AS b ON a.type = b.id ' .
										'where (a.login = "' . $login . '" ' . 
				  ($this->useEmail ? ' or a.email = "' . $login . '"' : '') .
				  					') and a.' . 'type in ' . $type . ' limit 1';
				
            
				$query = $this->connection->query( $sql );
				$row   = $this->connection->fetch( $query );
				if( strlen( $pwd ) != 128 ) $pwd = $this->toPassword( $pwd );

				//Verifica se o usuário está ativo
				if( $row[ 'status' ] == $this->statusList['PENDING'] ){
					throw( new Exception( LGN_PENDING, 0x9004 ) );
				}

				//Verifica se o usuário está ativo
				if( $row[ 'status' ] == $this->statusList['INACTIVE'] ){
					throw( new Exception( LGN_INATIVE, 0x9002 ) );
				}
				
				//Verifica se o password está correto
				if( $pwd != $row[ 'pwd' ] ){
					throw( new Exception( LGN_PASS_INVALID, 0x9003 ) );
				}
				
				
				//Salva a sessão
            if( $this->useCookie ){
               setcookie( strtolower($row['type']) . "Logged" , true, time() + $this->cookieTime, '/');
               setcookie( strtolower($row['type']) . "Cod"    , $row['code'], time() + $this->cookieTime, '/');
               setcookie( strtolower($row['type']) . "Login"  , $row['login'], time() + $this->cookieTime, '/');
               setcookie( strtolower($row['type']) . "Email"  , $row['email'], time() + $this->cookieTime, '/');
               setcookie( strtolower($row['type']) . "Name"   , $row['name'], time() + $this->cookieTime, '/');
               setcookie( strtolower($row['type']) . "Type"   , $row['type'], time() + $this->cookieTime, '/');
               setcookie( strtolower($row['type']) . "TypeCod", $row['type_cod'], time() + $this->cookieTime, '/');
               
            } else {
               $_SESSION[ $row['type'] . "Logged"]  = true;
               $_SESSION[ $row['type'] . "Cod"]     = $row['code'];
               $_SESSION[ $row['type'] . "Login"]   = $row['login'];
               $_SESSION[ $row['type'] . "Email"]   = $row['email'];
               $_SESSION[ $row['type'] . "Name"]    = $row['name'];
               $_SESSION[ $row['type'] . "Type"]    = $row['type'];
               $_SESSION[ $row['type'] . "TypeCod"] = $row['type_cod'];
				}
            
				return $row['code'];
				
			}
			
			
			
			//Remove a sessão do usuario logado
			function logout( $type ){

				if( $this->isLogged( $type ) ){

					//Loga a saida do sistema
					$usr = $this->getLogged( 'Cod', $type );
					
					$type   = $this->getType( $type, true );
					$type   = explode( ',', strtoupper( $type ) );
               $fields = ['Logged', 'Cod', 'Name', 'Login', 'Email', 'Type', 'Typecod'];
					//Desloga todos os tipos de usuários informados
					foreach( $type as $a ){
						
                  if( $this->useCookie ){
                     
                     foreach( $fields as $fld ){
                        setcookie(strtolower($a . $fld), 0, 2);
                        unset( $_COOKIE[ strtolower($a . $fld)] );
                     }
                     
                     //Para os sistemas que criam outras váriaveis de sessão para o usuário e não limpão manualmente no logoff
                     foreach( $_COOKIE as $i => $v ){
                        if( substr( $i, 0, strlen( $a ) ) === strtolower($a) ){
                           setcookie($i, 0, -60, '/');
                           unset($_COOKIE[ $i ]);
                        }
                     }
                  } else {
                     foreach( $fields as $fld ){
                        unset( $_SESSION[ $a . $fld] );
                     }
                     
                     //Para os sistemas que criam outras váriaveis de sessão para o usuário e não limpão manualmente no logoff
                     foreach( $_SESSION as $i => $v ){
                        if( substr( $i, 0, strlen( $a ) ) === $a ) unset($_SESSION[ $i ]);
                     }
                  }
					}
					
				} else {
					
					return false;
					
				}
				
				return false;
				
			}
			
			
			//Busca qual o tipo de usuário logado.
			//Retorno pode ser 1 = SHORTCODE, 2 - ID, 3 - NAME
			function loggedType( $type, $return = 1 ){
            
				//Converte o tipo para o formato certo ou busca ele da db caso não seja informado
				if( empty( $types ) ){
					
					$query = $this->connection->query( 'SELECT shortcode FROM ' . $this->typeTable );
					$type = array();
					while( $row = $this->connection->fetch( $query ) ){
						$type[] = $row['shortcode'];
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
							$sql = 'SELECT ' . ($return == 2 ? 'id AS a' : 'name AS a' ) . ' FROM ' . $this->typeTable . ' WHERE shortcode = "' . $a . '"';
							$row = $this->connection->fetch( $this->connection->query( $sql ) );
							return $row[ 'a' ];
						}
					}
				}
				
				return false;
			
			}
			
			
			//Retorna um campo especifico, independente de quem estiver logado
			function getLogged( $field, $type = ''){
				
				if( empty( $type ) ){
					
					$query = $this->connection->query( 'SELECT shortcode from ' . $this->typeTable );
					$type = [];
					while( $row = $this->connection->fetch( $query ) ){
						$type[] = $row['shortcode'];
					}
					
				} else {
					$type = explode( ',', $type );
				}
				
				/*$field    = strtolower( $field );
				$field[0] = strtoupper( $field[0] );*/
				
				foreach( $type as $a ){
               if( $this->useCookie ){
                  if( isset( $_COOKIE[ strtolower( $a  . $field ) ] ) &&
                     !empty( $_COOKIE[ strtolower( $a  . $field ) ] ) ){
                     return $_COOKIE[ strtolower( $a  . $field )];
                  }
               } else {
                  if( isset( $_SESSION[ strtoupper( $a ) . $field ] ) &&
                     !empty( $_SESSION[ strtoupper( $a ) . $field ] ) ){
                     return $_SESSION[ strtoupper( $a ) . $field ];
                  }
               }
				}
				
				return '';
			}
         
         
			//Verifica se o usuario está logado
		   function isLogged( $type ){
            
            if( !$this->configured ) $this->configure( $this->userTable, $this->typeTable);
            
				$type = $this->getType( $type, true );
				$type = explode( ',', strtoupper( $type ) );
				$n    = 0;
				
				foreach( $type as $a ){
               if( $this->useCookie ){
                  $a = strtolower( $a );
                  if( isset( $_COOKIE[ $a . 'logged'] ) &&
                      isset( $_COOKIE[ $a . 'cod'] ) &&
                      isset( $_COOKIE[ $a . 'name'] ) &&
                      isset( $_COOKIE[ $a . 'login'] ) &&
                      isset( $_COOKIE[ $a . 'email'] ) ){

                     if( $_COOKIE[ $a . 'logged'] == true &&
                        !empty( $_COOKIE[ $a . 'cod'] ) &&
                        !empty( $_COOKIE[ $a . 'login'] ) &&
                        !empty( $_COOKIE[ $a . 'email'] ) ){
                           
                        $typeCod = $this->getType( $a );

                        if( $this->connection->exist( $this->userTable, 'id', $_COOKIE[ $a . 'Cod'], "type IN " . $typeCod . ' AND ' . $this->statusField . '=' . $this->statusList['ACTIVE'] ) ){
                           $n++;
                        }
                     }
                  }
               } else {
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
                        if( $this->connection->exist( $this->userTable, 'id', $_SESSION[ $a . 'Cod'], "type IN " . $typeCod . ' AND ' . $this->statusField . '=' . $this->statusList['ACTIVE'] ) ){
                           $n++;
                        }
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
				$table = $this->typeTable;
				$where = '';
				
				if( $this->connection->tableExist( $table ) ){
					
					$type  = explode(',',$type);
					foreach( $type as $a ){
						$where .= (empty( $where ) ? '' : ',') . '"' . $a . '"'; 
					}
					
					$sql   = 'SELECT id AS cod, shortcode AS small FROM ' . $table .
									' WHERE id IN (' . $where . ') OR shortcode IN (' . $where . ') OR name IN (' . $where . ')';
					
					$query = $this->connection->query( $sql );
					while( $row = $this->connection->fetch( $query ) ){
						
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
					$sql 	= "SELECT email AS email, id AS cod, COUNT(*) AS numUser FROM " . $this->userTable . " WHERE id = '" . $user . "' AND type in " . $type;
				}else{
					$sql 	= "SELECT email AS email, id AS cod, COUNT(*) AS numUser FROM " . $this->userTable . " WHERE (login = '" . $user . "' OR email = '" . $user . "') AND typecod IN " . $type;
				}
				$ret 	= $this->connection->fetch($this->connection->query($sql));
				return [	"exists" => ($ret['numUser'] ? 1 : 0),
							"cod"		=> $ret['cod'],
							"email" 	=> $ret['email'] ];
				
			}
			
			
			//Altera a senha do usuário
			function changePass( $userId , $pass ){
				
				$cryptPass 	= $this->toPassword($pass);
				$sql  = "UPDATE " . $this->userTable . " SET password = '" . $cryptPass . "' WHERE id = " . $userId . " LIMIT 1";
				return ($this->connection->query($sql));
				
         }
         
			
         //Seta a propriedade useCookie
         function setCookie( $val, $cookieTime = ''){
            //Forçar valor lógico
            $this->useCookie  = ($val == true ? true : false);

            if( is_numeric( $time ) )  $this->cookieTime = $cookieTime;
         }
		
		}

