<?php
//TODO - Criar rotina para converter os diretorios e urls, removedo os ../ e colocando o caminho absoluto

define( 'PROJECT_ID' , 'CONTROL');
define( 'SYSDIR_NAME', '../system/');

   @session_start();
   
   //Caminho absoluto para conseguir localizar as classes independente de onde esse arquivo for chamado
   $tmpFile    = str_replace( "\\", "/", __FILE__ );
   $absPath    = substr( $tmpFile, 0, strlen( $tmpFile ) - strlen( end( explode( "/", $tmpFile ) ) ) );
   
   //Localizando a pasta do sistema
   $dots = '';
   while( !file_exists( $absPath . $dots . SYSDIR_NAME . 'init.php' ) ) $dots .= '../';
   $systemPath = $absPath . $dots . SYSDIR_NAME;
      
   
   $_SESSION['absPath']    = $absPath;
   $_SESSION['systemPath'] = $systemPath;

   //Inicia o objeto global
   $globals              = new StdClass();
   $globals->db          = new StdClass();
   $globals->environment = new StdClass();
   
   //DB de produção
   if( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'PRODUÇÃO') !== false ){
      
      error_reporting( 0 );
      
      $globals->db->name     = "";
      $globals->db->host     = "";
      $globals->db->user     = "";
      $globals->db->password = "";

   //DB de testes
   } elseif( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'HOMOLOGAÇÃO') !== false ){
      
      error_reporting( E_ERROR | E_WARNING | E_PARSE );
      $globals->db->name     = "";
      $globals->db->host     = "";
      $globals->db->user     = "";
      $globals->db->password = "";

   //DB de desenvolvimento
   } else {
               
      error_reporting( E_ALL );
      $globals->db->name     = "control";
      $globals->db->host     = "localhost";
      $globals->db->user     = "user";
      $globals->db->password = "user$";
   }

   //Remove o cache do SMARTY
   define('TPL_CACHE',0);
   
   require_once( $systemPath . 'init.php' );
   
   //Localiza a URL base
   $baseURL = $globals->tools->curPageUrl(); 
   $baseURL = substr( $baseURL, 0, min(strrpos( $baseURL, '/' ), strlen( $baseURL )) );
   if( substr( $baseURL, -1, 1 ) != '/' ) $baseURL .= '/';
   
   //Localizando a URL do sistema
   $systemURL  = $baseURL . $dots . SYSDIR_NAME;
   
   
   //Dados referentes ao environment do sistema
   $globals->environment->absPath        = $absPath;
   $globals->environment->systemPath     = $systemPath;
   $globals->environment->site_url       = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'BASE_URL'      , $baseURL );
   $globals->environment->system_url     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSTEM_URL'    , $systemURL);
   $globals->environment->plugin_url     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_URL'    , $globals->environment->system_url . 'plugin/');
   $globals->environment->dir_view       = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VIEW_DIR'      , $globals->environment->absPath . 'view/');
   $globals->environment->dir_controller = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'CONTROLLER_DIR', $globals->environment->absPath . 'controller/');
   $globals->environment->dir_model      = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'MODEL_DIR'     , $globals->environment->absPath . 'model/');
   $globals->environment->dir_include    = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'INCLUDE_DIR'   , $globals->environment->absPath . 'inc/');
   $globals->environment->dir_plugin     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_DIR'    , $globals->environment->systemPath . 'plugin/');
   $globals->environment->dir_plugin     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSINCLUDE_DIR', $globals->environment->systemPath . 'inc/');
   $globals->environment->accessLevel    = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ACCESS_LEVEL'  , 'USR' );
   

   $globals->smarty->setTemplateDir( $globals->environment->dir_view );
   //$globals->smarty->setCompileDir('/web/www.example.com/guestbook/templates_c/');
   //$globals->smarty->setConfigDir('/web/www.example.com/guestbook/configs/');
   //$globals->smarty->setCacheDir('/web/www.example.com/guestbook/cache/');
   $globals->smarty->caching = TPL_CACHE;
   $globals->login->config('client','cli');
   
   require_once( $systemPath . 'inc/constants.php' );
?>
