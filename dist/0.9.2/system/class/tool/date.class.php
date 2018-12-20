<?php
/*
	Tools - HTML
		Extensão do tools com funções referente a datas.
		Conversões, calculos e etc.
*/

		class Tool_Date{
			
			
   		//Retorna o dia da semana, versáo compelta ou abreviada
			function nToWeek( $n, $lAbre = false ){
				
				$ret = '';
				
				switch( $n ){
					case 1: $ret = ($lAbre ? 'Dom' : 'Domingo'      ); break;
					case 2: $ret = ($lAbre ? 'Seg' : 'Segunda Feira'); break;
					case 3: $ret = ($lAbre ? 'Ter' : 'Terça Feira'  ); break;
					case 4: $ret = ($lAbre ? 'Qua' : 'Quarta Feira' ); break;
					case 5: $ret = ($lAbre ? 'Qui' : 'Quinta Feira' ); break;
					case 6: $ret = ($lAbre ? 'Sex' : 'Sexta Feira'  ); break;
					case 7: $ret = ($lAbre ? 'Sab' : 'Sabado'       ); break; 
				}
				
				return $ret;
			}
			
			
			//Retorna o nome do mes, opçao de nome completo e abreviado
			function nToMonth( $n, $lAbre = false ){
				
				$ret = '';
				
				switch( $n ){
					case  1: $ret = ($lAbre ? 'Jan' : 'Janeiro'   ); break;
					case  2: $ret = ($lAbre ? 'Fev' : 'Fevereiro' ); break;
					case  3: $ret = ($lAbre ? 'Mar' : 'Março'     ); break;
					case  4: $ret = ($lAbre ? 'Abr' : 'Abril'     ); break;
					case  5: $ret = ($lAbre ? 'Mai' : 'Maio'      ); break;
					case  6: $ret = ($lAbre ? 'Jun' : 'Junho'     ); break;
					case  7: $ret = ($lAbre ? 'Jul' : 'Julho'     ); break;
					case  8: $ret = ($lAbre ? 'Ago' : 'Agosto'    ); break;
					case  9: $ret = ($lAbre ? 'Set' : 'Setembro'  ); break;
					case 10: $ret = ($lAbre ? 'Out' : 'Outubro'   ); break;
					case 11: $ret = ($lAbre ? 'Nov' : 'Novembro'  ); break;
					case 12: $ret = ($lAbre ? 'Dez' : 'Dezembro'  ); break;
				}
				
				return $ret;
			}
		
			
			//Converte o formato da data para o mysql
			function dateTosql( $date, $ltime = false ){
				
				if( empty( $date ) ){
					return 'null';
				}
				
				$ret  = substr($date,  6, 4) . substr($date, 3, 2) . substr($date, 0, 2);
				$ret .= substr($date, 11, 2) . substr($date,14, 2) . substr($date,17, 2);
				
				if( $ltime ){
					$ret  = str_pad( trim( $ret ), 14, '0', STR_PAD_RIGHT );
				} else {
					$ret  = str_pad( trim( $ret ), 8, '0', STR_PAD_RIGHT );
				}
	
				return $ret;
			}
         
         
         //Converte o formato da data para o mysql
			function sqlTodate( $date, $ltime = false ){
				
				if( empty( $date ) ){
					return '00/00/0000' . ($ltime ? ' 00:00' : '');
				}
            
				$ret  = substr($date,  6, 2) . '/' . substr($date, 4, 2)  . '/' . substr($date, 0, 4);
            
				if( $ltime ){
               $ret .= ' ' . substr($date, 8, 2) . ':' . substr($date,10, 2);
				}
	
				return $ret;
			}
		
		}
