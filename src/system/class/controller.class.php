<?php

		abstract class Controller extends GojiraCore{

			//Propriedades da classe
			protected $globals;
         protected $model;

			function __construct1( $globals ){

				$this->globals = $globals;
            $class         = substr( get_class( $this ), 0, -11 );

            //Se o model não existir, gera um exception, mas nesse instante, isso é previsto
            //por isso não deve deixar ele passar daqui.
            try{
               $this->model = Model::Load( $class , $globals );
				} catch( Exception $e ){
               $this->model = new AppModel( $globals );
            }

         }
         
         //Construtor alternativo que recebe um model pré-instanciado
         function __construct2( $globals, $model ){

            $this->globals = $globals;
            $this->model   = $model;

         }


			//Instancia um controller a parte
         static function Load( $class, $globals = ''){

            if( $globals == '' ){
               $globals = $GLOBALS['globals'];
            }

            $file    = strtolower($class) . '.php';
				$class   = ucfirst( $class ) . '_Controller';
				$dir     = $globals->environment->controllerPath;

				//Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){

               //Instancia o objeto
					require_once( $dir . $file );
               
               if( class_exists( $class ) ){
                  $objController = new $class( $globals );
                  return $objController;
               
               } else {
                  //Classe declarada errada
                  throw( new ControllerException( "Wrong class declaration for controller <strong>\"" . $class . "\"</strong> in <strong>\"" . $dir . $file . "\"</strong>", 0x1002 ) );
               }
            } else {
					//Controller não localizado no disco
					throw( new ControllerException( "Controller <strong>\"" . $class . "\"</strong> not found in <strong>\"" . $dir . $file . "\"</strong>", 0x1001 ) );

				}

         }

			//Executa um método dentro do model
			static function Call( $class, $method, $param, $globals = ''){


               if( $globals == '' ){
                  $globals = $GLOBALS['globals'];
               }
               
               $objController = Controller::Load( $class, $globals );

               //Se o metodo existir
					if( method_exists( $objController, $method ) ){

                  //Retorna o resultado da função dentro do model
                  if( $objController->beforeCall( $class, $method, $param) !== false ){
                     $ret = call_user_func_array( array( $objController, $method), array( $param ));
                     return $ret;
                     
                  } else {
                     //Execução não altorizada
						   throw( new ControllerException( "Unauthorized execution for method <strong>\"" . $method . "\"</strong> in Controller Object <strong>\"" . $class . "\"</strong>", 0x1012 ) );
                  }

					} else {

						//Metodo não localizado dentro do controller
						throw( new ControllerException( "Method <strong>\"" . $method . "\"</strong> not found in Controller Object <strong>\"" . $class . "\"</strong>", 0x1011 ) );
					}



         }
         
         
         //Verifica se um controller existe
         static function Exists( $class ){
            
            $ret     = false;
            $globals = $GLOBALS['globals'];
            $file    = strtolower($class) . '.php';
				$class   = ucfirst( $class ) . '_Controller';
				$dir     = $globals->environment->controllerPath;
            
            //Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){
               
               require_once( $dir . $file );
            
               if( class_exists( $class ) ){
                  $ret = true;
               }
            }
            
            return $ret;
            
         }
            
         
         //Executa antes do método chamado ser executado
         function beforeCall( $class, $method, $param ){
            return true;
         }


		}


		//Objeto de erro personalizadp
		class ControllerException extends Exception{

			function __construct( $message, $err_cod ){
				parent::__construct( $message, $err_cod );
			}

		}
