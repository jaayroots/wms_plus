<!--<script language="JavaScript" type="text/javascript" src="<?php //echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php //echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->

<script>
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
        //initProductTable();
        $("#date_from").datepicker().keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
			//$('#sDate1').text($('#datepicker').data('date'));
			$('#date_from').datepicker('hide');
		}).bind("cut copy paste",function(e) {
			  e.preventDefault();
		  });
        $("#as_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
            }
        });

        $('#formLocationHistoryReport').submit(function(){
            find_data();
            return false;
        });  

        $('#search').click(function() {
            find_data();
        });

        $('#clear').click(function() {
            $('#doc_type').val('');
            $('#serial').val('');
            $('#ref_value').val('');
            $('#date_from').val('');
            $('#product_id').val('');
            $('#product').val(''); // Add By Akkarapol, 17/09/2013, Defect ID 342, เพิ่ม id ของช่อง Product Code เข้าไปเพื่อให้ตอนกดปุ่ม Clear ข้อมูลในช่องจะได้หายไปด้วย
            $('#current_location').val('');
            $('#lot').val('');
            $('#pallet_code').val('');
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
    
            //alert('search');
            var doc_type = $('#doc_type').val();
            var serial = $('#serial').val();
            var ref_value = $('#ref_value').val();
            var date_from = $('#date_from').val();
            var product_id = $('#product_id').val();
            var pallet_code = $('#pallet_code').val();
            //var current_location = $('#current_location :selected').val(); //COMMENT BY POR 2013-10-29 ยกเลิกการใช้แบบ select
            var current_location = $('#current_location').val(); //ADD BY POR 2013-10-29 เรียกใช้จาก text แทน select
            var lot = $('#lot').val();

            if (product_id == "" && current_location == "" && lot == "" && serial == "" && doc_type == "" && serial == "" && doc_type == "") {
                alert('Please select at least 1 condition');
                $('#report').html('Please select at least 1 condition');
            }
            else {

                $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');
                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url(); ?>/location_history_report/showLocationHistoryReport', // in here you should put your query 
                    data: 'doc_type=' + doc_type + '&serial=' + serial + '&ref_value=' + ref_value + '&date_from='
                            + date_from + '&product_id=' + product_id + '&current_location=' + current_location + '&lot=' + lot + '&pallet_code=' + pallet_code,
                    success: function(data)
                    {
                        //alert(data);
//                        console.log(data);
                        $("#report").html(data);
                        
                        //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                        $("#pdfshow").show();
                        $("#excelshow").show();
                        //END ADD
                    }
                });
            }
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
    
    //ADD BY POR 2013-10-28 เพิ่ม auto complete ของ location
    function lookup_location(inputVar, text_search) {
        if (text_search.length != 0) {
            $.post("<?php echo site_url(); ?>/report/showLocationList", {text_search: text_search}, function(val, data) { 
                if (data.length > 0) {
                    $('#locations').show();
                    $('#autoLocationsList').html(val, data);
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
    
    function fill_location(id) {
        $('#current_location').val(id);
        $('#Location_Code').val(id);
        setTimeout("$('#locations').hide();", 500);
    }
    
    function exportFile(file_type){
        if(file_type=='EXCEL'){
                $("#formLocationHistoryReport").attr('action',"<?php echo site_url(); ?>"+"/report/exportLocationHistoryReportToPDF")
                $("#formLocationHistoryReport").submit();
                //getParam();
        }else{
                $("#formLocationHistoryReport").attr('action',"<?php echo site_url(); ?>"+"/report/exportLocationHistoryReportToPDF")
                $("#formLocationHistoryReport").submit();
        }
        
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
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="formLocationHistoryReport" name="formLocationHistoryReport" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    
                    <tr>
                        <td valign="top">
                            <?php echo _lang('product_code') ?> : 
                        </td>
                         <td>
<!--                            <input type="text" placeholder="Product Code" id="product" name="product" autocomplete="off" onkeyup="lookup('product', this.value)"  onblur="fill('', '', '');" />
                            <div class="suggestionsBox" id="suggestions" style="display:none;">
                                <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
                            </div>-->
                             <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 290px;">
                                <div style="position: relative;margin-bottom: 3px;">
                                                <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 290px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                        </div>
                            </table>
                            <input type="hidden" id="product_id" name="product_id" />
                        </td>
                        <td>Current Location : </td>
                        <td>
                            <?php 
                            //echo $current_location;
                            //echo form_input("current_location", "", "id = 'current_location' placeholder='Current Location'")                             echo "<input type=\"text\" class=\"actual_location\" placeholder=\"Current Location\" value=\"\" />";
                            ?>
                            
                            <!--ADD BY POR แก้ไขให้ใช้ แบบ auto complete แทน แบบ combo-->
                            <input type="text" placeholder="Current Location" id="current_location" name="current_location" autocomplete="off" onkeyup="lookup_location('current_location', this.value)"  onblur="fill_location('');" />
                            <div class="suggestionsBox" id="locations" style="display:none;">
                                <div class="suggestionList" id="autoLocationsList"> &nbsp; </div>
                            </div>
                            <input type="hidden" id="Location_Code" name="Location_Code" />
                            <!--END ADD-->
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>LOT : 
                        </td>
                         <td>
                            <?php echo form_input("lot", "", "id = 'lot' placeholder='LOT'") ?>
                        </td>
                        
                        <td>Serial : </td>
                        <td><?php echo form_input("serial", "", "id = 'serial' placeholder='SERIAL'") ?></td>
                        <td >
                           
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Document Ref : 
                        </td>
                         <td>
                                 <select name="doc_type" id="doc_type">
					<option value="Document_No">Document No.</option>
					<option value="Doc_Refer_Ext">Refer External No.</option>
					<option value="Doc_Refer_Int">Refer Internal No.</option>
					<option value="Doc_Refer_Inv">Invoice No.</option>
					<option value="Doc_Refer_CE">Customs Entry</option>
					<option value="Doc_Refer_BL">BL No.</option>
				</select>
                            <?php echo form_input("ref_value", "", "id = 'ref_value' class='input-small' placeholder='VALUE'") ?>
                        </td>
                        
                        <td>Date From : </td>
                        <td><?php echo form_input("date_from", "", "id = 'date_from' placeholder='SELECT DATE'") ?></td>
                        <td >
                           
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    
                    <?php if($this->config->item('build_pallet')): ?>
                    <tr>
                        <td>Pallet Code : </td>
                        <td>
                            <?php echo form_input("pallet_code", "", "id = 'pallet_code' placeholder='Pallet Code'") ?>
                        </td>                        
                        
                        <td> </td>
                        <td> </td>
                        <td >
                           
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <?php else:  ?>
                        <input type='hidden' id="pallet_code" placeholder="Pallet Code" name="pallet_code" >
                    <?php endif; ?>
<!--                    <tr>
                        <td colspan="100%" style="text-align:center;">
                            <a href="<?php echo site_url(); ?>/location_history_report/direct_link">Location History Report SWA</a>
                        </td>
                    </tr>-->
                    <tr>
                        <td colspan="100%" style="text-align:center;">
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                            <!--<a style='float: right;' href="<?php// echo site_url(); ?>/location_history_report/direct_link"><?php //echo img("css/images/icons/excel-icon.png"); ?></a>-->
                        </td>
                    </tr>
                </table>

            </fieldset>

            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="">
                Please click search
            </div>
        </fieldset>

    </TD>
</TR>