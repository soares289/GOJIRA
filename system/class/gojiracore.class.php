<?php
/*	GojiraCore.class.php - Extende as funcionalidades de um
	objeto para que possa trabalhar com as propriedades de
	forma mais segura. usando emcapsulamento, além de facilitar
   o uso do GLOBALS - BASEADO NO propert.class.php.

	por Carlson - 2016-07-12
*/


	abstract class GojiraCore{


      /***   Construtores ***/
      function __construct(){

         $args = func_get_args();
         $argn = func_num_args();

         if( $argn > 0 ){
            if (method_exists($this, $function = '__construct'.$argn)) {
               call_user_func_array(array($this,$function),$args);
            } else {
               throw( new Exception('Numero de parametros invalido') );
            }
         }
      }



      //Busca propriedades inexistentes dentro do globals
      function __get( $index ){

         //Funcionalidade original, para trabalhar com funções set/get
         if (method_exists($this, ($method = 'get_'.$index))) {
				return $this->$method();;

			} else {

            //Nova funcionálidade - Verifica se a propriedade existe
            //Se não existir, verifica se está dentro do globals
            //Se não tiver, retorna os erros padrões do PHP

            $stack = debug_backtrace();

            try{
               //Verifica se é uma propriedade private
               $rp = new ReflectionProperty(get_class($this),$index);
               if( $rp->isPrivate() ){
                  trigger_error('Uncaught Error: Cannot access private property ' . get_class($this) . '::' . $index . ' called from ' . $stack[0]['file'] . ' on line ' . $stack[0]['line'], E_USER_ERROR);

               //Se não for private, retorna a propriedade
               } else {
                  return $this->$index;
               }

            //Só chega aqui se a propriedade não existir
            }	catch( Exception $e ){

               //Se ela existe dentro do globals (E o globals existe), retorna
               if( isset( $this->globals ) && isset( $this->globals->$index ) ){
                  return $this->globals->$index;

               //Caso não exista a propriedade, dispara o erro padrão do PHP
               } else {
                  trigger_error('Undefined property ' . get_class($this) . '::' . $index . ' called from ' . $stack[0]['file'] . ' on line ' . $stack[0]['line']);

               }
            }
         }

         return null;
      }


		//Verifica se uma propriedade existe no objeto
		public function __isset($name) {
			if (method_exists($this, ($method = 'isset_'.$name))) {
				return $this->$method();
			}
			else return;
		}


		//Define um valor a uma propriedade
		public function __set($name, $value) {

			if (method_exists($this, ($method = 'set_'.$name))) {
				$this->$method($value);
			}
		}

		//Elimina uma propriedade
		public function __unset($name) {

			if (method_exists($this, ($method = 'unset_'.$name))) {
				$this->$method();
			}
		}


		//Retorna o caminho absoluto atual
		public function __absPath(){
			return substr( __FILE__, 0, strlen( __FILE__ ) - strlen( end( explode( "/", __FILE__ ) ) ) );
		}

	}
