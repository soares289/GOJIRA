<?php

		require_once( "properties.class.php" );

		abstract class Controller extends Properties{
			
			//Propriedades da classe
			protected $globals;
         protected $tools;
         protected $environment;
			protected $model;
			
			
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
			 
			function __construct1( $globals ){
				
				$this->globals     = $globals;
            $this->tools       = $globals->tools;
            $this->environment = $globals->environment;
            $class             = substr( get_class( $this ), 0, -11 );
				$this->model       = Model::Call( $class , $globals );
				
			}

			
			
			
			//Invoca o modelo correto
			static function Call( $class, $method, $globals, &$param){
            
            $file    = strtolower($class) . '.php';
				$class   = strtoupper( substr( $class, 0, 1 ) ) . substr( $class, 1 ) . '_Controller';
				$dir     = $globals->environment->dir_controller;

				//Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){

					//Instancia o objeto
					require_once( $dir . $file );

					$objController = new $class( $globals );

					if( method_exists( $objController, $method ) ){

						//Retorna o resultado da função dentro do model
						return call_user_func_array( array( $objController, $method), array( $param ));
                  
					} else {

						//Metodo não localizado dentro do model
						throw( new ControllerException( "Method <strong>\"" . $method . "\"</strong> not found in Controller Object <strong>\"" . $class . "\"</strong>", 0x1001 ) );
					}
					
				} else {
					
					//Model não localizado no disco
					//throw( new ControllerException( "Controller <strong>\"" . $class . "\"</strong> not found in <strong>\"" . $dir . $file . "\"</strong>", 0x1001 ) );
					
				}
				
			}
			
			
			
			abstract function index( $param );
			
		}



		//Objeto de erro personalizadp
		class ControllerException extends Exception{
			
			function __construct( $message, $err_cod ){
				parent::__construct( $message, $err_cod );
			}
			
		}
		
