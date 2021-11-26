<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    //$('#myModal').modal('toggle');
    $(document).ready(function() {

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

        // If found key F5 remove unload event!!
        $(document).on('keydown keyup', function(e) {

            if (e.which === 116) {
                window.onbeforeunload = null;
            }

            if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
                window.onbeforeunload = null;
            }
            
        });
        // Add By Ball        	
        // Add By Akkarapol, 19/12/2013, เพิ่ม $('#showMovement tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
        $('#showMovement tbody tr td[title]').hover(function() {
            
            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;            
            if(chk_title != chk_innerHTML){
                $(this).show_tooltip();
            }           
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 
            
        }, function() {
            $(this).hide_tooltip();
        });
        // END Add By Akkarapol, 19/12/2013, เพิ่ม $('#showMovement tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย  

                
        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if($(this).val()!=''){
                $( this ).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        
        // Add By Akkarapol, 23/09/2013, เพิ่ม onKeyup ของช่อง manPower ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="manPower"]').keyup(function(){            
            if($(this).val()!=''){
                $( this ).removeClass('required');
            }else{
                $( this ).addClass('required');
                
            }
        });
        // END Add By Akkarapol, 23/09/2013, เพิ่ม onKeyup ของช่อง manPower ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง


        initProductTable();
        $("#genManPowerBtn").click(function(){
           var inputManPower = $("#manPower").val(); 
           var totalWorkTodo = $('#showMovement tr').length;
           var showManPower = totalWorkTodo / inputManPower;
           $("#showManPower").val(parseInt(showManPower));
        });
    });
    function postRequestAction(module, sub_module, action_value, next_state) {

        //validate Engine called here.//
       
        var rowData = $('#showMovement').dataTable().fnGetData();
        var num_row = rowData.length;
        if (num_row <= 0) {
            alert("Please Select Counting Detail");
            return false;
        }
        if (confirm("Are you sure to do following action : " + action_value + "?")) {
            //handmade call data from datatable.//    
            //getValueFromTableData();

            var f = document.getElementById("frmCounting");
            var actionType = document.createElement("input");
            actionType.setAttribute('type', "hidden");
            actionType.setAttribute('name', "action_type");
            actionType.setAttribute('value', action_value);
            f.appendChild(actionType);

            var toStateNo = document.createElement("input");
            toStateNo.setAttribute('type', "hidden");
            toStateNo.setAttribute('name', "next_state");
            toStateNo.setAttribute('value', next_state);
            f.appendChild(toStateNo);
      
            
            var oTable = $('#showMovement').dataTable().fnGetData();
            for(i in oTable){
                //ADD BY POR 2013-12-03 เอา comma ออกก่อนส่งค่าไปบันทึก
                oTable[i][3] = oTable[i][3].replace(/\,/g,'');
                oTable[i][4] = oTable[i][4].replace(/\,/g,'');
                //END ADD
            
                var prodItem	= document.createElement("input"); 
                prodItem.setAttribute('type',"hidden"); 
                prodItem.setAttribute('name',"prod_list[]"); 
                prodItem.setAttribute('value',oTable[i]);
                f.appendChild(prodItem);
            }
            var data_form = $("#frmCounting").serialize();
            var message = "";
            
            var statusisValidateForm = validateForm();
            //alert(statusisValidateForm);
            if(statusisValidateForm === true  ){
                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
                    // alert("Update Complete,Current Status : " + data.status);
                    switch (data.status) {
                        case 'C001':
                            message = "Save Daily Counting Complete";
                            break;
                        case 'C002':
                            message = "Confirm Daily Counting Complete";
                            break;
                        case 'C003':
                            message = "Approve Daily Counting Complete";
                            break;
                        case 'C004':        // add by kik : 09-01-2013
                            window.onbeforeunload = null;
                            message = "Reject Daily Counting Complete";
                            break;
                        case 'C005':        // add by kik : 09-01-2013
                            window.onbeforeunload = null;
                            message = "Reject and Return Daily Counting Complete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/counting/";
                    redirect(url);
                }, "json");
            }else{
               alert("Please Check Your Require Information (Red label).");
            }           
        }
    }

    function initProductTable() {
        $('#showMovement').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
            ,  "aoColumns": [
                {"sWidth": "40px;","sClass": "center"},
                {"sWidth": "80px;","sClass": "left"},
                {"sWidth": "80px;","sClass": "center"},
                {"sWidth": "80px;","sClass": "center"},
                {"sWidth": "80px;","sClass": "center"},
                {"sWidth": "80px;","sClass": "left"},
                {"sWidth": "80px;","sClass": "left"},
                {"sWidth": "80px;","sClass": "center"},
                {"sWidth": "80px;","sClass": "center"}
                ]
        });
//        .makeEditable({
//            sUpdateURL: '<?php echo site_url() . "/counting/saveEditedRecord"; ?>'
//                    , sAddURL: '<?php echo site_url() . "/counting/saveEditedRecord"; ?>'
//                    
//        });
}
    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
			window.onbeforeunload = null;
        	url = "<?php echo site_url(); ?>/counting/";
            redirect(url)
        }
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

</script>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmCounting" name="frmCounting" >
            <fieldset>
                <legend>Counting </legend>
                <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
<!--                    <tr>
                        <td>
                            <div>
                                <label for="manPower">Enter Man Power : </label>
                                <input type="text" name="manPower" id="manPower" disabled/>
                                <input type="button" class="btn success" name="genMoveMent" id="genManPowerBtn" value="Get Movement"/>
                            </div>
                        </td>
                        <td>
                         <div id="manPowerResult">
                                <table>
                                    <tr>
                                        <td><label for="showManPower">Work : </label></td>
                                        <td><input type="text" name="showManPower" id="showManPower" disabled></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>-->
                    <tr>
                        <td colspan="2">
                            <div id="defDataTable_wrapper_top" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                                <div style="width:100%;overflow-x: auto;" id="showDataTableTop"> 
                                    <table width="100%" cellpadding="2" cellspacing="0" border="0" class="display" id="showMovement">
                                        <thead>
                                            <tr>
                                                <th><?php echo _lang('product_code'); ?></th>
                                                <th><?php echo _lang('product_name'); ?></th>
                                                <th><?php echo _lang('location_code'); ?></th>
                                                <th><?php echo _lang('system'); ?></th>
                                                <th><?php echo _lang('physical'); ?></th>
                                                <th><?php echo _lang('lot'); ?></th>
                                                <th><?php echo _lang('serial'); ?></th>
                                                <th><?php echo _lang('modified_date'); ?></th>
                                                <th><?php echo _lang('expired_date'); ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $sum_Reserv_Qty=0;
                                            $sum_Confirm_Qty=0;
                                            foreach($listCounting as $rows){
                                                    echo "<tr>";
                                                    echo "<td>".$rows->Product_Code."</td>";
                                                    
                                                    // Edit By Akkarapol, 19/12/2013, ใส่ title ใน td เพื่อใช้ในการแสดง tooltip                             
                                                    // echo "<td style='text-align: left;'>".$rows->Product_NameEN."</td>";
//                                                    echo "<td style='text-align: left;' title='" . htmlspecialchars($rows->Product_NameEN, ENT_QUOTES) . "' >" . $rows->Product_NameEN . "</td>";
                                                    echo "<td style=\"text-align: left;\" title=\"" . str_replace('"','&quot;',$rows->Product_NameEN) . "\">" . $rows->Product_NameEN . "</td>"; // Edit by Ton! 20140114
                                                    // END Edit By Akkarapol, 19/12/2013, ใส่ title ใน td เพื่อใช้ในการแสดง tooltip
                                                    
                                                    echo "<td>".$rows->Location_Code."</td>";
                                                    echo "<td style='text-align: right;'>".set_number_format($rows->Reserv_Qty)."</td>";
                                                    echo "<td style='text-align: right;'>".set_number_format($rows->Confirm_Qty)."</td>";
                                                    echo "<td>".$rows->Product_Lot."</td>";
                                                    echo "<td>".$rows->Product_Serial."</td>";
                                                    echo "<td>".$rows->Product_Mfd."</td>";
                                                    echo "<td>".$rows->Product_Exp."</td>";
                                                    echo "</tr>";
                                                    $sum_Reserv_Qty+=$rows->Reserv_Qty;
                                                    $sum_Confirm_Qty+=$rows->Confirm_Qty;
                                            }?>
                                        </tbody>
                                        
                                        <!-- show total qty : by kik : 01-11-2013-->
                                            <tfoot>
                                                     <tr>
                                                            <th colspan="3" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>

                                                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_Reserv_Qty);?></th>
                                                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_Confirm_Qty);?></th>
                                
                                                            <th colspan="4" ></th>

                                                     </tr>
                                            </tfoot>
                                        <!-- end show total qty : by kik : 01-11-2013-->
                                        
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                  
                </table>
            </fieldset>
            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
            <?php echo form_hidden('process_id', $process_id); ?>
            <?php echo form_hidden('present_state', $present_state); ?>
		
            <?php if(isset($flow_id)){
                       echo form_hidden('flow_id', $flow_id);
                  }
	     ?>
	     <input type="hidden" name="token" value="<?php echo $token?>" />
        </form>
    </TD>
</TR>

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



