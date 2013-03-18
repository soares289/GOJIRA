<?php
/*
	Tools - HTML
		Extensão do tools com funções referente a sistema de arquivos.
		Funções para localizar arquivos, listar, buscar infos, etc.
*/

		class Tool_Fs{
			
			
			//Retorna a imagem no formato certo
			function getThumb( $img ){
				
				if( file_exists( $img . '.jpg' ) )  return $img . '.jpg';
				if( file_exists( $img . '.jpeg' ) ) return $img . '.jpeg';
				if( file_exists( $img . '.png' )	)  return $img . '.png';
				if( file_exists( $img . '.gif' )	)  return $img . '.gif';
				return '';
			}
			
			
			
			
			//Retorna a primeira imagem encontrada em um diretório
			function getFirst( $dir ){
				
				$files = $this->getFiles( $dir, 'jpg,png,gif,jpeg' );
				
				if( count( $files ) > 0 )	return $files[0]['path'];
				
				//foreach( $files as $f ){
				//	if( strpos( "|jpg|jpeg|png|gif", $f['ext'] ) > 0 ){
				//		return $f['path'];
				//	}
				//}
				
			}
			
			
			//Busca todos os arquivos de um diretorio
			function getFiles( $path, $filter = '*', $recursive = false ){
				
				//Ajusta os filtros
				$filter = strtolower( $filter );
				
				//Ajusta o nome da pasta
				if( substr( $path, -1, 1) != '/' ) $path .= '/';
				
				//Se o caminho informado nao for um diretorio, pula fora
				if( ! is_dir( $path ) ) return array();
				
				$files = array();
				$dir   = opendir( $path );
				
				while( ($file = readdir( $dir )) !== false ){
					
					if( $file != '.' && $file != '..' && strtolower($file) != '.ds_store' ){
					
						//Se for um diretorio e estiver ativo a recursividade
						if( is_dir( $path . $file ) && $recursive ){
							
							//Busca os arquivos de dentro do diretorio
							$files[] = array('file' => $file, 'path' => $path . $file, 'type' => filetype( $path . $file ), 'ext' => '');
							$files = array_merge( $files, getFiles( $path . $file, $filter, true ) );
							
						} else {
							
							//Adiciona um arquivo na lista
                     $ext = explode('.',$file);
							$ext = strtolower(end($ext));
							if( $filter == '*' || strpos( '|' . $filter, $ext) ){
								$files[] = array('file' => $file,
                                         'name' => substr( $file, 0, strlen($file) - 1 - strlen($ext)),
                                         'path' => $path . $file,
                                         'type' => filetype( $path . $file ),
                                         'ext'  => $ext);
							}
							
						}
					}
				}
				
				return $files;
				
			}
			
         
         
         //Salva a imagem de uma url para o disco
         function saveToDisk( $url, $file ){
			
				$ch   = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
				$contents = curl_exec($ch);
				curl_close($ch);
				
            $f = fopen( $file, "w" );
            fwrite( $f, $contents );
            fclose( $f );
         
         }
         
         
         //Converte uma pasta(tem que ser caminho absoluto) para uma url, no dominio atual
         function path_to_url($path, $url){
      
            $path = str_replace( "\\", "/", $path);
            
            //Filtra só a parte que importa do path
            $new_path = substr( $path, strlen($_SERVER['DOCUMENT_ROOT']) + 1);
            
            //Deixa em um formato usavel
            $new_path = explode( '/', $new_path );
            
            //Busca a base da URL
            $ret_url  = substr( $url, 0, strpos( $url, $_SERVER['SERVER_NAME']) + strlen($_SERVER['SERVER_NAME']) + 1);
            
            for( $c = 0; $c < count( $new_path ); $c++ ){
               if( strlen( $new_path[$c] ) > 0 )
                  $ret_url .= $new_path[$c] . '/';
            }
            
            return $ret_url;
         }
		
		}
      