<?php
/*
 * Post.class.php - Gerencia a requisiçao de posts do blog
 * Cuida dos filtros, gerenciamento das tags, etc.
 *
 */
	
	require_once( 'properties.class.php' );


		class Log extends Properties{
			
			
			//Propriedades da classe
			private $conn;
			private $tools;
			
			
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
				
				$this->conn  = $conn;
				$this->tools = $tools;
				
			}
			
		  
		  
		  
		  
		  
		  
		  /***   METHODOS DO OBJETO   ***/
		  
		  
		  function add( $class, $subclass, $table, $refcod, $usrcod, $desc = '', $lRet = false){
			  
			  $class    = $this->tools->antiInjection( $class );
			  $subclass = $this->tools->antiInjection( $subclass );
			  $table    = $this->tools->antiInjection( $table );
			  $refcod   = $this->tools->antiInjection( $refcod );
			  $usrcod   = $this->tools->antiInjection( $usrcod );
			  $desc     = $this->tools->antiInjection( $desc );
			  $ip       = 'inet_aton( "' . $_SERVER['REMOTE_ADDR'] . '" )';
			  
			  $sql = 'insert into log(logclass, logsubclass, logtable, logrefcod, usrcod, logtext, logip, logdate) value' .
			  				'("' . $class . '","' . $subclass . '","' . $table . '",' . $refcod . ',' . $usrcod . ',"' . $desc . '",' . $ip . ',now())';
			  
			  if( $lRet ){
				  return $sql;
			  } else {
				  return $this->conn->execute( $sql );
			  }
			  
		  }
		
		}

