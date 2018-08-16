 // JavaScript Document

	//Envia mensagem de alerta
   function sendMsg( data, redirect, func ){
      
      var ret = {};
      
      if( typeof data === 'object' ){
         ret = data;
      } else {
         try{
            ret = $.parseJSON( data );
         } catch( err ){
            ret = {};
         }
      }
      
      func       = func || false;
      redirect   = redirect || '';
      
      if( typeof ret.redirect != 'undefined' ) redirect    = ret.redirect;
      if( typeof ret.success  == 'undefined' ) ret.success = 0;
      if( typeof ret.msg      == 'undefined' ) ret.msg     = '';
      
		var classe = (ret.success <= 0 || ret.success == false ? 'msgerror' : (ret.success == 1 || ret.success == true ? 'msgsuccess' : 'msgwarning'));
      
      
      //Se tem mensagem
		
      if( ret.msg.length > 0 ){
         
         $('body').append("<div id='msgboxMask'></div><div id='msgbox' class='" + classe + "'><div id='msgboxClose'></div><p></p></div>");
         $( 'html, body' ).animate( { scrollTop: 0 }, 'slow' );
         $("#msgbox #msgboxClose").click(function(){
               
               if( ret.success == 1 || ret.success == true ){
                  
                  if( func ){
                     ret.ret = func.call(null, ret);
                  } else {
                     ret.ret = true;
                  }
                  
						if( ret.ret ){
							
	                  if( redirect.replace(' ','').length <= 0 ){
                        
                        //document.location.reload();
								$('#msgbox').remove();
		                  $('#msgboxMask').remove();
                        
								try{
									$(window).hashchange();
								} catch( e ){
								   document.location.reload();
								}
                        
	                  } else {
	                     document.location = redirect;
	                  }
                     
						} else {
							$('#msgbox').remove();
	                  $('#msgboxMask').remove();
						}
                  
               } else {
               
                  $('#msgbox').remove();
                  $('#msgboxMask').remove();
                  if( func ){ func.call() } 
               }
               
            });
         $('#msgboxMask').click(function(){ $("#msgbox #msgboxClose").click(); });
         
         
         var maskHeight = $(document).height() + 30;
         var maskWidth = $(window).width();
         
         //Define largura e altura do div#mask iguais Ã¡s dimensÃµes da tela
         $('#msgboxMask').css({'width':maskWidth,'height':maskHeight});
         
         //efeito de transiÃ§Ã£o
         $('#msgboxMask').fadeTo(500,0.7,function(){
               $("#msgbox p").html( ret.msg );
               $('#msgbox').fadeIn(500);
            });
         
         
         //armazena a largura e a altura da janela
         var winH = $(window).height();
         var winW = $(window).width();

         //centraliza na tela a janela popup
         $('#msgbox').css('top',  winH/2-$('#msgbox').height()/2);
         $('#msgbox').css('left', winW/2-$('#msgbox').width()/2);
         

      //Se não tem a mensagem, apenas redireciona/recarrega
      } else {
         
         if( ret.success == 1 || ret.success == true ){
            
            if( func ){ ret.ret = func.call(null,ret) }
            
            if( ret.ret ){
							
					if( redirect.replace(' ','').length <= 0 ){
	               try{
							$(window).hashchange();
						} catch( e ){
						   document.location.reload();
						}
					} else {
	               document.location = redirect;
	            }
				}
         }
         
      }
   }
	
	   
   //Adiciona msg de erro nos inputs
   function errMsg( msg ){
      for( var i in msg ){
         $( msg[i].id ).after( '<span class="errMsg">' + msg[i].msg + '</span>' )
                       .bind( 'keydown, change', function(){
                                                               if( $(this).next().hasClass('errMsg') ){
                                                                  $(this).next().fadeOut(500,function(){
                                                                                                $(this).remove();
                                                                                              });
                                                               }
                                                            });
      }
   }
	
	//Monta um custom select - Usado no cadastro do mamatraca
	function setSelect( selector, def ){
		
		var idn      = 1;
		var n        = 1;
		var id       = '';
		var text     = '';
		var selected = '';
		
		$(selector).each(function(){

				n     = 1;
				id    = 'select_box' + idn++;
				text  = '<div class="select_box" id="' + id + '">';
				text += '<div class="select_value"><span>#value#</span></div>';
				text += '<ul class="select_list">';
				
				//Monta a lista de opcoes
				$('option', this).each(function(){
					
						val   = $(this).attr('value');
						opt   = $(this).html();
						
						if( $(this).attr('selected')){
							selected = opt;
							text    += '<li id="item' + n + '" class="item' + n + ' select_item select_selected">' + opt + '</li>';
						} else {
							text    += '<li id="item' + n + '" class="item' + n + ' select_item">' + opt + '</li>';
						}
						
						$(this).addClass('old_item' + n);
						n++;
						
					});
				
				text += '</ul></div>';
				
				//Marca o item selecionado
				selected = (selected == '' ? def : selected);
				text     = text.replace("#value#",selected);

				//Coloca a minha lista na tela
				$(this).hide().after(text);
				
			});
			
		$(".select_box .select_list").hide();
		
		$(".select_box .select_value").unbind('click.select_box');
		$(".select_box .select_value").bind('click.select_box',function(){
			
				var id = $(this).parent().attr('id');
				
				$("#" + id + ' .select_list ').toggle(200,function(){
				
						if($("#" + id + ' .select_list ').is(":visible")){
							
							$("#" + id).addClass("clicked");
							
						}else{
							
							$("#" + id).removeClass("clicked");
							
						}	
					
					});
				
				
			});


		$(".select_box .select_item").unbind('click.select_box').unbind('mouseenter.select_box').unbind('mouseleave.select_box');
		$(".select_box .select_item").bind('click.select_box',function(){
				
				var id   = $(this).parent().parent().attr('id');
				var sbox = $("#" + id).prev();
				
				//Altera o select original
				$('option:selected',sbox).removeAttr('selected');
				$('option.old_' + $(this).attr('id'),sbox).attr('selected',true);
				
				//Adiciona a classe correta no selecionado
				$("#" + id + ' .select_list li.select_selected').removeClass("select_selected");
				$(this).addClass("select_selected");
				$("#" + id).removeClass("clicked");
				
				//Pega o selecionado e joga no elemento visivel
				$("#" + id + ' .select_value span').html( $(this).html() );
				$("#" + id + ' .select_list ').toggle(200);
				
			}).bind('mouseenter.select_box',function(){
					$(this).addClass("select_hover");
				}).bind('mouseleave.select_box',function(){
						$(this).removeClass("select_hover");
					});
	
		
		
	}
	
	
	//Cria um input:file com o uploadify
	function defUploadify( uploader, id, dir, name, ext, auto, onInit, onEnd, onSelect ){
		
		var base = "";
		if( typeof plugin_url !== 'undefined' )  base = plugin_url;
		
		if( base.length > 0 ) base += "uploadify/";
		
		$("#" + id.toString()).uploadify({  uploader         : uploader,
													   "swf"            : base + "uploadify.swf", //base + "uploadify/uploadify.swf",
														"formData"       : {dir:dir, name:name, extension:ext},
														"cancelImg"      : base + "cancel.png",
														"folder"         : dir,
														"fileTypeExts"   : ext,
														"fileTypeDesc"   : "Arquivos " + ext.toString(),
														"buttonText"     : "SELECIONE",
														"auto"           : auto,
														"onUploadStart"  : onInit,
														"onQueueComplete": onEnd,
														"onSelect"       : onSelect
													});
	}
