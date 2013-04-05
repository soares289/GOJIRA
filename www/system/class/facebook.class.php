<?php
/* Facebook.class.php - por Carlson A. Soares - 2013-03-27 15:17

   Metodos para facilicar a integração com o facebook
*/
   
   //TODO - Verificar possibilidade de extender a API principal do facebook

//Constantes de Erro

   
   //SDK para PHP do facebook
   require_once( dirname(__FILE__) . '/facebook/facebook.php' );
   
   
   class Facebook extends Properties{
      
      //Dados do aplicativo
      private $appId;
      private $appSecret;
      
      //Objeto do SDK do Facebook
      private $fb;
      
      
      //Informações buscadas
      private $token;
      private $user;
      
      
//__________________________________________________________________
//SETs & GETs
//------------------------------------------------------------------
      
      
      //Token de acesso
      function get_token(){ return $this->token; }
      
      //ID do usuário
      function get_user(){ return $this->user; }
      
      
      
      
//__________________________________________________________________
      
      
      //Construtor padrão, requer o AppID e AppSecret da aplicação
      function __construct( $appId, $appSecret ){
      
         if( empty( $appId ) ) throw(new Exception());
      
         $this->appId     = $appId;
         $this->appSecret = $appSecret;
         
         $this->initSDK();
      }
      
    
   
//__________________________________________________________________
      
    
      //Inicia o sdk do facebook
      private init_sdk(){
      
         $config   = array( 'appId'  => $this->appId,
                            'secret' => $this->appSecret );
                            
         $this->fb = new Facebook( $config );
      }
      
      
      
      
   }