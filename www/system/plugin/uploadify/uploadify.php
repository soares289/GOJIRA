<?php

fclose(fopen( "passou_aki", "w" ));

// Define a destination
$targetFolder = $_REQUEST['dir'];  //'/uploads'; // Relative to the root

if (!empty($_FILES)) {
	
	
	// Validate the file type
	$fileTypes = $_REQUEST['extension'];
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = '../' . $targetFolder; //$_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . (empty( $_REQUEST['name'] ) ? $fileParts['basename'] : $_REQUEST['name']); //$_FILES['Filedata']['name'];
	
	if ( strpos( $fileTypes, $fileParts['extension'] ) !== false ){ //   in_array($fileParts['extension'],$fileTypes)) {
		
		$targetFile .= '.' . $fileParts['extension'];
		move_uploaded_file($tempFile,$targetFile);
		
		echo '1';
	} else {
		echo 'Invalid file type.';
	}
}
?>