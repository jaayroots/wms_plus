<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>

    var built_pallet = '<?php echo $this->config->item('build_pallet'); ?>';
    var ci_pallet_id = 6;  
    var ci_qty = 7; 


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
        }).keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#sDate1').text($('#datepicker').data('date'));
            //$('#as_date').datepicker('hide');

            // Add By Akkarapol, 24/09/2013, เพิ่ม การตรวจสอบ ตอนที่ changeDate ใน datePicker ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
            // END Add By Akkarapol, 24/09/2013, เพิ่ม การตรวจสอบ ตอนที่ changeDate ใน datePicker ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $('#frmPreDispatch').submit(function(){
            find_data();
            return false;
        });
                
        $('#search').click(function() {
            find_data();
        });

        $('#clear').click(function() {
            $('#product').val('');
            $('#as_date').val('');
            $('#warehouse_id').val('');
            $('#category_id').val('');
            $('#period').val('');
            $('#step').val('');
            $('#product_id').val('');
            $('#remain_day').val('');
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

        $("#remain_day").keyup(function() {
            this.value = this.value.replace(/[^0-9\.]/g, '');
        });


    });

    function find_data(){        
        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {
            var as_date = $('#as_date').val();
            var renter_id = $('#renter_id').val();
            var warehouse_id = $('#warehouse_id').val();
            var category_id = $('#category_id').val();
            var product_id = $('#product_id').val();
            var remain_day = $('#remain_day').val();



            if (product_id == "" && as_date == "" && warehouse_id == "" && category_id == "" && product_id == "" && remain_day == "") {
                alert('Please select "Warehouse" or "<?php echo _lang('product_category') ?>" or "<?php echo _lang('product_code') ?>" or "As Date" or "Remain day"');
                $('#report').html('Please select "Warehouse" or "<?php echo _lang('product_category') ?>" or "<?php echo _lang('product_code') ?>" or "As Date" or "Remain Day" ');
            }
            else {
                $("#report").hide();
                $("#reports").show();
                $("#pdfshow").show();
                $("#excelshow").show();
                inittable();
            }
        } // if validate ok
        else {
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
            "sAjaxSource": "<?php echo site_url(); ?>/report/showExpireReport",
            "fnServerData": function(sSource, aoData, fnCallback) {
                aoData.push(
                        {"name": "as_date", "value": $('#as_date').val()},
                {"name": "renter_id", "value": $('#renter_id').val()},
                {"name": "warehouse_id", "value": $('#warehouse_id').val()},
                {"name": "category_id", "value": $('#category_id').val()},
                {"name": "product_id", "value": $('#product_id').val()},
                {"name": "remain_day", "value": $('#remain_day').val()}
                );
                $.getJSON(sSource, aoData, function(json) {
                    var total = 0;
                    $.each(json.aaData, function(key, val) {
                        total += parseFloat(val[ci_qty].replace(/\,/g, '')); //EDIT BY POR 2014-01-14 แก้ไขให้สามารถคำนวณ , ได้
                    });

                    $("#total").html(set_number_format(total));
                    fnCallback(json);
                });
            },
            "fnDrawCallback": function(oSettings) {
                //$("#reports_filter").hide();
                //$("#reports_length").hide();
            },
            "aoColumnDefs": [
                {"sClass": "left_text", "aTargets": [1]},        
                {"sClass": "left_text", "aTargets": [2]},
                {"sClass": "right_text", "aTargets": [5]},                
                {"sClass": "right_text", "aTargets": [7]},
            ]
        });
        if (!built_pallet) { // check config built_pallet if It's false then hide a column Pallet Code
            $('#reports').dataTable().fnSetColumnVis(ci_pallet_id, false);
        }
    }

    function exportFile(file_type) {
        if (file_type == 'EXCEL') {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportExpiredToExcel")
        } else {
            $("#form_report").attr('action', "<?php echo site_url(); ?>" + "/report/exportExpiredPdf")
        }
        $("#form_report input[name='renter_id']").val($('#renter_id').val());
        $("#form_report input[name='warehouse_id']").val($('#warehouse_id').val());
        $("#form_report input[name='category_id']").val($('#category_id').val());
        $("#form_report input[name='product_id']").val($('#product_id').val());
        $("#form_report input[name='as_date']").val($('#as_date').val());
        $("#form_report input[name='remain_day']").val($('#remain_day').val());
        $("#form_report").submit();
    }
    // END BALL

    function validateForm() {
        //validate engine 
        var status;
        $("form").each(function() {
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
        <form  method="post" action="" target="_blank" id="form_report">
            <input type="hidden" name="renter_id" value="">
            <input type="hidden" name="warehouse_id" value="">
            <input type="hidden" name="category_id" value="">
            <input type="hidden" name="product_id" value="">
            <input type="hidden" name="as_date" value="">
            <input type="hidden" name="remain_day" value="">
        </form>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
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
                        <td>Product Category</td>
                        <td><?php echo form_dropdown('category_id', $category_list, '', 'id="category_id"'); ?></td>
                        <td valign="top">
                            <?php echo _lang('product_code') ?>
                        </td>
                        <td>
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 290px;">
                                <div style="position: relative;">
                                                <?php echo form_input("product", "", "id='product' placeholder='".  _lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                        </div>
                            </table>
<!--                            <input type="text"  id="product" name="product" autocomplete="off" onkeyup="lookup('product', this.value);"  onblur="fill('', '', '');"; />
                            <div class="suggestionsBox" id="suggestions" style="display:none;">
                                <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
                            </div>-->
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
                            <input type="text" placeholder="Date Format" id="as_date" name="as_date" value="<?php echo date("d/m/Y"); ?>" class="required" >
                        </td>
                        <td>Remain Day
                        </td>
                        <td>
                            <input type="text" placeholder="Optional Integer" id="remain_day" name="remain_day" />
                        </td>
                        <td></td>
                        <td></td>
                        <td >
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                    </tr>

                </table>

            </fieldset>

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
            <fieldset>
                <legend>Search Result </legend>
                <div id="report" style="margin:10px">Please click search</div>
                <table id="reports" cellpadding="0" cellspacing="0" border="0" aria-describedby="defDataTable_info" class="well" style="max-width: none; display: none;">
                    <thead>
                        <tr>
			<th><?php echo _lang('no');?></th>
                        <th width="100"><?php echo _lang('product_code');?></th>
			<th width="500"><?php echo _lang('product_name');?></th>
			<th><?php echo _lang('lot');?>/<?php echo _lang('serial');?></th><!--#defect 336 เพิ่ม column เพื่อแสดง Lot/Serial-->
			<th><?php echo _lang('expired_date');?></th>
			<th><?php echo _lang('remain_days');?></th>
			<th><?php echo _lang('pallet_code');?></th>
			<th><?php echo _lang('qty');?></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                    <th colspan="7">Total</th>
                    <th id="total" style="text-align: right"></th>
                    </tfoot>				
                </table>
            </fieldset>
        </form>
    </TD>
</TR>