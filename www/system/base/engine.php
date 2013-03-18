<?php 
	
	require_once( 'inc/functions.php' );
	require_once( 'configs.php' );
	
	//Verifica se está logado
	//TODO - Verificar se é area que exige login
	/*
	if( ! $globals->login->isLogged( "usr" ) ){
		require_once( 'login.php' );
		exit;
	}
	*/
	
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
	
	
	$logged = $globals->login->isLogged( $globals->environment->accessLevel );
	
	
	if( !$logged && $class != 'login' && $proc != 'upload'){
		//TODO - Criar indice com as areas e configurar quais cada usuário pode acessar
		//TODO - Liberar aqui algumas areas
		$class = "home";
		$proc  = "index";
	}
	
	
	try{
		
		//TODO - Verificar se o usuário tem acesso a esse metodo
		if( $ajax ) $param = $_POST;
		$objData = Controller::Call( $class, $proc, $globals, $param );
		
		if( isset( $objData->view_file ) ){
			$file = $objData->view_file . '.tpl';
		} else {
               		$file = $class . '/' . $proc . '.tpl';
		}
		if( !$ajax ) require_once( 'inc/header.php' );
		
		if( file_exists( $globals->environment->dir_view . $file ) ){
			
			//require_once( $file );
			$globals->smarty->assign( 'param', $param );
			$globals->smarty->assign( 'objData', $param );
			$globals->smarty->display( $file );
			
		} elseif( is_string( $objData ) ){
			
			echo $objData;
			
		}
		
		if( !$ajax ) require_once( 'inc/footer.php' );
		
	} catch( ControllerException $e ){

		die( $e->getMessage() . ' in line <strong>' . $e->getLine() . '</strong>' );
	
	}

?>
