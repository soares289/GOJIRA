<?php
/* Quando uma classe de auxilio for estanciada, verifica se ela está em uma dessas pastas, então da include na mesma
   ps: Só funciona em PHP5 ou superior;
*/

   //Trabalha com os helpers
   function helper_loader($className) {
      
      $globals   = $GLOBALS['globals'];
      $className = strtolower( $className );
      
      
      //Procura dentro da pasta Helpers do sistema
      if( file_exists( $globals->environment->systemPath . 'helpers/' . $className . '.php' ) ){
         require_once($globals->environment->systemPath . 'helpers/' . $className . '.php');
      
      //Procura dentro da pasta Class do sistema
      } elseif( file_exists( $globals->environment->systemPath . 'class/' . $className . '.php' ) ){
         require_once($globals->environment->systemPath . 'class/' . $className . '.php');
         
      //Procura dentro da pasta INC do sistema
      } elseif( file_exists( $globals->environment->systemPath . 'inc/' . $className . '.php' ) ){
         require_once($globals->environment->systemPath . 'inc/' . $className . '.php');
         
      
      //Busca dentro da pasta Helpers do projeto
      } elseif( file_exists( $globals->environment->absPath . 'helpers/' . $className . '.php' ) ){
         require_once($globals->environment->absPath . 'helpers/' . $className . '.php');
      
      //Busca dentro da pasta class do projeto      
      } elseif( file_exists( $globals->environment->absPath . 'class/' . $className . '.php' ) ){
         require_once($globals->environment->absPath . 'class/' . $className . '.php');
      
      
      //Busca dentro da pasta inc do projeto      
      } elseif( file_exists( $globals->environment->absPath . 'inc/' . $className . '.php' ) ){
         require_once($globals->environment->absPath . 'inc/' . $className . '.php');
      }
      
   }
   
   spl_autoload_register( 'helper_loader' );