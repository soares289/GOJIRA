<?php
//TODO - Só instanciar o Connection conectando se tiver recebido os dados
//TODO - Verificar se conexão foi realizada com sucesso, se não, gerar uma exception
//TODO - Remover a pasta JS do sistema, pensar em algo melhor pra fazer com aquilo

      @session_start();
      
      //Precisa saber a pasta do sistema que está sendo iniciado
      if( !isset( $absPath ) ) throw( new Exception('$absPath não localizado') );
      
      //Se não existir, cria agora
      if( !isset( $systemPath ) ) $systemPath = str_replace( "\\", "/", dirname(__FILE__) ) . '/';
      
      //Localiza a URL base
      $baseURL = $_SERVER['SERVER_NAME'] . (isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '');
      $baseURL = substr( $baseURL, 0, min(strrpos( $baseURL, '/' ), strlen( $baseURL )) );
      if( substr( $baseURL, -1, 1 ) != '/' ) $baseURL .= '/';
      
      //Se não tiver a url do sistema, gera agora
      //Em boa parte dos casos vai ser assim, mas talvez tenha como deixar isso mais preciso
      if( ! isset( $systemURL ) ) $systemURL = $baseURL . 'system/';
      
      //Configurações globais
      ini_set("default_socket_timeout", 60);
      ob_start();
      date_default_timezone_set('America/Sao_Paulo');
      
      //Classes BASE usadas por quase tudo
      require_once( $systemPath . 'class/gojiracore.class.php' );
      require_once( $systemPath . 'class/tool.class.php' );
      require_once( $systemPath . 'class/connection.class.php' );
      require_once( $systemPath . 'class/config.class.php' );
      require_once( $systemPath . 'class/log.class.php' );
      require_once( $systemPath . 'class/login.class.php' );
      require_once( $systemPath . 'class/controller.class.php' );
      require_once( $systemPath . 'class/model.class.php' );
      require_once( $systemPath . 'class/collection.class.php' );
      require_once( $systemPath . 'class/smarty/Smarty.class.php' );
      
      
//Seta as globais
      
      //Define a variavel global, caso ela ainda não exista
      if( !isset( $globals ) )              $globals              = new StdClass();
      if( !isset( $globals->environment ) ) $globals->environment = new StdClass();
      if( !isset( $globals->db ) ){
         $globals->db           = new StdClass();
         $globals->db->host     = '';
         $globals->db->user     = '';
         $globals->db->password = '';
         $globals->db->name     = '';
      }
      
      //Objetos mais comumente usados
      $globals->tools    = new Tool();
      $globals->conn     = new Connection( $globals->db->host, $globals->db->user, $globals->db->password, $globals->db->name);
      $globals->cfg      = new Config( $absPath );
      $globals->smarty   = new Smarty();
      $globals->login    = new Login( $globals->conn, $globals->tools );
      
      $protocol = 'http' . (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 's' : '');
            
      //Dados referentes ao environment do sistema
      $globals->environment->protocol         = $protocol;
      $globals->environment->absPath          = $absPath;
      
      $globals->environment->systemPath       = $systemPath;
      $globals->environment->systemLibPath    = $systemPath . 'lib/';
      $globals->environment->systemIncPath    = $systemPath . 'inc/';
      $globals->environment->systemVendorPath = $systemPath . 'vendor/';
      $globals->environment->systemPluginPath = $systemPath . 'plugin/';
      $globals->environment->baseUrl          = '//' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'BASE_URL'      , $baseURL );
      $globals->environment->rootUrl          = '//' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ROOT_URL'      , $baseURL . 'webroot/' );
      $globals->environment->systemUrl        = '//' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSTEM_URL'    , $systemURL);
      $globals->environment->systemPluginUrl  = '//' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'PLUGIN_URL'    , $systemURL . 'plugin/');
      $globals->environment->rootPath         = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ROOT_DIR'      , $globals->environment->absPath . 'webroot/');
      $globals->environment->viewPath         = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VIEW_DIR'      , $globals->environment->absPath . 'core/view/');
      $globals->environment->controllerPath   = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'CONTROLLER_DIR', $globals->environment->absPath . 'core/controller/');
      $globals->environment->modelPath        = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'MODEL_DIR'     , $globals->environment->absPath . 'core/model/');
      $globals->environment->includePath      = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'INCLUDE_DIR'   , $globals->environment->absPath . 'core/inc/');
      $globals->environment->vendorPath       = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VENDOR_DIR'   , $globals->environment->absPath . 'core/vendor/');
      $globals->environment->libPath          = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'LIB_DIR'   , $globals->environment->absPath . 'core/lib/');
      
      $globals->environment->accessLevel     = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ACCESS_LEVEL'  , 'ADM' );
      
      
      //Configura o smarty
      $globals->smarty->setTemplateDir( $globals->environment->viewPath );
      $globals->smarty->caching = TPL_CACHE;
      
      //Adiciona o controle de helpers (autoload)
      require_once( $globals->environment->systemIncPath . 'helpers.php' );
      
      //Cria o controller padrão de escopo de aplicação
      if( file_exists( $globals->environment->controllerPath . 'appController.php' ) ){
         require_once( $globals->environment->controllerPath . 'appController.php' );
      } else {
         class AppController extends Controller{};
      }
      
      
      //Cria o model padrão de escopo de aplicação
      if( file_exists( $globals->environment->modelPath . 'appModel.php' ) ){
         require_once( $globals->environment->modelPath . 'appModel.php' );
      } else {
         class AppModel extends Model{};
      }
      
      //Cria os parametos de inicialização das classes
      if( isset( $_POST['class'] ) ){ 
         //Para requisições ajax
         //Ex: $.post("engine.php",{"class":'home','proc':'index'},function(data){});
         $globals->environment->ajaxRequest = true;
         $class = $_POST['class'];
         $proc  = ( isset($_POST['proc']) ? $_POST['proc'] : '');
         $param = array();
         
      } else {
         //Cria os parametos base para o engine.php
         //É possivel agora enviar isso via post diretamente
         //Ex: $.post(baseURL + "home/index/",{},function(data){});
                  
         //Filtra o protocolo que está sendo usado
         $protocol = 'http';
         if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $protocol .= "s";
         }
         
         $curr_url  = parse_url( $globals->tools->curPageUrl() );
         $base_url  = parse_url( $protocol . ':' . $globals->environment->baseUrl );

         list( $class, $proc, $param) = $globals->tools->queryToParam( substr( $curr_url['path'], strlen($base_url['path']) ) );
         
         if( $class == 'engine.php' ) $class = '';
         if( $class == '' && isset( $_GET['query'] ) )
            list( $class, $proc, $param) = $globals->tools->queryToParam( $_GET['query'] );
      }
      
      $param = array_merge( $param, $_POST );
      