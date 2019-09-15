<?php
/*
	Tools - HTML
		Extensão do tools com funções referentes a rede.
		Requisições a servidores externos, envio de emails, etc.
*/
      

      //Classe de email PHPMailer usada pelo tools/network.class.php
      require_once( __DIR__ . '/../../vendor/PHPMailer-6.0.7/src/PHPMailer.php' );
      require_once( __DIR__ . '/../../vendor/PHPMailer-6.0.7/src/SMTP.php' );
      require_once( __DIR__ . '/../../vendor/PHPMailer-6.0.7/src/Exception.php' );
      use PHPMailer\PHPMailer\PHPMailer;
      use PHPMailer\PHPMailer\SMTP;
      use PHPMailer\PHPMailer\Exception;
   
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
		      if ( ($protocol == 'http'  && $_SERVER["SERVER_PORT"] != "80") ||
                 ($pageURL == 'https' && $_SERVER["SERVER_PORT"] != "443") ){
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
			function sendMail( $dest, $subject, $msg, $config ){
            
            /*if( !class_exists('PHPMailer', false) ){
               throw(new Exception('PHPMailer not found'));
               exit;
            }*/

            if( !isset( $config['from']) ){
               throw(new Exception('From is required in config array'));
               exit;
            }

            if( !isset( $config['host'] ) )   $config['host'] = 'localhost';
            if( !isset( $config['port'] ) )   $config['port'] = '587';
            if( !isset( $config['secure'] ) ) $config['secure'] = 'tls';
            if( !isset( $config['name'] ) )   $config['name'] = substr( $from, 0, strpos($from, '@'));

				$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch

				$mail->IsSMTP(); // telling the class to use SMTP

            $name = $config['name'];
            
            if( !isset( $config['user'] ) ){
               $mail->SMTPAuth   = false;                               // disable SMTP authentication
               
            } else {
               $mail->SMTPAuth   = true;                                // enable SMTP authentication
               $mail->Username   = $config['user'];                     // GMAIL username
					$mail->Password   = $config['password'];                 // GMAIL password
               
            }
            
            //Adiciona os destinatarios
            if( is_array( $dest ) ){
               foreach( $dest as $a ) $mail->addAddress( $a );
            } else {
               $mail->addAddress($dest);
            }


            //Adiciona Copia
            if( isset( $config['cc'] ) ){
               if( is_array( $config['cc'] ) ){
                  foreach( $config['cc'] as $a ) $mail->addCC( $a );
               } else {
                  $mail->addCC( $config['cc'] );
               }
            }

            //Adiciona Copia oculta
            if( isset( $config['bcc'] ) ){
               if( is_array( $config['bcc'] ) ){
                  foreach( $config['bcc'] as $a ) $mail->addBCC( $a );
               } else {
                  $mail->addBCC( $config['bcc'] );
               }
            }

				try {

               //Configura o smtp
					$mail->SMTPDebug  = 0;                                   // enables SMTP debug information (for testing)
               $mail->SMTPSecure = $config['secure'];  //"ssl";//"tls";             // sets the prefix to the servier
					$mail->Host       = $config['host'];                               // sets GMAIL as the SMTP server
					$mail->Port       = $config['port'];                               // set the SMTP port for the GMAIL server
					$mail->CharSet    = 'UTF-8';
               $mail->setFrom($config['from'], $config['name']);
               $mail->addReplyTo($config['from'], $config['name']);

					$mail->Subject = $subject;
					$mail->MsgHTML($msg);
					$mail->Send();

				} catch (Exception $e) {
					throw $e;
				}
            
         }


         
         
         //Usa o php para enviar um email
         function sendMailPHP( $dest, $sub, $msg, $from ){
      	
				if(PATH_SEPARATOR == ";"){
					$quebra_linha = "\r\n"; //Se for Windows
				}else{ 
					$quebra_linha = "\n"; //Se "nÃ£o for Windows"
				}


				$emailsender = $from;
				$headers = "MIME-Version: 1.1" .$quebra_linha;
				$headers .= "Content-type: text/html; charset=utf-8" .$quebra_linha;
				
				$headers .= "From: " . $emailsender.$quebra_linha;
				$headers .= "Reply-To: " . $emailsender . $quebra_linha;
				
            $sub = "=?UTF-8?B?".base64_encode($sub)."?=";
            
				if(!mail($dest, $sub, $msg, $headers ,"-r".$emailsender)){ // Se for Postfix
               $headers .= "Return-Path: " . $emailsender . $quebra_linha; // Se "não for Postfix"
               mail($dest, $sub, $msg, $headers, $headers );
				}
            
         }
         
			
			//Busca o conteudo de uma url usando curl
			function curlGet( $url, $sendPost = '', $outFile = '', $ref = '' ){
				
				
				$ch   = curl_init();
				
				curl_setopt ($ch, CURLOPT_URL, $url);
				curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
				
				//Tempo limite de conexão e total
				curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
				curl_setopt ($ch, CURLOPT_TIMEOUT, 20);
				
				//Seguir redirects
				@curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, true);
				
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
            
            //Download de arquivos, não utilizar para arquivos muito grande
            if( !empty( $outFile ) ){
               $fp = fopen( $outFile, 'w+');
               curl_setopt($ch, CURLOPT_FILE, $fp);
            }
            //Para arquivos grandes, ver doc ref: curl_setopt($ch, CURLOPT_WRITEFUNCTION, "curl_callback");

				$ret = curl_exec($ch);
				curl_close($ch);
				
				return $ret;
			}
			
		}
      