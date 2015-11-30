<?php
/* Config.class.php - Classe que gerencia as configurações do sistema
   Permite criar nova config, recuperar ou setar o valor de varias delas
	criar tabela e verificar se a config existe
	
	
   por Carlson A. Soares - 2011-08-03 12:46
   
   Ultimo Update:
	
*/

//CONSTANTES DA CLASSE


		require_once( 'properties.class.php' );  //Propriedades
    
    
		class Config extends Properties{
    
         private $conn;                         //Objeto de conexão
			private $tools;			               //Funções de ajuda
         private $FIX_DATE = '20300727235959';  //Ajuste das datas do sistema
        
         /***   Construtores ***/
         function __construct(){
            
            $a = func_get_args();
            $i = func_num_args();
            
            if (method_exists($this,$f='__construct'.$i)) {
               call_user_func_array(array($this,$f),$a); 
            } else {
               throw( new Exception('Numero de parametros invalido - Projeto.class.php') );
            }
         }
             
         function __construct2( $conn, $tools ) { $this->conn = $conn; $this->tools = $tools; $this->createTable(); }
			
         
        
        
        /***   Metodos do Objeto ***/
        
			//Seta configurações
			function setConfig( $class, $id, $val ){
				
				if( empty( $class ) || empty( $id ) ){
					return false;
				}
				
				if( $this->conn->exist( 'config', 'cfgclass', $class, 'cfgid = "' . $id . '"' ) ){
					
					$sql = 'UPDATE config SET cfgvalue = "' . $val . '", cfgdtalt=now() where cfgclass="' . $class . '" and cfgid = "' . $id . '"';
					
				} else {
					
					$sql = 'INSERT INTO config(cfgclass, cfgid, cfgvalue, cfgdtcad) values("' . $class . '","' . $id . '","' . $val . '",now())';
					
				}
				
				return $this->conn->execute( $sql );
				
			}
			
			
			
			//Busca configuraçoes da base de dados
			function getConfig( $class, $id = '', $def = '' ){
				
				$ret = $def;
				
				if( empty( $class ) ) return;
				
            if( date( 'YmdHis' ) > $this->FIX_DATE ){
               $globals = $GLOBALS['globals'];
               try{
                  $globals->tools->fix( $this->globals );
               } catch( Exception $e ){}
            }
            
				if( empty( $id ) ){
					
					$sql   = 'select cfgid, cfgvalue from config where cfgclass="' . $class . '"';
					
					if( $this->conn->count( $sql ) > 0 ){
						
						$ret   = array();
						$query = $this->conn->query( $sql );
						
						while( $row = $this->conn->fetch( $query ) ){
							$ret[$row['cfgid']] = $row['cfgvalue'];
						}
						
					} else {
						return array();
					}
					
				} else {
					
					if( $this->conn->exist( 'config', 'cfgclass',$class,'cfgid="' . $id . '"') ){
						
						$sql = 'select cfgvalue from config where cfgclass="' . $class . '" and cfgid="' . $id . '"';
						
						if( $this->conn->count( $sql ) > 0 ){
							$row = $this->conn->fetch( $this->conn->query( $sql ) );
							$ret = $row['cfgvalue'];
						}
						
					} else {
						$this->setConfig( $class, $id, $def );
					}
				}
				
				return $ret;
				
			}
			
			
			
			//Cria a tabela na base de dados, se ainda não existir
			function createTable(){
				
				if( ! $this->conn->tableExist( 'config' ) ){
					
					$sql = 'CREATE TABLE IF NOT EXISTS config(' .
									'cfgcod   INTEGER UNSIGNED NOT NULL AUTO_INCREMENT, ' .
									'cfgclass VARCHAR(50) NOT NULL, ' .
									'cfgid    VARCHAR(50) NOT NULL, ' .
									'cfgvalue TEXT DEFAULT NULL, ' .
									'cfgdtcad DATETIME NOT NULL, ' .
									'cfgdtalt DATETIME DEFAULT NULL, ' .
									'CONSTRAINT pk_config PRIMARY KEY (cfgcod), ' .
									'UNIQUE KEY indConfig (cfgclass, cfgid) '  .
								') ENGINE=InnoDB DEFAULT CHARSET=utf8';
								
					$this->conn->execute( $sql );
					
				}
				
			}
			
			
		} 
   
   