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

            $file  = strtolower($class) . '.php';
            $class = ucwords(strtolower($class), "_0123456789") . '_Controller';
				$dir   = $globals->environment->controllerPath;

				//Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){

               //Instancia o objeto
					require_once( $dir . $file );
               
               if( class_exists( $class ) ){
                  $objController = new $class( $globals );
                  $objController->onLoad();
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

			//Executa um método dentro do controller
			static function Call( $class, $method, $param, $globals = ''){


            if( $globals == '' ){
               $globals = $GLOBALS['globals'];
            }

            $ret           = '';
            $objController = Controller::Load( $class, $globals );

            //Se o metodo existir
            if( method_exists( $objController, $method ) ){

               //Retorna o resultado da função dentro do model
               if( $objController->beforeCall( $class, $method, $param) !== false ){
                  $ret = call_user_func_array( array( $objController, $method), array( $param ));
                  
               } else {

                  if( method_exists( $objController, 'unauthorized' ) ){
                     $ret = call_user_func_array( array( $objController, 'unauthorized'), array( $method, $param ));
            
                  } else {
                     //Execução não altorizada
                     throw( new ControllerException( "Unauthorized execution for method <strong>\"" . $method . "\"</strong> in Controller Object <strong>\"" . $class . "\"</strong>", 0x1012 ) );
                     
                  }
               }

            } elseif( method_exists( $objController, 'not_found')  ) {
               $ret = call_user_func_array( array( $objController, 'not_found'), array( $method, $param ));
               
            } else {
               //Metodo não localizado dentro do controller
               throw( new ControllerException( "Method <strong>\"" . $method . "\"</strong> not found in Controller Object <strong>\"" . $class . "\"</strong>", 0x1011 ) );
            }

            return $ret;

         }
         
         
         //Verifica se um controller existe
         static function Exists( $class ){
            
            $ret     = false;
            $globals = $GLOBALS['globals'];
            $file    = strtolower($class) . '.php';
            $class   = ucwords(strtolower($class), "_0123456789") . '_Controller';
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
            

         //Executa quando o objeto é instanciado
         function onLoad(){}


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
