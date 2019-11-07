<?php

   class Engine extends GojiraCore{

      static function Render( $class, $proc, $param ){

         $class   = strtolower( $class );
         $proc    = strtolower( $proc );
         $globals = $GLOBALS['globals'];

         try{

            //Esses valores devem vir do [systemPath]/init.php
            $objData = Controller::Call( $class, $proc, $param, $globals );

            //Retorna o view
            $template_ext = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'TEMPLATE_EXTENSION', '.html');
            if( isset( $objData->view_file ) ){
               $file = $objData->view_file . $template_ext;
            } else {
               $file = $class . '/' . $proc . $template_ext;
            }

            //Passa as variaveis para o template
            $globals->smarty->assign( 'class'      , $class );
            $globals->smarty->assign( 'proc'       , $proc );
            $globals->smarty->assign( 'param'      , $param );
            $globals->smarty->assign( 'objData'    , $objData );
            $globals->smarty->assign( 'globals'    , $globals );
            $globals->smarty->assign( 'env'        , $globals->environment );

            foreach( $globals as $index => $obj ){
               if( !preg_match('/db|database|conn|connection|smarty/i', $index) ){
                  $globals->smarty->assign( $index, $globals->$index );   
               }
            }


            //Renderiza o view
            if( file_exists( $globals->environment->viewPath . $file ) ){
               return $globals->smarty->fetch( $file );

            } elseif( !is_object( $objData ) ){
               return $objData;

            }

         
         //Se der erro em alguma coisa, faz com que todos fiquem sabendo
         } catch( ControllerException $e ){
            
            if( Controller::Exists('Error') ){
               
               $error = Controller::Load( 'Error' );
               $param['class'] = $class;
               $param['proc']  = $proc;
               $param['error'] = $e;
               
               switch( $e->getCode() ){
                  case 0x1001:
                  case 0x1011:
                     header("HTTP/1.0 404 Not Found");
                     if( method_exists( $error, 'error_404' ) ){
                        echo Engine::Render( 'Error', 'error_404', $param );
                        exit;
                     } else {
                        throw( $e );
                     }
                     break;
                  case 0x1012:
                     header('HTTP/1.0 403 Forbidden');
                     if( method_exists( $error, 'error_403' ) ){
                        echo Engine::Render( 'Error', 'error_403', $param );
                        exit;
                     } else {
                        throw( $e );
                     }
                     break;
                  case 0x1002:
                     header('HTTP/1.1 500 Internal Server Error');
                     if( method_exists( $error, 'error_500' ) ){
                        echo Engine::Render( 'Error', 'error_500', $param );
                        exit;
                     } else {
                        throw( $e );
                     }
                     break;
                  default:
                     header('HTTP/1.1 500 Internal Server Error');
                     if( method_exists( $error, 'error_unknow' ) ){
                        echo Engine::Render( 'Error', 'error_unknow', $param );
                        exit;
                     } else {
                        throw( $e );
                     }
               }
            } else {
               header('HTTP/1.1 500 Internal Server Error');
               throw( $e );
            }

         } catch( Exception $e ){
            
            if( Controller::Exists('Error') ){
               
               $error = Controller::Load( 'Error' );
               $param['class'] = $class;
               $param['proc']  = $proc;
               $param['error'] = $e;
               
               header('HTTP/1.1 500 Internal Server Error');
               if( method_exists( $error, 'error_500' ) ){
                  echo Engine::Render( 'Error', 'error_500', $param );
               } elseif( method_exists( $error, 'error_unknow' ) ){
                  echo Engine::Render( 'Error', 'error_unknow', $param );
               } else {
                  throw( $e );
               }
            } else {
               throw( $e );
            }
         }
         
      }
      
      
   }
