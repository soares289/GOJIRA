<?php
/* Quando uma classe de auxilio for instanciada, verifica se ela está em uma dessas pastas, então da include na mesma
   ps: Só funciona em PHP5 ou superior;
*/

   //Trabalha com os helpers
   function helper_loader($className, $lRec = false) {
      
      $env       = $GLOBALS['globals']->environment;
      $className = str_replace( "\\","/", $className );
      
      if( file_exists( $env->vendorPath . $className . '.php' ) ){
         require_once( $env->vendorPath . $className . '.php' );
         
      } elseif( file_exists( $env->systemIncPath . $className . '.php' ) ){
         require_once( $env->systemIncPath . $className . '.php' );
         
      } elseif( file_exists( $env->systemVendorPath . $className . '.php' ) ){
         require_once( $env->systemVendorPath . $className . '.php' );
         
      } elseif( ! $lRec ){
         helper_loader( strtolower( $className ), true );
      }
      
   }
   
   spl_autoload_register( 'helper_loader' );