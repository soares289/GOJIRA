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
		         //$str = (function_exists("mysql_real_escape_string") ? mysql_real_escape_string($str) : mysql_escape_string($str));
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
			
			//Gera uma chave de criptografia baseado em determinada string
         function encrypt_key( $key ){
            
            $hash = hash('sha256'   , $key, false );
            $ret  = pack( 'H*', $hash );
            
            return $ret;
         }
         
         
         
         //Encripta uma string
         function encrypt_str( $key, $value, $algorithm = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC ){
            
            # create a random IV to use with CBC encoding
            $iv_size = mcrypt_get_iv_size($algorithm, $mode );
            $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
            
            # creates a cipher text compatible with AES (Rijndael block size = 256)
            # to keep the text confidential - only suitable for encoded input that never ends with value 00h
            # (because of default zero padding)
            $ciphertext = mcrypt_encrypt( $algorithm, $key, $value, $mode , $iv);
            
            # prepend the IV for it to be available for decryption
            $ciphertext = $iv . $ciphertext;
            
            # encode the resulting cipher text so it can be represented by a string
            $ciphertext_base64 = base64_encode($ciphertext);
            
            return $ciphertext_base64;
         }
         
         
         //Desencripta uma string
         function decrypt_str( $key, $value, $algorithm = MCRYPT_RIJNDAEL_256, $mode = MCRYPT_MODE_CBC ){
            
            # Decode to bin
            $ciphertext_dec = base64_decode($value);
            
            # Get the size of iv
            $iv_size = mcrypt_get_iv_size($algorithm, $mode );
            
            # retrieves the IV, iv_size should be created using mcrypt_get_iv_size()
            $iv_dec = substr($ciphertext_dec, 0, $iv_size);
            
            # retrieves the cipher text (everything except the $iv_size in the front)
            $ciphertext_dec = substr($ciphertext_dec, $iv_size);
            
            # may remove 00h valued characters from end of plain text
            $plaintext_dec = mcrypt_decrypt($algorithm, $key, $ciphertext_dec, $mode , $iv_dec);
            
            return $plaintext_dec;
             
         }
         
		}
