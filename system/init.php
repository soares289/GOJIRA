<?php
      
      //Configurações globais
      ini_set("default_socket_timeout", 60);
      ob_start();
      date_default_timezone_set('America/Sao_Paulo');
      
      //Classes BASE usadas por quase tudo
      require_once( "class/tool.class.php" );
      require_once( "class/connection.class.php" );
      require_once( "class/config.class.php" );
      require_once( "class/log.class.php");
      require_once( "class/login.class.php" );
      require_once( "class/controller.class.php" );
      require_once( "class/model.class.php" );
      require_once( "class/collection.class.php" );
      require_once( "class/smarty/Smarty.class.php" );
      
      
//Seta as globais
      
      //Define a variavel global, caso ela ainda não exista
      if( !isset( $globals ) ){
         $globals              = new StdClass();
         $globals->db          = new StdClass();
         $globals->environment = new StdClass();
      }
      
      //Objetos mais comumente usados
      $globals->tools    = new Tool();
      $globals->conn     = new Connection( $globals->db->host, $globals->db->user, $globals->db->password, $globals->db->name);
      $globals->cfg      = new Config( $globals->conn, $globals->tools );
      $globals->smarty   = new Smarty();
      $globals->log      = new Log( $globals->conn, $globals->tools );
      $globals->login    = new Login( $globals->conn, $globals->tools );
      
      $protocol = 'http' . (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 's' : '') . '://'.
                  ( strtolower( substr( $_SERVER['SERVER_NAME'], 0, 4 ) ) == 'www.' ? 'www.' : '');
            
      //Dados referentes ao environment do sistema
      $globals->environment->protocol        = $protocol;
      $globals->environment->absPath         = $absPath;
      $globals->environment->systemPath      = $systemPath;
      $globals->environment->libPath         = $systemPath . 'lib/';
      $globals->environment->site_url        = $protocol . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'BASE_URL'      , $baseURL );
      $globals->environment->system_url      = $protocol . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSTEM_URL'    , $systemURL);
      $globals->environment->plugin_url      = $protocol . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_URL'    , $systemURL . 'plugin/');
      $globals->environment->dir_view        = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VIEW_DIR'      , $globals->environment->absPath . 'view/');
      $globals->environment->dir_controller  = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'CONTROLLER_DIR', $globals->environment->absPath . 'controller/');
      $globals->environment->dir_model       = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'MODEL_DIR'     , $globals->environment->absPath . 'model/');
      $globals->environment->dir_include     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'INCLUDE_DIR'   , $globals->environment->absPath . 'inc/');
      $globals->environment->dir_plugin      = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_DIR'    , $globals->environment->systemPath . 'plugin/');
      $globals->environment->dir_sys_include = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSINCLUDE_DIR', $globals->environment->systemPath . 'inc/');
      $globals->environment->accessLevel     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ACCESS_LEVEL'  , 'ADM' );
      

      $globals->smarty->setTemplateDir( $globals->environment->dir_view );
      $globals->smarty->caching = TPL_CACHE;
      
      
      require_once( $systemPath . 'inc/helpers.php' );
      include_once( $systemPath . 'inc/version_fix.php' );
      include_once( $systemPath . 'inc/constants.php' );