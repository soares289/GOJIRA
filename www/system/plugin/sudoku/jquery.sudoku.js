/*
   JQuery Sudoku - feito por Carlson A. Soares - 29/10/2012
   
   Cria, em uma div determinada, um jogo de sudoku completo.
*/

   $.fn.sudoku = function(options){
   
         var defaults = {
               use_image: false,
               image_base: "images/",
               alert_color: "#ffddaa",
               confirm_color: "#aaddff",
               default_color: "#ffffff"               
            };
            
         var options = $.extend(defaults, options);
         var count   = 0;

         $(this).each(function(){
               
               var base  = "sqsudoku";
               var div   = $("<div></div>");
               var table = $("<table></table>");
               var id    = base + count;

               count++;

               $(div).addClass("jqsudoku");
               $(div).addClass(id);

               for( r = 1; r <= 9; r++ ){

                  var tr = $("<tr></tr>");

                  for( c = 1; c <= 9; c++ ){
                     
                     var td  = $("<td></td>");
                     var row = base + "_row" + Math.ceil(r/3)
                     /$(td).addClass("sqsudoku_cell sqsudo")

                     $(tr).append(td);
                  }
                  $(table).append( tr );
               }
               
               
            });
         
      }
   