<?php
/* DEPRECADO - Na lista de remoções para versões futuras
      ~~ Carlsom A. - 2017-03-21
*/

		abstract class Customization implements IteratorAggregate, Countable, ArrayAccess{

			protected $collection_data = [];
			protected $structure;


			//Para podeer usar no FOREACH
			function getIterator() : Traversable{	return new ArrayIterator( $this->collection_data ); }

			//Para ter opção de serializar e dessserializar a data
         public function __serialize(): array { return $this->collection_data; }
         public function __unserialize(array $data): void { $this->collection_data = $data; }

			//Para poder usar o count do array
			public function count() :int{ return count( $this->collection_data ); }

			//Para poder usar o objeto como array
			public function offsetSet( $index, $value): void {

		   	if (is_null($index)) {
		   		$this->collection_data[] = $this->defField( $value, null );
		   	} else {
					if( isset( $this->collection_data[$index] ) ){

			    		$this->collection_data[$index]->value = $value;
					} else {
						$this->collection_data[$index] = $this->defField( $value, $index );
					}
		   	}

		   }

			//Para verificar se existe o campo no array( com o isset );
		   public function offsetExists( $index ): bool{ return isset($this->collection_data[$index]); }

			//Remover campos com o unset
		   public function offsetUnset($index): void { unset( $this->collection_data[$index] ); }

			//buscar valores dentro do objeto
		   public function offsetGet($index): mixed    {
				if( !isset($this->collection_data[$index]) ){
					$this->collection_data[$index] = $this->defField( $index, null );
				}
				$val = &$this->collection_data[$index];
				return $val;
			}

			//Buscar valor do array como sendo propriedade
			function __get( $index ){

				if( isset( $this->$index ) ) return $this->$index;
				if( isset( $this->collection_data[ $index ] ) ){

					return $this->collection_data[ $index ];

				} else {
					return null;
				}

			}


			//Setar um novo campo no array como se fosse uma propriedade
			function __set( $index, $value ){

				if( isset( $this->$index ) && $index == 'structure' ){
					$this->structure = &$value;
				} elseif( isset( $this->collection_data[ $index ] ) ){
					$this->collection_data[ $index ]->value = $value;
				} else {
					$this->collection_data[ $index ] = $this->defField( $value, $index );
				}

			}


			abstract protected function defField( $value, $index );
			//abstract protected function refactor();

			//TODO - abstract public reset()
			//TODO - abstract public undo()
			//TODO - abstract public default()


		}


		class CustomData{

			protected $rows = [];
         
         public function __serialize(): array { return $this->rows; }
         public function __unserialize(array $data): void { $this->rows = $data; }

			function __construct( $structure ){

				$this->structure       = &$structure;
				$this->collection_data = &$this->rows;

			}

			protected function defField( $value, $index ){

				$row = new CustomRow( $this->structure );
				return $row;
			}


			//TODO - Seek( $aPk )
			//TODO - Filter( $aPk )
			//TODO - Search( $field, $term )

		}



		//linha de registros customizada
		class CustomRow extends Customization{

			private $fields = array();

			function __construct( &$structure ){

				$this->structure       = &$structure;
				$this->collection_data = &$this->fields;

			}

			protected function defField( $value, $index ){

				if( isset( $this->structure[ $index ] ) ){
					$field = new CustomField( $value, $this->structure[ $index ] );
				} else {
					$a = array();
					$field = new CustomField( $value, $a );
				}
				return $field;
			}
		}


		//Campo customizado
		class CustomField{

			private $m_value;
			private $m_last    = null;
			private $m_reset   = null;
			private $m_default = null;
			private $m_format  = null;

			function __construct( $value, &$format ){

				$this->m_format  = &$format;
				$this->m_value   = $value;
				$this->m_last    = $value;
				$this->m_reset   = $value;
				$this->m_default = null;

				if( isset( $format->default_value ) ) $this->m_default = $format->default_value;

			}

			//Seta os valor nas propriedades privadas do objeto
			function __set( $field, $value ){

				if( $field == 'value' ){

					$this->m_last = $this->m_value;
					if( is_null( $this->m_reset ) ) $this->m_reset = $this->m_value;
					$this->m_value = $value;

				} else {
					throw( new Exception( 'Property <strong>' . $field . '</strong> not found!' ) );
				}

			}

			//Busca as propriedades privadas do objeto
			function __get( $field ){

				if( $field == 'value'  ) return $this->m_value;
				if( $field == 'format' ) return $this->m_format;

				return null;

			}

			//Retorna o campo ao valor anterior
			function undo(){
				if( ! is_null( $this->m_last ) ) $this->m_value = $this->m_last;
			}

			//Retorna o campo ao valor inicial
			function reset(){
				if( ! is_null( $this->m_reset ) ) $this->m_value = $this->m_reset;
			}

			//Seta o valor padrao no campo
			function setDefault(){
				if( ! is_null( $this->m_default ) ) $this->m_value = $this->m_default;
			}


			//Busca os valores adicionais do campo
			public function getDefaultValue()  { return $this->m_default;	}
			public function getLastValue()     { return $this->m_last;	}
			public function getOriginalValue() { return $this->m_reset; }


			//Retorno do objeto quando usado como string
			function __toString(){
				return $this->m_value . '';
			}

		}
