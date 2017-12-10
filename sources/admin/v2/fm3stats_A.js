/* 
 * Feuille de marque en ligne
 * Javascript partie A
 */


            //Messages 
            function avertissement(texte) { 
                $('#avert').append('<div class="avertText">' + texte + '</div>');
                $('.avertText:last').show('blind',{},800).text(texte).delay(1500).fadeOut(1200);
            }
            //Alert
            function custom_alert(output_msg, title_msg) { 
                if (output_msg == '')
                    output_msg = lang.Aucun_message;
                if (title_msg == '')
                    title_msg = lang.Attention;
                $('div.simple_alert').remove();
                $("<div></div>").html(output_msg).dialog({
                    dialogClass:'simple_alert',
                    title: title_msg,
                    resizable: false,
                    modal: true,
                    buttons: {
                        "Ok": function() {
                            $( this ).dialog( "close" );
                        }
                    }
                });
            }



			$(function() {
				
				$.editable.addInputType('autocomplete', { //Plugin Autocomplete pour jEditable
					element : $.editable.types.text.element,
					plugin : function(settings, original) {
						$('input', this).autocomplete(settings.autocomplete);
					}
				});
				$.editable.addInputType('catcomplete', { //Plugin Autocomplete avec categories pour jEditable
					element : $.editable.types.text.element,
					plugin : function(settings, original) {
						$('input', this).catcomplete(settings.autocomplete);
					}
				});
				$.editable.addInputType('spinner', { //Plugin spinner pour jEditable
					element : $.editable.types.text.element,
					plugin : function(settings, original) {
						$('input', this).spinner(settings.spinner);
					}
				});
				$.widget( "custom.catcomplete", $.ui.autocomplete, { // Widget autocomplete avec gestion des categories
					_create: function() {
						this._super();
						this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
					},
					_renderMenu: function( ul, items ) {
						var that = this,
						currentCategory = "";
						$.each( items, function( index, item ) {
							var li;
							if ( item.category != currentCategory ) {
								ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
								currentCategory = item.category;
							}
							li = that._renderItemData( ul, item );
							if ( item.category ) {
								li.attr( "aria-label", item.category + " : " + item.label );
							}
						});
					}
				});
				
				
				$( document ).tooltip();
				$("#chrono_ajust").mask("99:99");
				$("#periode_ajust").mask("99:99");
				$("#time_evt").mask("99:99");
				$("#end_match_time, #time_end_match").mask("99h99");
                $('#list_up').hide();
                $('#liste_evt').click(function(e){
                    e.preventDefault();
                    $('#list, #list_header, #list_up, #list_down').toggle();
                });
            });
