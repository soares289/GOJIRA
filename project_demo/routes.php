<?php
/*
   routes.php - por Carlsom A. Soares - 2023-08-25

   Esquema de routes implementado em fase de testes. 
   Objetivos - Ser simples de usar
               Simplificar a definição de rotas no sistema
               Não ser obrigatório o uso
               Ser leve
   
   Em caso de uma regra não se aplicar aqui, segue o fluxo padrão 
   do sistema de /class/proc/arg$n-name/arg$n-value/).

   Aos poucos vou aprimorando isso
*/

   //Refine as rotas custom do sistema
   $routes = [
      
      //Home depois do splash
      ['match' => '/home/', 'replace' => 'home/index', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Pagina inicial -> Splash Screen
      ['match' => ''      , 'replace' => 'home/splash', 'type_in' => 'exactly', 'type_out' => 'exactly'],

      //Projetos + Detalhes do projeto
      ['match' => '/projeto[s]?\/([\w\-]+)[\/]?/i' , 'replace' => 'project/detail/permalink/$1/', 'type_in' => 'regex', 'type_out' => 'regex'],
      ['match' => '/projetos[\/]?/i' , 'replace' => 'project/view/', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Sobre
      ['match' => '/sobre/i', 'replace' => 'home/about', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Galerias
      ['match' => '/galeria[s]?\/([\w\-]+)[\/]?/i' , 'replace' => 'project/gallery/permalink/$1/', 'type_in' => 'regex', 'type_out' => 'regex'],
      ['match' => '/galerias[\/]?/i' , 'replace' => 'project/galleries/', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Contato
      ['match' => '/contato/i', 'replace' => 'home/contact', 'type_in' => 'regex', 'type_out' => 'exactly'],

   ];






   require_once( $systemPath . 'routes.php' );