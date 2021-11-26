<script>
    
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Dispatch'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#ShowDataTableForInsert'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowDispatchList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';
    
    //$('#myModal').modal('toggle');
    $(document).ready(function() {

        initProductTable();
        $("#preDispatchDate").datepicker();
        $("#preDeliveryDate").datepicker();
        $("#estDispatchDate").datepicker();
        $('#getBtn').click(function() {
            showModalTable();
            $('#defDataTableModal_filter label input[type=text]').unbind('keypress keyup')
                    .bind('keypress keyup', function(e) {
                if (e.keyCode == 13)
                {
                    showModalTable($('#defDataTableModal_filter label input[type=text]').val());
                }
            });
        });

        $('#search_submit').click(function() {
            var allVals = [];
            $('.modal-body :checked').each(function() {
                allVals.push($(this).val());
            });

            $.ajax({
                type: 'post',
                dataType: 'json',
                url: '<?php echo site_url() . "/pre_dispatch/showSelectData" ?>', // in here you should put your query 
                data: 'post_val=' + allVals + "&tableName=ShowDataTableForInsert", // here you pass your id via ajax .
                // in php you should use $_POST['post_id'] to get this value 
                success: function(r)
                {
                    $.each(r.product, function(i, item) {
                        var del = "<a ONCLICK=\"\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                        $('#ShowDataTableForInsert').dataTable().fnAddData([item.Inbound_id, item.product_code, item.Product_NameEN
                                    , item.Product_Status, item.Actual_Location_Id, item.Product_License
                                    , item.Product_lot, item.Product_Serial, item.Product_Mfd
                                    , item.Product_Exp, item.Temp_Balance_Qty, del]);
                    });
                    initProductTable();
                }


            });
            $('.modal.in').modal('hide');
        });

        $("#select_all").click(function() {
            $(".modal-body input").each(function() {
                $(this).attr("checked", "checked");
            });
        });

        $("#deselect_all").click(function() {
            $(".modal-body input").each(function() {
                $(this).removeAttr("checked");
            });
        });
    });
    $('#myModal').modal('toggle').css({
        // make width 90% of screen
        'width': function() {
            return ($(document).width() * 0.95) + 'px';
        },
        // center model
        'margin-left': function() {
            return -($(this).width() / 2);
        }

    });

    function postRequestAction(module, sub_module, action_value, next_state) {
        
	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;

        //validate Engine called here.//

        var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
        var num_row = rowData.length;
        if (num_row <= 0) {
            alert("Please Select Product Order Detail");
            return false;
        }
//        if (confirm("Are you sure to do following action : " + action_value + "?")) {
            //handmade call data from datatable.//    
            getValueFromTableData();

            var f = document.getElementById("frmDispatch");
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

            global_data_form = $("#frmDispatch").serialize();
            var message = "";

            var statusisValidateForm = validateForm();

            if (statusisValidateForm === true) {
                
//                if(global_sub_module != 'rejectAction' && global_sub_module !='rejectAndReturnAction'){
//                    validation_data();
//                }else{
                    var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                    $('#div_for_alert_message').html(mess);
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function() {
                                        return ($(window).width() - $(this).width()) / 2;
                                }
                        });
//                }
                
//                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
//                    // alert("Update Complete,Current Status : " + data.status);
//                    switch (data.status) {
//                        case 'C001':
//                            message = "Save Pre-Dispatch Complete";
//                            break;
//                        case 'C002':
//                            message = "Confirm Pre-Dispatch Complete";
//                            break;
//                        case 'C003':
//                            message = "Approve Pre-Dispatch Complete";
//                            break;
//                    }
//                    alert(message);
//                    url = "<?php echo site_url(); ?>/pre_dispatch/";
//                    redirect(url);
//                }, "json");
            } else {
                alert("Please Check Your Require Information (Red label).");
            }
//        }
    }

    function getValueFromTableData() {
        // var items = [[1,2],[3,4],[5,6]];
        var xx = [];
        var rowCount = $('#ShowDataTableForInsert tbody tr');
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
    function showModalTable() {

        var product_code = $('#productCode').val();
        // $('#defDataTableModal_filter label input[type=text]').val(product_code); // <-- add this line for search option
        $.ajax({
            type: 'post',
            url: '<?php echo site_url() . "/pre_dispatch/getSelectPreDispatchData" ?>', // in here you should put your query 
            data: 'post_val=' + product_code + '&tableName=defDataTableModal', // here you pass your id via ajax .
            // in php you should use $_POST['post_id'] to get this value 
            success: function(r)
            {
                // alert(r);
                $(".modal-body").attr("style", "padding:0px;");
                $(".modal-body").html('<div style="width:100%;overflow-x: auto;margin:0px auto;">' + r + '</div>');
                //alert($('#defDataTableModal_filter label input[type=text]').val());
            }
        });

        $('#mymodal').show();  // put your modal id 
    }


    function initProductTable() {
        $('#ShowDataTableForInsert').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>'
        }).makeEditable({
            sUpdateURL: '<?php echo site_url() . "/dispatch/saveEditedRecord"; ?>'
                    , sAddURL: '<?php echo site_url() . "/dispatch/saveEditedRecord"; ?>'
                    , "aoColumns": [
                {"onblur": 'submit', "sClass": "right"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', cssclass: "required"},
                {"onblur": 'submit', "sWidth": "50px", "sClass": "right"}]
        });


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


    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowDispatchList";
            redirect(url)
        }
    }

</script>
<style>
    #myModal {
        width: 1024px; /* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -512px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }
</style>
<TR class="content" style='height:100%' valign="top">
    <TD>
        <form class="<?php echo config_item("css_form"); ?>" method="POST" action="" id="frmDispatch" name="frmDispatch" >
            <fieldset style="margin:0px auto;">
                <legend>Transfer Order</legend>
                <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                    <tr>
                        <td>
                            Renter :
                        </td>
                        <td>
                            {renter_id_select}
                        </td>
                        <td>
                            Shipper :
                        </td>
                        <td>
                            {frm_warehouse_select}
                        </td>
                        <td>
                            Consignee :
                        </td>
                        <td>
                            {to_warehouse_select}
                        </td>                    
                    </tr>
                    <tr>
                        <td>
                            Dispatch Type :
                        </td>
                        <td>
                            {dispatch_type_select}
                        </td>
                        <td>
                            Dispatch Date :
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="preDispatchDate" name="preDispatchDate" class="required" >
                        </td>
                        <td>
                            Delivery Date :
                        </td>
                        <td>
                            <input type="text" placeholder="Date Format" id="preDeliveryDate" name="preDeliveryDate" class="required">
                        </td>                    
                    </tr>
                    <tr>
                        <td>
                            Document No. :
                        </td>
                        <td>
                            <input type="text" placeholder="Document No." id="DocNo" name="DocNo" >
                        </td>
                        <td>
                            Refer External No. :
                        </td>
                        <td>
                            <input type="text" placeholder="Invoice No." id="invoiceNo" name="invoiceNo" class="required">
                        </td>
                        <td>
                            Refer Internal No. :
                        </td>
                        <td>
                            <input type="text" placeholder="Transfer Order No." id="TranfOrderNo" name="TranfOrderNo" class="required">
                        </td>                    
                    </tr>
                    <tr>
                        <td>
                            Est. Dispatch Date :
                        </td>
                        <td colspan="6">
                            <input type="text" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" class="required" >
                        </td>
                    </tr>
                </table>
            </fieldset>
            <fieldset>
                <legend>Order Detail </legend>
                <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                    <tr>
                        <td>
                            <div style="width:90%;">
                                Product Code : 
                                <input type="text" name="productCode" id="productCode" class="input-medium" placeholder="Product Code">
<!--                                <input type="button" name="btnPrdCodeSearch" id="btnPrdCodeSearch" value="Get Detail" class="btn success">-->
                                <a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div id="defDataTable_wrapper" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                                <div style="width:100%;overflow-x: auto;" id="showDataTable"> 
                                    <table cellpadding="2" cellspacing="0" border="0" class="display" id="ShowDataTableForInsert">
                                        <thead>
                                            <tr>
                                                <td>Id</td>
                                                <td>Product Code</td>
                                                <td>Product Name</td>
                                                <td>Product Status</td>
                                                <td>Product License</td>
                                                <td>Product Lot</td>
                                                <td>Product Serial</td>
                                                <td>Product Mfd</td>
                                                <td>Product Exp</td>
                                                <td>Temp Balance Qty</td>
                                            </tr>
                                        </thead>
                                        <tbody>

                                        </tbody>
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

            <?php
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id);
            }
            ?>
        </form>
    </TD>
</TR>

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

<?php $this->load->view('element_modal_message_alert'); ?>