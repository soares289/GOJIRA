<?php
/*
	Tools - HTML
		Extensão do tools com funções voltada a segurança do sistema.
		Proteção contra ataques de SqlInjection, arrumando strings, etc.
*/
	
		class Tool_Security{
			
			
			//Remove conteudo que pode ser usado para sql_injection de uma query
		   function antiInjection($str, $lTags = false, $lHtml = false) {
		   	
				if( !$lHtml ) {
			      
					$str = preg_replace("/(from|alter table|select|insert|delete|update| where|drop table|show tables|\*|--|\\\\)/i","",$str);
			      $str = trim($str);//limpa espaços vazio
			
					//Se o segundo parametro for true, remove as tags html
			      if( !$lTags ){ 
						$str = strip_tags($str);
					}
					
				}
		
		      if (!is_numeric($str)) {
		         $str = (get_magic_quotes_gpc() ? stripslashes($str) : $str);
		         $str = (function_exists("mysql_real_escape_string") ? mysql_real_escape_string($str) : mysql_escape_string($str));
		      }
				
		      return $str;
		   }
		
		
		
			
			
			
			// Url Amigaveis
			function getFriendlyUrl($str){
				
				$str = trim(strtolower($str));
				$char 	= array(" ","ç","á","é","í","ó","ú","ä","ë","ï","ö","ü","à","è","ì","ò","ù","â","ê","î","ô","û","ã","õ","!","@","#","$","%","^","&","*","(",")","_","=","+","[","]","{","}","\\","|","/","?","<",">",".",",","p");
				$replace = array("-","c","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","o", "", "", "", "", "", "", "", "", "", "","_", "", "", "", "", "", "",  "", "", "", "", "", "", "", "","&#112;");
				
				return(str_replace($char,$replace, $str));
				
			}
			
			
			
			//Ajusta o valor para facilitar na comparação
			function clean( $str ){
				
				$encode = mb_detect_encoding($str.'x', 'UTF-8, ISO-8859-1');
				if( $encode != 'UTF-8' ) $str = mb_convert_encoding( $str, 'UTF-8', $encode );
				
				//return strtolower( $str );
				$str     = trim(mb_strtolower($str, 'UTF-8'));
				$char 	= array("ç","á","é","í","ó","ú","ä","ë","ï","ö","ü","à","è","ì","ò","ù","â","ê","î","ô","û","ã","õ","!","@","#","$","%","^","&","*","(",")","_","=","+","[","]","{","}","\\","|","/","?","<",">",".",",","\"","'",'-',':');
				$replace = array("c","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","o", "", "", "", "", "", "", "", "", "", "","_", "", "", "", "", "", "",  "", "", "", "", "", "", "", "",  "", "",'','');

				return(str_replace($char,$replace, $str));
				
			}
			
			
		}
