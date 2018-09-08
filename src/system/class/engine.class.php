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
               if( !preg_match('/db|conn|smarty/i', $index) ){
                  $globals->smarty->assign( $index, $globals->$index );   
               }
            }


            //Renderiza o view
            if( file_exists( $globals->environment->viewPath . $file ) ){
               echo $globals->smarty->fetch( $file );

            } elseif( !is_object( $objData ) ){
               echo $objData;

            }

         
         //Se der erro em alguma coisa, faz com que todos fiquem sabendo
         } catch( ControllerException $e ){
            
            if( Controller::Exists('Error') ){
               
               $error = Controller::Load( 'Error' );
               $param['class'] = $class;
               $param['proc']  = $proc;
               
               switch( $e->getCode() ){
                  case 0x1001:
                  case 0x1011:
                     if( method_exists( $error, 'error_404' ) ){
                        header("HTTP/1.0 404 Not Found");
                        Engine::Render( 'Error', 'error_404', $param );
                        exit;
                     }
                     break;
                  case 0x1012:
                     if( method_exists( $error, 'error_403' ) ){
                        Engine::Render( 'Error', 'error_403', $param );
                        exit;
                     }
                     break;
                  case 0x1002:
                     if( method_exists( $error, 'error_500' ) ){
                        header('HTTP/1.1 500 Internal Server Error');
                        Engine::Render( 'Error', 'error_500', $param );
                        exit;
                     }
                     break;
                  default:
                     throw( $e );
               }
            }
         } catch( Exception $e ){
            
            if( Controller::Exists('Error') ){
               
               $error = Controller::Load( 'Error' );
               $param['class'] = $class;
               $param['proc']  = $proc;
               
               header('HTTP/1.1 500 Internal Server Error');
               if( method_exists( $error, 'error_500' ) ){
                  Engine::Render( 'Error', 'error_500', $param );
               } else {
                  die( $e->getMessage() . ' in line <strong>' . $e->getLine() . '</strong>' );
               }
            } else {
               throw( $e );
            }
         }
         
      }
      
      
   }
