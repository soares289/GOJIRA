<?php
   
   $query = $_GET['query'] ?? '';
   
   foreach( $routes ?? [] as $route ){

      //Normalizar a entrada
      $route['type_in']  = $route['type_in']  ?? 'regex';
      $route['type_out'] = $route['type_out'] ?? 'exactly';

      //Verifica se a condição foi atendida
      $match =  ($route['type_in'] == 'exactly' && $query == $route['match']) ||
                ($route['type_in'] == 'regex'   && preg_match( $route['match'], $query));

      if( $match ){
         //Retorno exato - Só substituiu o query
         if( $route['type_out'] == 'exactly' ){
            $query = $route['replace'];

         //Retorno com regex, usa regex pra substituir
         } elseif( $route['type_out'] == 'regex'){
            $query = preg_replace( $route['match'], $route['replace'], $query );

         }

         //Só considera o primeiro acerto
         break;
      }

   }
   
   $_GET['query'] = $query;