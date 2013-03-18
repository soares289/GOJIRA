<? 
//COMO USAR:********************************************************************************************
//EXEMPLO RESIZE:	thumb.php?img=fotos.jpg&m=400&t=resize  (m é o tamanho maximo de largura ou altura)
//EXEMPLO CROP:   thumb.php?img=fotos.jpg&w=400&h=200&t=crop
//******************************************************************************************************


	error_reporting( E_ALL );
	
   //Retorna a extensão do arquivo
   function file_type( $file  ){
		
      if( strpos( $file, '?' ) > 0 ){
         $file = explode( '?' , $file );
         $file = $file[0];
      }

		if( function_exists('mime_content_type')){

		    $ret = mime_content_type( $file );

      } elseif( function_exists('finfo_file') ) {

         $finfo = finfo_open(FILEINFO_MIME_TYPE);
         $ret   = finfo_file($finfo, $filename);

      } else {
         
         $ext = explode('.', $file);
         $ext = strtolower( end( $ext ) );
         switch( $ext ){
            case 'jpg':  $ret = 'image/jpeg'; break;
            case 'jpeg': $ret = 'image/jpeg'; break;
            case 'gif':  $ret = 'image/gif'; break;
            case 'png':  $ret = 'image/png'; break;
         }

      }
		
      return $ret;
   }
   
   
   //Cria uma imagem a partir de um arquivo no disco
   function createImage( $filename ){
      
      $type = file_type( $filename );
      
      if( $type == 'image/jpeg' ){
         $image = imagecreatefromjpeg($filename);
      } elseif( $type == 'image/gif' ){
         $image = imagecreatefromgif($filename);
      } elseif( $type == 'image/png' ){
         $image = imagecreatefrompng($filename);
      }
      
      return $image;
   }

   
   //Retorna o header correto dependendo do formato da imagem
   function imageHeader( $filename, $image_f ){
   
      $type = file_type( $filename );

      if( $type == 'image/jpeg' ){
         header('Content-type: image/jpeg');
         imagejpeg($image_f, null, 80);
			
      } elseif( $type == 'image/gif' ){
         header('Content-type: image/gif');
         imagegif($image_f, null, 80);
			
      } elseif( $type == 'image/png' ){
         header('Content-type: image/png');
         imagepng($image_f, null, 8);
			
      }
      
   
   }
	
	
   ///Se um filtro diver sido informado, aplica o mesmo sobre a imagem
   function setImageFilter( $image_f, $f, $hue ){

      //PARTE DE FILTROS
      if( !is_numeric( $f ) && $f != '' ){
         $tmp = get_defined_constants();
         $f   = $tmp['IMG_FILTER_' . strtoupper($f)];
      }
      //Se um filtro foi escolhido
      if( $f != '' ){

         $arr = array($image_f, $f);
         if( isset( $_GET['fp1'] ) ) $arr[] = $_GET['fp1'];
         if( isset( $_GET['fp2'] ) ) $arr[] = $_GET['fp2'];
         if( isset( $_GET['fp3'] ) ) $arr[] = $_GET['fp3'];
         if( isset( $_GET['fp4'] ) ) $arr[] = $_GET['fp4'];

         call_user_func_array( 'imagefilter', $arr );

      }


      if( $hue != '' ){
         imagefilterhue($image_f,hexdec(substr($hue,0,2)),hexdec(substr($hue,2,2)),hexdec(substr($hue,4,2)));
      }

   }


	//Colorizar manualmente      
   function setImageColor( $image_f, $c ){

      if( $c != '' ){
         
         $r = hexdec(substr($c,0,2));
         $g = hexdec(substr($c,2,2));
         $b = hexdec(substr($c,4,2));
         
         $width  = imagesx( $image_f );
         $height = imagesy( $image_f );
         
         for( $x = 0; $x < $width; $x++ ){
            for( $y = 0; $y < $height; $y++ ){

               $colorIndex         = imagecolorat($image_f, $x, $y);
               //$colorInfo          = array('red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0);
               //$colorInfo['red']   = ($colorIndex >> 16) & 0xFF;
               //$colorInfo['green'] = ($colorIndex >> 8) & 0xFF; 
               //$colorInfo['blue']  = $colorIndex & 0xFF;
               //$colorInfo['alpha'] = ($colorIndex >> 24) & 0xFF;
               $colorInfo          = imagecolorsforindex($image_f, $colorIndex);
               $colorInfo['red']   = ($colorInfo['red'] * 0.25) + ($r * 0.75);
               $colorInfo['green'] = ($colorInfo['green'] * 0.25) + ($g * 0.75);
               $colorInfo['blue']  = ($colorInfo['blue'] * 0.25) + ($b * 0.75);
               $color              = imagecolorallocatealpha($image_f, $colorInfo['red'], $colorInfo['green'], $colorInfo['blue'], $colorInfo['alpha']);
               imagesetpixel($image_f, $x, $y, $color);
               //print_r( $colorInfo );
               
            }
         }  
      }

   }


   //Executa o filtro de HUE Saturation
	function imagefilterhue($im,$r,$g,$b){
		
		$rgb = $r+$g+$b;
		$col = array($r/$rgb,$b/$rgb,$g/$rgb);
		$height = imagesy($im);
		$width = imagesx($im);
		
		for($x=0; $x<$width; $x++){
		  	for($y=0; $y<$height; $y++){
				
				$rgb = ImageColorAt($im, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$newR = $r*$col[0] + $g*$col[1] + $b*$col[2];
				$newG = $r*$col[2] + $g*$col[0] + $b*$col[1];
				$newB = $r*$col[1] + $g*$col[2] + $b*$col[0];
				imagesetpixel($im, $x, $y,imagecolorallocate($im, $newR, $newG, $newB));
				
			}
		}
		
	}
	
	
   
   //PEGA O MAIOR TAMANHO ******************************************************************
   function thumb_resize($filename, $max_size, $w = 0, $h = 0) {
		 
	 	// Get new dimensions
	 	$type = file_type( $filename );
      list($width, $height) = getimagesize($filename);
      
		//Se for informado width
		if( $w > 0 ){
			
			if( $h > 0 && ($w * $height) / $width > $h ){
				$new_height = $h;
         	$new_width = ($h * $width)/$height;
			} else {
				$new_width = $w;
	         $new_height = ($w * $height)/$width;
			}
		
		//se for informado height
		} elseif( $h > 0 ){
			
			if( $w > 0 && ($h * $width) / $height > $w ){
				$new_width  = $w;
	         $new_height = ($w * $height)/$width;
			} else {
				$new_height = $h;
         	$new_width  = ($h * $width)/$height;
			}
		
		//Se não for informado nenhum dos 2, usa o metodo padrao
		} elseif ($width > $height){
			
         $new_width = $max_size;
         $new_height = ($max_size * $height)/$width;
			
      } else {
			
         $new_height = $max_size;
         $new_width = ($max_size * $width)/$height;
			
      }

      $image_p = imagecreatetruecolor($new_width, $new_height);
      
		if($type == 'image/gif' or $type == 'image/png'){
			imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
		   imagealphablending($image_p, false);
		   imagesavealpha($image_p, true);
		}
		
      $image = createImage( $filename );
      
      imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
      return $image_p;
   }
	
	
	
   //CROP NOS TAMANHOS PRÉ DEFINIDOS ******************************************************************
   function thumb_crop($filename, $desired_width, $desired_height, $bordersize, $position) {
   
      // Get new dimensions
		$type = file_type( $filename );
		
      list($width, $height) = getimagesize($filename);
		
      if($desired_width/$desired_height > $width/$height):
         $new_width = $desired_width;
         $new_height = $height * ($desired_width / $width);
      else:
         $new_width = $width * ($desired_height / $height);
         $new_height = $desired_height;
      endif;
		
		
		
    
      // Resize
      $image_p = imagecreatetruecolor($new_width, $new_height);
      $image_f = imagecreatetruecolor($desired_width, $desired_height);
      $image = createImage( $filename );
		
		if($type == 'image/gif' or $type == 'image/png'){
			imagecolortransparent($image_p, imagecolorallocatealpha($image_p, 0, 0, 0, 127));
		   imagealphablending($image_p, false);
		   imagesavealpha($image_p, true);
			
			imagecolortransparent($image_f, imagecolorallocatealpha($image_f, 0, 0, 0, 127));
		   imagealphablending($image_f, false);
		   imagesavealpha($image_f, true);
		}
		
      imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    
	 
      // Adjust position
      switch($position){
			
         case("topleft"):
            $x = $bordersize;
            $y = $bordersize;
            break;
            
         case("topright"):
            $x = $new_width - $desired_width + $bordersize;
            $y = $bordersize;
            break;
        
         case("bottomleft"):
            $x = $bordersize;
            $y = $new_height - $desired_height + $bordersize;
            break;
        
         case("bottomright"):
            $x = $new_width - $desired_width + $bordersize;
            $y = $new_height - $desired_height + $bordersize;
            break;
        
         case("center"):
            $x = ($new_width - $desired_width) / 2 + $bordersize;
            $y = ($new_height - $desired_height) / 2 + $bordersize;
            break;
    }
    
    // Resample with 1px border
    imagecopyresampled($image_f, $image_p, $bordersize, $bordersize, $x, $y,    $desired_width  - 2 * $bordersize, 
                                                                                $desired_height - 2 * $bordersize, 
                                                                                $desired_width  - 2 * $bordersize, 
                                                                                $desired_height - 2 * $bordersize);
    
    return $image_f;
}


$img  = (isset($_GET['img']) ? $_GET['img'] : '');				//Nome da imagem
$type = (isset($_GET['t'])   ? $_GET['t']   : '');		//Tipo     = crop ou resize
$m    = (isset($_GET['m'])   ? $_GET['m']   : 0);				//MAX      = Tamanho max, usado no resize
$w    = (isset($_GET['w'])   ? $_GET['w']   : 0);				//WIDTH    = Largura da imagem, obrigatorio no crop e opcional no resize
$h    = (isset($_GET['h'])   ? $_GET['h']   : 0);				//HEIGHT   = Altura da imagem, obrigatorio no crop e opcional no resize
$b    = (isset($_GET['b'])   ? $_GET['b']   : 0);				//BORDER   = Borda usada no crop, opcional
$c    = (isset($_GET['c'])   ? $_GET['c']   : '');				//COLOR    = Cor usado no colorir
$p    = (isset($_GET['p'])   ? $_GET['p']   : 'center');		//POSITION = Posição que o crop vai remover da imagem, opcional, padrao centro, opcoes: centro, bottomright, bottomleft, topright, topleft
$f    = (isset($_GET['f'])   ? $_GET['f']   : '');				//Filtros - Ver pagina: http://php.net/manual/pt_BR/function.imagefilter.php
$hue  = (isset($_GET['hue']) ? $_GET['hue'] : '');				//HUE - Saturacao = Recebe uma cor RGB em hex

//EXEMPLO RESIZE:	thumb.php?img=fotos.jpg&m=400&t=resize  (m é o tamanho maximo de largura ou altura)
if( $type == "resize" ){
	$image_f = thumb_resize($img, $m, $w, $h);

//EXEMPLO CROP:		thumb.php?img=fotos.jpg&w=400&h=200&t=crop
} elseif( $type == "crop" ){
	$image_f = thumb_crop($img, $w, $h, $b, $p);

//CASO NÃO QUEIRA PASSAR NENHUM TIPO DE REDIMENSIONAMENTO, APENAS OS FILTROS
} else {
   $image_f = createImage( $img );
   imagecolortransparent($image_f, imagecolorallocatealpha($image_f, 0, 0, 0, 127));
   imagealphablending($image_f, false);
   imagesavealpha($image_f, true);

}


setImageFilter( $image_f, $f, $hue);
setImageColor( $image_f, $c );


imageHeader( $img, $image_f );
