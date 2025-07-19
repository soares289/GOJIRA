<?php

   //Refine as rotas custom do sistema - Mais infos em gojira/system/routes.php
   $routes = [
      
      //Home depois do splash
      ['match' => '/home/', 'replace' => 'home/index', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Pagina inicial -> Splash Screen
      ['match' => ''      , 'replace' => 'home/splash', 'type_in' => 'exactly', 'type_out' => 'exactly'],

      //Projetos + Detalhes do projeto
      ['match' => '/projeto[s]?\/([\w\-]+)[\/]?/i' , 'replace' => 'project/detail/permalink/$1/', 'type_in' => 'regex', 'type_out' => 'regex'],
      ['match' => '/projetos[\/]?/i' , 'replace' => 'project/view/', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Sobre
      ['match' => '/sobre/i', 'replace' => 'page/about', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Galerias
      ['match' => '/galeria[s]?\/([\w\-]+)[\/]?/i' , 'replace' => 'project/gallery/permalink/$1/', 'type_in' => 'regex', 'type_out' => 'regex'],
      ['match' => '/galerias[\/]?/i' , 'replace' => 'project/galleries/', 'type_in' => 'regex', 'type_out' => 'exactly'],

      //Contato
      ['match' => '/contato/i', 'replace' => 'page/contact', 'type_in' => 'regex', 'type_out' => 'exactly'],

   ];