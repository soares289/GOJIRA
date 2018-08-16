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
         
      } elseif( file_exists( $env->libPath . $className . '.php' ) ){
         require_once( $env->libPath . $className . '.php' );
         
      } elseif( file_exists( $env->includePath . $className . '.php' ) ){
         require_once( $env->includePath . $className . '.php' );
         
      } elseif( file_exists( $env->systemLibPath . $className . '.php' ) ){
         require_once( $env->systemLibPath . $className . '.php' );
         
      } elseif( file_exists( $env->systemIncPath . $className . '.php' ) ){
         require_once( $env->systemIncPath . $className . '.php' );
         
      } elseif( file_exists( $env->systemPluginPath . $className . '.php' ) ){
         require_once( $env->systemPluginPath . $className . '.php' );
         
      } elseif( ! $lRec ){
         helper_loader( strtolower( $className ), true );
      }
      
   }
   
   spl_autoload_register( 'helper_loader' );