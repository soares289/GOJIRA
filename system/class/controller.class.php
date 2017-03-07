<?php

		abstract class Controller extends GojiraCore{

			//Propriedades da classe
			protected $globals;
         protected $model;

			function __construct1( $globals ){

				$this->globals     = $globals;
            $class             = substr( get_class( $this ), 0, -11 );

            //Se o model não existir, gera um exception, mas nesse instante, isso é previsto
            //por isso não deve deixar ele passar daqui.
            try{
               $this->model       = Model::Load( $class , $globals );
				} catch( Exception $e ){
               $this->model = new AppModel( $globals );
            }

			}


			//Instancia um controller a parte
         static function Load( $class, $globals = ''){

            if( $globals == '' ){
               $globals = $GLOBALS['globals'];
            }

            $file    = strtolower($class) . '.php';
				$class   = strtoupper( substr( $class, 0, 1 ) ) . substr( $class, 1 ) . '_Controller';
				$dir     = $globals->environment->controllerPath;

				//Se localizar o arquivo no disco
				if( file_exists(  $dir . $file ) ){

               //Instancia o objeto
					require_once( $dir . $file );
					$objController = new $class( $globals );
               return $objController;

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
						return call_user_func_array( array( $objController, $method), array( $param ));

					} else {

						//Metodo não localizado dentro do model
						throw( new ControllerException( "Method <strong>\"" . $method . "\"</strong> not found in Controller Object <strong>\"" . $class . "\"</strong>", 0x1001 ) );
					}



			}


		}


		//Objeto de erro personalizadp
		class ControllerException extends Exception{

			function __construct( $message, $err_cod ){
				parent::__construct( $message, $err_cod );
			}

		}
