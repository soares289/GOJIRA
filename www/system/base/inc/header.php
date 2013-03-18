<!DOCTYPE html>
<html>
   <head>
	
      <meta charset="UTF-8">
      <title></title>
	   
      <!-- Load das folhas de estilo -->
      <?php //$globals->tools->load( 'css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/themes/ui-darkness/jquery-ui.css', false, true );  ?>
      <?php $globals->tools->load( 'css', $globals->environment->system_url . 'css/main.css', false, true );  ?>
      <?php $globals->tools->load( 'css', $globals->environment->system_url . 'css/reset.css', false, true );  ?>
      <?php $globals->tools->load( 'css', $globals->environment->site_url   . 'css/main.css', false, true );  ?>
      <?php $globals->tools->load( 'css', $globals->environment->site_url   . 'css/style.css', false, true );  ?>
	   
      <!-- Load dos scripts javascriipt -->
      <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/jquery-1.7.1.min.js', false, true );  ?>
      <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/jquery.ba-hashchange.min.js', false, true );  ?>
      <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/jquery-ui.min.js', false, true); ?>
      <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/swfobject.js', false, true); ?>
      <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/functions.js', true, true); ?>
      <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/loader.js', false, true );  ?>
      <?php $globals->tools->load( 'js', $globals->environment->plugin_url . 'slider/jquery.slider.js', false, true );  ?>
      <?php $globals->tools->load( 'js', $globals->environment->site_url   . 'js/functions.js', false, true );  ?>
      <?php $globals->tools->load( 'js', $globals->environment->site_url   . 'js/main.js', false, true );  ?>
	   
	   
   </head>
   <body class="<?php echo $class . ' ' . $proc; ?>">
      <div class="body">
         <div class="header">
         </div>   
            
         <div class="main">
            <div class="content">
