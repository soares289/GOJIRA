<?php
/* content.class.php - Classe responsavel por gerenciar conteudos
   multi-linguagem para o projeto.
   
   por Carlson A. Soares - 2013/01/23
*/
   
   class Content extends GojiraCore{
   
      private $conn;             //Classe de conexão
      private $tools;            //Classe com ferramentas necessarias para o funcionamento da classe
         
      /***   Construtores ***/
      function __construct(){
         
         $a = func_get_args();
         $i = func_num_args();
         
         if (method_exists($this,$f='__construct'.$i)) {
            call_user_func_array(array($this,$f),$a); 
         } else {
            throw( new Exception('Numero de parametros invalido') );
         }
      }
          
      function __construct3( $language, $conn, $tools ) {
      
         $this->language = $language;
         $this->conn     = $conn;
         $this->tools    = $tools;
         
      } 
      
      
      function get( $key, $sub = '' ){
         //TODO - Buscar conteudo
         //TODO - Criar caso não exista
         //TODO - Rever nome
      }
      
   }
