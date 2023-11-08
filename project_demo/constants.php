<?php
/*
CONFIGURAÇÕES PARA VERSÕES FUTURAS
- (Sessão ou global) Save behavior - Ao salvar, avisa que salvou e permanece na página ou retorna para a tela de listagem
- (Sessão ou global) Permalink Behavior - Se o permalink deve ficar como read-only, oculto ou liberado para usuários não ADM
*/


define('PROJECT_ID' , 'PROJDEMO');
define('PROJECT_NAME', 'Project Demo');
define('VERSION', '0.0.1');
define('ENVIRONMENT', 'DEV');

//BASE DE DADOS
define('DB_NAME', 'db_name');
define('DB_HOST', 'localhost');
define('DB_USER', 'db_user');
define('DB_PWD', 'db_host');

//ENVIO DE EMAIL
define('SMTP_HOST', '');
define('SMTP_PORT', '');
define('SMTP_SECURE', '');
define('SMTP_USER', '');
define('SMTP_PASSWORD', '');
define('SMTP_FROM', '');
define('SMTP_NAME', '');

// define('RECAPTCHA_KEY', '');
// define('RECAPTCHA_SECRET', '');


//STATUS GERAIS DO SISTEMA
define('STATUS_PENDING', 0);  //Pendente - Precisa de aprovação
define('STATUS_INACTIVE', 1);  //Inativo - Não ativo, está aprovado mas não é visível ainda
define('STATUS_ACTIVE', 2);   //Ativo - Aprovado e visivel

define('LOGIN_EXPIRE_TIME', 48 * 3600);   //48 horas

//CONTROLE DE ACESSO
   define('ACCESS_LEVEL', 'MTR,ADM,MNG,USR');

   //Tipos/niveis de usuários base
   define( 'USER_TYPE_MASTER', 1);
   define( 'USER_TYPE_ADMIN', 2);
   define( 'USER_TYPE_MANAGER', 3);
   define( 'USER_TYPE_USER', 500);

   //Nome dos tipos usado nas áreas de PJ
   define('USER_TYPE_NAME', [
      1     => 'Usuário Master',
      2     => 'Administrador',
      3     => 'Gestor',
      500   => 'Usuário' ]);

   //Tamanho máximo uploads
   define('MAX_IMAGE_SIZE',  40 * 1024 * 1024); //4mb
   define('MAX_VIDEO_SIZE', 500 * 1024 * 1024); //20mb
   define('MAX_FILE_SIZE',  200 * 1024 * 1024); //20mb

   //Quantidade de itens por pagina
   define('LIBRARY_PAGE_LEN', 24);       //Ideal que seja um multiplo de (2,3,4);
   define('ADMIN_PAGE_LEN', 12);         //Quantos itens aparecem por página no admin
   
//Tipos de mensagens
define('MSG_TYPE_ALERT', '1');      //Alertas do sistema
define('MSG_TYPE_ACCEPT', '2');     //Aprovações
define('MSG_TYPE_BULLETIN', '3');   //Comunicados
define('MSG_TYPE_MSG', '4');        //Mensagem


//CONSTANTES INTERNAS
define('PERMALINK_MAX_LEN', 45);
define('EXCERPT_LEN', 120);


define('SECTION_PROJECT', 1);


define('MENU_MAIN', 1);
