<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.dataTables.editable.js" ?>"></script>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.js" ?>"></script>-->
<script>
    
    var separator = "<?php echo SEPARATOR; ?>"; // Add By Akkarapol, 22/01/2013, Add Separator for use in Page
    
    //$('#myModal').modal('toggle');
    $(document).ready(function() {

        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 23/09/2013, เพิ่ม onKeyup ของช่อง manPower ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="manPower"]').keyup(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 23/09/2013, เพิ่ม onKeyup ของช่อง manPower ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง


        initProductTable();
        $("#workload").attr("disabled", "disabled");

        /*$("#genManPowerBtn").click(function(){
         var chkValidate =  validateForm();
         if(chkValidate === true){
         var inputManPower = $("#manPower").val(); 
         // var totalWorkTodo = $('#showMovement tr').length;//total
         var totalWorkTodo = $('#total').val();
         var showManPower = totalWorkTodo / inputManPower;
         $("#workload").val(Math.ceil(showManPower));
         }
         else{
         alert("please enter manpower!");  
         }
         });*/

    });
    function postRequestAction(module, sub_module, action_value, next_state) {

        // add short-cut validate
        var chkValidate = validateForm();
        if (chkValidate != true) {
            alert("please enter manpower!");
            return;
        }

        //validate Engine called here.//
        var rowData = $('#showMovement').dataTable().fnGetData();
        var num_row = rowData.length;
        if (num_row <= 0) {
            alert("Please Select Counting Detail");
            return false;
        }
        for (i in rowData) {
            qty = rowData[i][10];
            if (qty == "") {
                alert('Please fill all Receive Qty');
                return false;
            }
        }
        // remove by ball 20130916 for validate when click generate
        /*if($("#workload").val() == ""){
         alert("Please Click Get Movement first!");
         $("#workload").attr("disabled","disabled");
         return false;
         };*/
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
            for (i in oTable) {
                //ADD BY POR 2013-12-03 เอา comma ออกก่อนส่งค่าไปบันทึก
                oTable[i][4] = oTable[i][4].replace(/\,/g,'');
                //END ADD
            
                oTable[i] = oTable[i].join(separator); // Edit By Akkarapol, 22/01/2014, Set oTable[i] to join with SEPARATOR
                
                var prodItem = document.createElement("input");
                prodItem.setAttribute('type', "hidden");
                prodItem.setAttribute('name', "prod_list[]");
                prodItem.setAttribute('value', oTable[i]);
                f.appendChild(prodItem);
            }
            $("#workload").removeAttr('disabled');


            var data_form = $("#frmCounting").serialize();
            var message = "";
            var statusisValidateForm = validateForm();
            //alert(statusisValidateForm);

            $("#workload").attr("disabled", "disabled");

            if (statusisValidateForm === true) {
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
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/counting/";
                    redirect(url);
                }, "json");
            } else {
                alert("Please Check Your Require Information (Red label).");
            }
        }
    }

    function initProductTable() {
        $('#showMovement').dataTable({
            "bJQueryUI": true,
            "bSort": true,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
                    , "aoColumns": [
                {"sWidth": "40px;", "sClass": "right"},
                {"sWidth": "50px;", "sClass": "right"},
                {"sWidth": "60px;", "sClass": "left"},
                {"sWidth": "60px;", "sClass": "center"},
                {"sWidth": "60px;", "sClass": "right"},
                {"sWidth": "60px;", "sClass": "right"},
                {"sWidth": "60px;", "sClass": "right"},
                {"sWidth": "60px;", "sClass": "right"},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right"},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
                {"sWidth": "60px;", "sClass": "right", "bVisible": false},
            ]
        });

        $('#showMovement').dataTable().fnSetColumnVis(5, false);
        $('#showMovement').dataTable().fnSetColumnVis(6, false);
//        .makeEditable({
//            sUpdateURL: '<?php echo site_url() . "/counting/saveEditedRecord"; ?>'
//                    , sAddURL: '<?php echo site_url() . "/pre_dispatch/saveEditedRecord"; ?>'
//                    
//        });
    }

    function exportExcelPallet(){
        window.open("<?php echo base_url()?>index.php/counting/getcountingMovementList");
    }

    function getValueFromTableData() {
        // var items = [[1,2],[3,4],[5,6]];
        var xx = [];
        var rowCount = $('#showMovement tbody tr');
        $(rowCount).each(function() {
            var $tds = $(this).find('td');
            var tmp = [];
            $tds.each(function() {//alert(i+","+j+":"+$(this).text());
                //tmpArray[i][j] = $(this).text();
                tmp.push($(this).text());
            });
            tmp.push("%%%");
            xx.push(tmp);
        });
        $("#queryText").val(xx);
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
			window.onbeforeunload = null;
        	url = "<?php echo site_url(); ?>/counting/";
            redirect(url);
        }
    }

    function validateForm() {

        //validate engine 
        var status;
        $("form").each(function() {
            $(this).validate({
                rules: {
                    manPower: {
//                        required: true,
//                        number : true,
                        min: 1
                    }
                }
            });
            $(this).valid();
            status = $(this).valid();
        });
        return status;

        //end of validate engine
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
</script>

<div class="<?php echo config_item("css_form"); ?>">
    <form class="" method="POST" action="" id="frmCounting" name="frmCounting" >
        <fieldset>
            <legend>Counting </legend>
            <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                <? if ($withData == "true") { ?>
                    <tr>
                        <td>
                            <div>
                                Total  : {total} 
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div>
                                <label for="manPower">Enter Man Power : </label>
                                <input type="text" name="manPower" id="manPower" class="required number" style="width: 200px;"/>
                                 <br>
                                 
                                <!--//add for ISSUE 3312 : by kik : 20140120-->
                                <div style='margin:5px 0px 10px 120px'>
                                
                                <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                                </div>
                            </div>
                           
                        </td>
                    </tr>
                <? } ?>
                <tr>
                    <td colspan="2">
                        <div id="defDataTable_wrapper_top" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                            <div style="width:100%;overflow-x: auto;" id="showDataTableTop"> 
                                <table  width="100%" cellpadding="2" cellspacing="0" border="0" class="display" id="showMovement">
                                    <thead>
                                        <tr>
                                            <th><?php echo _lang('no'); ?></th>
                                            <th><?php echo _lang('product_code'); ?></th>
                                            <th><?php echo _lang('product_name'); ?></th>
                                            <th><?php echo _lang('location_code'); ?></th>
                                            <th><?php echo _lang('qty'); ?></th>
                                            <th>Id</th>
                                            <th><?php echo _lang('lot'); ?></th>
                                            <th><?php echo _lang('serial'); ?></th>
                                            <th><?php echo _lang('actual_location'); ?></th>
                                            <th>Item</th>
                                            <th>Item Id</th>
                                            <th>Status</th>
                                            <th><?php echo _lang('product_sub_status'); ?></th>
                                            <th>License</th>
                                            <th><?php echo _lang('product_mfd'); ?></th>
                                            <th><?php echo _lang('product_exp'); ?></th>
                                            <th>Unit Id</th>																																																																								
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        //i.Product_Id as 'Id',i.Product_Code,p.Product_NameEN ,l.Location_Code,i.Receive_Qty
                                        $i = 0;
                                        $sum_all_qty = 0;
                                        foreach ($listMovement as $rows) {
                                            echo "<tr>";
                                            echo "<td>" . ($i + 1) . "</td>";
                                            echo "<td>" . $rows->Product_Code . "</td>";
                                            echo "<td style='text-align: left;'>" . $rows->Product_NameEN . "</td>";
                                            echo "<td>" . $rows->Location_Code . "</td>";
                                            echo "<td style='text-align: right;'>" . set_number_format($rows->QTY) . "</td>";
                                            echo "<td>" . $rows->Id . "</td>";
                                            echo "<td>" . $rows->Lot . "</td>";
                                            echo "<td>" . $rows->Serial . "</td>";
                                            echo "<td>" . $rows->Actual_Location_Id . "</td>";
                                            echo "<td>" . $rows->Item_From . "</td>";
                                            echo "<td>" . $rows->Item_From_Id . "</td>";
                                            echo "<td>" . $rows->Product_Status . "</td>";
                                            echo "<td>" . $rows->Product_Sub_Status . "</td>";
                                            echo "<td>" . $rows->Product_License . "</td>";
                                            echo "<td>" . $rows->Product_Mfd . "</td>";
                                            echo "<td>" . $rows->Product_Exp . "</td>";
                                            echo "<td>" . $rows->Unit_Id . "</td>";
                                            echo "</tr>";
                                            $i++;
                                            $sum_all_qty+=$rows->QTY;
                                        }
                                        ?>
                                    </tbody>

                                    <!-- show total qty : by kik : 01-11-2013-->
                                    <tfoot>
                                        <tr>
                                            <th colspan="4" class ='ui-state-default indent'  style='text-align: center;'><b>Total</b></th>

                                            <th class ='ui-state-default indent'  style='text-align: right;'><?php echo set_number_format($sum_all_qty); ?></th>

                                            <th colspan="12" ></th>

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
        <input type="hidden" name="total" id="total" value="{total}"/>
        <input type="hidden" name="workload" id="workload" />
        <input type="hidden" name="token" value="<?php echo $token?>" />
        <?php echo form_hidden('process_id', $process_id); ?>
        <?php echo form_hidden('present_state', $present_state); ?>
        <?php echo form_hidden("counting_type", "CT01"); // DAILY?>	
        <?php
        if (isset($flow_id)) {
            echo form_hidden('flow_id', $flow_id);
        }
        ?>
    </form>
</div>
<pre>
<?php //print_r($listMovement); ?>
</pre>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <!--    <form action="" method="post">-->

    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Transfer Order</h3>
<!--        <input  value="" type="text" name="prdModalval" id="prdModalval" disabled>-->
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



