<?php
/*
	Conjunto de funções criadas para ajudar no decorrer do sistema.
	Separada em categorias, divididas por subclasses, cada uma em seu
	arquivo, na subpasta tool.
	Carregada para dentro dessa classe automaticamente.
*/

      //Classe de email PHPMailer usada pelo tools/network.class.php
      require_once( dirname( __FILE__ ) . '/../vendor/phpmailer/PHPMailerAutoload.php' );
		
		class Tool{
			
			private $tools;
			
			/***   Construtores ***/
			function __construct(){
				
				//Busca a pasta base onde se encontram os arquivos de subclasse
            $tmpFile  = str_replace( "\\", "/", __FILE__ );
            $tmpFileA = explode( "/", $tmpFile );
				$absPath  = substr( $tmpFile, 0, strlen( $tmpFile ) - strlen( end( $tmpFileA ) ) );
            
				$files    = array();
				$base     = __CLASS__;
				$dir      = opendir( $absPath . strtolower( $base ) );
				
				//Lista os arquivos dela
				while( ($file = readdir( $dir )) !== false ){
					
               $fileA = explode('.',$file);
					$ext   = strtolower(end($fileA));
					$cls   = explode('.',$file);
					$cls   = $cls[0];
					$cls   = strtoupper( substr($cls, 0, 1 ) ) . strtolower( substr( $cls, 1, strlen( $cls ) - 1 ));
					
					if( $ext == 'php' ){

						require_once( strtolower( $base ) . '/' . $file );
						$cls = $base . '_' . $cls;
						$this->tools[] = new $cls();
						
					}
				}
				
				
			}
		
		
			
			
			//Verifica se o metodo procurado existe
			function __call( $method, $args ){
				
				if( is_array( $this->tools ) ){
					
					//Procura em todos os objetos pelo metodo
					foreach( $this->tools as $tool ){
						
						//Onde achar o metodo primeiro, executa
						if( method_exists( $tool, $method ) ){
							return call_user_func_array( array( $tool, $method), $args);
						}
						
					}
					
				}
            
				//Se não foi localizado, retorna um erro de Metodo Não localizado
				throw( new BadMethodCallException() );
			}
			
	}

