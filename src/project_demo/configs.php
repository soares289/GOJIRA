<?php
//TODO - Criar rotina para converter os diretorios e urls, removedo os ../ e colocando o caminho absoluto
define( 'PROJECT_ID' , 'GOJIRA_PROJECT_DEMO');
define( 'SYSDIR_NAME', '../system/');


   @session_start();

   setlocale(LC_MONETARY, 'pt_BR');

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
   if( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'production.com') !== false ){

      error_reporting( 0 );

      $globals->db->name     = "";
      $globals->db->host     = "";
      $globals->db->user     = "";
      $globals->db->password = "";


   //DB de testes
   } elseif( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'homologation.com') !== false ){


      error_reporting( E_ALL );

      $globals->db->name     = "homodb";
      $globals->db->host     = "localhost";
      $globals->db->user     = "homodb";
      $globals->db->password = "homopwd";



   //DB de desenvolvimento
   } else {

      error_reporting( E_ALL );
      $globals->db->name     = "salonline";
      $globals->db->host     = "localhost";
      $globals->db->user     = "user";
      $globals->db->password = "user$";

   }

   //Remove o cache do SMARTY
   define('TPL_CACHE',0);

   require_once( $systemPath . 'init.php' );

   //Variaveis de ambiente referente ao login
   $globals->environment->logged = $globals->login->isLogged( $globals->environment->accessLevel );

   if( $globals->environment->logged ){
      $globals->user = new StdClass();
      $globals->user->cod     = $globals->login->getLogged('Cod');
      $globals->user->name    = $globals->login->getLogged('Name');
      $globals->user->login   = $globals->login->getLogged('Login');
      $globals->user->email   = $globals->login->getLogged('Email');
      $globals->user->type    = $globals->login->getLogged('Type');

   } else {
      $globals->user = new StdClass();
      $globals->user->cod     = '';
      $globals->user->name    = '';
      $globals->user->login   = '';
      $globals->user->email   = '';
      $globals->user->company = '';
      $globals->user->image   = '';
      $globals->user->type    = '';

   }

   //Renderiza o view que vem da url
   echo Engine::Render( $class, $proc, $param );