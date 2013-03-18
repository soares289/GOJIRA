<?php
/*
	Tools - HTML
		Extensão do tools com funções referentes a multimedia/Embbed de midia, e outras coisas referentes a 
		imagem/video
*/
		class Tool_Helper{
			
			
			//Converte a query para parametros
			function queryToParam( $query ){
				
				$query = explode( '/', $query );
				$class = $query[ 0 ];
				$proc  = (count( $query ) > 1 ? $query[1] : '');
				$param = array();
				
				if( count( $query ) > 2 ){
					for( $c = 2; $c < count( $query ); $c += 2 ){
						$param[ $query[ $c ] ] = (count( $query ) > ($c+1) ? $query[ $c + 1 ] : ''); 
					}
				}
				
				return array( $class, $proc, $param );
		
			}
			
			
			//executa request de alguma coisa
			function request( $class, $proc, $param = array(), $lRet = false ){
			
            $globals = $GLOBALS['globals'];
            $objData = Controller::Call( $class, $proc, $globals, $param );
         
            if( isset( $objData->view_file ) ){
               $file = $objData->view_file . '.tpl';
            } else {
               $file = $class . '/' . $proc . '.tpl';
            }

            if( is_string( $objData ) ){
               
               if( $lRet ) return $objData;
               echo $objData;

            } else { //if( file_exists( $globals->environment->dir_view . $file ) ){

               $smarty = new Smarty();
               $smarty->setTemplateDir( $globals->environment->dir_view );
               $smarty->assign( 'class'      , $class );
               $smarty->assign( 'proc'       , $proc );
               $smarty->assign( 'param'      , $param );
               $smarty->assign( 'objData'    , $objData );
               $smarty->assign( 'tools'      , $globals->tools );
               $smarty->assign( 'environment', $globals->environment );

               if( $lRet ) return $smarty->fetch( $file );
               $smarty->display( $file );
   
            }

            
			}
			
			
		}