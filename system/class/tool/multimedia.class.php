<?php
/*
	Tools - HTML
		Extensão do tools com funções referentes a multimedia/Embbed de midia, e outras coisas referentes a 
		imagem/video
*/
		class Tool_Multimedia{
			
			
			//Verifica qual tipo de video e retorna o ID do mesmo e o tipo
			function getVideoId( $url ){
            
            $id  = '';
            $ret = '';

            if( preg_match( '/(youtube\.|youtu\.be)/i', $url) ){
					
					$ret = 'youtube';
					$id  = $this->getYoutubeId( $url );
					
				} elseif( strpos( strtolower($url), 'vimeo') > 0 ) {
					
					$ret = 'vimeo';
					$id  = $this->getVimeoId( $url );
					
				}
				
				return array( $id, $ret );
				
			}
			
			
			
			//Pega o ID do video no Youtuve
		   function getYoutubeId( $url ){
		      
		      if( ! preg_match( '/(youtube\.|youtu\.be)/i', $url) ){
		         return '';
		      }
            
            
            if( strpos( strtolower($url), 'youtu.be' ) !== false ){  //É um link encurtado youtu.be/YDsa3a_3a
               $aUrl = explode( '/', $url );
               return end( $aUrl );

            } else {
               
               if( !strpos($url, "#!v=") === false ){  //Em caso de ser um link de quando clica nos related
                  $url = str_replace('#!v=','?v=',$url);
               }
               
               parse_str( parse_url( $url, PHP_URL_QUERY ) );
               
               if( isset( $v ) ){
                  return $v;
               } else { //Se não achou, é por que é o link de um video de canal ex: http://www.youtube.com/user/laryssap#p/a/u/1/SAXVMaLL94g
                  $aUrl = explode( '/', $url );
                  //return substr( $url, strrpos( $url,'/') + 1, 11);
                  return end($aUrl);
               }

            }
		   }
			
			
			
			//Pega o ID do video no Vimeo
		   function getVimeoId( $url ){
		      
		      if( !preg_match('/vimeo\./i', $url) ){
		         return '';
		      }
				
				$id = end( explode( "/", $url ) );
				
				if( strlen( $id ) < 8 ){
					return '';
				}
				
				return $id;
				
		   }
		
		
		
		   //Adiciona um embeded do youtube ou Vimeo
		   function embedVideo( $id, $width, $height, $type = "youtube", $autoplay = false, $url = false ){
		
		      
				if( strtolower( $type ) == "youtube" ){
					
					if( $url ){
						$ret = 'http://www.youtube.com/embed/' . $id . '?rel=0&wmode=transparent&autoplay=' . ($autoplay ? 1 : 0);
					} else {
						$ret = '<iframe src="http://www.youtube.com/embed/' . $id . '?rel=0&wmode=transparent&autoplay=' . ($autoplay ? 1 : 0) . '" width="' . $width . '" height="' . $height . '" frameborder="0" allowfullscreen></iframe>';
					}
				
				} elseif( strtolower( $type ) == "vimeo" ){
					
					if( $url ){
						$ret = 'http://player.vimeo.com/video/' . $id . '?title=0&amp;byline=0&amp;portrait=0&autoplay=' . ($autoplay ? 1 : 0);
					} else {
						$ret = '<iframe src="http://player.vimeo.com/video/' . $id . '?title=0&amp;byline=0&amp;portrait=0&autoplay=' . ($autoplay ? 1 : 0) . '" width="' . $width . '" height="' . $height . '" frameborder="0"></iframe>';
					}
				}
				
				return $ret;
		   }


		
			//Busca o thumb do video (Se possivel)
			function getVideoThumb( $videoId, $videoSrc ){
				
				if( $videoSrc == "youtube" ){
					
					$ret = "http://i1.ytimg.com/vi/" . $videoId . "/default.jpg";
					
				} elseif( $videoSrc == "vimeo" ) {
					
					$json = file_get_contents('http://vimeo.com/api/v2/video/' . $videoId . '.json');
					$arr  = json_decode( $json, true );
					$ret  = $arr[0]['thumbnail_large'];
					
				}
				
				return $ret;
				
			}
			
			
			
			
			//A partir da URL de um arquivo no ISSU, busca o seu id, que pode ser usado para gerar o codigo de embeded
			function issu_get_id( $url ){
				
				$ch   = curl_init();
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1); 
				$ret = curl_exec($ch);
				curl_close($ch);
				
				$n1 = strpos( $ret, 'documentId' ) + 11;
				$n2 = strpos( $ret, '"', $n1 );
				$id = substr( $ret, $n1, $n2 - $n1 );
				
				return $id;
				
			}
			
			
			
			
			
			
			
			//retorna o codigode para embedar um arquivo do issu a partir de um codigo recebido
			function issu_get_embed( $id, $width, $height ){
				
				$ret = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" style="width: ' . $width . 'px;height: ' . $height . 'px" id="928ddaf9-16fe-a102-ceca-8610fbe6249f" >' .
			        			'<param name="movie" value="http://static.issuu.com/webembed/viewers/style1/v2/IssuuReader.swf?mode=mini&amp;backgroundColor=%23222222&amp;documentId=' . $id . '" />' .
				            '<param name="allowfullscreen" value="true"/>' .
				            '<param name="menu" value="false"/>' .
				            '<param name="wmode" value="transparent"/>' .
			            	'<embed src="http://static.issuu.com/webembed/viewers/style1/v2/IssuuReader.swf" type="application/x-shockwave-flash" allowfullscreen="true" menu="false" wmode="transparent" ' .
			                     'style="width:' . $width . 'px;height:' . $height . 'px" ' .
			                     'flashvars="mode=mini&amp;backgroundColor=%23222222&amp;documentId=' . $id . '" /></object>';
				return $ret;
			}
			
         
         
         
         //Converte um arquivo PNG para um arquivo JPG respeitando a transparencia (transparencia vira branco)
         function png2jpg( $originalFile, $outputFile = '', $quality = 70 /*0 Pior qualidade, maior compressão, 100 melhor qualidade, pior compressão*/){
            
            if( $outputFile == '' ) $outputFile = substr( $originalFile, 0, strlen( $originalFile ) - 3 ) . 'jpg';
            
            $image = imagecreatefrompng($originalFile);
            $bg    = imagecreatetruecolor(imagesx($image), imagesy($image));
            
            imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
            imagealphablending($bg, TRUE);
            imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
            imagedestroy($image);
            imagejpeg($bg, $outputFile, $quality);
            imagedestroy($bg);
            
         }

		}
      