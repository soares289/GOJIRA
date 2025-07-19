<?php
/*
   routes.php - por Carlsom A. Soares - 2023-08-25

   Esquema de routes implementado em caráter de testes. 
   Objetivos - Ser simples de usar
               Simplificar a definição de rotas no sistema
               Não ser obrigatório o uso
               Ser leve
   
   Em caso de uma regra não se aplicar aqui, segue o fluxo padrão 
   do sistema de /class/proc/arg$n-name/arg$n-value/).

   Aos poucos vou aprimorando isso

   FORMATO DO ROUTES:
   - Deve ser um array, cada item é um outro array com a seguinte estrutura:
   
   match    => STR | Query de entrada
   type_in  => STR | Como a query de entrada vai ser lida. Pode ter os valores:
                     exactly => Comparação direta, case sensitive
                     regex   => Será tratado com uma regex usando preg_match, para identificar e extrair partes
   replace  => STR | Query se saída
   type_out => STR | Como a query de saída será tratada
                     exactly => Exatamente como foi digitado
                     regex   => Será tratada como regex usando preg_replace, e usará a entrada como base

   EXEMPLOS:
      Identificando url exata. Sempre que for acessado a url /contato/, internamente vai para page/contact/
      url acessada: [BASE]/contato/
      ['match' => '/contato/', 'type_in' => 'exactly', 'replace' => 'page/contact/', 'type_out' => 'exactly']

      Identificando urls dinamicamente. Pega o permalink do projeto e repassa para o controller responsável
      url acessada: [BASE]/projeto/casa-inteligente/
      ['match' => '/projeto[s]?\/([\w\-]+)[\/]?/i', 'type_in' => 'regex',
                   'replace' => 'project/detail/permalink/$1/', 'type_out' => 'regex']

      Identificando urls dinamicamente mas enviando sempre para um link fixo.
      No caso, pode ser acessado /projeto /projetos /projetos /projetos/ (case insensitive) e vai sempre para /project/view/
      url acessada: [BASE]/projeto/casa-inteligente/
      ['match' => '/projeto[s]?[\/]?/i' , 'replace' => 'project/view/', 'type_in' => 'regex', 'type_out' => 'exactly'],

   OBS:
      - type_out => regex só funciona se o type_in for regex também, pois o regex usado na entrada é base para o replace de saída.
      - type_in => regex pode ser usado para identificar uma URL fixa, porem de forma case-insensitive (ex: 'match' => '/contato/i')
      - Considera apenas a primeira regra. Mesmo se tiver mais de uma regra que a URL acessada dê match, apenas a primeira é considerada
*/

function routes_apply( $routes, $query ){
   
   foreach( $routes as $route ){

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

   return $query;
}


//Se tiver sido definido durante a inicialização do sistema
if( isset( $routes ) ){
   $query = $_GET['query'] ?? '';
   $_GET['query'] = routes_apply( $routes, $query );
}