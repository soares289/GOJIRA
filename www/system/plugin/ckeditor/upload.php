<?php session_start();

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
?>
<?php if($_GET['page'] == "list"){ ?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="UTF-8">
		<title></title>
      <style type="text/css">
      	*{ margin: 0; padding: 0; }
      </style>
	</head>
	<body>
   <form action="?page=upload" method="post" enctype="multipart/form-data">
		<input type="file" name="file"><input type="submit" value="Enviar">
   </form>
	</body>
	</html>
<?php }elseif($_GET['page'] == "upload"){ ?>
	
   <?php
      
      //define a pasta das imagens
      if( isset( $_SESSION['dir_upload'] ) ){
         $path = $_SESSION['dir_upload'];
      } else {
         $path = str_replace("\\","/",$_SERVER['DOCUMENT_ROOT']);
         $path = $path . '/uploads/';
      }
      
      //Define a url atual
      $url        = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME'] .  $_SERVER['REQUEST_URI'];
      
      
		$file 		= $_FILES['file'];
		$filear 		= explode(".",$file['name']);
		$fileName 	= $filear[0] . md5(uniqid());
		$ext 			= end($filear);
      $url        = path_to_url( $path, $url );
		$pathFile 	= $path . $fileName . strtolower( $ext );
		
      mkdir( $path, true );
   
		if($ext == "jpg" || $ext == "jpeg" || $ext == "png" || $ext == "gif"){
			
			if(move_uploaded_file($file['tmp_name'], $pathFile)){
			
			?>
         	<script type="text/javascript">
	           	parent.editor.insertHtml('<img src="<?php echo $url . $fileName; ?>" alt="">');
            </script>
         <?php
				
			}else{
			
				echo("Não foi");
				
			}
			
		}else{
		
			echo("Erro: O arquivo deve estar na extenção .jpg ou .png ou .gif");
			
		}
	
	?>
   
<?php }else{ ?>
<div id="contentEditorImageUpload">
<script type="text/javascript">
	$(document).ready(function(){
		
		$("#editorImageUploadClose").click(function(){
		
			$("#contentEditorImageUpload").remove();
			
		});/*
		$("#editorImageUpload #file").uploadify({
			 "uploader"  : "swf/uploadify.swf",
			 "script"    : "uploadify.php",
			 "scriptData": {dir:"images/uploaded/"},
			 "cancelImg" : "images/cancel.png",
			 "folder"    : "../images/uploaded/",
			 "fileExt"   : "*.jpg",
			 "buttonText": "SELECIONE",
			 "auto"      : true,
			 "onAllComplete": function(){ alert("subiu!"); },
			 "onOpen": function(){ uploading = true; }
	  });*/
		
	});
</script>
<style type="text/css">
#editorImageUpload{
		background: #FFF;
		border: 1px solid #CCC; 
		border-radius: 10px; 
		padding: 10px; 
		width: 400px; 
		height: 90px; 
		position: fixed; 
		top: 50%; 
		left: 50%; 
		margin-top: -15px; 
		margin-left: -200px;
}
#editorImageUploadClose{
		background: #f00; 
		border-radius: 20px; 
		width: 15px; 
		cursor: pointer; 
		height: 15px; 
		line-height: 15px; 
		text-align:center; 
		position: absolute; 
		top: 10px; 
		right: 10px;
}
#editorImageUploadTitle{
		padding-bottom: 10px;
		border-bottom: 1px solid #ccc;
		margin-bottom: 20px;
}
</style>
<div id="editorImageUpload">

	<div id="editorImageUploadClose">x</div>
	<div id="editorImageUploadTitle">Adcionar imagem via upload</div>

	<iframe src="upload.php?page=list" frameborder="0" height="60px" width="400px"></iframe>
   
</div>
<?php } ?>
</div>