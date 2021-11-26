<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>

    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 22/01/2013, Add Separator for use in Page
    var flagReq = false;
    $(document).ready(function() {
        // $( "#pass_excel" ).hide();
		/**
        * Search Product Code By AutoComplete
        */
       $("#txt_product_code_from, #txt_product_code_to").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $($(event.target).next()).attr("placeholder",'');
               $($(event.target).next()).removeClass('required');
           },
           source: function( request, response ) {
              $.ajax({
                 url: "<?php echo site_url(); ?>/product_info/ajax_show_product_list",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: request.term
                 },
                 success: function( val, data ) {
                     if(val != null){
                        response( $.map( val, function( item ) {
                         return {
                           label: item.product_code + ' ' + item.product_name,
                           value: item.product_code
                         }
                       }));
                     }
                 },
             });
           },
           open: function( event, ui ) {
               var auto_h = $(window).innerHeight()-$('#suggest_product').position().top-50;
               $('.ui-autocomplete').css('max-height',auto_h);
           },
           focus: function( event, ui ) {
               $($(event.target).next()).attr("placeholder", ui.item.label);
               return false;
           },
           select: function( event, ui ) {
               $($(event.target).next()).attr("placeholder",'');
               $(event.target).val( ui.item.value );
             return false;
           },
           close: function(){
               $($(event.target).next()).attr("placeholder",'');
           }
       });

		/**
        * Search Product Code By AutoComplete
        */
       $("#location_from, #location_to").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $($(event.target).next()).attr("placeholder",'');
               $($(event.target).next()).removeClass('required');
           },
           source: function( request, response ) {
              $.ajax({
                 url: "<?php echo site_url(); ?>/product_info/ajax_show_location_list",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: request.term
                 },
                 success: function( val, data ) {
                     if(val != null){
                        response( $.map( val, function( item ) {
                         return {
                           label: item.Location_Code,
                           value: item.Location_Code
                         }
                       }));
                     }
                 },
             });
           },
           open: function( event, ui ) {
               var auto_h = $(window).innerHeight()-$('#suggest_location').position().top-50;
               $('.ui-autocomplete').css({'max-height' : auto_h, width: 200});
           },
           focus: function( event, ui ) {
               $($(event.target).next()).attr("placeholder", ui.item.label);
               return false;
           },
           select: function( event, ui ) {
               $($(event.target).next()).attr("placeholder",'');
               $(event.target).val( ui.item.value );
             return false;
           },
           close: function(){
               $($(event.target).next()).attr("placeholder",'');
           }
       });

        //call intitial function
        initProductTable();
        init_criteria_disabled($("#form_search_criteria tr:first select"));

		function init_criteria_disabled(selector)
		{
			$("#form_search_criteria tr.criteria").hide();
			$('#' + selector.val()).show();
			//$("#form_search_criteria").find('tr:not("#selector_parent")').css({backgroundColor: 'transparent'});
			//$("#form_search_criteria").find('input:not([type="radio"],[class="enabled required"]),select:not("#sel_type_of_search")').prop('disabled', true).removeClass('error required');
			$("#search_btn").prop('disabled', false);
			//$(".enabled").prop('disabled', false).addClass('required');
			$("[name='is_urgent']").attr("disabled", false);
			$('#' + selector.val()).find('input:not("#txt_product_code_to,#txt_product_code_hilight,#location_to"), select').prop('disabled', false).addClass('required');

			required_check();
		}

        $("select[name=sel_type_of_search]").change(function(){
        	init_criteria_disabled($(this));
       });

        // Add By Akkarapol, 24/09/2013, เพิ่ม onKeyup ของช่อง Top  ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        // Modify Ball , Remove red border when elm have data
        function required_check()
        {
	        $('.required').on("change keyup focusin", function() {
	            if ($(this).val() != '') {
	                $(this).removeClass('required');
	            } else {
	                $(this).addClass('required');
	            }
	        });

	        var patt=/enabled/g;
	        $('input.required[type="text"]').each(function(k, v)
			{
				if($(v).val() !== "" && ! patt.test($(v).prop('class')) )
				{
					$(this).removeClass('required');
				}
	        });


        }
        // END Add By Akkarapol, 24/09/2013, เพิ่ม onKeyup ของช่อง Top ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        //datepicker
        //$.browser.chrome = /chrome/.test(navigator.userAgent.toLowerCase());

		if(/chrom(e|ium)/.test(navigator.userAgent.toLowerCase())){
        //if ($.browser.chrome) {

            // Comment By Akkarapol, 24/09/2013, เพื่อที่จะใช้ on ChangeDate แล้ว จำเป็นต้องเขียนเป็นฟังก์ชั่นที่ละเอียดกว่านี้
            //            $("#date_from").datepicker({dateFormat: 'mm/dd/yy'});
            //            $("#date_to").datepicker({dateFormat: 'mm/dd/yy'});
            // END Comment By Akkarapol, 24/09/2013, เพื่อที่จะใช้ on ChangeDate แล้ว จำเป็นต้องเขียนเป็นฟังก์ชั่นที่ละเอียดกว่านี้

            // Add By Akkarapol, 24/09/2013, เพิ่มเนื่องจากการจะใช้ on ChangeDate นั้น จำเป็นต้องเขียนฟังก์ชั่นให้ละเอียดกว่าที่เขียนไว้ด้านบน
           
            $('#date_from').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_from').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });


            $('#date_from_receive').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_from').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });


            $('#date_to').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_to').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });

            $('#date_to_receive').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_to').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });
            // END Add By Akkarapol, 24/09/2013, เพิ่มเนื่องจากการจะใช้ on ChangeDate นั้น จำเป็นต้องเขียนฟังก์ชั่นให้ละเอียดกว่าที่เขียนไว้ด้านบน


        } else {

            // Comment By Akkarapol, 24/09/2013, เพื่อที่จะใช้ on ChangeDate แล้ว จำเป็นต้องเขียนเป็นฟังก์ชั่นที่ละเอียดกว่านี้
            //            $("#date_from").datepicker({dateFormat: 'mm/dd/yy'});
            //            $("#date_to").datepicker({dateFormat: 'mm/dd/yy'});
            // END Comment By Akkarapol, 24/09/2013, เพื่อที่จะใช้ on ChangeDate แล้ว จำเป็นต้องเขียนเป็นฟังก์ชั่นที่ละเอียดกว่านี้

            // Add By Akkarapol, 24/09/2013, เพิ่มเนื่องจากการจะใช้ on ChangeDate นั้น จำเป็นต้องเขียนฟังก์ชั่นให้ละเอียดกว่าที่เขียนไว้ด้านบน
            $('#date_from').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_from').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });
        
            $('#date_from_receive').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_from').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            }); 

            $('#date_to').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_to').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });
            // END Add By Akkarapol, 24/09/2013, เพิ่มเนื่องจากการจะใช้ on ChangeDate นั้น จำเป็นต้องเขียนฟังก์ชั่นให้ละเอียดกว่าที่เขียนไว้ด้านบน
            $('#date_to_receive').datepicker({onRender: function(date) {
                }}).keypress(function(event) {
                event.preventDefault();
            }).on('changeDate', function(ev) {
                //$('#date_to').datepicker('hide');
                if ($(this).val() != '') {
                    $(this).removeClass('required');
                } else {
                    $(this).addClass('required');
                }
            }).bind("cut copy paste", function(e) {
                e.preventDefault();
            });
        }


        //modal click event
        $("#search_btn").click(function() {
            var statusisValidateForm = validateForm();
            var typeVal = $("#sel_type_of_search").val();
            var val_1 = "";
            var val_2 = "";
            var val_3 = "";
            if (typeVal == 'period_of_storage') {
                val_1 = $("#txtfrom").val();
                val_2 = $("#txtto").val();
            } else if (typeVal == 'dead_stock') {
                val_1 = $("#selectOperand").val();
                val_2 = $("#aging").val();
            } else if (typeVal == 'top_movement1') {
                val_1 = $("#date_from").val();
                val_2 = $("#date_to").val();
                val_3 = $("#top").val();
            } else if (typeVal == 'product_code') {
                val_1 = $("#txt_product_code_from").val();
                val_2 = $("#txt_product_code_to").val();
            } else if (typeVal == 'location_code') {
                val_1 = $("#location_from").val();
                val_2 = $("#location_to").val();
            }else if (typeVal == 'receive_date') {
                val_1 = $("#date_from_receive").val();
                val_2 = $("#date_to_receive").val();
               
            } 

            if (statusisValidateForm === true) {
             
				var total = 0;
                var oTable = $('#showProductTable').dataTable();
                $.get('<?php echo site_url() . "/countingCriteria/movementAjax" ?>', {'type': typeVal, 'val1' : val_1, 'val2': val_2, 'val3': val_3}, function(data) {
                	// console.log(data.aaData);
                    // return
                    // $( "#pass_excel" ).show();
                    oTable.fnClearTable();
                    $.each(data.aaData, function(i, item) {
                        var $checkbox = "<input type='checkbox' class='checked_item' name='chkCountingCriteria[]' value='"+item['0']+"' />";
                        oTable.dataTable().fnAddData([
							$checkbox
							, item['0']
							, item['1']
                            , item['2']
							/*, item['3']
							, item['16']
                            , item['12']
                            , item['13']
                            , item['3']
                            , item['4']
                            , item['5']
                            , item['6']
                            , item['7']
                            , item['8']
                            , item['9']
                            , item['10']
                            , item['11']
                            , item['14']
							, item['0']*/
						]
						);
                        total = i;
                    });
                    $('#total').val(total + 1);
                    initProductTable();
                	calculate_man();
                    // display export button                   
                    if(typeVal == "receive_date"){
                        $("#export_button").show();
                    }else{
                        $("#export_button").hide();
                    }
                    // end display export button
                }, "json");
            }
            else {
                alert("Please Check Your Require Information (Red label).");
                return false;
            }
        });

        $("#chkCountingCriteriaAll").click(function() {
            var status = this.checked;
            //alert(status);

            if (status == true) {
                $("input", $('#showProductTable').dataTable().fnGetNodes()).each(function() {
                    $(this).attr('checked', status);
                });
            }
            else {
                $("input", $('#showProductTable').dataTable().fnGetNodes()).each(function() {
                    $(this).attr('checked', status);
                });
            }
        });

      });

      function initProductTable() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true
            ,"bSort": false
            ,"bDestroy": true
            ,"sPaginationType": "full_numbers"
            ,"sDom": '<"H"lfr>t<"F"ip>'
			,"aoColumns": [
				{"sWidth": "10%", "sClass": "center"},
				{"sWidth": "10%"},
				{"sWidth": "70%", "sClass": "left"},
				{"sWidth": "10%"},
            ]
		});
        //new FixedHeader( oTable );
      }

	 // Calculate Function
	  function calculate_man() {
		var inputManPower = $("#manPower").val();
		var workingDay = $("#workingDay").val();
		var trax = $('#total').val();
		var jobTrax = Math.ceil((trax/workingDay)/inputManPower );
		$("#workingLoad").val(jobTrax);
		var avgPerMan = trax / jobTrax
		$("#taskAverage").val(Math.ceil(avgPerMan));
     }
	 // End Calculate

      function exportFileExcel () {
       
        var criteria = $("#sel_type_of_search").val();
        var from_date = $("#date_from_receive").val();
        var to_date = $("#date_to_receive").val();
        window.open('<?php echo site_url(); ?>/countingCriteria/exportExcelReceive?criteria=' + criteria+'&from_date='+ from_date +'&to_date='+to_date);

      }

     function exportFileExcel_nonFEFO () {
        console.log('d')
        return
        window.open('<?php echo site_url(); ?>/countingCriteria/exportExcelReceive?criteria=' + criteria+'&from_date='+ from_date +'&to_date='+to_date);

     }


      function postRequestAction(module, sub_module, action_value, next_state) {

        if (flagReq) {
            alert("Please wait. server processing your request.");
            return;
        }

        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {
        	calculate_man();
            var oTable = $('#showProductTable').dataTable();
            var checkedElement = new Array();

            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;

            if (num_row <= 0) {
                alert("Please Click Search Button!");
                return false;
            }
            var chkChecked = 0;
            $("input[type='checkbox']:checked").each(function() {
                chkChecked = chkChecked + 1;
            });

            if (chkChecked == 0) {
                alert("Please click on checkboxes before going to next step!");
                return false;
            }
            if (confirm("Are you sure to do following action :  To Next Step ?")) {
                //handmade call data from datatable.//
                //getValueFromTableData();
				$('input[name="prod_list[]"]').remove();
                var f = document.getElementById("frmCountingCriteria");

                var oTable = $('#showProductTable').dataTable();
                var get_data = oTable.fnGetData();
                var i;
                for (i in get_data)
                {
                    var tmp = oTable.fnGetNodes();
                    var is_checked = $(tmp[i]).find('.checked_item').prop('checked');
					if (is_checked)
					{

                            get_data[i] = get_data[i].join(separator); // Edit By Akkarapol, 22/01/2014, Set get_data[i] to join with SEPARATOR

	                    var prodItem = document.createElement("input");
	                    prodItem.setAttribute('type', "hidden");
	                    prodItem.setAttribute('name', "prod_list[]");
	                    prodItem.setAttribute('value', get_data[i]);
	                    f.appendChild(prodItem);
					}
                }

                $("input:checked", $('#showProductTable').dataTable().fnGetNodes()).each(function() {
                    var checkedElements = document.createElement("input");
                    checkedElements.setAttribute('type', "hidden");
                    checkedElements.setAttribute('name', "checkedElement[]");
                    checkedElements.setAttribute('value', $(this).val());
                    f.appendChild(checkedElements);
                });

                $("#selectIds").val(checkedElement);

                var data_form = $("#frmCountingCriteria").serialize();
                var message = "";
                flagReq = true;
                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
                    flagReq = false;
                    switch (data.status) {
                        case 'C001':
                            message = "Save Criteria Counting Complete";
                            break;
                        case 'C002':
                            message = "Confirm Criteria Counting Complete";
                            break;
                        case 'C003':
                            message = "Approve Criteria Counting Complete";
                            break;
                        case 'C004':        // add by kik : 08-01-2013
                            window.onbeforeunload = null;
                            message = "Reject Criteria Counting Complete";
                            break;
                        case 'C005':        // add by kik : 08-01-2013
                            window.onbeforeunload = null;
                            message = "Reject and Return Criteria Counting Complete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/countingCriteria/";
                    redirect(url);
                }, "json");

                //$("#frmCountingCriteria").submit();

            }
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
     }

     function validateForm() {
        var status;
        $("form").each(function() {
            $(this).validate();
            $(this).valid();
            status = $(this).valid();
        });
        return status;
     }

     function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
			window.onbeforeunload = null;
            url = "<?php echo site_url(); ?>/countingCriteria/";
            redirect(url);
        }
     }



        // Add By Ball
        // Prevent unwant redirect pages
        $(window).on('keydown keyup', function(e){
            if (e.which === 116) {
                window.onbeforeunload = null;
            }
            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
        });

      window.onbeforeunload = function() {
        return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
      };
       // Add By Ball

      </script>

        <div class="well" style='height:100%'>
            <form class="" method="POST" action="<?php echo site_url() . '/countingCriteria/countingCriteriaStep2/'; ?>" id="frmCountingCriteria" name="frmCountingCriteria">
                <label style="font-weight: bold;">Counting Criteria Step 1</label>
                <div id="content">
                    <fieldset>
                        <legend>Condition Detail</legend>
                        <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" id="form_search_criteria">
                            <tr id="selector_parent">
                                <td style="text-align: right; vertical-align: middle; width: 10%;">
                                    <label class="radio">Criteria : </label>
                                </td>
                                <td style="text-align:left; vertical-align: middle;" colspan="6">
                                    <select name="sel_type_of_search" id="sel_type_of_search">
                                        <option value="product_code"><?php echo _lang('product_code') ?></option>
                                        <option value="location_code">Location Code</option>
                                        <option value="period_of_storage">Period Of Storage</option>
                                        <option value="dead_stock">Dead Stock</option>
                                        <option value="top_movement">Top Movement</option>
                                        <option value="receive_date">Receive Date</option>
                                    </select>
                                    <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                                </td>
                            </tr>
                            <tr id="product_code" class="criteria">
                                <td style="text-align:right;">
                                    <label class="control-label" for="txt_product_code_from"><?php echo _lang('product_code') ?> : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls" style="height:30px;">
                                        <table id="suggest_product" cellspacing="0" cellpadding="0" style="float:left; padding: 0px; width: 200px;">
                                            <div style="position: relative;">
                                                <?php echo form_input("txt_product_code_from", "", "id='txt_product_code_from' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; margin: 0px; width: 200px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                                <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; margin: 0px; width: 200px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?>
                                            </div>
                                        </table>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="txt_product_code_to">To : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls" style="height:30px;">
                                        <table cellspacing="0" cellpadding="0" style="float:left; padding: 0px; width: 200px;">
                                            <div style="position: relative;">
                                                <?php echo form_input("txt_product_code_to", "", "id='txt_product_code_to' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; margin: 0px; width: 200px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                                <?php echo form_input("txt_product_code_hilight", "", "id='txt_product_code_hilight' autocomplete='off' style='border: none; margin: 0px; width: 200px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?>
                                            </div>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                            <tr id="location_code" class="criteria">
                                <td style="text-align:right;">
                                    <label class="control-label" for="location_from">From : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <div id="suggest_location" style="float:left; padding: 0px; width: 100px;"></div>
                                        <input type="text" name="location_from" id="location_from" placeholder="From location" class="">
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="location_to">To : </label>
                                </td>
                                <td style="text-align:left;" colspan="3">
                                    <div class="controls">
                                        <input type="text" name="location_to" id="location_to" placeholder="To" class="">
                                    </div>
                                </td>
                            </tr>
                            <tr id="period_of_storage" class="criteria">
                                <td style="text-align:right;">
                                    <label class="control-label" for="txtfrom">From : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <input type="text" name="txtfrom" id="txtfrom" placeholder="From" class=" number">
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="txtto">To : </label>
                                </td>
                                <td style="text-align:left;" colspan="3">
                                    <div class="controls">
                                        <input type="text" name="txtto" id="txtto" placeholder="To" class=" number">
                                    </div>
                                </td>
                            </tr>
                            <tr id="dead_stock" class="criteria">
                                <td style="text-align:right;">
                                    <label class="control-label" for="selectOperand">Condition : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <Select name="selectOperand" id="selectOperand" class="">
                                            <option value="" selected> -- Please select -- </option>
                                            <option value="=" > = </option>
                                            <option value=">=" > >= </option>
                                            <option value="<=" > <= </option>
                                            <option value=">" > > </option>
                                            <option value="<" > &lt; </option>
                                        </select>
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="aging">Aging : </label>
                                </td>
                                <td style="text-align:left;" colspan="3">
                                    <div class="controls">
                                        <input type="text" name="aging" id="aging" placeholder="Aging" class="">
                                    </div>
                                </td>
                            </tr>
                            <tr id="top_movement" class="criteria">
                                <td style="text-align:right;">
                                    <label class="control-label" for="date_from">Date From : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <input type="text" name="date_from" id="date_from" placeholder="Date From" class="" >
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="date_to">Date To : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <input type="text" name="date_to" id="date_to" placeholder="Date To" class="" >
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="top">Top : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <input type="text" name="top" id="top" placeholder="Top" class=" number">
                                    </div>
                                </td>
                            </tr>
                            <tr id="receive_date" class="criteria">
                                <td style="text-align:right;">
                                    <label class="control-label" for="date_from_receive">Date From : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <input type="text" name="date_from_receive" id="date_from_receive" placeholder="Date From" class="" >
                                    </div>
                                </td>
                                <td style="text-align:right;">
                                    <label class="control-label" for="date_to_receive">Date To : </label>
                                </td>
                                <td style="text-align:left;">
                                    <div class="controls">
                                        <input type="text" name="date_to_receive" id="date_to_receive" placeholder="Date To" class="" >
                                    </div>
                                </td>
                                
                            </tr>
                            <tr>
                                <td style="text-align:right"><label class="control-label" for="workingDay">Working Day : </label></td>
                                <td style="text-align:left"><input class="enabled required" type="text" name="workingDay" id="workingDay" value="1"></td>
                                <td style="text-align:right"><label class="control-label" for="manPower"> Man Power / Work  : </label></td>
                                <td style="text-align:left"><input class="enabled required" type="text" name="manPower" id="manPower" value="1"></td>
                                <td style="text-align:right;"><label class="control-label" for="workingDay">Document : </label></td>
                                <td style="text-align:left">
                                    <select name="counting_type" id="counting_type" class="enabled required">
                                        <?php
                                        foreach ($counting_type as $k => $val) :
                                        
                                            echo "<option value=\"".$val->Dom_Code."\">".$val->Dom_EN_Desc."</option>";
                                            //p($val->Dom_Code);exit;
                                        endforeach;
                                        ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="8" style="text-align:center;">
                                    <input type="button" id="search_btn" class="btn btn-primary" value="Search" style="font-weight: bold;margin: 10px;">
                                    <!-- <input type="button" id="pass_excel" class="btn btn-primary" value="Export Excel" style="font-weight: bold;margin: 10px;"> -->
                                </td>
                            
                            </tr>
                        </table>
                    </fieldset>
                    <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                        <tr>
                            <td colspan="2">
                                <div id="defDataTable_wrapper_top" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                                    <div style="width:100%;overflow-x: auto;" id="showDataTableTop">
                                        <table cellpadding="2" cellspacing="0" border="0" class="display" id="showProductTable" width="100%">
                                            <thead>
                                                <tr>
                                                    <th><input type="checkbox" name="chkCountingCriteriaAll" id="chkCountingCriteriaAll"></th>
                                                    <th><?php echo _lang('product_code'); ?></th>
                                                    <th><?php echo _lang('product_name'); ?></th>
                                                    <th><?php echo _lang('actual_location'); ?></th>

                                                    <!-- <th>LOT</th>
                                                    <th>Serial</th>
                                                    <th>Product Mfd</th>
                                                    <th>Product Exp</th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th>
                                                    <th style="display: none;"></th> -->
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>

                </div>
                <input type="hidden" name="queryText" id="queryText" value=""/>
                <input type="hidden" name="search_param" id="search_param" value=""/>
                <!-- <input type="hidden" name="counting_type" id="counting_type" value="CT02"> -->
                <input type="hidden" name="selectIds" id="selectIds" value="">
                <input type="hidden" name="total" id="total" value="">
                <input type="hidden" name="workingLoad" id="workingLoad" value="">
                <input type="hidden" name="taskAverage" id="taskAverage" value="">
                <?php echo form_hidden('process_id', $process_id); ?>
                <?php echo form_hidden('present_state', $present_state); ?>

                <?php
                if (isset($flow_id)) {
                    echo form_hidden('flow_id', $flow_id);
                }
                ?>
            </form>
            <div>

                <!-- Modal -->
                <div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                    <!--    <form action="" method="post">-->

                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
                        <h3 id="myModalLabel">Transfer Order</h3>
                    </div>
                    <div class="modal-body">

                        <!-- // working area-->
                    </div>
                    <div class="modal-footer">
                        <div style="float:left;">
                            <input class="btn red" value="Select All" type="button" id="select_all">
                            <input class="btn red" value="Deselect All" type="button" id="deselect_all">
                        </div>
                        <div style="float:right;">
                            <input class="btn btn-primary" value="Select" type="submit" id="search_submit">
                            <button class="btn" data-dismiss="modal" aria-hidden="true">Cancel</button>
                        </div>
                        
                    </div>
                    <!--    </form>-->
                </div>