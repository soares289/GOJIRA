// JavaScript Document
/* loader.js - Por Carlson A. Soares
	Cuida de carregar o conteudo via ajax, de acordo com a hash atual do link.
	Tem também trigers nos eventos de ajaxStart e Stop com a tela de loader
 */



$(document).ready(function(){
       
       $.showLoader = (typeof $.showLoader == 'undefined' ? true : $.showLoader);
       
		//Evento de quando a hash muda(como mudar de link)
      if( typeof $(window).hashchange == 'function' ){
         $(window).hashchange( function(){
            
               var hash = location.hash;
               
               if( hash.length < 3 ){
                  return false;
               }
               
               hash     = hash.split( "/" );
            
               loadContent( hash );
               
               
            });
		}
      
      $(document).ajaxStart(function(){ showLoading(); });
      $(document).ajaxStop(function(){ hideLoading(); });
      if( typeof $(window).hashchange == 'function' ){
         $(window).hashchange();
      }
      //keepAlive();
		
		
	});
	
	
	
	//Carrega o conteudo do site via ajax
	function loadContent( hash ){
		
		var query = hashToQuery( hash );
	
		$.post("engine.php",query[0],function(data){

				$("div.main").attr("class","main " + hash[1]);
				$(query[1]).html( data );
				var obj = $( query[1] );
				
				automation( $(obj) );
			
			});
	}
	
	
	
	function hashToQuery( hash ){
		
		var query  = "class=" + hash[1];
		var target = ".content";
		
		query += "&proc=" + ( hash.length > 2 ? hash[2] : "index" );
		
		if( hash.length > 3 ){
			for( var c = 3; c < hash.length; c += 2 ){	
				if( c < (hash.length-1) ){
					if( hash[ c ] == "target" ){
						target = "#" + hash[ c + 1 ];
					} else {
						query  += "&" + hash[ c ] + "=" + hash[ c + 1 ];
					}
				}
			}
		}
		
		return new Array( query, target );
	}
	
	
	//Mostra uma imagem de loading
	function showLoading(){
		
		if( $.noshowloading ) return false;
		
      if( $.showLoader ){
      
         var base = "";
         if( typeof system_url == "string" ) base = system_url;
         
         
         $("body").append( '<div id="loadingMask" style="display: none; background-image: url(' + base + 'images/bg.png); position: fixed; top: 0px; left: 0px; width: 100%; height: 100%;"><img src="' + base + 'images/loading.gif?2"/></div>' );
         
         var w = $("#loadingMask").width() * 0.05;
         $("#loadingMask img").css({width: w, left: "45%"});
         
         var h = ($("#loadingMask").height() - $("#loadingMask img").height()) / 3;
         $("#loadingMask img").css({position: "absolute", top:  h});
         
         $("#loadingMask").fadeIn(200);
         
		}
	}
	
	
	
	//Esconde a imagem de loading
	function hideLoading(){
		
		$("#loadingMask").fadeOut(200, function(){
				$(this).remove();
			});
	   
	}
	
	
	//Seta o lightbox nas imagens
	function setLightbox(){

		if( $.fn.fancybox ){
			$("a.lightbox").fancybox({
					'overlayShow'	: false,
					'transitionIn'	: 'elastic',
					'transitionOut'	: 'elastic'
				});
		}
	}
	
	
	
	//Mantem a sessão viva enquanto estiver na tela de edição
   //Mantem uma sessão ativa
	function keepAlive(){
		
		$.noshowloading = true;
		$.post("index.php",{},function(){ $.noshowloading = false; });
		setTimeout(keepAlive,80000);
		
	}
	
	
	
	function automation( obj ){
		
      setLightbox();
      
		//Funções que devem ser ativadas automaticamente
		$(".automatic", obj).each(function(){
				hash = $(this).attr('param');
				if( hash && hash.length > 3 ) loadContent( hash.split( "/" ) );
			});
		
		//Botões que devem rodar em background
		$(".auto_button", obj).click(function(evt){
				
				evt.preventDefault();
				var alt   = $(this).attr('alt') || 'executar a operação';
				var hash  = $(this).attr('href');
            var conf  = $(this).hasClass('auto_confirm');
            var query = hashToQuery( hash.split('/') );

				if( conf ){
					if( !confirm( "Tem certeza que deseja " + alt + "?" ) ) return;
				}
							
				$.post("engine.php",query[0],function(data){
                  
                  if( typeof sendMsg == 'function' ){
                  
                     sendMsg( data, "", function(ret){
                           if( ret.success == 1 || ret.success == true ){
                              if( typeof $(window).hashchange == 'function' ){
                                 $(window).hashchange();
                              }
                           }
                           return true;
                        });
                        
                  } else {
                     
                     try{
                        var ret = $.parseJSON( data );
                     } catch( err ){
                        var ret = {};
                     }                     
                     if( typeof ret.msg != 'undefined' && ret.msg != '' ) alert( ret.msg );

                  }
						
						
					});
			
			});
      
      if( typeof $.fn.datepicker == 'function' ){
         $(".date", obj).datepicker({ dateFormat: "dd/mm/yy" })
         $(".datetime", obj).datepicker({ dateFormat: "dd/mm/yy 00:00" })
      }
      
      if( typeof $.fn.mask == 'function' ){
         $(".date", obj).mask("99/99/9999");
         $(".datetime", obj).mask("99/99/9999 99:99");
         $(".mask-fone, .mask-phone", obj).mask("(99) 9999-9999?9",{placeholder:" "});
         $(".mask").each(function(){
               var mask = $(this).attr("rel");
               if( mask != "" && mask != undefined ) $(this).mask(mask);
            });
      }
		
      
      if( typeof $.fn.asmSelect == 'function' ){
         
         $("select", obj).each(function(){
               //Se for um select multiple, adiciona o plugin asmSelect
               if( $(this).attr( 'multiple' ) == 'multiple' ){
               
                  //Salva as classes do elemento, para depois adicionar ao gerado pelo asmSelect
                  var cls = $(this).attr( 'class' );
                  
                  //Se não tiver um titulo, coloca um padrão
                  if( $(this).attr('title') == undefined ) $(this).attr( 'title', 'Selecione' );
                  
                  //Adiciona o plugin ao elemento
                  $(this).asmSelect({ addItemTarget: 'bottom',
                                      animate: true,
                                      highlight: true,
                                      sortable: true});
                  $("select.asmSelect",$(this).parent()).addClass( cls );
               }
            });
         
       
      }
		
	}