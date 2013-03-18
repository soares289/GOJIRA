$(document).ready(function(){
		
		$.login_begin = function(){
			$(".login .loginSubmit").click(function(){
				
					var login = $(".login #usrName").val();
					var pwd   = $(".login #usrPwd").val();
	
					$.post("engine.php", {class:"login", proc: "login", login:login, pwd:pwd}, function( data ){
							
							sendMsg( data, "", function(a,b,c){
									
									if( a[0] == 1 ){
										document.location.reload();
									}
								});
							
						});
					
				});
			$(".login input").keydown(function(evt){
				   
					if( evt.keyCode == 13 ){
						$(".login .loginSubmit").click();
					}
					
				});
		};
	});