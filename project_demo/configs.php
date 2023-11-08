<?php

define('SYSDIR_NAME', '../system/');
require_once('constants.php');

   @session_start();

   setlocale(LC_MONETARY, 'pt_BR');
   date_default_timezone_set('America/Sao_Paulo');

   error_reporting( E_ALL );

   //Remove o cache do SMARTY
   define('TPL_CACHE',0);

   //Caminho absoluto para conseguir localizar as classes independente de onde esse arquivo for chamado
   $absPath    = str_replace( "\\", "/", __DIR__ ) . '/';
   $systemPath = $absPath . SYSDIR_NAME;

   //Inicia o objeto global
   $globals              = new StdClass();
   $globals->database    = new StdClass();

   $globals->database->name     = DB_NAME;
   $globals->database->host     = DB_HOST;
   $globals->database->user     = DB_USER;
   $globals->database->password = DB_PWD;

   
   if( file_exists('routes.php') ){
      require_once( 'routes.php' );
   }
   
   require_once( $systemPath . 'init.php' );
   
   //Gera as urls e arquivos para uploads e images
   $globals->environment->globalUrl  = $globals->environment->baseUrl;
   $globals->environment->imageUrl   = $globals->environment->rootUrl . 'images/';
   $globals->environment->libraryUrl = $globals->environment->rootUrl . 'uploads/media_library/';
   $globals->environment->uploadUrl  = $globals->environment->rootUrl . 'uploads/';
   $globals->environment->uploadPath = $globals->environment->rootPath . 'uploads/';

   //Seta variaveis para os componentes globais
   $globals->environment->componentPath           = $globals->environment->absPath . 'component/';
   $globals->environment->componentModelPath      = $globals->environment->absPath . 'component/model/';
   $globals->environment->componentControllerPath = $globals->environment->absPath . 'component/controller/';
   $globals->environment->componentViewPath       = $globals->environment->absPath . 'component/view/';

   
   if( empty( $class ) ) $class = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'DEFAULT_CLASS' , 'home' );
   if( empty( $proc  ) ) $proc  = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'DEFAULT_METHOD', 'index' );

   $globals->environment->accessLevel = ACCESS_LEVEL;
   
   //Variaveis de ambiente referente ao login
   $globals->environment->logged = $globals->login->isLogged( $globals->environment->accessLevel );
   
   //Usado nos js do sistema
   if( file_exists( $globals->environment->rootPath . '/js/' . $class . '.js' ) ){
      $globals->environment->class_js = $globals->environment->rootUrl . '/js/' . $class . '.js';
   }

   
   $globals->user = new StdClass();
   if( $globals->environment->logged ){
      $globals->user->cod          = $globals->login->getLogged('Cod', $globals->environment->accessLevel);
      $globals->user->name         = $globals->login->getLogged('Name', $globals->environment->accessLevel);
      $globals->user->login        = $globals->login->getLogged('Login', $globals->environment->accessLevel);
      $globals->user->email        = $globals->login->getLogged('Email', $globals->environment->accessLevel);
      $globals->user->type         = $globals->login->getLogged('Type', $globals->environment->accessLevel);
      $globals->user->typecod      = $globals->login->getLogged('TypeCod', $globals->environment->accessLevel);
      $globals->user->profile      = $globals->login->getLogged('Profile', $globals->environment->accessLevel);
      $globals->user->picture      = $globals->login->getLogged('Picture', $globals->environment->accessLevel);

   } else {
      $globals->user->cod     = '';
      $globals->user->name    = '';
      $globals->user->login   = '';
      $globals->user->email   = '';
      $globals->user->type    = '';
      $globals->user->typecod = '';
      $globals->user->profile = '';
      $globals->user->picture = '';
      
   }


   //Inicia os componentes e deixa ele disponível no globals
   $globals->translate = Component::Load('Translate');
   $globals->access    = Component::Load('Access');
   $globals->log       = Component::Load('Log');
   $globals->mail      = Component::Load('mail_sender');
   $globals->mail->configure( ['smtp' => ['host'     => SMTP_HOST,
                                          'port'     => SMTP_PORT,
                                          'secure'   => SMTP_SECURE,
                                          'user'     => SMTP_USER,
                                          'password' => SMTP_PASSWORD,
                                          'from'     => SMTP_FROM,
                                          'name'     => SMTP_NAME] ] );
                                          
   //Define se o sistema é multi-idioma e qual o idioma padrão
   $lang = Model::Load('Language');
   $globals->environment->default_lang = $lang->getData('`default`=1 AND status = ' . STATUS_ACTIVE)[0];
   $globals->environment->multi_lang   = $lang->countData('status = ' . STATUS_ACTIVE) > 1;

   //Busca o idioma atual
   if( !isset( $_SESSION['current-lang'] ) ) $_SESSION['current-lang'] = $globals->environment->default_lang->id;
   $globals->environment->lang = $lang->getData($_SESSION['current-lang'])[0];

   //Verifica se é uma requisição ajax
   $globals->environment->is_ajax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
   
   //Renderiza o view que vem da url
   if( !defined('GOJIRA_PREVENT_RENDER') )
      echo Engine::Render( $class, $proc, $param );