<?php

define('LOGIN_INVALID_USER', '0|Invalid user name or password!');
define('LOGIN_INVALID_PWD' , '0|Invalid user name or password!');


   	class Login_Controller extends Controller{
			
			function index( $param ){
				
				if( isset( $param['pwd'] ) ){
               echo $this->globals->login->toPassword( $param['pwd'] );
					return $this->globals->login->toPassword( $param['pwd'] );
				}
				
			}
			
			
			//Executa o login
			function login( $param ){
				
				$usr = $this->globals->tools->antiInjection( $param['login'] );
				$pwd = $this->globals->tools->antiInjection( $param['pwd'] );
				$ret = array('success' => false, 'msg' => '');
				
				if( strlen( $usr ) < 3 ){
               $ret['msg'] = LOGIN_INVALID_USER;
            } elseif( strlen( $pwd ) < 6 ){
               $ret['msg'] = LOGIN_INVALID_PWD;
            } else {
               
               try{
                  
                  $usr  = $this->globals->login->login( $usr, $pwd, $this->globals->environment->accessLevel );
                  $this->globals->environment->partner = $_SESSION['partner'] = $this->model->getPartner( $usr );	
                  $ret['success'] = true;
                  
               } catch( Exception $e ){
                  $ret['msg'] = $e->getMessage();
               }
            }
            
				return json_encode( $ret );
				
			}
			
			
         //Logoff do sistema
			function logoff( $param ){
				
				$this->globals->login->logoff( $this->globals->environment->accessLevel );
				return json_encode(array('success' => true, 'msg' => ''));
				
			}
			
      }
		
?>