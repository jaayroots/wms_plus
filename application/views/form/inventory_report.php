<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    //$('#myModal').modal('toggle');
    $(document).ready(function() {
        
        var show_auto = '<?php echo $show_auto?>';
//        alert(show_auto);exit();
        if(show_auto == 'Y'){
//            alert(show_auto);exit();
            var renter_id = '<?php echo $renter_id?>';
            var product_id = '<?php echo $product_id?>';
            var as_date = '<?php echo $tdate?>';
            var product_name = '<?php echo $product_name?>';
            
            $('#renter_id').val(renter_id);
            $('#product').val(product_name);
            $('#as_date').val(as_date);
            $('#product_id').val(product_id);
            
            show_data();
        }
    
    
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
               $('#product_id').val( ui.item.value );
               $('#product').val( ui.item.label );
             return false;
           },
           close: function(){
               $('#highlight_productCode').attr("placeholder",'');
           }
       });

        // Add By Akkarapol, 25/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 25/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง


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

            // Add By Akkarapol, 25/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');
            }
            // Add By Akkarapol, 25/09/2013, เพิ่ม การเช็คว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });


        $('#clear').click(function() {
            $('#product').val('');
            $('#as_date').val('');
            $('#status_id').val('');
            $('#product_id').val('');
            $('#report').html('Please click search');

            // Add By Akkarapol, 25/09/2013, เวลากดปุ่ม Clear ค่า จะให้ช่อง '#as_date' นั้น ใส่ขอบแดงด้วย
            if ($('#as_date').val() != '') {
                $('#as_date').removeClass('required');
            } else {
                $('#as_date').addClass('required');
            }
            // Add By Akkarapol, 25/09/2013, เวลากดปุ่ม Clear ค่า จะให้ช่อง '#as_date' นั้น ใส่ขอบแดงด้วย

            //ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report 
            $("#pdfshow").hide();
            $("#excelshow").hide();
            //END ADD
        });

        $('#frmPreDispatch').submit(function(){
            show_data();
            return false;
        });

        $("#product").click(function() {
            $('#product').val('');
            $('#product_id').val('');
            $('#highlight_productCode').attr("placeholder",'');
        });
        
    });

// Comment By Akkarapol, 05/11/2013, คอมเม้นต์ทิ้ง เพราะหน้านี้ไม่ต้องประกาศ .dataTable แต่ไปทำในไฟล์ view/report/inventory_report.php อยู่แล้ว ซึ่งไฟล์นั้นคือไฟล์ที่ใช้งาน dataTable ไม่ใช่ ไฟล์นี้ ไฟล์นี้แค่เป็นโครงให้เท่านั้น
//    function inittable() {
//        var oTable = $('#tbreport').dataTable({
//            "bJQueryUI": true,
//            "bSort": false,
//            //"bAutoWidth": false,
//            "sPaginationType": "full_numbers",
//            //"sScrollY": "900px",
//            "sScrollX": "100%",
//            //"sScrollXInner": "200%",
//            "bScrollCollapse": true,
//        });
//
////        new FixedColumns(oTable, {
//////            "iLeftColumns": 3
//////                    , "sLeftWidth": 'relative'
//////                    , "iLeftWidth": 40
////                    //,"iRightColumns": 1
////                    //,"iRightWidth": 7
////                    //,"sRightWidth": 'relative'
////
////        });
//    }
// END Comment By Akkarapol, 05/11/2013, คอมเม้นต์ทิ้ง เพราะหน้านี้ไม่ต้องประกาศ .dataTable แต่ไปทำในไฟล์ view/report/inventory_report.php อยู่แล้ว ซึ่งไฟล์นั้นคือไฟล์ที่ใช้งาน dataTable ไม่ใช่ ไฟล์นี้ ไฟล์นี้แค่เป็นโครงให้เท่านั้น

    function show_data(){
        
        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {
            var as_date = $('#as_date').val();
            var renter_id = $('#renter_id').val();
            var status_id = $('#status_id').val();
            var product_id = $('#product_id').val();

            if (as_date == "") {
                alert('Please select "Date"');
                $('#report').html('Please select "Date"');
            } else {
                $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');
                $.ajax({
                    type: 'post',
                    url: '<?php echo site_url(); ?>/report/showInventoryReport',
                    data: 'renter_id=' + renter_id + '&status_id=' + status_id + '&product_id='
                            + product_id + '&as_date=' + as_date,
                    success: function(data)
                    {
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
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>  
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>Renter</td>
                        <td><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'id="renter_id" class="required" '); ?></td>
                        <td>Product Status</td>
                        <td><?php echo form_dropdown('status_id', $status_list, '', 'id="status_id" '); ?></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td valign="top"> <?php echo _lang('product_code') ?></td>
                        <td>
                            <table id="table_of_productCode" cellspacing="0" cellpadding="0" border="5" style="float:left; height: 27px; padding: 0px; width: 470px;">
                                <div style="position: relative;">
                                    <?php echo form_input("product", "", "id='product' placeholder='"._lang('product_code')."' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 420px;  background-color: transparent; position: absolute; z-index: 6; left: 0px; outline: none; background-position: initial initial; background-repeat: initial initial;' "); ?>
                                    <?php echo form_input("highlight_productCode", "", "id='highlight_productCode' autocomplete='off' style='border: none; padding: 5px; margin: 0px; height: auto; width: 420px; position: absolute; z-index: 1; -webkit-text-fill-color: silver; color: silver; left: 0px;' "); ?> 
                                </div>
                            </table>
<!--                            <input type="text"  id="product" name="product" autocomplete="off" placeholder="Product Code" onkeyup="lookup('product', this.value);"  onblur="fill('', '', '');"; />
                            <div class="suggestionsBox" id="suggestions" style="display:none;">
                                <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
                            </div>
                            --><input type="hidden" id="product_id" name="product_id" />
                        </td>
                        <td>Date</td>
                        <td> <input type="text" placeholder="Date Format" id="as_date" name="as_date" value="<?php echo date("d/m/Y"); ?>" class="required" ></td>
                        <td>
                            <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" onclick='show_data();' />
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
            <div id="report" style="margin:10px">Please click search</div>
        </fieldset>
    </TD>
</TR>