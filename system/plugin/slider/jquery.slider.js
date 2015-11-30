/*
	JQuery Slider - feito por Carlson A. Soares - 22/05/2012
	
	Feito para a fanpage do Oswald.
	
	Não copie sem me avisar.
	
	email: soares_289@hotmail.com
	
	TODO - Implementar o button_hover e arrow_over;
*/

	$.fn.slider = function(options){
	
			var defaults = {
					time : 6000,						//Tempo entre uma imagem e outra
					speed: "fast",						//Velocidade da transicao
					width: 300,							//Largura do slider
					height: 150,						//Altura do slider
					no_arrows: false,
               no_buttons: false,
					arrow_left:   "images/slider/arrow_left.png",
					arrow_right:  "images/slider/arrow_right.png",
					button_on:    "images/slider/button_on.png",
					button_off:   "images/slider/button_off.png",
					button_hover: "images/slider/button_hover.png"
				};
				
			var options = $.extend(defaults, options);
			
			$(this).each(function(){
					
					
					$("ul", $(this)).each(function(){
                     
                     if( $("li",$(this)).length <= 0 ) return;
                     
                     if( options.no_arrows ){
                        options.arrow_left = options.arrow_right = "#";
                     }
                     
                     if( options.no_buttons ){
                        options.button_off = options.button_on = "#";
                     }
                     
							//Cria as areas principais do slider
							var current_item = 0;
							var total_item   = 0;
							var main_timer   = 0;
							var container    = $("<div class=\"slider_main\"></div>");
							var images       = $("<div class=\"slider_images\"><div class=\"slider_wrapper\"></div></div>");
							var buttons      = $("<div class=\"slider_buttons\"><ul></ul></div>");
							var arrow_left   = $("<div class=\"slider_arrow slider_arrow_left\"><img src=\"" + options.arrow_left + "\" alt=\"\" /></div>");
							var arrow_right  = $("<div class=\"slider_arrow slider_arrow_right\"><img src=\"" + options.arrow_right + "\" alt=\"\" /></div>");

							var setPosition;
							var countTime;
							
							//Junta todas as areas do slider
							$(container).append( images );
                     
                     if( !options.no_buttons ){
                        $(container).append( buttons );
                     }
							
							if( !options.no_arrows ){
								$(container).append( arrow_left );
								$(container).append( arrow_right );
							} 
							
							//Seta o css fixo dos itens mais importantes
							$(images).css({position: "relative", width: options.width, height: options.height, overflow: "hidden"});
							$(".slider_wrapper", $(images)).css({position: "relative", top: 0, left: 0});
							
							//Responsavel por movimentar de uma imagem para outra
							setPosition = function( next ){
									
									//Animação de movimento do slide
									var npos = $(".slider_wrapper #slider_image" + next.toString(), $(this)).position();
									$(".slider_wrapper", $(images)).animate({left: -npos.left}, options.speed);
									
									current_item = next;
									main_timer   = 0;
									
									//Remove classe ativa do botao anteriior
									$(".slider_button_active", $(buttons)).removeClass("slider_button_active");
									
									//Adiciona classe ativa no botao clicado
									$(".slider_button#slider_button" + next.toString(), $(buttons)).addClass("slider_button_active");
									
									
								};
							
							countTime = function(){
									main_timer += 100;
									if( main_timer >= options.time && options.time > 0 ){
										$(arrow_right).click();
										main_timer = 0;
									}
									setTimeout( countTime, 100 );
								};
							
							
							//Seta da esquerda, evento click
							$(arrow_left).click(function(){
									var next = current_item - 1;
									if( next < 1 ) next = total_item;
									setPosition.call(container, next);
								});
							$(arrow_right).click(function(){
									var next = current_item + 1;
									if( next > total_item ) next = 1;
									setPosition.call(container, next);
								});
							
							
							//Adiciona as imagens e os botoes no slider
							$("li",$(this)).each(function(){
								
									total_item++;
									
									var ind    = total_item;
									var img    = $("<div></div>");
									var button = $("<li class=\"slider_button\" id=\"slider_button" + ind.toString() +  "\">" + total_item.toString() +  "</li>");
									
									$(img).append( $(this).html() )
											.addClass("slider_image")
											.attr("id", "slider_image" + ind.toString())
											.css({position:"relative", float: "left", width: options.width, height: options.height, overflow:"hidden"});
									
									$(".slider_wrapper",images).append(img);											
									$("ul", $(buttons)).append( button );
									
									//Função do botão, seta a imagem atual para a do botão clicado
									$(button).click(function(){
											var cur = $(this).attr("id").replace("slider_button","");		
											setPosition.call(container, cur);
										});
									
								});
								
							
								
							$(".slider_wrapper", $(images)).css({width: (options.width+2) * total_item});
							$(this).before( container );
							$(this).remove();
                     if( options.time > 0 ) countTime.call();
							$(arrow_right).click();
						});
					
					
				});
			
		}
	