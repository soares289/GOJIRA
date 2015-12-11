<?php 
//TODO - Criar metodo de acesso por controller/view

   require_once( 'configs.php' );
   
	
	//Busca o diretório onde os views estão salvos e o nome do arquivo
	if( isset( $_POST['class'] ) ){ 
	
		$globals->environment->ajaxRequest = true;
		$class = $_POST['class'];
		$proc  = ( isset($_POST['proc']) ? $_POST['proc'] : '');
      $param = array();
		
	} else {
		
		$globals->environment->ajaxRequest = false;
		if( isset( $_GET['query'] ) ){
			$query = $_GET['query'];
		} else {
			$query = '';
		}
		
		list( $class, $proc, $param) = $globals->tools->queryToParam( $query );
	}
	
   
	if( empty( $class ) ) $class = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'DEFAULT_CLASS' , 'home' );
	if( empty( $proc  ) ) $proc  = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'DEFAULT_METHOD', 'index' );
	
	
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
      $globals->user->type    = '';
      
   }

   try{
      
      //Inicia o controller e busca o seu retorno
      $param = array_merge( $param, $_POST );

      $objData = Controller::Call( $class, $proc, $globals, $param );
      
      //Retorna o view
      $template_ext = $globals->cfg->getConfig( PROJECT_ID . '_ENGINE', 'TEMPLATE_EXTENSION', '.html');
      if( isset( $objData->view_file ) ){
         $file = $objData->view_file . $template_ext;
      } else {
         $file = $class . '/' . $proc . $template_ext;
      }
      
      //Passa as variaveis para o template
      $globals->smarty->assign( 'class'      , $class );
      $globals->smarty->assign( 'proc'       , $proc );
      $globals->smarty->assign( 'param'      , $param );
      $globals->smarty->assign( 'objData'    , $objData );
      $globals->smarty->assign( 'tools'      , $globals->tools );
      $globals->smarty->assign( 'cfg'        , $globals->cfg );
      $globals->smarty->assign( 'login'      , $globals->login );
      $globals->smarty->assign( 'environment', $globals->environment );
      $globals->smarty->assign( 'user'       , $globals->user );
      $globals->smarty->assign( 'globals'    , $globals );

      
      //Renderiza o view
      if( file_exists( $globals->environment->viewPath . $file ) ){
         echo $globals->smarty->fetch( $file );
         
      } elseif( !is_object( $objData ) ){
         echo $objData;	
         
      }
      
   //Se der erro em alguma coisa, faz com que todos fiquem sabendo
   } catch( ControllerException $e ){

      die( $e->getMessage() . ' in line <strong>' . $e->getLine() . '</strong>' );
   
   }

