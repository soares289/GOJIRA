<?php
//TODO - Criar rotina para converter os diretorios e urls, removedo os ../ e colocando o caminho absoluto

define( 'PROJECT_ID' , 'NOME_DO_PROJETO');
define( 'SYSDIR_NAME', '../system/');

   @session_start();
   
   //Caminho absoluto para conseguir localizar as classes independente de onde esse arquivo for chamado
   $absPath    = str_replace( "\\", "/", dirname(__FILE__) ) . '/';
   
   //Localizando a pasta do sistema
   $dots = '';
   while( !file_exists( $absPath . $dots . SYSDIR_NAME . 'init.php' ) ) $dots .= '../';
   $systemPath = $absPath . $dots . SYSDIR_NAME;
   
   //Localizando a URL do sistema
   $baseURL = $_SERVER['SERVER_NAME'] . (isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '');
   $baseURL = substr( $baseURL, 0, min(strrpos( $baseURL, '/' ), strlen( $baseURL )) );
   if( substr( $baseURL, -1, 1 ) != '/' ) $baseURL .= '/';
   $systemURL  = $baseURL . $dots . SYSDIR_NAME;
   
   //Inicia o objeto global
   $globals              = new StdClass();
   $globals->db          = new StdClass();
   
   //DB de produção
   if( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'PRODUCAO') !== false ){
      
      error_reporting( 0 );
      
      $globals->db->name     = "";
      $globals->db->host     = "";
      $globals->db->user     = "";
      $globals->db->password = "";


   //DB de testes
   } elseif( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'HOMOLOGAÇÃO') !== false ){
      
      error_reporting( E_ALL );
      
      $globals->db->name     = "";
      $globals->db->host     = "";
      $globals->db->user     = "";
      $globals->db->password = "";
      
      

   //DB de desenvolvimento
   } else {
      
      error_reporting( E_ALL );
      $globals->db->name     = "test";
      $globals->db->host     = "localhost";
      $globals->db->user     = "user";
      $globals->db->password = "user$";
   
   }
   
   //Remove o cache do SMARTY
   define('TPL_CACHE',0);
   
   require_once( $systemPath . 'init.php' );
   
   