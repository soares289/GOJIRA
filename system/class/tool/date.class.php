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
		
			
			//Converte o formato da data brasileira (dd/mm/yyyy) para o mysql (yyyymmdd)
			function dateTosql( $date, $ltime = false ){
				
				if( empty( $date ) ){
					return 'NULL';
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
         
         
         //Converte o formato da data do mysql (yyyymmdd) para o formato brasileiro (dd/mm/yyyy)
			function sqlTodate( $date, $ltime = false ){
				
				if( empty( $date ) ) return;
            
				$ret  = substr($date,  6, 2) . '/' . substr($date, 4, 2)  . '/' . substr($date, 0, 4);
            
				if( $ltime ){
               $ret .= ' ' . substr($date, 8, 2) . ':' . substr($date,10, 2);
				}
	
				return $ret;
         }
         


         //Converte uma data (padrão yyyy-mm-dd ou dd-mm-yyyy para o formato usado nos inputs do html)
         function dateToHtml( $date, $ltime = false ){
         
            $separator = '-';

            if( empty( $date ) ) return '';
            if( strpos( $date, '/') > 0 ) $separator = '/';

            $split = explode( $separator, $date );

            //Padrão internacional de data yyyy-mm-dd hh:ii:ss
            if( strlen( $split[0] ) > 2 ){
               $ret = substr($date, 0, 4) . '-' . substr($date, 5, 2) . '-' . substr($date, 8, 2);

            //Padrão brasileiro dd-mm-yyyy hh:ii:ss
            } else {
               $ret = substr($date, 6, 4) . '-' . substr($date, 3, 2) . '-' . substr($date, 0, 2);

            }

            if( $ltime ){
               $ret .=  'T' . substr( $date, 11, 5);
            }

            return $ret;

         }

         
         //Converte uma data no formato dos inputs do html para o padrão brasileiro (dd-mm-yyyy hh:ii:ss, formato usado no saveRow do framework)
         function htmlToDate( $date, $ltime = false ){
         
            if( empty( $date ) ) return 'NULL';

            $separator = '-';
            if( strpos( $date, '/') > 0 ) $separator = '/';

            $ret = substr($date, 8, 2) . $separator . substr($date, 5, 2) . $separator . substr($date, 0, 4);

            if( $ltime ) $ret .=  ' ' . substr( $date, 11, 5) . ':00';

            return $ret;

         }
		

         
         //Retorna uma string com o tempo decorrido (ex: 4 minutos)
         function timeElapsed($sec){
            
            $ret = 'Agora mesmo';

            if( $sec > 1 ){
               
               $date = [    'ano' => 31536000,
                            'mes' =>  2592000,
                            'dia' =>    86400,
                           'hora' =>     3600,
                         'minuto' =>       60,
                        'segundo' =>        1];
         
               foreach( $date as $name => $time ){
                  $qtde = $sec / $time;

                  if( $qtde >= 1 ) {
                     $ret = 'há ' . ($name == 'ano' ? 'mais de ' : '') . floor( $qtde ) . ' ' . $name;
                     if( $qtde >= 2 ){
                        $ret .= ($name == 'mes' ? 'es' : 's');
                     }
                     break;
                  }
               }
            }


            return $ret;
         }

		}
