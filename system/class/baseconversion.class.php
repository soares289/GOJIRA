<?php
/*
	Conversão numerica de qualquer base para qualquer base
	Ex: Decinal para Octal, Decimal para Base 44, etc
	
	por: Carlson A. Soares - 2013-01-18
*/
	class BaseConversion{
	
		//retorna o array de caracteres usado na base
		function get_base( $n ){
			
			$a = array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',	//DECIMAL
						'A', 'B', 'C', 'D', 'E', 'F',						//HEXA
						'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'X', 'Y', 'Z', 'W',		//BASE USANDO TODO ALFABETO
						'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'x', 'y', 'z', 'w'); 	//BASE COM ALFABETO MINUSCULO
			
			if( is_string( $n ) ) $n = strtoupper( $n );
			
			switch( $n ){
				case 2:
				case 'BINARY':
				case 'BIN':
					$n = 2; break;
				
				case 8:
				case 'OCT':
				case 'OCTAL':
					$n = 8; break;
				
				case 10:
				case 'DEC':
				case 'DECIMAL':
					$n = 10; break;
				
				case 16:
				case 'HEX':
				case 'HEXAL':
					$n = 16; break;
				
				case 'MAX':
					$n = count( $a );
					
				default:
					$n = abs( $n );
			}
			
			if( $n < count( $a ) ){
				$ret = array_slice( $a, 0, $n );
			} else {
				$ret = $a;
			}

			return $ret;
		
		}
		
		//Converte de qualquer base para qualquer base
		function base_to_base( $num, $from_base, $to_base ){
		
			if( ! is_array( $from_base ) ) $from_base = $this->get_base( $from_base );
			if( ! is_array( $to_base ) ) $to_base = $this->get_base( $to_base );
			
			//Converte a base de entrada para decimal
			$base = 0;
			for( $c = 1; $c <= strlen( $num ); $c++ ){
				$pos = array_search( substr( $num, -$c, 1), $from_base);
				$base += pow( count( $from_base ), $c - 1 ) * $pos;
			}
			
			//Converte para a base de destino
			$ret = '';
			while( $base > 0 ){
				$pos  = $base % count( $to_base );
				$base = floor( $base / count( $to_base ));
				$ret  = $to_base[ $pos ] . $ret;
			}
			
			return $ret;
		}
		
	}
   
   