<?php
	
	if( isset( $_POST['command'] ) ){
		
		require_once('class/console.class.php');
		
		$cmd     = $_POST['command'];
		$console = new Console();
		
		echo $console->execute( $cmd );
		exit;
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<script type="text/javascript" src="js/jquery-1.7.1.min.js"></script>
   <script type="text/javascript">
		
		var executing = false;
		$(document).ready( function(){
			
				var hist = new Array();
				var hpos = 0;
			
				$("#execute").click(function(){
						
						if( executing ) return;
						
						var command = $("#command").val();
						executeing  = true;
						
						
						$("#command").val( "" );
						$(".log").append( command + "<br> Processando... <br>" );
						
						hist.push( command );
						hpos = hist.length;
						
						$.post("",{command:command},function(data){
								executing = false;
								var l = $(".log").html();
								
								$('.log').html( l.substr(0, l.length - 20) + "> " + data + "<br><br>$~:&nbsp;" );
								$(".log").prop({ scrollTop: $(".log").prop("scrollHeight") });
							});

						$(".log").prop({ scrollTop: $(".log").prop("scrollHeight") });
						
					});
					
				$("#command").keydown(function(evt){
					   
						if( evt.keyCode == 13 ) $("#execute").click();
						
						if( evt.keyCode == 40 || evt.keyCode == 38 ){
							
							if( evt.keyCode == 38 ) hpos--;
							if( evt.keyCode == 40 ) hpos++;
							if( hpos >= hist.length ) hpos = hist.length - 1;
							if( hpos < 0 ) hpos = 0;
							
							$(this).val( hist[ hpos ] );
							
						}
						
					});
			});
			
   </script>
	<meta charset="UTF-8">
	<title>Console Screen</title>
   <style type="text/css">
		.console{ position: absolute; width: 99%; height: 98%; }
		.log{ position: absolute; border: 1px solid #ccc; bottom: 35px; top: 0px; right: 5px; left: 5px; overflow: auto; }
		.controls{ position: absolute; bottom: 30px; left: 5px; right: 5px; }
		#command{ position: absolute; top: 0px; left: 0px; right: 160px; }
		#execute{ position: absolute; top: 0px; right: 0px; width: 150px; }
   </style>
</head>
<body>
	<div class="console">
		<div class="log">
      	$~:&nbsp;
	   </div>
      <div class="controls">
		   <input type="text" name="command" id="command">
		   <input type="submit" value="Executar" id="execute">
      </div>
   </div>
</body>
</html>