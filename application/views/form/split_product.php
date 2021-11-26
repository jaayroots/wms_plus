<?php // Create by Ton! 20130617             ?>
<SCRIPT>
    var splitNo = 1;
    
    $(document).ready(function(){
        getProdData();        
    });
       
    function getProdData(){
        // <<<<<------ START COMMENT OUT by Ton! 20130903 ------>>>>>
        //        var orderId = $('#order_Id').val();
        //        var prodCode = $('#ProductCode').val();

        //        $.post("<?php //echo site_url() ?>/product_info/getDataProd", {orderId:orderId ,prodCode:prodCode },function(data){
        // <<<<<------ END COMMENT OUT by Ton! 20130903 ------>>>>>
        
        // <<<<<------ START ADD by Ton! 20130903 ------>>>>>
        var itemId = $('#item_Id').val();
        $.post("<?php echo site_url() ?>/product_info/getDataProd", {itemId:itemId},function(data){
            // <<<<<------ END ADD by Ton! 20130903 ------>>>>>
            if (data=="") {
                AddItem();
            }else{
                $.each(data, function(i, obj) {
                    var prodMFD="";
                    if (!obj.dayMFD||!obj.monthMFD||!obj.yearMFD) {
                        prodMFD="";     
                    }else{
                        
//                     fix defect 511 ,edit format date and can do input date manual and change format to dd/mm/yyyy : by kik : 2013-12-03
//                     prodMFD=obj.dayMFD+"/"+obj.monthMFD+"/"+obj.yearMFD;     // comment old code 
    
                        var month = obj.monthMFD.toString();
                        month = month.length > 1 ? month : '0' + month;
                        var day = obj.dayMFD.toString();
                        day = day.length > 1 ? day : '0' + day;
                        prodMFD=day+"/"+month+"/"+obj.yearMFD;
//                        end fix defect 511 : by kik : 2013-12-03
                    }
                    
                    var prodEXP="";
                    //if (!obj.dayMFD||!obj.monthMFD||!obj.yearMFD) { //COMMENT BY POR 2013-12-11 แก้ไขให้แสดง EXP ให้ถูกต้อง
                    if (!obj.dayEXP||!obj.monthEXP||!obj.yearEXP) { //ADD BY POR 2013-12-11  แก้ไขให้แสดง EXP ให้ถูกต้อง
                        prodEXP="";     
                    }else{
//                        fix defect 511 ,edit format date and can do input date manual and change format to dd/mm/yyyy : by kik : 2013-12-03
//                        prodEXP=obj.dayEXP+"/"+obj.monthEXP+"/"+obj.yearEXP;

                        //var month = obj.monthMFD.toString(); //COMMENT BY POR 2013-12-11 แก้ไขให้แสดง EXP ให้ถูกต้อง
                        var month = obj.monthEXP.toString(); //ADD BY POR 2013-12-11  แก้ไขให้แสดง EXP ให้ถูกต้อง
                        month = month.length > 1 ? month : '0' + month;
                        //var day = obj.dayMFD.toString(); //COMMENT BY POR 2013-12-11 แก้ไขให้แสดง EXP ให้ถูกต้อง
                        var day = obj.dayEXP.toString(); //ADD BY POR 2013-12-11  แก้ไขให้แสดง EXP ให้ถูกต้อง
                        day = day.length > 1 ? day : '0' + day;
                        //prodEXP=day+"/"+month+"/"+obj.yearMFD; //COMMENT BY POR 2013-12-11 แก้ไขให้แสดง EXP ให้ถูกต้อง
                        prodEXP=day+"/"+month+"/"+obj.yearEXP; //ADD BY POR 2013-12-11  แก้ไขให้แสดง EXP ให้ถูกต้อง
//                        end fix defect 511 : by kik : 2013-12-03
                        
                        
                    }
                    
                    var html="<TABLE width='100%' id=\'infoProd"+splitNo+"'\">";
                    html+="<TR>";
                    html+="<TD  class='split_title'  for=\"lot[]\"><?php echo _lang('lot'); ?> </TD>";
                    html+="<TD  class='split_box'  ><INPUT TYPE=\"text\" ID=\"lot[]\" NAME=\"lot[]\" value=\""+obj.Product_Lot+"\"></TD>";
                    html+="<TD  class='split_title'  ><?php echo _lang('lot_qty'); ?> </TD>";
                    //                    html+="<TD><INPUT TYPE=\"text\" ID=\"lot_qty[]\" NAME=\"lot_qty[]\" for=\"lot[]\" value=\""+obj.Reserv_Qty+"\" onblur=\"calQty('')\"></TD>";
                    
                    html+="<TD  class='split_box'  ><INPUT TYPE=\"text\" ID=\"lot_qty"+splitNo+"\" NAME=\"lot_qty[]\" for=\"lot[]\" value=\""+obj.Confirm_Qty+"\" onblur=\"calQty('')\"></TD>";
                    html+="<TD colspan=\"4\"></TD>";
                    html+="</TR>";
                    html+="<TR>";
                    html+="<TD  class='split_title'  ><?php echo _lang('serial'); ?> </TD>";
                    html+="<TD  class='split_box'  ><INPUT TYPE=\"text\" ID=\"lot_serial[]\" NAME=\"lot_serial[]\" for=\"lot[]\" value=\""+obj.Product_Serial+"\"></TD>";
                    html+="<TD  class='split_title'  ><?php echo _lang('product_mfd'); ?> </TD>";
                    html+="<TD  class='split_box'  ><INPUT TYPE=\"text\" placeholder=\"Date Format\" ID=\"lot_prod_mfd[]\" NAME=\"lot_prod_mfd[]\" for=\"lot[]\" class=\"dateMFD\" value=\""+prodMFD+"\"></TD>";
                    html+="<TD  class='split_title'  ><?php echo _lang('product_exp'); ?> </TD>";
                    html+="<TD  class='split_box'  ><INPUT TYPE=\"text\" placeholder=\"Date Format\" ID=\"lot_prod_exp[]\" NAME=\"lot_prod_exp[]\" for=\"lot[]\" class=\"dateEXP\" value=\""+prodEXP+"\"></TD>";
                    html+="<TD  class='split_icon'  >";
                    html+="<img class='minicon' align='middle' src=\"<?php echo base_url() . 'images/plus.png'; ?>\" alt=\"Plus Split\" align=\"center\" onClick=\"AddItem()\" style='margin-right:4px;'>";
                    html+="</TD>";
                    if (splitNo>1) {    
                        html+="<TD  class='split_title'  >";
                        html+="<img class='minicon' align='middle' src=\"<?php echo base_url() . 'images/minus.png'; ?>\" alt=\"Subtraction Split\" align=\"center\" onClick=\"RemoveItem('infoProd"+splitNo+"')\" style='margin-right:4px;'>";
                        html+="</TD>";
                    }else{
                        html+="<TD  class='split_icon'  >";
                        html+="&nbsp";
                        html+="</TD>";
                    }
                    html+="</TR>";
                    html+="<TR>";
                    html+="<TD colspan=\"8\"><hr></TD>";
                    html+="</TR>";
                    html+="</TABLE>";
               
                    $("#splitTable").append(html);
                    $(".dateMFD").datepicker({ dateFormat: 'dd-mm-yy' });   // fix defect 511 : by kik : 2013-12-03
                    $(".dateEXP").datepicker({ dateFormat: 'dd-mm-yy' });   // fix defect 511 : by kik : 2013-12-03
//                    start comment for fix defect 511 : by kik : 2013-12-03
//                    $(".dateMFD").datepicker().keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
//                        //$('.dateMFD').datepicker('hide');
//                    }).bind("cut copy paste",function(e) {
//                        e.preventDefault();
//                    });
//
//                    $(".dateEXP").datepicker().keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
//                        //$('.dateEXP').datepicker('hide');
//                    }).bind("cut copy paste",function(e) {
//                        e.preventDefault();
//                    });
//                    end fix defect 511 : by kik : 2013-12-03

                    splitNo++;
                    calQty('');
                });
            }
        }, "json");
    }
    
    function submitFrm(){
        if (calQty('END')) {

            var PutAwayRule = $("#PutAwayRule").val();
            $.post('<?php echo site_url() ?>/product_info/validationProdSpilt', $('#frmProdInfo').serialize(), function(data1){
                if(data1=="OK"){
                    
                    var chk_date = true;
                    //Start add code for check validate date text format dd/mm/yyyy only : defect 511 
                    //by kik : 2013-12-03
                    
                    // 2014-06-11 disabled validate 
                    $('input[name="lot_prod_mfd[]"]').each(function(){
                        var prod_mfd_split = "";
                        prod_mfd_split = $(this).val();
                        var reg_prod_mfd_split = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
                            if (prod_mfd_split != "" && !(reg_prod_mfd_split.test(prod_mfd_split))) {
                                alert('Please fill Product Mfd format dd/mm/yyyy only. example 31/01/2000');
                                chk_date = false;
                                return false;                                
                            }
                    });

                    // require if FEFO
                    if(chk_date && PutAwayRule == "FEFO"){   // defect 511  by kik : 2013-12-03
                        $('input[name="lot_prod_exp[]"]').each(function(){
                            var prod_exp_split = "";
                            prod_exp_split = $(this).val();
                            var reg_prod_exp_split = /^(((0[1-9]|[12]\d|3[01])\/(0[13578]|1[02])\/((19|[2-9]\d)\d{2}))|((0[1-9]|[12]\d|30)\/(0[13456789]|1[012])\/((19|[2-9]\d)\d{2}))|((0[1-9]|1\d|2[0-8])\/02\/((19|[2-9]\d)\d{2}))|(29\/02\/((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))))$/g;
                                if (prod_exp_split != "" && !(reg_prod_exp_split.test(prod_exp_split))) {
                                    alert('Please fill Product Exp format dd/mm/yyyy only. example 31/01/2000');
                                    chk_date = false;
                                    return false;

                                }
                        });
                    }
                    //end add code for check validate date text format dd/mm/yyyy only : defect 511  //by kik : 2013-12-03
                    
                    if(chk_date){   // defect 511  by kik : 2013-12-03
                        
                        if(confirm("Are you sure to save split product ?")){
                            $.post('<?php echo site_url() ?>/product_info/saveProdSplit', $('#frmProdInfo').serialize(), function(data2){
                                if(data2=="OK"){
                                    alert("Save successfully.");
                                    window.onbeforeunload = null;
                                    window.location.reload();
                                    return true;
                                }else{
                                    alert("Save unsuccessfully.");
                                    return false;
                                }
                            },"json");
                        }
                    }
                    
                    // <<<<<------ START ADD by Ton! 20130903 ------>>>>>
                }else if(data1=="LS"){
                    alert("Please input <?php echo _lang('lot_or_serial'); ?>");
                    return false;
                }else if(data1=="QTY"){
                    alert("Please input <?php echo _lang('lot_qty'); ?>");
                    return false;
                }else if(data1=="MFD"){
                    alert("Please input <?php echo _lang('product_mfd'); ?>");
                    return false;
                }else if(data1=="EXP" && PutAwayRule == "FEFO"){
                    alert("Please input <?php echo _lang('product_exp'); ?>");
                    return false;                
                }else{
                    alert(data1);
                    return false;       
                }
                // <<<<<------ END ADD by Ton! 20130903 ------>>>>>
                //
                // <<<<<------ START COMMENT OUT by Ton! 20130903 ------>>>>>
                //                else{
                //                    alert("Please input info.");
                //                    return false;
                //                }
                // <<<<<------ END COMMENT OUT by Ton! 20130903 ------>>>>>
            },"json");            
        }
    }
    
    
    function calQty($type){
    
        var resQty=0;
        
        if ($("#ReservQty").val()=="") {
            resQty=parseFloat(0);
            $("#ReservQty").val(0);
        }else{
            resQty=$("#ReservQty").val();
            resQty=parseFloat(resQty);
        }
//        alert(resQty);
        var netQty=0; 
        
        $('input[name="lot_qty[]"]').each(function(){

            var value_qty= 0;
            var tmp_qty1 = 0;
            var tmp_qty2 = 0;
            var tmp_qty3 = 0;
            var qty = 0;
            var txt_id;
            var tmp_netQty;
            var tmp_netQty2;
            
            //รับค่า qty
            value_qty = $(this).val();
            
            //ตัด , (comma) ในรอบแรก
            value_qty = value_qty.replace(/\,/g, '');
            
            //แปลงจากตัวอักษร ให้เป็นตัวเลข ซึ่งหลักทศนิยม อาจจะยังไม่ถูกต้อง เพราะไม่ได้ไปอ่าน config แค่ให้เป็นตัวเลขเฉยๆ
            tmp_qty1=parseFloat(value_qty);  
            
            //แปลง tmp_netQty ให้เป็น number format ที่ config ไว้ ให้จุดทศนิยมถูกต้องก่อน
            tmp_qty2 = set_number_format(value_qty);
            
            //ตัด comma ออก เพื่อใช้คำนวนอีกรอบ
            tmp_qty3 = tmp_qty2.replace(/\,/g, '');
            
            //แปลงกับเป็นตัวเลขอีกครั้ง ให้สามารถเอาไปคำนวนได้
            qty=parseFloat(tmp_qty3);  
            
            //ตรวจสอบถ้าไม่มีค่าให้ qty เท่ากับศูนย์
            if (isNaN(qty)) {
                qty=0;
            }
            
            //----------------- คำนวน Net Qty -----------------------
            
            //เก็บค่า qty ทั้งหมดไว้ในตัวแปร tmp_netQty
            tmp_netQty = parseFloat(netQty)+qty;
            
            //แปลง tmp_netQty ให้เป็น number format ที่ config ไว้ ให้จุดทศนิยมถูกต้องก่อน
            tmp_netQty = set_number_format(tmp_netQty);
            
            //ตัด comma ออก เพื่อใช้คำนวน
            tmp_netQty2 = tmp_netQty.replace(/\,/g, '');
            
            //แปลงกับเป็นตัวเลขอีกครั้ง ให้สามารถเอาไปคำนวนได้
            netQty=parseFloat(tmp_netQty2); 
            //----------------- end คำนวน Net Qty -----------------------
            
            //เซ็ตให้หน้าบ้านแสดงผลเป็นเลข format ตามที่ config ไว้
            txt_id = $(this).attr("id");
            $( "#"+txt_id ).val(set_number_format(qty));
            
        });
        
        if (netQty>resQty) {
            alert("Sum <?php echo _lang('lot_qty'); ?> More(>) <?php echo _lang('receive_qty'); ?>");
            return false;
        }
        if ($type=="END") {
            if(netQty<resQty){
                alert("Sum <?php echo _lang('lot_qty'); ?> Less than(<) <?php echo _lang('receive_qty'); ?>");
                return false;
            }
        }
            
//        $("#BalanceQty").val(parseFloat(resQty-netQty));    // defect 516 Spilt: ไม่สามารถ split กรณี QTY = float  by kik : 2013-12-03
        $("#BalanceQty").val(set_number_format(parseFloat(resQty-netQty)));    // fix number format show "e" : by kik : 2014-02-25
//        $("#BalanceQty").val(parseInt(resQty-netQty));    // comment for defect 516 Spilt: ไม่สามารถ split กรณี QTY 
        return true;
    }
 
    function AddItem(){
        var html="<TABLE width='100%' id=\'infoProd"+splitNo+"'\">";
        html+="<TR>";
        html+="<TD class='split_title' for=\"lot[]\"><?php echo _lang('lot'); ?> </TD>";
        html+="<TD class='split_textbox' ><INPUT TYPE=\"text\" ID=\"lot[]\" NAME=\"lot[]\"></TD>";
        html+="<TD class='split_title' ><?php echo _lang('lot_qty'); ?> </TD>";
        html+="<TD class='split_textbox' ><INPUT TYPE=\"text\" ID=\"lot_qty"+splitNo+"\" NAME=\"lot_qty[]\" for=\"lot[]\" onblur=\"calQty('')\"></TD>";
        html+="<TD colspan=\"4\"></TD>";
        html+="</TR>";
        html+="<TR>";
        html+="<TD class='split_title' ><?php echo _lang('serial'); ?> </TD>";
        html+="<TD class='split_textbox' ><INPUT TYPE=\"text\" ID=\"lot_serial[]\" NAME=\"lot_serial[]\" for=\"lot[]\"></TD>";
        html+="<TD class='split_title' ><?php echo _lang('product_mfd'); ?> </TD>";
        html+="<TD class='split_textbox' ><INPUT TYPE=\"text\" placeholder=\"Date Format\" ID=\"lot_prod_mfd[]\" NAME=\"lot_prod_mfd[]\" for=\"lot[]\" class=\"dateMFD\"></TD>";
        html+="<TD class='split_title' ><?php echo _lang('product_exp'); ?> </TD>";
        html+="<TD class='split_textbox' ><INPUT TYPE=\"text\" placeholder=\"Date Format\" ID=\"lot_prod_exp[]\" NAME=\"lot_prod_exp[]\" for=\"lot[]\" class=\"dateEXP\"></TD>";
        html+="<TD class='split_icon' >";
        html+="<img class='minicon' align='middle' src=\"<?php echo base_url() . 'images/plus.png'; ?>\" alt=\"Plus Split\" align=\"center\" onClick=\"AddItem()\" style='margin-right:4px;'>";
        html+="</TD>";
        html+="<TD class='split_icon'>";
        html+="<img class='minicon' align='middle' src=\"<?php echo base_url() . 'images/minus.png'; ?>\" alt=\"Subtraction Split\" align=\"center\" onClick=\"RemoveItem('infoProd"+splitNo+"')\" style='margin-right:4px;'>";
        html+="</TD>";
        html+="</TR>";
        html+="<TR>";
        html+="<TD colspan=\"8\"><hr></TD>";
        html+="</TR>";
        html+="</TABLE>";
        
        $("#splitTable").append(html);
        
                    $(".dateMFD").datepicker({ dateFormat: 'dd-mm-yy' });   // fix defect 511 : by kik : 2013-12-03
                    $(".dateEXP").datepicker({ dateFormat: 'dd-mm-yy' });   // fix defect 511 : by kik : 2013-12-03
//                    start comment for fix defect 511 : by kik : 2013-12-03
//                    $(".dateMFD").datepicker().keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
//                        //$('.dateMFD').datepicker('hide');
//                    }).bind("cut copy paste",function(e) {
//                        e.preventDefault();
//                    });
//
//                    $(".dateEXP").datepicker().keypress(function(event) {event.preventDefault();}).on('changeDate', function(ev){
//                        //$('.dateEXP').datepicker('hide');
//                    }).bind("cut copy paste",function(e) {
//                        e.preventDefault();
//                    });
//                    end fix defect 511 : by kik : 2013-12-03
        
        splitNo++;
    }
    
    function RemoveItem(obj){
        $('#'+obj).remove();
        
        calQty('');
    }
    
//    function closePage(){
//        window.close();
//    }
</SCRIPT>
<style>
    .split_title{
        width: 80px;	
    }
    .split_textbox{
        width: 170px;	
    }
    .split_icon{
        width: 30px;	
    }
    
</style>


        <FORM CLASS="form-horizontal" ID="frmProdInfo" NAME="frmProdInfo" METHOD='post'>
            <input type="hidden" id="order_Id" name="order_Id" value="<?php echo $orderId ?>"/>
            <input type="hidden" id="item_Id" name="item_Id" value="<?php echo $itemId ?>"/>
            <input type="hidden" id="PutAwayRule" name="PutAwayRule" value="<?php echo $PutAwayRule ?>"/>
            
            <legend>&nbsp;Order&nbsp;</legend>
            <TABLE width='100%'>
                <TR>
                    <TD><?php echo _lang('document_no'); ?> </TD>
                    <TD><INPUT TYPE="text" ID="DocumentNo" NAME="DocumentNo" VALUE="<?php echo $DocumentNo ?>" readonly></TD>
                    <TD><?php echo _lang('document_external'); ?> </TD>
                    <TD><INPUT TYPE="text" ID="DocReferExt" NAME="DocReferExt" VALUE="<?php echo $DocReferExt ?>" readonly></TD>
                    <TD><?php echo _lang('receive_type'); ?> </TD>
                    <TD><INPUT TYPE="text" ID="ReservType" NAME="ReservType" VALUE="<?php echo $ReservType ?>" readonly></TD>
                </TR>
                <TR>
                    <TD><?php echo _lang('product_code'); ?> </TD>
                    <TD><INPUT TYPE="text" ID="ProductCode" NAME="ProductCode" VALUE="<?php echo $ProductCode ?>" readonly></TD>
                    <TD><?php echo _lang('product_name'); ?> </TD>
                    <TD><INPUT TYPE="text" ID="ProductName" NAME="ProductName" VALUE="<?php echo $ProductNameEN ?>" readonly></TD>
                    <TD><?php echo _lang('receive_qty'); ?>  </TD>
<!--                    <TD><INPUT TYPE="text" ID="ReservQty" NAME="ReservQty" VALUE="<?php //echo $ReservQty ?>" readonly></TD>--><!--COMMENT OUT by Ton! 20130903-->
                    <TD><INPUT TYPE="text" ID="ReservQty" NAME="ReservQty" VALUE="<?php echo $ConfirmQty ?>" readonly></TD><!--EDIT by Ton! 20130903-->
                </TR>
                <TR>
                    <TD colspan="4"></TD>
                    <TD><?php echo _lang('balance'); ?> </TD>
<!--                    <TD><INPUT TYPE="text" ID="BalanceQty" NAME="BalanceQty" VALUE="<?php //echo $ReservQty ?>" readonly></TD>--><!--COMMENT OUT by Ton! 20130903-->
                    <TD><INPUT TYPE="text" ID="BalanceQty" NAME="BalanceQty" VALUE="<?php echo $ConfirmQty ?>" readonly></TD><!--EDIT by Ton! 20130903-->
                </TR>
            </TABLE>
        </FIELDSET>
        <FIELDSET CLASS="field_border" ID="split_prod" NAME="split_prod" ALIGN="center" style='margin: 10px; padding: 10px;'>    
            <legend>&nbsp;Split Information&nbsp;</legend>
            <TABLE id="splitTable" border="0" class="pt8" width="100%"></TABLE>
        </FIELDSET>
    </FORM>
