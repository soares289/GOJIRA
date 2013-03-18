<?php

define( 'PROJECT_ID', '');

   @session_start();
   
	//Caminho absoluto para conseguir localizar as classes independente de onde esse arquivo for chamado
	$tmpFile    = str_replace( "\\", "/", __FILE__ );
	$absPath    = substr( $tmpFile, 0, strlen( $tmpFile ) - strlen( end( explode( "/", $tmpFile ) ) ) );
	$systemPath = $absPath . '../system/';
	
	$_SESSION['absPath']    = $absPath;
	$_SESSION['systemPath'] = $systemPath;
	
	
	//DB de produção
   if( strpos(strtolower( $_SERVER['HTTP_HOST'] ),' PRODUCAO ') !== false ){
		error_reporting( 0 );
      $globals->db->name     = "";
		$globals->db->host     = "";
		$globals->db->user     = "";
		$globals->db->password = "";

	//DB de testes
	} elseif( strpos(strtolower( $_SERVER['HTTP_HOST'] ),'carlsonfreela.com') !== false ){
		error_reporting( E_ERROR | E_WARNING | E_PARSE );
      $globals->db->name     = "";
		$globals->db->host     = "";
		$globals->db->user     = "";
		$globals->db->password = "";

	//DB de desenvolvimento
   } else {
		error_reporting( E_ALL );
		$globals->db->name     = "";
		$globals->db->host     = "";
		$globals->db->user     = "";
		$globals->db->password = "";

   }
	
	require_once( $systemPath . 'init.php' );
	
	//Tenta localizar a url atual do site e a url do sistema de base
	$url = $globals->tools->curPageUrl(); 
	$url = substr( $url, 0, min(strrpos( $url, '/' ), strlen( $url )) );
	$adm = str_replace( '/administrador', '', str_replace( '/admin', '', str_replace( '/painel', '', $url)));
	
	if( substr( $url, -1, 1 ) != '/' ) $url = $url . '/';
	if( substr( $adm, -1, 1 ) != '/' ) $adm = $adm . '/';
	
	//Dados referentes ao environment do sistema
	$globals->environment->absPath        = $absPath;
	$globals->environment->systemPath     = $systemPath;
	$globals->environment->site_url       = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'BASE_URL'      , $url );
	$globals->environment->system_url     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSTEM_URL'    , $adm . 'system/');
	$globals->environment->plugin_url     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_URL'    , $globals->environment->system_url . 'plugin/');
	$globals->environment->dir_view       = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VIEW_DIR'      , $globals->environment->absPath . 'view/');
	$globals->environment->dir_controller = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'CONTROLLER_DIR', $globals->environment->absPath . 'controller/');
	$globals->environment->dir_model      = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'MODEL_DIR'     , $globals->environment->absPath . 'model/');
	$globals->environment->dir_plugin     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_DIR'    , $globals->environment->systemPath . 'plugin/');
	$globals->environment->accessLevel    = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ACCESS_LEVEL'  , 'ADM,SAD' );

      $globals->smarty->setTemplateDir( $globals->environment->dir_view );
      $globals->smarty->setCompileDir('/web/www.example.com/guestbook/templates_c/');
      $globals->smarty->setConfigDir('/web/www.example.com/guestbook/configs/');
      $globals->smarty->setCacheDir('/web/www.example.com/guestbook/cache/');
	
	require_once( $systemPath . 'inc/constants.php' );
?>
