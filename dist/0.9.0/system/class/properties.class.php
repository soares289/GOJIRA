<?php
/*	Properties.class.php - Extende as funcionalidades de um
	objeto para que possa trabalhar com as propriedades de
	forma mais segura. usando emcapsulamento.
	
	por Carlson - 2011-02-17 08:59

   Obs: Não é mais usada por padrão - Mantida por referencia e para
        o caso de algum lib de terceiro desejar usar as funcionálidades.
*/


	abstract class Properties
	{
		
		
		//Get - Busca uma propriedade do objeto
		public function __get($name) {
			if (method_exists($this, ($method = 'get_'.$name))) {
				return $this->$method();;
			}
			else return;
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
