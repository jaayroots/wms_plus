<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
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
    
    
    
        // Add By Akkarapol, 24/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 24/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง


        //initProductTable();
        $('#product_id').val('');
        $("#frm_date").datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function(selectedDate) {
                $("#to_date").datepicker("option", "minDate", selectedDate);
            }
        }).on('changeDate', function(ev) {
            //$('#sDate1').text($('#datepicker').data('date'));
            //$('#frm_date').datepicker('hide');
        }).keypress(function(event) {
            event.preventDefault();
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });
        $("#to_date").datepicker({onRender: function(date) {
                
//                var edate = $('#frm_date').val();
//                //var rdate=$('#receive_date').val();
//                from = edate.split("/");
//                f = new Date(from[2], from[1] - 1, from[0]);
//                //alert(f);
//                return edate.valueOf() < f.valueOf() ? 'disabled' : '';
            }}).on('changeDate', function(ev) {
            //$('#sDate1').text($('#datepicker').data('date'));
            //$('#to_date').datepicker('hide');
        }).keypress(function(event) {
            event.preventDefault();
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });


        $('#frmStockMove').submit(function(){
            find_data();
            return false;
        });
                
                

        $('#search').click(function() {
            find_data();
        });
        

        $('#clear').click(function() {
            $('#product').val('');
            $('#frm_date').val('');
            $('#to_date').val('');
            $('#product_id').val('');
            $('#doc_value').val('');
            $('#report').html('Please click search');

            // Add By Akkarapol, 27/09/2013, เพิ่ม Class required ให้กับช่อง from date และ to date 
            $('#frm_date').addClass('required');
            $('#to_date').addClass('required');
            // END  Add By Akkarapol, 27/09/2013, เพิ่ม Class required ให้กับช่อง from date และ to date 
            
            //ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report 
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
        });

        $("#product").click(function() {
            $('#product').val('');
            $('#product_id').val('');
        });
        /*
         $("#product").keyup(function(){
         $.post('<?php echo site_url(); ?>/report/showProductList',{text_search:$(this).val()},function (data,val){
         $('#suggestions').show();
         $('#autoSuggestionsList').html(data,val);
         });
         });
         
         
         */
        
        
        $('#show_condition').change(function() {
            var show_by = $( "#show_condition option:selected" ).val();  //Edit by Por 2014-04-23 change radio button to dropdown list
            if(show_by != 'show_item'){
               $('#product').removeClass('req_product');
            }else{
                $('#product').addClass('req_product');
            }
        });
        
        
    });


    function find_data(){

        var statusisValidateForm = validateForm();
        
        var show_by = $( "#show_condition option:selected" ).val();  //Edit by Por 2014-04-23 change radio button to dropdown list
        
        if(show_by == 'show_item' && $('#product').val() == ""){
            statusisValidateForm = false;
        }
        
        if (statusisValidateForm === true) {
            var renter_id = $('#renter_id').val();
            //var show_by = $("input[name='conditionDetail']:checked").val();
            
            
            var product = $('#product').val();
            var fdate = $('#frm_date').val();
            var tdate = $('#to_date').val();
            var product_id = $('#product_id').val();
            var doc_type = $('#doc_type').val(); //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document
            var doc_value = $('#doc_value').val(); //ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาด้วย document
            //validateForm();
            if (renter_id == "" && product == "" && fdate == "" && tdate == "") {
                alert('Please select "Renter" or "Product" or "Start Date" or "To Date"');
                $('#report').html('Please select "Renter" or "Product" or "Start Date" or "To Date"');
            }
            else {

                $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url(); ?>/report/getStockMovement', // in here you should put your query 
                    data: 'renter_id=' + renter_id + '&product=' + product + '&product_id=' + product_id + '&fdate=' + fdate + '&tdate=' + tdate // here you pass your id via ajax 
                    + '&doc_type=' + doc_type + '&doc_value='+ doc_value//ADD BY POR 2013-10-28 ส่งค่าเพิ่มเติม เนื่องจากเพิ่มให้ค้นหาด้วย document ได้
                    + '&show_by=' + show_by , //add by kik for show by : 20140409 
                    success: function(data)
                    {
                        //alert(data);
                        $("#report").html(data);

                        //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                        $("#pdfshow").show();
                        $("#excelshow").show();
                        //END ADD
                    }
                });
            }
        } // close if validate ok
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
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
    function fill(id, code, name) {
        $('#product').val(code + ' ' + name);
        $('#product_id').val(id);
        setTimeout("$('#suggestions').hide();", 500);
    }

    function validateForm() {
       
//        validate engine 
        var status;
        $("form").each(function() {
            $(this).validate();
            $(this).valid();
            status = $(this).valid();
        });
        $('label.error').hide();
        return status;

        //end of validate engine
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
    
    .req_product{border: 1px solid #FF0000 !important;}
</style>
<TR class="content" style='height:100%' >
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmStockMove" name="frmStockMove" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria</legend>
                <table cellpadding="1" cellspacing="1" border="0" style="width:98%; margin:0px auto;" >
                    <tr>
                        <td>Renter :  </td>
                        <td ><?php echo form_dropdown('renter_id', $data['renter_list'], $data['renter_id'], 'id="renter_id" class="required" '); ?></td>
                        <td>Show by : </td>
                        <td>
                            <select name="conditionDetail" id="show_condition">
                                <option value="show_item" selected>By item</option>
                                <option value="show_total">By total</option>
                                <option value="show_movement">By movement</option>
                            </select>
                            <!-- Edit by Por 2014-04-23 Edit radio to list-->
<!--                             <label class="radio">
                            	<input type="radio" name="conditionDetail" id="show_item" value="show_item" checked="checked"> By item
                            </label>
                            <label class="radio">
                                  <input type="radio" name="conditionDetail" id="show_total" value="show_total" > By total
                                  
                            </label> 
                            <label class="radio">  
                                  <input type="radio" name="conditionDetail" id="show_movement" value="show_movement" > By movement
                            </label> -->
                        </td>
                    </tr>
                    <tr >
                        <td>
                            <?php echo _lang('product_code'); ?> :
                        </td>
                        <td>
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" style="float:left; height: 27px; padding: 0px; width: 250px;">
                                <div style="position: relative;">
                                                <?php echo form_input("product", "", "id='product' class='req_product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 250px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                                <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 250px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                </div>
                                        
                            </table>
                            <input type="hidden" id="product_id" name="product_code" />
                            
                        </td>
                        <!--ADD BY POR 2013-10-28 เพิ่มเติมให้สามารถค้นหาตาม document ได้-->
                        <td>
                            Document Field :
                        </td>
                        <td colspan="2">
                            <select name="doc_type" id="doc_type">
                                <option value="Document_No">Document No.</option>
                                <option value="Doc_Refer_Ext">Refer External No.</option>
                                <option value="Doc_Refer_Int">Refer Internal No.</option>
                                <!--<option value="Doc_Refer_Inv">Invoice No.</option>
                                <option value="Doc_Refer_CE">Customs Entry</option>
                                <option value="Doc_Refer_BL">BL No.</option>-->
                            </select>
                            <input id="doc_value" class="input-small" type="text" placeholder="VALUE" value="" name="doc_value">
                        </td>
                        <!--END ADD-->
                    </tr>
                    <tr>
                        <td>
                            From Date :
                        </td>
                        <td>
                            <input type="text" class="required" id="frm_date" name="frm_date" value="<?php echo date("d/m/Y"); ?>" >
                        </td>
                        <td>
                            To Date :
                        </td>
                        <td>
                            <input type="text" class="required" id="to_date" name="to_date" value="<?php echo date("d/m/Y"); ?>" >
                        </td>
                        <td align="right">
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                        </td>
                    </tr>
                </table>

                <!--</fieldset>-->

                <input type="hidden" name="queryText" id="queryText" value=""/>
                <input type="hidden" name="search_param" id="search_param" value=""/>
            </fieldset>
        </form>    

        <fieldset>
            <legend>Search Result</legend>
            <div id="report" style="margin:10px">
                Please click search
            </div>
        </fieldset>

    </TD>
</TR>