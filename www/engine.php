<?php 
//TODO - Criar metodo de acesso por controller/view

   require_once( 'configs.php' );
   
   if( file_exists( $globals->environment->dir_include . 'functions.php' ) )
        require_once( $globals->environment->dir_include . 'functions.php' );
	
	
	//Busca o diretório onde os views estão salvos e o nome do arquivo
	if( isset( $_POST['class'] ) ){ 
	
		$ajax  = true;
		$class = $_POST['class'];
		$proc  = ( isset($_POST['proc']) ? $_POST['proc'] : '');
		
	} else {
		
		$ajax = false;
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
	$logged = $globals->login->isLogged( $globals->environment->accessLevel );
   $globals->environment->logged = $logged;
	
   //Redireciona para o LOGIN caso não esteja logado.
	if( !$logged && $class != 'login' && $proc != 'upload'){
		//TODO - Criar indice com as areas e configurar quais cada usuário pode acessar
		//TODO - Liberar aqui algumas areas
		$class = "login";
		$proc  = "index";
	}
	
	try{
		
      //Inicia o controller e busca o seu retorno
		if( $ajax ) $param = $_POST;
		$objData = Controller::Call( $class, $proc, $globals, $param );
		
      //Retorna o view
		if( isset( $objData->view_file ) ){
			$file = $objData->view_file . '.tpl';
		} else {
   			$file = $class . '/' . $proc . '.tpl';
		}
		
      //Passa as variaveis para o template
		$globals->smarty->assign( 'class'      , $class );
      $globals->smarty->assign( 'proc'       , $proc );
      $globals->smarty->assign( 'param'      , $param );
      $globals->smarty->assign( 'objData'    , $objData );
      $globals->smarty->assign( 'tools'      , $globals->tools );
      $globals->smarty->assign( 'environment', $globals->environment );
      
      //Caso tenha um arquivo PHP de header
		if( !$ajax && file_exists( $globals->environment->dir_include . 'header.php' ) )
                     require_once( $globals->environment->dir_include . 'header.php' );
		
      //Renderiza o view
		if( file_exists( $globals->environment->dir_view . $file ) ){
         
			echo $globals->smarty->fetch( $file );
			
		} elseif( !is_object( $objData ) ){
			echo $objData;	
		}
		
      //Caso tenha um arquivo PHP de footer
		if( !$ajax && file_exists( $globals->environment->dir_include . 'footer.php' ) )
                     require_once( $globals->environment->dir_include . 'footer.php' );
		
   //Se der erro em alguma coisa, faz com que todos fiquem sabendo
	} catch( ControllerException $e ){

		die( $e->getMessage() . ' in line <strong>' . $e->getLine() . '</strong>' );
	
	}

