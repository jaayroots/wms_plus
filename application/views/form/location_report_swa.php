
<SCRIPT>
    var show_per_page = 'All';
    
    $(document).ready(function() {
        //ADD BY POR 201-05-07 ตรวจสอบว่าค่าเริ่มต้นคืออะไร ถ้าเป็น show all จะให้ซ่อนปุ่น radio
       init_criteria_disabled();

                
       $("input[name=conditionDetail]").change(function(){
        	init_criteria_disabled();
       });
       
       $('#search').click(function() {
            find_data();
        });
        
        $( "#txtkeyword_date" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function( selectedDate ) {
                $( "#txtkeyword_date" ).datepicker( "option", "minDate", selectedDate );
            }
        }).on('changeDate', function(ev){
              
        });
        
        $( "#txtfrom_date" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function( selectedDate ) {
                $( "#txtfrom_date" ).datepicker( "option", "minDate", selectedDate );
            }
        }).on('changeDate', function(ev){
              
        });
        
        $( "#txtto_date" ).datepicker({
            defaultDate: "+1d",
            changeMonth: true,
            numberOfMonths: 1,
            onClose: function( selectedDate ) {
                $( "#txtto_date" ).datepicker( "option", "minDate", selectedDate );
            }
        }).on('changeDate', function(ev){
              
        });
           
                
       $('#clear').click(function() {
//            alert('test');
                $('#txtkeyword').val('');
                $('#txtfrom').val('');
                $('#txtto').val('');
                
                $('#txtkeyword_date').val('');
                $('#txtfrom_date').val('');
                $('#txtto_date').val('');
                $('#booking').val('');
                $('#inv').val('');
                
                $('#show_condition').val('cond_all');
                $('#report').html('Please click search');

                //ADD BY POR 2013-11-05 กำหนดให้ซ่อนปุ่ม print report 
                $("#pdfshow").hide();
                $("#excelshow").hide();
                //END ADD
       });
       
       //ADD BY POR 2014-05-07 auto complete for keyword
       $("#txtkeyword").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
           },
           source: function( request, response ) {        
             $.ajax({
                 url: "<?php echo site_url(); ?>/report/ajax_autocomplete_location_swa",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: $('#txtkeyword').val(),
                   show_condition: $('#show_condition').val()
                 },
                 success: function( val, data ) {
                    //ADD BY POR 2014-05-07
                    //กรณีคนละเงื่อนไข จะต้องแสดงผลคนละแบบ
                    var labels;
                    var values;
                    var show_condition = $('#show_condition').val();
                    //END ADD
                    
                    if(val != null){
                        response( $.map( val, function( item ) {
                            labels = item.value_select;
                            values = item.value_select;
                       
                            return {
                               label: labels,
                               value: values
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
               $('#txtkeyword').val( ui.item.value );
             return false;
           },
           close: function(){
               $('#highlight_productCode').attr("placeholder",'');
           }
       });
       
       $("#txtfrom").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
           },
           source: function( request, response ) {        
             $.ajax({
                 url: "<?php echo site_url(); ?>/report/ajax_autocomplete_location_swa",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: $('#txtfrom').val(),
                   show_condition: $('#show_condition').val()
                 },
                 success: function( val, data ) {
                    //ADD BY POR 2014-05-07
                    //กรณีคนละเงื่อนไข จะต้องแสดงผลคนละแบบ
                    var labels;
                    var values;
                    var show_condition = $('#show_condition').val();
                    //END ADD
                    
                    if(val != null){
                        response( $.map( val, function( item ) {
                            labels = item.value_select;
                            values = item.value_select;
                       
                            return {
                               label: labels,
                               value: values
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
               $('#txtfrom').val( ui.item.value );
             return false;
           },
           close: function(){
               $('#highlight_productCode').attr("placeholder",'');
           }
       });
       
       $("#txtto").autocomplete({
           minLength: 0,
           search: function( event, ui ) {
               $('#highlight_productCode').attr("placeholder",'');
           },
           source: function( request, response ) {        
             $.ajax({
                 url: "<?php echo site_url(); ?>/report/ajax_autocomplete_location_swa",
                 dataType: "json",
                 type:'post',
                 data: {
                   text_search: $('#txtto').val(),
                   show_condition: $('#show_condition').val()
                 },
                 success: function( val, data ) {
                    //ADD BY POR 2014-05-07
                    //กรณีคนละเงื่อนไข จะต้องแสดงผลคนละแบบ
                    var labels;
                    var values;
                    var show_condition = $('#show_condition').val();
                    //END ADD
                    
                    if(val != null){
                        response( $.map( val, function( item ) {
                            labels = item.value_select;
                            values = item.value_select;
                       
                            return {
                               label: labels,
                               value: values
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
               $('#txtto').val( ui.item.value );
             return false;
           },
           close: function(){
               $('#highlight_productCode').attr("placeholder",'');
           }
       });
       //END ADD POR

        // pagination   
        $('#pagination a').live('click', function() {
            var $this = $(this);
//            alert($(this));exit();
//            alert($this.data('page'));
//            alert($this.data('all'));
//            exit();
            return show_data_of_page($this.data('page'),$('#paginate_per_page').val());
        });
        
       
    });
        
    function change_show_per_page(page,ipp){
        show_data_of_page(page,ipp);
    }
    
   function show_data_of_page(page,ipp){
//            show_per_page = $('#paginate_per_page').val();
            var show_per_page = ipp;
//            alert(page);
//            alert(ipp);
            var renter_id = $('#renter_id').val();
            var sort_by = $( "#sortCond option:selected" ).val();  
            var show_cond = $( "#show_condition option:selected" ).val();  
            var cond_deatil = $("input[name='conditionDetail']:checked").val();
            var txt_keyword = $("#txtkeyword").val();
            var inv = $("#inv").val();
            // var booking = $("#booking").val();
            var booking = $("#booking:checked").val() ? 0 : 1;
            var txt_from = $("#txtfrom").val();
            var txt_to = $("#txtto").val();
            
            if(show_cond == 'cond_rcv_date'){
                txt_keyword = $("#txtkeyword_date").val();
                txt_from = $("#txtfrom_date").val();
                txt_to = $("#txtto_date").val();
            }
            

            ipp = show_per_page; // I am returning 30 results per page, change to what you want
//            alert(ipp);exit();
            $.ajax({
                type: 'get',
                url: '<?php echo site_url(); ?>/report/get_location_swa', // in here you should put your query 
                data: 'renter_id=' + renter_id + 
                        '&sort_by=' + sort_by + 
                        '&show_cond=' + show_cond + 
                        '&cond_deatil=' + cond_deatil + 
                        '&txt_keyword=' + txt_keyword + 
                        '&booking=' + booking + 
                        '&inv=' + inv + 
                        '&txt_from='+ txt_from + 
                        '&txt_to=' + txt_to + 
                        '&page=' + page + 
                        '&ipp=' + ipp + 
                        '&w=' + $('#formLocationReport').width(),
                success: function(response) { 
                    $("#report").html(response);
                    
                    //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                    $("#pdfshow").show();
                    $("#excelshow").show();
                    //END ADD
                    // pagination
//                    $('#pagination').html(pagination);             
                },
                error: function() {
                    alert('An error occurred');
                }
            });

            return false;
   }
   
   
   function find_data(){
//       alert('test');

        var renter_id = $('#renter_id').val();
        var sort_by = $( "#sortCond option:selected" ).val();  
        var show_cond = $( "#show_condition option:selected" ).val();  
        var cond_deatil = $("input[name='conditionDetail']:checked").val();
        
        //ADD BY POR 2014-05-16 กรณีเป็น show all ไม่ต้อง validate
        if(show_cond!='cond_all'){
            if(show_cond=='cond_rcv_date'){
                if(cond_deatil=='search_general'){
                    if($("#txtkeyword_date").val()==''){
                        alert("Please input date for search");
                        return false;
                    }
                }else{
                    if($("#txtfrom_date").val()==''){
                        alert("Please input 'From' for search");
                        return false;
                    }
                    
                    if($("#txtto_date").val()==''){
                        alert("Please input 'To' for search");
                        return false;
                    }
                    
                    var fDate = new Date($("#txtfrom_date").val());
                    var lDate = new Date($("#txtto_date").val());
                    fDate = Date.parse(fDate);
                    lDate = Date.parse(lDate);

                    if(fDate > lDate) {
                        alert("'Date to' must be greater than 'Date from'");
                        return false;
                    }

                }
            }else{
                if(cond_deatil=='search_general'){
                    if($("#txtkeyword").val()==''){
                        alert("Please input data for search");
                        $("#txtkeyword").focus();
                        return false;
                    }
                }else{
                    if($("#txtfrom").val()==''){
                        alert("Please input 'From' for search");
                        $("#txtfrom").focus();
                        return false;
                    }
                    
                    if($("#txtto").val()==''){
                        alert("Please input 'To' for search");
                        $("#txtto").focus();
                        return false;
                    }
                    
                    if($("#txtto").val() < $("#txtfrom").val()){
                        alert("'Data to' must be greater than 'Data from'");
                        $("#txtto").focus();
                        return false;
                    }
                }
            }
                    
        }
        //END ADD
        
        var txt_keyword = $("#txtkeyword").val();
        var inv = $("#inv").val();
        // var booking = $("#booking").val();
        var booking = $("#booking:checked").val() ? 1 : 0;
        var txt_from = $("#txtfrom").val();
        var txt_to = $("#txtto").val();

        if(show_cond == 'cond_rcv_date'){
            txt_keyword = $("#txtkeyword_date").val();
            txt_from = $("#txtfrom_date").val();
            txt_to = $("#txtto_date").val();
        }

        $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

        $.ajax({
            type: 'get',
            url: '<?php echo site_url(); ?>/report/get_location_swa', // in here you should put your query 
            data: 'renter_id=' + renter_id + 
                    '&sort_by=' + sort_by + 
                    '&show_cond=' + show_cond + 
                    '&cond_deatil=' + cond_deatil + 
                    '&txt_keyword=' + txt_keyword + 
                    '&booking=' + booking + 
                    '&inv=' + inv + 
                    '&txt_from='+ txt_from + 
                    '&txt_to=' + txt_to + 
                    '&page=' + 1 + 
                    '&ipp=' + show_per_page + 
                    '&w=' + $('#formLocationReport').width(),
            success: function(data)
            {
//                    alert(data);exit();
                $("#report").html(data);
                // pagination
//                    $('#pagination').html(pagination);         

                //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                $("#pdfshow").show();
                $("#excelshow").show();
                //END ADD
            }
        });
    } 
    
    function exportFileByPass(file_type){
//    alert('xxx');exit();
            $('#form_report_export input[name=renter_id]').val($('#renter_id').val());
            $('#form_report_export input[name=sort_by]').val($( "#sortCond option:selected" ).val());
            $('#form_report_export input[name=show_cond]').val($( "#show_condition option:selected" ).val());
            $('#form_report_export input[name=cond_deatil]').val($("input[name='conditionDetail']:checked").val());
            
            if($( "#show_condition option:selected" ).val() == 'cond_rcv_date'){
                $('#form_report_export input[name=txt_keyword]').val($("#txtkeyword_date").val());
                $('#form_report_export input[name=booking]').val($("#booking").val());
                $('#form_report_export input[name=txt_from]').val($("#txtfrom_date").val());
                $('#form_report_export input[name=txt_to]').val($("#txtto_date").val());
            }else{
                $('#form_report_export input[name=txt_keyword]').val($("#txtkeyword").val());
                $('#form_report_export input[name=booking]').val($("#booking").val());
                $('#form_report_export input[name=txt_from]').val($("#txtfrom").val());
                $('#form_report_export input[name=txt_to]').val($("#txtto").val());
            }
                
            if (file_type == 'EXCEL') {
                $("#form_report_export").attr('action', "<?php echo site_url(); ?>" + "/report/exportLocationToExcel_swa")
            } else {
                $("#form_report_export").attr('action', "<?php echo site_url(); ?>" + "/report/export_location_swa_ToPDF")
            }
            $("#form_report_export").submit();
            
    } 
    
   function init_criteria_disabled()
    {
        var condition= $("#show_condition").val(); //condition select
        var s_type = $('[name="conditionDetail"]:checked').val();
        
        //Edit By Por
        $("#txtkeyword").val('');
        $("#txtfrom").val('');
        $("#txtto").val('');
        
        $("#txtkeyword_date").val('');
        $("#txtfrom_date").val('');
        $("#txtto_date").val('');
        
        if(s_type == 'search_general'){ //กรณีค้นหาด้วย keywords
            if(condition == 'cond_all'){
                $('input:radio[name=conditionDetail]').attr('disabled', 'true');
                $("#txtkeyword").show();
                $("#txtkeyword").prop('disabled', true);

                $("#txtfrom").prop('disabled', true);
                $("#txtto").prop('disabled', true);
               
                //===========================Date
                $("#txtkeyword_date").hide();
                
                $("#txtfrom_date").hide();
                $("#txtto_date").hide();
                
            }else if(condition=='cond_rcv_date'){
                $('input:radio[name=conditionDetail]').removeAttr('disabled');
                $("#txtkeyword").hide();
                
                $("#txtfrom").hide();
                $("#txtto").hide();
                
                //===========================Date
                $("#txtkeyword_date").show();
                $("#txtkeyword_date").prop('disabled', false);
                
                $("#txtfrom_date").show();
                $("#txtto_date").show();
                $("#txtfrom_date").prop('disabled', true);
                $("#txtto_date").prop('disabled', true);
                
            }else{
                $('input:radio[name=conditionDetail]').removeAttr('disabled');
                $("#txtkeyword").show();
                $("#txtkeyword").prop('disabled', false);
                
                $("#txtfrom").show();
                $("#txtto").show();
                $("#txtfrom").prop('disabled', true);
                $("#txtto").prop('disabled', true);
                
                //===========================Date
                $("#txtkeyword_date").hide();
                
                $("#txtfrom_date").hide();
                $("#txtto_date").hide();
                
                $("#txtkeyword").focus();
            }
            
        }else if (s_type == 'search_range'){ //กรณีค้นหาแบบช่วง
            if(condition == 'cond_all'){ //ถ้าเลือกแบบ show all จะไม่สามารถระบุเงื่อนไขใดๆได้
                $('input:radio[name=conditionDetail]').attr('disabled', 'true'); 
                
                $("#txtkeyword").show();
                $("#txtkeyword").prop('disabled', true);
                
                $("#txtfrom").show();
                $("#txtto").show();
                $("#txtfrom").prop('disabled',true);
                $("#txtto").prop('disabled', true);
                
                //===========================Date
                $("#txtkeyword_date").hide();
                
                $("#txtfrom_date").hide();
                $("#txtto_date").hide();
                
            }else if(condition == 'cond_rcv_date'){ //กรณีเลือกค้นด้วยวันที่
                $('input:radio[name=conditionDetail]').removeAttr('disabled'); //สามารถเลือกเงื่อนไขได้ว่าจะเลือกแบบ keywords หรือช่วง
                
                $("#txtkeyword").hide();
                
                $("#txtfrom").hide();
                $("#txtto").hide();
                
                //===========================Date
                $("#txtkeyword_date").show();
                $("#txtkeyword_date").prop('disabled', true);

                $("#txtfrom_date").show();
                $("#txtto_date").show();
                $("#txtfrom_date").prop('disabled', false);
                $("#txtto_date").prop('disabled', false);
            }else{
                $('input:radio[name=conditionDetail]').removeAttr('disabled'); //สามารถเลือกเงื่อนไขได้ว่าจะเลือกแบบ keywords หรือช่วง
                
                $("#txtkeyword").show();
                $("#txtkeyword").prop('disabled', true);
                
                $("#txtfrom").show();
                $("#txtto").show();
                $("#txtfrom").prop('disabled', false);
                $("#txtto").prop('disabled', false);
                
                //===========================Date
                $("#txtkeyword_date").hide();
                
                $("#txtfrom_date").hide();
                $("#txtto_date").hide();
                
                $("#txtfrom").focus();
            }
        }
        //End Edit
    }
    
</script>

<style>
    
#pagination { overflow: hidden; margin-bottom: 10px; text-align: center; }
#pagination a { display: inline-block; padding: 3px 5px; font-size: 14px; color: #333; border-radius: 3px; text-shadow: 0 0 1px #fff;  border: 1px solid #ccc;

    background: #ffffff;
    background: -moz-linear-gradient(top,  #ffffff 0%, #f6f6f6 47%, #ededed 100%);
    background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,#ffffff), color-stop(47%,#f6f6f6), color-stop(100%,#ededed));
    background: -webkit-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    background: -o-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    background: -ms-linear-gradient(top,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    background: linear-gradient(to bottom,  #ffffff 0%,#f6f6f6 47%,#ededed 100%);
    filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#ffffff', endColorstr='#ededed',GradientType=0 );
}
#pagination a:hover { border: 1px solid #333; }
#pagination a.current { color: #f00; }

</style>

<TR class="content" style='height:100%' valign="top">
    <TD>
        <form  method="post" action="" target="_blank" id="form_report_export">
            <input type="hidden" name="renter_id" />
            <input type="hidden" name="sort_by"  />
            <input type="hidden" name="show_cond"  />
            <input type="hidden" name="cond_deatil" />
            <input type="hidden" name="txt_keyword"  />
            <input type="hidden" name="booking" />
            <input type="hidden" name="inv" />
            <input type="hidden" name="txt_from"/>
            <input type="hidden" name="txt_to" />
            <input type="hidden" name="txt_keyword_date" />
            <input type="hidden" name="txt_from_date"/>
            <input type="hidden" name="txt_to_date" />
        </form>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="formLocationReport" name="formLocationReport" >
            <fieldset style="margin:0px auto;">
                <legend>Search Criteria </legend>

                 <table cellpadding="1" cellspacing="1" style="width:98%; margin:0px auto;" >
                    <tr>
                        <td>Renter: </td>
                        <td ><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'id="renter_id" class="required" '); ?></td>
                        <td style="text-align:right;">
                            Sort by : 
                            
                        </td>
                        <td colspan='2'>
                            <select name="sortCond" id="sortCond" class ='enabled'>
                                <option value="sort_prod" selected>Sort by Product</option>
                                <option value="sort_loc" >Sort by Location</option>
                            </select>
                        </td>
                    </tr>
                    
                    
                    <tr valign="middle">
                        <td >
                             Condition : 
                        </td>
                        <td >
                            <select name="show_condition" id="show_condition" class ='enabled' onchange="init_criteria_disabled();">
                                <option value="cond_all" >Show All</option>
                                <option value="cond_prod_code" selected><?php echo _lang('product_code'); ?></option>
<!--                                <option value="cond_prod_name">Product Name</option>-->
                                <option value="cond_lot_sel"><?php echo _lang('lot_serial'); ?></option>
                                <option value="cond_doc_ext" ><?php echo _lang('doc_refer_ext'); ?></option>
                                <option value="cond_rcv_date"><?php echo _lang('receive_date'); ?></option>
                                <option value="cond_loc_no"><?php echo _lang('location'); ?></option>
                            </select>
                        </td>
                        <td style="text-align:right;">
                            <label class="radio">
                            	<input type="radio" name="conditionDetail" id="search_general" value="search_general" checked="checked" >  Keywords :  
                            </label>
                        </td>
                        <td>
                            <?php echo form_input("txtkeyword", "", "id='txtkeyword' placeholder='keywords...' style='width: 207px;'  "); ?>
                            <?php echo form_input("txtkeyword_date", "", "id='txtkeyword_date' placeholder='Date...' style='width: 207px;'  "); ?>
                        </td>
                        
                        <td>
                            <label class="radio">
                            	<input type="radio" name="conditionDetail" id="sort_loc" value="search_range"> Range
                                From :</label>
                                    <?php echo form_input("txtfrom", "", "id='txtfrom' class=' number' placeholder='From' style='width: 200px;'  "); ?>
                                    <?php echo form_input("txtfrom_date", "", "id='txtfrom_date' class=' number' placeholder='Date From' style='width: 200px;'  "); ?>
<!--                                    <input type="text" name="txtfrom" id="txtfrom" placeholder="From" class=" number" style='width:200px;'>-->
                                To :
                                     <input type="text" name="txtto" id="txtto" placeholder="To" class=" number" style='width:200px;'> 
                                     <input type="text" name="txtto_date" id="txtto_date" placeholder="Date To" class=" number" style='width:200px;'> 
                            
                        </td>
                    </tr>
                    
                    <tr>          <td >
                             Type : 
                        </td>

                        <td style="text-align:left;">                            	
                         
                        <!-- <input type="checkbox" id="booking" name="booking"> -->

                        <select id="inv">
                        <option value="inv_book">Show All</option>
                        <option value="inv">Inventory</option>
                        <option value="book">RSV</option>

                        </select>
                        </td>
                        <td>
                        </td>
                        <td style="text-align:right;" colspan='3'>
                        <input type="submit" style="display: none;">
                            <input type="button" name="search" value="Search" id="search" class="button dark_blue" />
                            <input type="button" name="clear" value="Clear" id="clear" class="button dark_blue" />
                             <image style='cursor: pointer;' src="<?php echo base_url(); ?>css/images/icons/excel-icon.png" onclick="exportFileByPass('EXCEL')" alt='Export To Excel' title='Export To Excel'/>

   
                        </td> 
                       
                        <td>
                        </td>
                        
                    </tr>
                        
                </table>
                

            </fieldset>

            <input type="hidden" name="search_param" id="search_param" value=""/>
        </form>    

        <fieldset>
            <legend>Search Result </legend>
            <div id="report" style="margin:10px;text-align: center;">
                Please click search
            </div>
        </fieldset>

    </TD>
</TR>