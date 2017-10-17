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
            $globals->smarty->assign( 'tools'      , $globals->tools );
            $globals->smarty->assign( 'cfg'        , $globals->cfg );
            $globals->smarty->assign( 'login'      , $globals->login );
            $globals->smarty->assign( 'environment', $globals->environment );
            $globals->smarty->assign( 'env'        , $globals->environment );
            $globals->smarty->assign( 'user'       , $globals->user );
            $globals->smarty->assign( 'globals'    , $globals );


            //Renderiza o view
            if( file_exists( $globals->environment->viewPath . $file ) ){
               echo $globals->smarty->fetch( $file );

            } elseif( !is_object( $objData ) ){
               echo $objData;

            }

            //Finaliza a conexão
            //$globals->conn->disconnect();

         //Se der erro em alguma coisa, faz com que todos fiquem sabendo
         } catch( ControllerException $e ){

            die( $e->getMessage() . ' in line <strong>' . $e->getLine() . '</strong>' );
            
            //Finaliza a conexão
            $globals->conn->disconnect();
         }
      }
   }