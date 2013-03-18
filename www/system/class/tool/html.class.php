<?php
/*
	Tools - HTML
		Extensão do tools com funções que ajudam na escrita de html.
*/

		class Tool_Html{
			
			
			//Carrega arquivos linkados para o header da pagina
			function load( $type, $file, $refresh = true, $url = false ){
				
				switch( $type ){
					case 'js':
						echo '<script type="text/javascript" src="' . ( $url ? $file : 'js/' . $file . '.js' ) . ($refresh ? '?' . @date('YmdHis') : '') . '"></script>';
						break;
					case 'css':
						echo '<link rel="stylesheet" type="text/css" href="' . ( $url ? $file : 'css/' . $file . '.css' ) . ($refresh ? '?' . @date('YmdHis') : '') . '" media="all" />';
						break;
				}
				echo "\n";
				
			}
			
			
			
			
			// SEO
			function getSeo($title, $description, $keywords, $googlebot = "all, follow", $robots = "all, follow", $canonical = false){
					
				printf("<title>%s</title>\n",$title);
				printf("<meta name=\"description\" content=\"%s\">\n", preg_replace("/\[(.*?)\]/is","",strip_tags($description)));
				printf("<meta name=\"keywords\" content=\"%s\">\n", $keywords);
				printf("<meta name=\"copyright\" content=\"Agência Freela\">\n");
				printf("<meta name=\"googlebot\" content=\"%s\">\n", $googlebot);
				printf("<meta name=\"robots\" content=\"%s\">\n", $robots);
						
				if($canonical){
				
					printf("<meta name=\"canonical\" content=\"%s\">\n", $canical);
				}
			
			}
			
			
			
			
			//Escreve o menu de paginação do registros
		   function pagination( $qtde, $pagQtde, $url, $pag = 0, $ajax = false ){
				
				$ret   = '';
				if( $pag <= 0 ){
		      	$pag   = (isset( $_GET['pag'] ) ? $_GET['pag'] : 1);
				}
				
		      $nItem = ceil( $qtde / $pagQtde );
				
				if( $ajax ){
					
					$url .= '/';
					if( strpos( $url, '/pag/' ) !== false ){
						$url = substr( $url, 0, strpos( $url, '/pag/' ) ) .
									substr( $url, strpos( $url, '/', strpos( $url, '/pag/' ) + 5 ) );
					}
					$url = substr( $url, 0, strlen( $url ) - 1 );
					
					$link = $url . '/pag/';
					
				} else {
					
			      $link  = $url . '?' . (isset( $_GET['id'] ) ? 'id=' . $_GET['id'] . '&' : '') .
			                            (isset( $_GET['view'] ) ? 'view=' . $_GET['view'] . '&' : '') .
			                            (isset( $_GET['type'] ) ? 'type=' . $_GET['type'] . '&' : '') .
			                            (isset( $_GET['sort'] ) ? 'sort=' . $_GET['sort'] . '&' : '') .
			                            (isset( $_GET['inv'] ) ? 'inv=' . $_GET['inv'] . '&' : '') . 'pag=';
				
				}
		
		      if( $nItem > 1 ){
		         $ret .= '<div class="pagination"><ul>';
		         for( $c = 1; $c <= $nItem; $c++ ){
		            if( $pag == $c ){
		               $ret .= '<li><strong>' . $c . '</strong></li>';
		            } else {
		               $ret .= '<li><span><a href="' . $link . $c . '">' . $c . '</a></span></li>';
		            }
		         }
		         $ret .= '</ul></div>';
		      }
				
				return $ret;
		   }
			
			
			
			//Escreve um javascript na pagina
		   function script( $a ){
		      return '<script type="text/javascript">' .  $a . '</script>';
		   }
			
			
			//Escreve um campo de cabeçario de uma tabela
			function columnHeader( $def, $sort, $inv, $img, $field, $title){
				
				if( empty( $sort ) ){
					echo '<th>' . $title . '</th>';
				} else {
				   echo '<th><a href="' . $def . $field . ($sort == $field && $inv == false ? '/inv/1' : '/') . '">' . (isset( $img[$field] ) ? $img[$field] : '') . $title . '</a></th>';
				}
				
			}
			
			
		}
