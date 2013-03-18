/*
Copyright (c) 2003-2011, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/
CKEDITOR.plugins.add( "gallery",
{
	init: function( editor )
	{
		editor.addCommand( "insertGallery",
			{
				exec : function( editor )
				{    
					var galleryId = prompt("Qual o id da Galeria?");
					editor.insertHtml( "<br>[gallery=" + galleryId + "]" );
				}
			});
		editor.ui.addButton( "Gallery",
		{
			label: "Inserir Galeria de vídeo",
			command: "insertGallery",
			icon: this.path + "images/icon.png"
		} );
	}
} );
CKEDITOR.plugins.add( "imageUpload",
{
	init: function( editor )
	{
		editor.addCommand( "insertImageUpload",
			{
				exec : function( editor )
				{    
					$.post("upload.php",{},function(data){ $("body").append(data); });
				}
			});
		editor.ui.addButton( "ImageUpload",
		{
			label: "Fazer upload de uma imagem",
			command: "insertImageUpload",
			icon: this.path + "images/icon.png"
		} );
	}
} );


CKEDITOR.editorConfig = function( config )
{
	
	config.language = 'pt-br';
	config.skin = 'kama';
	
	config.toolbar = 'Full';
 
		config.toolbar_Full =
		[
			{ name: 'document', items : [ 'Source' ] },
			{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
			{ name: 'editing', items : [ 'Find','Replace','-','SelectAll','-','SpellChecker', 'Scayt' ] },
			{ name: 'tools', items : [ 'Maximize', 'ShowBlocks' ] },
			{ name: 'links', items : [ 'Link','Unlink','Anchor' ] },
			{ name: 'plugins', items : [ 'Gallery','ImageUpload' ]},
			//{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField' ] },
			'/',
			{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
			{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },

			{ name: 'insert', items : [ 'Image','MediaEmbed','Flash','Table','HorizontalRule','Smiley','SpecialChar','PageBreak','Iframe' ] }
			//{ name: 'styles', items : [ 'Styles','Format','Font','FontSize' ] },
			//{ name: 'colors', items : [ 'TextColor','BGColor' ] }
			
		];
		 
		config.toolbar_Basic =
		[
			['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', 'Unlink','-','About']
		];
	
};
