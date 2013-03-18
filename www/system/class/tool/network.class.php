<?php
/*
	Tools - HTML
		Extensão do tools com funções referentes a rede.
		Requisições a servidores externos, envio de emails, etc.
*/
		
		@include_once("Mail.php");
		
		
		class Tool_Network{
			
			
			
			
			//Retorna a url da pagina atual
		   function curPageURL() {
		
            //Filtra o protocolo que está sendo usado
		      $protocol = 'http';
		      if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
		         $protocol .= "s";
		      }
            
            //Base da url
		      $pageURL = $protocol . "://" . $_SERVER["SERVER_NAME"];
            
            //Se o servidor estiver rodando em uma porta diferente
		      if ( ($pageURL == 'http://'  && $_SERVER["SERVER_PORT"] != "80") ||
                 ($pageURL == 'https://' && $_SERVER["SERVER_PORT"] != "443") ){
               
		         $pageURL .= ":".$_SERVER["SERVER_PORT"];
               
            }
            
		      //Em alguns casos, REQUEST_URI não existe
            if( isset( $_SERVER['REQUEST_URI'] ) ){
               $pageURL .= $_SERVER["REQUEST_URI"];
            } else {
               $pageURL .= $_SERVER["SCRIPT_NAME"].
                              ( isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
            }
            
		      return $pageURL;
		   }
			
			
			
			
			
			//Envia um email
			function sendMail( $dest, $sub, $msg ){
            
				$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

				$mail->IsSMTP(); // telling the class to use SMTP

				try {

					$mail->SMTPDebug  = 0;                     // enables SMTP debug information (for testing)
					$mail->SMTPAuth   = true;                  // enable SMTP authentication
					$mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
					$mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
					$mail->Port       = 465;                   // set the SMTP port for the GMAIL server
					$mail->Username   = "vendas@tectotal.com.br";  // GMAIL username
					$mail->Password   = "2008tec2012";            // GMAIL password
					//$mail->AddReplyTo($dest);
					$mail->AddAddress($dest);
					$mail->CharSet = 'UTF-8';
					$mail->SetFrom('marketing@tectotal.com.br', 'TecTotal Serviços');

					// $root_url = substr($this->globals->environment->site_url,0,strrpos($this->globals->environment->site_url,'/',-2)) . '/';

					$mail->Subject = $sub;
					$mail->MsgHTML($msg);
					$mail->Send();

				} catch (Exception $e) {
					throw $e;
				}
            
         }
         
         
         //Usa o php para enviar um email
         function sendMailPHP( $dest, $sub, $msg ){
      	
				if(PATH_SEPARATOR == ";"){
					$quebra_linha = "\r\n"; //Se for Windows
				}else{ 
					$quebra_linha = "\n"; //Se "nÃ£o for Windows"
				}


				$emailsender = "fiervo@casa36.com.br";
				$headers = "MIME-Version: 1.1" .$quebra_linha;
				$headers .= "Content-type: text/html; charset=utf-8" .$quebra_linha;
				
				$headers .= "From: " . $emailsender.$quebra_linha;
				$headers .= "Reply-To: " . $emailsender . $quebra_linha;
				
				if(!mail($dest, $sub, $msg, $headers ,"-r".$emailsender)){ // Se for Postfix
			    $headers .= "Return-Path: " . $emailsender . $quebra_linha; // Se "não for Postfix"
			    mail($dest, $sub, $msg, $headers, $headers );
				}
            
         }
         
			
			//Busca o conteudo de uma url usando curl
			function curl_get( $url, $sendPost = '', $ref = '' ){
				
				
				$ch   = curl_init();
				
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				
				//Tempo limite de conexão e total
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
				
				//Seguir redirects
				curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
				
				//Enganar o servidor, para que ele acredite que seja um usuário
			   curl_setopt($ch, CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
				
				//Para o caso de precisar passar informações do post atual
				if( is_array( $sendPost ) || !empty( $sendPost ) ){
					curl_setopt($ch, CURLOPT_POST, TRUE);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $sendPost);
				}
				
				//Se for para enviar um site como referer
				if( !empty( $ref ) ){
					curl_setopt($ch, CURLOPT_REFERER, $ref);
				}
				
				$ret = curl_exec($ch);
				curl_close($ch);
				
				return $ret;
			}
			
		}
      