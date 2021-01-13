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
		         $str = $str;
		         //$str = (function_exists("mysql_real_escape_string") ? mysql_real_escape_string($str) : mysql_escape_string($str));
		      }
				
		      return $str;
		   }
		
		
         //Remove telefones de strings // Essa função serve para telefones em formato brasileiro
         function removePhone( $text, $replacement = '********' ){
            
            $pattern = '/([\(\+]{0,1}[0-9o]{2,3}[\)]{0,1}[\s\-\_\+]{0,2}){0,2}[0-9]{4,5}[\-\s]{0,1}[0-9]{4}/i';
            $ret     = preg_replace( $pattern, $replacement, $text );
            /* Formatos testados
               (+55) (011) 99999-9999 (com ou sem hifen) (com ou sem parenteses)
               9999-9999
               99999-9999
               99999999 / 999999999 (8 ou 9 digitos direto)
               Além de digito, se tiver a letra O para se passar por numero, também remove.
            */
            return $ret;
         }

         
         //Remove emails de uma string
         function removeEmail( $text, $replacement = '********' ){

            $pattern = '/([a-zA-Z0-9_\-\.\!\?\"]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)/i';
            $ret     = preg_replace( $pattern, $replacement, $text );

            return $ret;
         }
         

         //Remove urls dos links
         function removeURL( $text, $replacement = '********', $deep = true ){

            //Regex basica para remover apenas urls bem formatadas (com http e tudo mais). Mais simples e mais rápida.
            $pattern = '/(https?):\/\/[-A-Z0-9+&@#\/%?=~_|$!:,.;]*[A-Z0-9+&@#\/%=~_|$]/i';
            $text    = preg_replace( $pattern, $replacement, $text );

            //Faz uma filtragem mais profunda. É mais lento mas consegue remover urls mal formatadas (sem http, sem www, etc)
            if( $deep ){
               $pattern = "/\b(AC($|\/)|\.AD($|\/)|\.AE($|\/)|\.AERO($|\/)|\.AF($|\/)|\.AG($|\/)|\.AI($|\/)|\.AL($|\/)|\.AM($|\/)|\.AN($|\/)|\.AO($|\/)|\.AQ($|\/)|\.AR($|\/)|\.ARPA($|\/)|\.AS($|\/)|\.ASIA($|\/)|\.AT($|\/)|\.AU($|\/)|\.AW($|\/)|\.AX($|\/)|\.AZ($|\/)|\.BA($|\/)|\.BB($|\/)|\.BD($|\/)|\.BE($|\/)|\.BF($|\/)|\.BG($|\/)|\.BH($|\/)|\.BI($|\/)|\.BIZ($|\/)|\.BJ($|\/)|\.BM($|\/)|\.BN($|\/)|\.BO($|\/)|\.BR($|\/)|\.BS($|\/)|\.BT($|\/)|\.BV($|\/)|\.BW($|\/)|\.BY($|\/)|\.BZ($|\/)|\.CA($|\/)|\.CAT($|\/)|\.CC($|\/)|\.CD($|\/)|\.CF($|\/)|\.CG($|\/)|\.CH($|\/)|\.CI($|\/)|\.CK($|\/)|\.CL($|\/)|\.CM($|\/)|\.CN($|\/)|\.CO($|\/)|\.COM($|\/)|\.COOP($|\/)|\.CR($|\/)|\.CU($|\/)|\.CV($|\/)|\.CX($|\/)|\.CY($|\/)|\.CZ($|\/)|\.DE($|\/)|\.DJ($|\/)|\.DK($|\/)|\.DM($|\/)|\.DO($|\/)|\.DZ($|\/)|\.EC($|\/)|\.EDU($|\/)|\.EE($|\/)|\.EG($|\/)|\.ER($|\/)|\.ES($|\/)|\.ET($|\/)|\.EU($|\/)|\.FI($|\/)|\.FJ($|\/)|\.FK($|\/)|\.FM($|\/)|\.FO($|\/)|\.FR($|\/)|\.GA($|\/)|\.GB($|\/)|\.GD($|\/)|\.GE($|\/)|\.GF($|\/)|\.GG($|\/)|\.GH($|\/)|\.GI($|\/)|\.GL($|\/)|\.GM($|\/)|\.GN($|\/)|\.GOV($|\/)|\.GP($|\/)|\.GQ($|\/)|\.GR($|\/)|\.GS($|\/)|\.GT($|\/)|\.GU($|\/)|\.GW($|\/)|\.GY($|\/)|\.HK($|\/)|\.HM($|\/)|\.HN($|\/)|\.HR($|\/)|\.HT($|\/)|\.HU($|\/)|\.ID($|\/)|\.IE($|\/)|\.IL($|\/)|\.IM($|\/)|\.IN($|\/)|\.INFO($|\/)|\.INT($|\/)|\.IO($|\/)|\.IQ($|\/)|\.IR($|\/)|\.IS($|\/)|\.IT($|\/)|\.JE($|\/)|\.JM($|\/)|\.JO($|\/)|\.JOBS($|\/)|\.JP($|\/)|\.KE($|\/)|\.KG($|\/)|\.KH($|\/)|\.KI($|\/)|\.KM($|\/)|\.KN($|\/)|\.KP($|\/)|\.KR($|\/)|\.KW($|\/)|\.KY($|\/)|\.KZ($|\/)|\.LA($|\/)|\.LB($|\/)|\.LC($|\/)|\.LI($|\/)|\.LK($|\/)|\.LR($|\/)|\.LS($|\/)|\.LT($|\/)|\.LU($|\/)|\.LV($|\/)|\.LY($|\/)|\.MA($|\/)|\.MC($|\/)|\.MD($|\/)|\.ME($|\/)|\.MG($|\/)|\.MH($|\/)|\.MIL($|\/)|\.MK($|\/)|\.ML($|\/)|\.MM($|\/)|\.MN($|\/)|\.MO($|\/)|\.MOBI($|\/)|\.MP($|\/)|\.MQ($|\/)|\.MR($|\/)|\.MS($|\/)|\.MT($|\/)|\.MU($|\/)|\.MUSEUM($|\/)|\.MV($|\/)|\.MW($|\/)|\.MX($|\/)|\.MY($|\/)|\.MZ($|\/)|\.NA($|\/)|\.NAME($|\/)|\.NC($|\/)|\.NE($|\/)|\.NET($|\/)|\.NF($|\/)|\.NG($|\/)|\.NI($|\/)|\.NL($|\/)|\.NO($|\/)|\.NP($|\/)|\.NR($|\/)|\.NU($|\/)|\.NZ($|\/)|\.OM($|\/)|\.ORG($|\/)|\.PA($|\/)|\.PE($|\/)|\.PF($|\/)|\.PG($|\/)|\.PH($|\/)|\.PK($|\/)|\.PL($|\/)|\.PM($|\/)|\.PN($|\/)|\.PR($|\/)|\.PRO($|\/)|\.PS($|\/)|\.PT($|\/)|\.PW($|\/)|\.PY($|\/)|\.QA($|\/)|\.RE($|\/)|\.RO($|\/)|\.RS($|\/)|\.RU($|\/)|\.RW($|\/)|\.SA($|\/)|\.SB($|\/)|\.SC($|\/)|\.SD($|\/)|\.SE($|\/)|\.SG($|\/)|\.SH($|\/)|\.SI($|\/)|\.SJ($|\/)|\.SK($|\/)|\.SL($|\/)|\.SM($|\/)|\.SN($|\/)|\.SO($|\/)|\.SR($|\/)|\.ST($|\/)|\.SU($|\/)|\.SV($|\/)|\.SY($|\/)|\.SZ($|\/)|\.TC($|\/)|\.TD($|\/)|\.TEL($|\/)|\.TF($|\/)|\.TG($|\/)|\.TH($|\/)|\.TJ($|\/)|\.TK($|\/)|\.TL($|\/)|\.TM($|\/)|\.TN($|\/)|\.TO($|\/)|\.TP($|\/)|\.TR($|\/)|\.TRAVEL($|\/)|\.TT($|\/)|\.TV($|\/)|\.TW($|\/)|\.TZ($|\/)|\.UA($|\/)|\.UG($|\/)|\.UK($|\/)|\.US($|\/)|\.UY($|\/)|\.UZ($|\/)|\.VA($|\/)|\.VC($|\/)|\.VE($|\/)|\.VG($|\/)|\.VI($|\/)|\.VN($|\/)|\.VU($|\/)|\.WF($|\/)|\.WS($|\/)|\.XN--0ZWM56D($|\/)|\.XN--11B5BS3A9AJ6G($|\/)|\.XN--80AKHBYKNJ4F($|\/)|\.XN--9T4B11YI5A($|\/)|\.XN--DEBA0AD($|\/)|\.XN--G6W251D($|\/)|\.XN--HGBK6AJ7F53BBA($|\/)|\.XN--HLCJ6AYA9ESC7A($|\/)|\.XN--JXALPDLP($|\/)|\.XN--KGBECHTV($|\/)|\.XN--ZCKZAH($|\/)|\.YE($|\/)|\.YT($|\/)|\.YU($|\/)|\.ZA($|\/)|\.ZM($|\/)|\.ZW|\w*\.GOV|\w*\.ORG|\w*\.COM\.BR|\w*\.COM)\b/i";
               $pieces  = explode(' ', $text);
   
               foreach( $pieces as $index => $piece ){
   
                  if( strstr( $piece, "." ) ){
                     if( preg_match( $pattern,  $piece ) === 1){
                        $pieces[$index] = $replacement;
                     }      
                  }
               }
   
               return implode(' ',$pieces);
            }

         }
			
			// Url Amigaveis
			function getFriendlyUrl($str){
            
            $encode = mb_detect_encoding($str.'x', 'UTF-8, ISO-8859-1');
            if( $encode != 'UTF-8' ) $str = mb_convert_encoding( $str, 'UTF-8', $encode );
            
				$str = trim(mb_strtolower($str,'UTF-8'));
				$char 	= array(" ","ç","á","é","í","ó","ú","ä","ë","ï","ö","ü","à","è","ì","ò","ù","â","ê","î","ô","û","ã","õ");
				$replace = array("-","c","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","o");
            $regex   = '/[^\d^\w^-]/';
				
            $ret = str_replace($char,$replace, $str);
            $ret = preg_replace($regex, '', $ret );

            while( strpos($ret, '--') !== false ) $ret = str_replace( '--', '-', $ret );
            
            return $ret;
			}
			
			
			
			//Ajusta o valor para facilitar na comparação
			function clean( $str ){
				
				$encode = mb_detect_encoding($str.'x', 'UTF-8, ISO-8859-1');
				if( $encode != 'UTF-8' ) $str = mb_convert_encoding( $str, 'UTF-8', $encode );
				
				//return strtolower( $str );
				$str     = trim(mb_strtolower($str, 'UTF-8'));
				$char 	= array("ç","á","é","í","ó","ú","ä","ë","ï","ö","ü","à","è","ì","ò","ù","â","ê","î","ô","û","ã","õ","!","@","#","$","%","^","&","*","(",")","_","=","+","[","]","{","}","\\","|","/","?","<",">",".",",","\"","'",'-',':');
				$replace = array("c","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","e","i","o","u","a","o", "", "", "", "", "", "", "", "", "", "","_", "", "", "", "", "", "",  "", "", "", "", "", "", "", "",  "", "",'','');

				return str_replace($char,$replace, $str);
				
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
