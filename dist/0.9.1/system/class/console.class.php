<?php
/* console.class.php - Classe que gerencia as configurações do sistema
   Permite criar nova config, recuperar ou setar o valor de varias delas
	criar tabela e verificar se a config existe
	
	
   por Carlson A. Soares - 2012-07-05 11:49
   
   Ultimo Update:
	
*/

//CONSTANTES DA CLASSE


    	require_once( 'tool.class.php' );			//Tools
		
    
		class Console extends GojiraCore{
    		
			private $tools;
			
         //Construtores do objeto
         function __construct1( $tools ){

            $this->tools = $tools;

         }
         
			
			public function execute( $cmd ){
				
				//remove os espaços duplicados
				list($command, $param) = $this->cmd_split( $cmd );
				$command = strtolower( $command );
				
				if( method_exists( $this, $command ) ){
					return nl2br(call_user_func_array(array($this, $command), array( $param, count( $param ) ) ));
				} else {
					return "Comando não identificado";
				}
				
			}
			
			
			private function cmd_split( $cmd ){
				
				if (get_magic_quotes_gpc()) {
					$cmd = stripslashes( $cmd );
				}
				
				$asp = false;
				$ret = array();
				$tmp = explode( ' ', $cmd );
				$cmd = $tmp[0];
				
				if( count( $tmp ) > 1 ){
					
					
					for( $c = 1; $c < count( $tmp ); $c++ ){
					
						$a = $tmp[$c];
					
						if( $asp ){
							
							$ret[ count( $ret ) - 1 ] .= ' ' . $a;
							if( substr( $a, -1, 1) == '"' ) $asp = false;
							
						} elseif( $a != '') {
							
							$ret[] = $a;
							
						}
						
						if( substr( $a, 0, 1) == '"' ) $asp = true;
					}
					
				}
				
				foreach( $ret as $i => $a ){
					$ret[ $i ] = preg_replace_callback( "#[\$]((?:[^[]|\[(?!/?indent])|(?R))+)[\$]#", create_function( '$matches', '
																			foreach( $matches as $a ){
																				if( substr($a,0,1) == substr( $a,-1,1) && substr( $a,0,1 ) == "$" ){
																					$a = "d_" . str_replace( "$", "", $a);
																					return (isset($_SESSION[$a]) ? $_SESSION[$a] : "");
																				}
																			}
																		'), $a );
				}
				
				
				
				return array( $cmd, $ret);
				
			}
			
			
			private function ptest( $param, $val ){
				for( $c = 0; $c < $param; $c++ ) if( $param[ $c ] == $val ) return $c;
				return -1;
			}
			
			
			//Define variaveis para o console
			private function define( $param, $np ){
				
				if( $np != 2 ) return 'Parametros inválidos';
				$_SESSION[ 'd_' . $param[0] ] = $param[1];
				
			}
			
			
			//Cria um novo projeto
			private function mkproj( $param, $np ){

				$inv  = "Parametros inválido\n-n Nome do Projeto\n-d Diretorio do Projeto";
				
				if( $np <= 1 ) return $inv;
				
				$name = $this->ptest( $param, '-n') + 1;
				$dir  = $this->ptest( $param, '-d') + 1;
				
				if( $name == 0 || $name == $np || $dir == 0 || $dir == $np ){
					return $inv;
				}
				
				$system_url = $this->tools->curPageUrl();
				$system_url = substr( $system_url, 0, strrpos( $system_url, '/' ) + 1 );
				
				return $system_url;
				
			}
			
		} 
   
   