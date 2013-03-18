<?php
      
      //Configurações globais
      ini_set("default_socket_timeout", 60);
      ob_start();
      date_default_timezone_set('America/Sao_Paulo');

      
      //Classes BASE usadas por quase tudo
      require_once( "class/tool.class.php" );
      require_once( "class/connection.class.php" );
      require_once( "class/config.class.php" );
      require_once( "class/log.class.php");
      require_once( "class/login.class.php" );
      require_once( "class/controller.class.php" );
      require_once( "class/model.class.php" );
      require_once( "class/collection.class.php" );
      require_once( "class/smarty/Smarty.class.php" );
      require_once( "class/paypal.class.php" );
      require_once( "class/phpmailer.class.php" );
	
//Seta as globais

      //Objetos mais comumente usados
      $globals->tools    = new Tool();
      $globals->conn     = new Connection( $globals->db->host, $globals->db->user, $globals->db->password, $globals->db->name);
      $globals->cfg      = new Config( $globals->conn, $globals->tools );
      $globals->log      = new Log( $globals->conn, $globals->tools );
      $globals->login    = new Login( $globals->conn, $globals->tools );
      $globals->smarty   = new Smarty();      
