<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
	var oTable = null;
    //$('#myModal').modal('toggle');
    $(document).ready(function() {
        /**
     * Search Product Code By AutoComplete
     */
    $("#product").autocomplete({
        minLength: 0,
        search: function( event, ui ) {
            $('#highlight_productCode').attr("placeholder",'');
        },
        source: function( request, response ) {
          $.ajax({
              url: "<?php echo site_url(); ?>/report/ajax_show_product_list",
              dataType: "json",
              type:'post',
              data: {
                text_search: $('#product').val()
              },
              success: function( val, data ) {
                  if(val != null){
                     var flag_set_product_id = true;
                     response( $.map( val, function( item ) {
                        if(flag_set_product_id){
                            $('#product_id').val(item.product_id);
                            flag_set_product_id = false;
                        }
                      return {
                        label: item.product_code + ' ' + item.product_name,
                        value: item.product_id
                      }
                    }));
                  }
              },
          });
        },
        open: function( event, ui ) {
            var auto_h = $(window).innerHeight()-$('#table_of_productCode').position().top-50;
            $('.ui-autocomplete').css('max-height',auto_h);
        },
        focus: function( event, ui ) {
            $('#highlight_productCode').attr("placeholder", ui.item.label);
            return false;
        },
        select: function( event, ui ) {
            $('#highlight_productCode').attr("placeholder",'');
            $('#product').val( ui.item.label ); //โชว์อะไรในช่อง
            $('#product_id').val(ui.item.value); //ค่าที่ต้องการ assign
          return false;
        }
    });
        // Add By Akkarapol, 24/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 24/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง


        //initProductTable();

        $("#as_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
            }
        }).keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			//$('#as_date').datepicker('hide');
		}).bind("cut copy paste",function(e) {
			  e.preventDefault();
		  });

//        $('#frmShelflife').submit(function(){
//            find_data();
//            return false;
//        });

        $('#search').click(function() {
            find_data();
        });
        
        $('#clear').click(function() {
            var date_now = GetTodayDate();
            $('#product').val('');
            $('#as_date').val(date_now);
            $('#warehouse_id').val('');
            $('#category_id').val('');
            $('#period').val('');
            $('#step').val('');
            $('#product_id').val('');
            $('#report').html('Please click search');
            
            //ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report 
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
        });

        $("#product").click(function() {
            $('#product').val('');
            $('#product_id').val('');
        });

        $("#period").keyup(function() {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });

        $("#step").keyup(function() {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });
    });

        function find_data(){
            var statusisValidateForm = validateForm();
            if(statusisValidateForm === true  ){
                if (product_id == "" && as_date == "" && warehouse_id == "" && category_id == "" && product_id == "" && period == "" && step == "") {
                    alert('Please select "Warehouse" or "<?php echo _lang('product_category'); ?>" or "<?php echo _lang('product_code'); ?>" or "As Date" or "Period" or "Step"');
                    $('#report').html('Please select "Warehouse" or "<?php echo _lang('product_category'); ?>" or "<?php echo _lang('product_code'); ?>" or "As Date" or "Period" or "Step"');
                } else {
                    $("#report").hide();
                    $("#reports").show();
                    $("#pdfshow").show();
                    $("#excelshow").show();	
                    inittable();
                }
             } // validate ok
            else{
                alert("Please Check Your Require Information (Red label).");
                return false;
            } 
        }

	// BALL
	function inittable() {
		if (oTable != null) {
			oTable.fnDestroy();
		}
		oTable = $('#reports').dataTable({
			"bJQueryUI": true,
			"bSort": false,
			"bAutoWidth": false,
			"sPaginationType": "full_numbers",
			"bProcessing": true,
			"bServerSide": true,
			"sAjaxSource": "<?php echo site_url(); ?>/shelf_life_report/showShelfLifeReport",
			"fnServerData": function ( sSource, aoData, fnCallback ) {
				aoData.push(
					{ "name": "as_date", "value": $('#as_date').val() },
					{ "name": "renter_id", "value": $('#renter_id').val() },
					{ "name": "warehouse_id", "value": $('#warehouse_id').val() },
					{ "name": "category_id", "value": $('#category_id').val() },
					{ "name": "product_id", "value": $('#product_id').val() },
					{ "name": "remain_day", "value": $('#remain_day').val() },
					{ "name" : "step", "value": $('#step').val()}
				);
				$.getJSON( sSource, aoData, function (json) { 
					fnCallback(json);
					setTimeout(function(){
						var rows = $("#reports").dataTable().fnGetNodes();
						var m3 = 0;
						var m6 = 0;
						var m9 = 0;
						var m12 = 0;
						var m18 = 0;
						var m24 = 0;
						var max = 0;
						var total = 0;	
                     
						for(var i=0;i<rows.length;i++)
                                                {
                                                    //EDIT BY POR 2014-03-22 แก้ไขให้ตรวจสอบค่าที่ส่งมาว่ามีค่าหรือไม่ ถ้ามีค่าให้นำค่านั้นมาคำนวณโดยตัด comma ออก แต่ถ้าไม่มีค่าให้แปลงเป็น 0 ก่อนเพื่อจะได้นำไปคำนวณได้
                                                    m3 += parseFloat($(rows[i]).find("td:eq(3)").html().replace(/\,/g, '') || 0);
                                                    m6 += parseFloat($(rows[i]).find("td:eq(4)").html().replace(/\,/g, '') || 0);
                                                    m9 += parseFloat($(rows[i]).find("td:eq(5)").html().replace(/\,/g, '') || 0);
                                                    m12 += parseFloat($(rows[i]).find("td:eq(6)").html().replace(/\,/g, '') || 0);
                                                    m18 += parseFloat($(rows[i]).find("td:eq(7)").html().replace(/\,/g, '') || 0);
                                                    m24 += parseFloat($(rows[i]).find("td:eq(8)").html().replace(/\,/g, '') || 0);
                                                    max += parseFloat($(rows[i]).find("td:eq(9)").html().replace(/\,/g, '') || 0);
                                                    total += parseFloat($(rows[i]).find("td:eq(10)").html().replace(/\,/g, '') || 0);               	
                                                }
                                        
				        $("#reports tfoot th:eq(1)").html(set_number_format(m3));
				        $("#reports tfoot th:eq(2)").html(set_number_format(m6));
				        $("#reports tfoot th:eq(3)").html(set_number_format(m9));
				        $("#reports tfoot th:eq(4)").html(set_number_format(m12));
				        $("#reports tfoot th:eq(5)").html(set_number_format(m18));
				        $("#reports tfoot th:eq(6)").html(set_number_format(m24));
				        $("#reports tfoot th:eq(7)").html(set_number_format(max));
				        $("#reports tfoot th:eq(8)").html(set_number_format(total));
					}, 100);
				});				
			},
                        "aoColumnDefs": [
                            { "sClass": "left_text", "aTargets": [2]},  //ADD BY POR 2014-06-03 แก้ไขให้ product name ชิดซ้าย
                            { "sClass": "right_text", "aTargets": [3]},
                            { "sClass": "right_text", "aTargets": [4]},
                            { "sClass": "right_text", "aTargets": [5]},
                            { "sClass": "right_text", "aTargets": [6]},
                            { "sClass": "right_text", "aTargets": [7]},
                            { "sClass": "right_text", "aTargets": [8]},
                            { "sClass": "right_text", "aTargets": [9]},
                            { "sClass": "right_text", "aTargets": [10]},
                        ]
		});
	}	
	// END

    
    function GetTodayDate() {
        var tdate = new Date();
        var dd = tdate.getDate(); //yeilds day
        var MM = tdate.getMonth(); //yeilds month
        var yyyy = tdate.getFullYear(); //yeilds year
        var xxx = dd + "/" +( MM+1) + "/" + yyyy;

    return xxx;
    }

	function validateForm() {        
        //validate engine 
            var status;
            $("form").each(function () {
                $(this).validate();
                $(this).valid();
                status = $(this).valid();
            });
            return status;            
       //end of validate engine
    }
    
    function lookup(inputVar, text_search) {
        if (text_search.length != 0) {
            $.post("<?php echo site_url(); ?>/report/showProductList", {text_search: text_search}, function(val, data) {
                if (data.length > 0) {
                    $('#suggestions').show();
                    $('#autoSuggestionsList').html(val, data);
                    //$("#receive_phone").attr("disabled", "disabled");				
                }
            });
        }
    }
    function fill(id, code, name) {
        $('#product').val(code + ' ' + name);
        $('#product_id').val(id);
        setTimeout("$('#suggestions').hide();", 500);
    }
    function exportFile(file_type){
        if(file_type=='EXCEL'){
                $("#frmShelflife").attr('action',"<?php echo site_url(); ?>"+"/shelf_life_report/export_shelf_life_excel")
        }else{
                $("#frmShelflife").attr('action',"<?php echo site_url(); ?>"+"/shelf_life_report/export_shelf_life_pdf")
        }
        $("#frmShelflife input[name='renter_id']").val($('#renter_id').val());
        $("#frmShelflife input[name='warehouse_id']").val($('#warehouse_id').val());
        $("#frmShelflife input[name='category_id']").val($('#category_id').val());
        $("#frmShelflife input[name='product_id']").val($('#product_id').val());
        $("#frmShelflife input[name='as_date']").val($('#as_date').val());
        $("#frmShelflife input[name='remain_day']").val($('#remain_day').val());
        $("#frmShelflife").submit();
    }
</script>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
    #report{
        margin:5px;
        text-align:center;
    }
    .suggestionsBox {
        position: absolute;
        width: 200px;
        background-color: #f2f2f2;
        -moz-border-radius: 2px;
        -webkit-border-radius: 2px;
        border: 1px solid #333;
        color:#333;
        /*margin-top: 10px;*/
        margin-top:1px;
        margin-right: 0px;
        margin-bottom: 0px;
        margin-left: 0px;
        padding-right: 2px;
        padding-left: 2px;
        font-size:11px;	
        z-index:100;
    }

    .suggestionList {
        margin: 0px;
        padding-top: 0px;
        padding-right: 2px;
        padding-bottom: 2px;
        padding-left: 2px;
        height:200px;
        overflow:scroll;
    }

    .suggestionList ul {
        list-style:none;
    }

    .suggestionList li {
        margin: 0px 0px 3px 0px;
        padding: 3px;
        cursor: pointer;
        list-style-type: none;
        /*background-color: #E8E8E8;*/
        color:#000000;
    }

    .suggestionList li:hover {
        background-color: #659CD8;
    }
</style>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmShelflife" name="frmPreDispatch" target="_blank">
            <input type="hidden" name="renter_id" value="" />
            <input type="hidden" name="warehouse_id" value="" />
            <input type="hidden" name="category_id" value="" />
            <input type="hidden" name="product_id" value="" />
            <input type="hidden" name="as_date" value="" />
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>Renter</td>
                        <td><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'id="renter_id" class="required" '); ?></td>
                        <td>Warehouse</td>
                        <td><?php echo form_dropdown('warehouse_id', $warehouse_list, '', 'id="warehouse_id" '); ?></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><?php echo _lang('product_category'); ?></td>
                        <td><?php echo form_dropdown('category_id', $category_list, '', 'id="category_id"'); ?></td>
                        <td valign="top">
                            <?php echo _lang('product_code'); ?>
                        </td>
                        <td>
<!--                            <input type="text"  id="product" name="product" autocomplete="off" onkeyup="lookup('product', this.value)"  onblur="fill('', '', '');"; />
                            <div class="suggestionsBox" id="suggestions" style="display:none;">
                                <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
                            </div>-->
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 290px;">
                                <div style="position: relative;">
                                                <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                        </div>
                            </table>
                            <input type="hidden" id="product_id" name="product_id" />
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>As of Date
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="as_date" name="as_date"  value="<?php echo date("d/m/Y"); ?>" class="required" >
                        </td>
                        <td>
                        </td>
                        <td>
                            
                        </td>
                        <td></td>
                        <td></td>
                        <td >
                           
                        </td>
                    </tr>
                    <tr>
                        <td colspan="100%" style="text-align:center;">
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                    </tr>
                </table>

            </fieldset>

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px">
                Please click search
            </div>
				<table id="reports" cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" class="well" style="max-width: none; display: none;">
				<thead>
					<tr>
                                            <th rowspan="2" class="border-top"><?php echo _lang('no'); ?></th>
						<th rowspan="2" width="100" class="border-top"><?php echo _lang('product_code'); ?></th>
						<th rowspan="2" width="200" class="border-top"><?php echo _lang('product_name'); ?></th>
						<th colspan="7" class="border-top"><?php echo _lang('shelf_life'); ?></th>
						<th rowspan="2" class="border-top"><?php echo _lang('total'); ?></th>
					</tr>
					<tr>
						<th>3M</th>
			            <th>6M</th>
			            <th>9M</th>
						<th>1Y</th>
			            <th>1.5Y</th>
			            <th>2Y</th>
						<th>&gt;2Y</th>
					</tr>
				</thead>
				<tbody></tbody>
				<tfoot>
					<tr>
						<th colspan="3">Total</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
						<th style="text-align: right">0</th>
					</tr>				
				</tfoot>
				</table>            
        </fieldset>

    </TD>
</TR>
