<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<meta charset="UTF-8">
	<title><?php echo $globals->cfg->getConfig( PROJECT_ID, 'TITLE_BASE', ''); ?></title>
   
   <!-- Load das folhas de estilo -->
   <script type="text/javascript">
		
		var site_url   = "<?php echo $globals->environment->site_url; ?>";
		var system_url = "<?php echo $globals->environment->system_url; ?>";
		var plugin_url = "<?php echo $globals->environment->plugin_url; ?>";
		
   </script>
   <?php $globals->tools->load( 'css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.15/themes/ui-darkness/jquery-ui.css', false, true );  ?>
	<?php $globals->tools->load( 'css', $globals->environment->system_url . 'css/main.css', false, true );  ?>
   <?php $globals->tools->load( 'css', $globals->environment->system_url . 'css/reset.css', false, true );  ?>
   <?php $globals->tools->load( 'css', $globals->environment->plugin_url . 'uploadify/uploadify.css', false, true );  ?>
   <?php $globals->tools->load( 'css', $globals->environment->plugin_url . 'asmselect/jquery.asmselect.css', false, true );  ?>
   <?php $globals->tools->load( 'css', $globals->environment->plugin_url . 'fancybox/fancybox.css', false, true );  ?>
	<?php $globals->tools->load( 'css', $globals->environment->site_url . 'css/main.css', false, true );  ?>
   <?php $globals->tools->load( 'css', $globals->environment->site_url . 'css/style.css', false, true );  ?>
   
   <?php if( !$logged ) $globals->tools->load( 'css', 'login' );  ?>
   
   <!-- Load dos scripts javascriipt -->
	<?php //$globals->tools->load( 'js', 'http://code.jquery.com/jquery.min.js', false, trye );  ?>
	<?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/jquery-1.7.1.min.js', false, true );  ?>
   <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/jquery.ba-hashchange.min.js', false, true );  ?>
   <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/jquery-ui.min.js', false, true); ?>
   <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/swfobject.js', false, true); ?>
   
   <!-- plugins -->
   <?php $globals->tools->load( 'js', $globals->environment->plugin_url . 'asmselect/jquery.asmselect.js', false, true); ?>
   <?php $globals->tools->load( 'js', $globals->environment->plugin_url . 'fancybox/jquery.fancybox-1.3.4.pack.js' , false, true); ?>
   <?php $globals->tools->load( 'js', $globals->environment->plugin_url . 'maskedinput/jquery.maskedinput.js' , false, true); ?>
   <?php $globals->tools->load( 'js', $globals->environment->plugin_url . 'uploadify/jquery.uploadify-3.1.js' , false, true); ?>
   <?php $globals->tools->load( 'js', $globals->environment->plugin_url . 'ckeditor/ckeditor.js' , false, true); ?>

	<!-- Base dos scripts -->
	<?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/functions.js', true, true); ?>
   <?php $globals->tools->load( 'js', $globals->environment->system_url . 'js/loader.js', false, true );  ?>
   <?php $globals->tools->load( 'js', $globals->environment->site_url   . 'js/colorpicker.js', false, true );  ?>
   <?php $globals->tools->load( 'js', $globals->environment->site_url   . 'js/functions.js', false, true );  ?>
   <?php $globals->tools->load( 'js', $globals->environment->site_url   . 'js/main.js', false, true );  ?>
   
</head>
<body>