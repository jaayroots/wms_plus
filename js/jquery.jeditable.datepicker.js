/*
 * Datepicker for Jeditable
 *
 * Copyright (c) 2011 Piotr 'Qertoip' Wล�odarek
 *
 * Licensed under the MIT license:
 *   http://www.opensource.org/licenses/mit-license.php
 *
 * Depends on jQuery UI Datepicker
 *
 * Project home:
 *   http://github.com/qertoip/jeditable-datepicker
 *
 */

// add :focus selector
jQuery.expr[':'].focus = function( elem ) {
  return elem === document.activeElement && ( elem.type || elem.href );
};

$.editable.addInputType( 'datepicker', {

    /* create input element */
    element: function( settings, original ) {
      var form = $( this );
      var input = $( '<input />' );
      input.attr( 'autocomplete','off' );
      input.attr( 'class','text_datepicker' ); // Edit size textbox for datepicker : Modified by kik 
      form.append( input );
      return input;
    },
   
    /* attach jquery.ui.datepicker to the input element */
    plugin: function( settings, original ) {
      var form = this;
      var input = form.find("input");
      var t = setTimeout(function(){
    	  //console.log(input);
          input.focus(); // fix bug textbox not hide when click mouse other area : Modified by kik     	  
      }, 100);

      // Don't cancel inline editing onblur to allow clicking datepicker
      if (settings.is_required) {
          settings.onblur = input.addClass('error');    	      	  
      } else {
    	  settings.onblur = 'nothing';
    	  //settings.onblur = 'cancel'; // fix bug textbox not hide when click mouse other area : Modified by kik 
      }
      // Add flag for checking validate
      //settings.onblur = 'nothing';
      //settings.onblur = 'cancel'; // fix bug textbox not hide when click mouse other area : Modified by kik 
      
      
      datepicker = {
        onSelect: function() {
          // clicking specific day in the calendar should
          // submit the form and close the input field
          form.submit();
        },
        onRender: function(date) {
            console.log('c');
        },        
        onClose: function() {
          setTimeout( function() {
            if ( !input.is( ':focus' ) ) {
              // input has NO focus after 150ms which means
              // calendar was closed due to click outside of it
              // so let's close the input field without saving
              original.reset( form );
            } else {
              // input still HAS focus after 150ms which means
              // calendar was closed due to Enter in the input field
              // so lets submit the form and close the input field
              form.submit();
            }
           
            // the delay is necessary; calendar must be already
            // closed for the above :focus checking to work properly;
            // without a delay the form is submitted in all scenarios, which is wrong
          }, 150 );
        }
      };
   
      /*
      console.log(settings.datepicker);
      if (settings.datepicker) {
        jQuery.extend(datepicker, settings.datepicker);
      }
      */
		var elm = input.datepicker({
			startDate: '-0m'
		}).on('changeDate', function(ev){
			//input.datepicker('hide'); Disable id because it disabled from main datepicker
			// EDIT BY BALL
			if (ev.viewMode == "days")
			{
				form.submit();
			}
		}).blur(function(){
      		var flag = true;
      		var _this = this;
  			$('div.datepicker.dropdown-menu').click(function(){
  				flag = false;
  			});
  			setTimeout(function(){
  	      		if ($(_this).val() != "" && flag) {
  	      			form.submit();  	      			
  	      		}
  			}, 200);
      	}).on('focus', function(ev){
      		//console.log('on focus');
      	});

		setTimeout(function(){
			elm.focus();
		}, 100);

		
    }
} );