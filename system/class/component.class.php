<?php

		abstract class Component extends GojiraCore{

			//Propriedades da classe
         protected $globals;
         protected $environment;
         protected $env;
         protected $model;


			function __construct2( $globals, $model ){

            $this->globals     = $globals;
            $this->environment = $globals->environment;
            $this->env         = $globals->environment;
            $this->model       = $model;
            
         }


         //Propriedades da classe
         function get_componentName(){ return str_replace( '_ComponentController', '', get_class( $this ) );  }
         


			//Instancia um controller a parte
         static function Load( $class, $globals = ''){

            if( $globals == '' ){
               $globals = $GLOBALS['globals'];
            }

            $file  = strtolower($class) . '.php';
            $class = ucwords(strtolower($class), "_0123456789");

            $controllerClass = $class . '_ComponentController';
				$controllerDir   = $globals->environment->componentControllerPath;

            $modelClass = $class . '_ComponentModel';
            $modelDir   = $globals->environment->componentModelPath;

            //Verifica se o controller (parte base e principal) existe
            if( file_exists( $controllerDir . $file ) ){

               //Se o model desse componente existir
               if( file_exists(  $modelDir . $file ) ){
                  
                  require_once( $modelDir . $file );
                  try{
                     $modelObj = new $modelClass( $globals );
                  } catch( Exception $e ){
                     throw( new ComponentException( "Error in instantiation of Model for component <strong>\"" . $class . "\"</strong> MSG: " . $e->getMessage(), 0x3002 ) );
                  }

               //Se não existir um model para o componente, instancia um model generico para facilitar acesso a base
               } else {
                  $modelObj = new AppModel( $globals );
               }

               if( method_exists( $modelObj, 'onLoad') ){
                  $modelObj->onLoad();
               }

               require_once( $controllerDir . $file );

               try{

                  $controllerObj = new $controllerClass( $globals, $modelObj );
                  
                  if( method_exists( $controllerObj, 'onLoad') ){
                     $controllerObj->onLoad();
                  }

                  return $controllerObj;

               } catch( Exception $e ){
                  throw( new ComponentException( "Error in instantiation of Controller for component <strong>\"" . $class . "\"</strong> MSG: " . $e->getMessage(), 0x3003 ) );
               }
               
               
            } else {
               throw( new ComponentException( "Component Controller <strong>\"" . $class . "\"</strong> not found in <strong>\"" . $controllerDir . $file . "\"</strong>", 0x3001) );
            }
            
         }



         //Renderizar um view do component
         protected function render( $view, $data ){

            $ret          = '';
            $template_ext = $this->globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'TEMPLATE_EXTENSION');
            $file         = $this->env->componentViewPath . $view . $template_ext;

            if( file_exists( $file ) ){

               $this->globals->smarty->assign( 'component', $this->componentName );
               $this->globals->smarty->assign( 'environment', $this->env );
               $this->globals->smarty->assign( 'env', $this->env );
               $this->globals->smarty->assign( 'objData', $data );

               if( is_array( $data ) || is_object( $data ) ){
                  foreach( $data as $i => $a ){
                     if( is_string($i) ){
                        $this->globals->smarty->assign( $i, $a );
                     }
                  }

               }
               
               $ret = $this->globals->smarty->fetch( $file );
            }
            return $ret;
         }


		}
      
		//Objeto de erro personalizadp
		class ComponentException extends Exception{

			function __construct( $message, $err_cod ){
				parent::__construct( $message, $err_cod );
			}

		}
