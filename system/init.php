<?php

      @session_start();

      define('GOJIRA_VERSION', '0.9.4');

      //Precisa saber a pasta do sistema que está sendo iniciado
      if( !isset( $absPath ) ) throw( new Exception('$absPath não localizado') );

      //Se não existir, cria agora
      if( !isset( $systemPath ) ) $systemPath = str_replace( "\\", "/", dirname(__FILE__) ) . '/';

      //Classes BASE usadas por quase tudo
      require_once( $systemPath . 'class/gojiracore.class.php' );
      require_once( $systemPath . 'class/tool.class.php' );
      require_once( $systemPath . 'class/connection.class.php' );
      require_once( $systemPath . 'class/config.class.php' );
      require_once( $systemPath . 'class/login.class.php' );
      require_once( $systemPath . 'class/component.class.php' );
      require_once( $systemPath . 'class/controller.class.php' );
      require_once( $systemPath . 'class/model.class.php' );
      require_once( $systemPath . 'class/collection.class.php' );
      require_once( $systemPath . 'class/engine.class.php' );
      require_once( $systemPath . 'class/smarty-3.1.33/libs/Smarty.class.php' );


      //Objetos mais comumente usados
      $globals->tools  = new Tool();
      $globals->cfg    = new Config( $absPath );
      $globals->smarty = new Smarty();

      //Localiza a URL base
      if( !isset( $baseURL )){
         if( PHP_SAPI === 'cli'){
            $baseUrl = 'cli://';
         } else {
            $baseURL = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'BASE_URL');

            if( empty( $baseURL ) ){
               $baseURL = $_SERVER['SERVER_NAME'] . (isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : '');
               $baseURL = substr( $baseURL, 0, min(strrpos( $baseURL, '/' ), strlen( $baseURL )) );
               if( substr( $baseURL, -1, 1 ) != '/' ) $baseURL .= '/';

               $globals->cfg->setConfig(PROJECT_ID . '_ENGINE', 'BASE_URL', $baseURL);
            }
         }
      }

      //Se não tiver a url do sistema, gera agora
      //Em boa parte dos casos vai ser assim, mas talvez tenha como deixar isso mais preciso
      if( ! isset( $systemURL ) ) $systemURL = $baseURL . 'system/';

      //Configurações globais
      ini_set("default_socket_timeout", 60);
      ob_start();
      date_default_timezone_set('America/Sao_Paulo');



//Seta as globais

      //Define a variavel global, caso ela ainda não exista
      if( !isset( $globals ) )              $globals              = new StdClass();
      if( !isset( $globals->environment ) ) $globals->environment = new StdClass();
      if( !isset( $globals->database ) ){
         $globals->database           = new StdClass();
         $globals->database->host     = '';
         $globals->database->user     = '';
         $globals->database->password = '';
         $globals->database->name     = '';

         $globals->db = $globals->database;     //DEPRECADO - na lista de remoção em versões futuras. Carlsom A. Soares - 2019-09-15
      }

      //Verifica se tem os dados para conexão
      if( isset( $globals->database ) ){
         
         $globals->connection = new Connection( $globals->database->host, $globals->database->user, $globals->database->password );
         
         //Seleciona o db configurado
         if( isset( $globals->database->name ) ){
            $globals->connection->selectDatabase( $globals->database->name );
         }

         $globals->conn = $globals->connection; //DEPRECADO - na lista de remoção em versões futuras. Carlsom A. Soares - 2019-09-15
      }

      $globals->login  = new Login( $globals->connection, $globals->tools );


      //Dados referentes ao environment do sistema - Paths e Urls

      /* Protocolo atual */
      $protocol                       = 'http' . (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 's' : '');
      $globals->environment->protocol = $protocol;
      
      //Base do sistema
      $globals->environment->absPath          = $absPath;
      $globals->environment->baseUrl          = $protocol . '://' . $baseURL;

      //webroot / acessivel pela url
      $globals->environment->rootUrl          = $protocol . '://' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ROOT_URL'      , $baseURL . 'webroot/' );
      $globals->environment->rootPath         = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ROOT_DIR'      , 'webroot/');

      /* Core do sistema */
      $globals->environment->corePath         = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'CORE_DIR'      , 'core/');
      $globals->environment->viewPath         = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VIEW_DIR'      , 'core/view/');
      $globals->environment->controllerPath   = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'CONTROLLER_DIR', 'core/controller/');
      $globals->environment->modelPath        = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'MODEL_DIR'     , 'core/model/');
      $globals->environment->includePath      = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'INCLUDE_DIR'   , 'core/inc/');
      $globals->environment->libPath          = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'LIB_DIR'       , 'core/lib/');

      /* Vendors */
      $globals->environment->vendorUrl        = $protocol . '://' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VENDOR_URL', $baseURL . 'vendor/' );
      $globals->environment->vendorPath       = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'VENDOR_DIR', 'vendor/');
      
      /* Gojira */
      $globals->environment->systemUrl        = $protocol . '://' . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'SYSTEM_URL'    , $systemURL);
      $globals->environment->systemPath       = $systemPath;

      $globals->environment->systemIncPath    = $systemPath . 'inc/';
      $globals->environment->systemVendorPath = $systemPath . 'vendor/';
      
      //Módulo de componentes
      $globals->environment->componentPath           = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'COMPONENT_DIR' , 'component/');
      $globals->environment->componentModelPath      = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'COMPONENT_MODEL_DIR' , 'component/model/');
      $globals->environment->componentControllerPath = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'COMPONENT_CONTROLLER_DIR' , 'component/controller/');
      $globals->environment->componentViewPath       = $absPath . $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'COMPONENT_VIEW_DIR' , 'component/view/');
      
      //Nível de acesso padrão
      $globals->environment->accessLevel = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'ACCESS_LEVEL'  , 'ADM' );


      //Configura o smarty
      $globals->smarty->setTemplateDir( $globals->environment->viewPath );
      $globals->smarty->caching = Smarty::CACHING_OFF;

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
         
         if( isset( $_GET['query'] ) ){
            list( $class, $proc, $param) = $globals->tools->queryToParam( $_GET['query'] );
         }

         if( empty( $class ) ){
            if( PHP_SAPI === 'cli' ){
               $param = [];

               //O primeiro sempre é o nome do arquivo
               if( count( $argv ) > 1){
                  for( $i = 1; $i < count($argv); $i++){
                     $arg = explode('=', $argv[$i]);
                     $param[ $arg[0] ] = $arg[1];
                  }
               }

               //Se receber o parameto e classe pelos argumentos
               $class = (isset( $param['class'] ) ? $param['class'] : '');
               $proc  = (isset( $param['proc'] ) ? $param['proc'] : '');

            } else {
               $curr_url  = parse_url( $globals->tools->curPageUrl() );
               $base_url  = parse_url( $globals->environment->baseUrl );
               list( $class, $proc, $param) = $globals->tools->queryToParam( substr( $curr_url['path'], strlen($base_url['path']) ) );
            }
         }
         
      }
      
      if( empty( $class ) ) $class = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'DEFAULT_CLASS' , 'home' );
      if( empty( $proc  ) ) $proc  = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'DEFAULT_METHOD', 'index' );

      $param = array_merge( $param, $_POST );
