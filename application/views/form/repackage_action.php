<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var separator = "<?php echo SEPARATOR; ?>";
    var allVals = new Array();
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var allVals = new Array();
    var form_name = "form_receive";
    var ci_prod_code = 1;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_reserv_qty = 9;
    var ci_confirm_qty = 10;
    var ci_remark = 14;
    //Define Hidden Field Datatable
    var ci_prod_id = 15;
    var ci_prod_status = 16;
    var ci_prod_sub_status = 17;
    var ci_unit_id = 18;
    var ci_item_id = 19;
    var ci_suggest_loc = 20;
    var ci_actual_loc = 21;
    var ci_list = [
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_prod_id', value: ci_prod_id},
        {name: 'ci_prod_status', value: ci_prod_status},
        {name: 'ci_unit_id', value: ci_unit_id},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
    ]

    $(document).ready(function() {

        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        initProductTable();

        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+$/i.test(value);
        }, "Document Format is invalid.");

        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark] ").attr("disabled", true);

    });

    function initProductTable() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            // "bSort": false,
            // "bRetrieve": true,
            // "bDestroy": true,
            // "sPaginationType": "full_numbers",
            // "sDom": '<"H"lfr>t<"F"ip>',
            // "sScrollY": "200px",
//            "sScrollX": "100%",
            // "sScrollXInner": "110%",
            "bScrollCollapse": true,
            "aoColumnDefs": [
                {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [1]},
                {"sWidth": "15%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
                {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "7%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [7]},
                {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [8]},
                {"sWidth": "3%", "sClass": "right_text", "aTargets": [9]},
                {"sWidth": "5%", "sClass": "right_text", "aTargets": [10]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [11]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [13]}
            ]
        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            }
            , "aoColumns": [
                null
                        , null
                        , null
                        , {
                    loadurl: '<?php echo site_url() . "/pre_receive/getProductStatus"; ?>',
                    loadtype: 'POST',
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                        console.log(settings);
                        var oTable = $('#showProductTable').dataTable( );
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_prod_status);
                        return value;
                    }
                }
                , {
                    loadurl: '<?php echo site_url() . "/pre_receive/getSubStatus"; ?>',
                    loadtype: 'POST',
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                        console.log(settings);
                        var oTable = $('#showProductTable').dataTable( );
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                        return value;
                    }
                }
                , null
                        , null// Serial
                        , null// MFD
                        , null// EXP
                        , null
                        , null
                        , null
                        , null
                        , null
                        , {onblur: 'submit'}
                , null
                        , null
                        , null
            ]
        });

        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_actual_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_confirm_qty, false);
//        $('#showProductTable tbody tr[title]').tooltip();
        // Add By Akkarapol, 23/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
        $('#showProductTable tbody tr td[title]').hover(function() {

            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;
            if (chk_title != chk_innerHTML) {
                $(this).show_tooltip();
            }
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 

        }, function() {
            $(this).hide_tooltip();
        });
        // END Add By Akkarapol, 23/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย  

        new FixedColumns(oTable, {
            "iLeftColumns": 0,
            "sLeftWidth": 'relative',
            "iLeftWidth": 0
        });
    }

    function postRequestAction(module, sub_module, action_value, next_state) {
        var statusisValidateForm = validateForm();
        if (statusisValidateForm === true) {
            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }

            for (i in rowData) {

                // Add By Akkarapol, 04/09/2013, เพิ่มการตรวจสอบว่าหากเลือก Sub Status เป็น Return หรือ Re-Pack จะต้องเลือก Product Status = Normal เท่านั้น
                prod_status = rowData[i][ci_prod_status];
                prod_sub_status = rowData[i][ci_prod_sub_status];
                if (prod_sub_status == 'SS001' || prod_sub_status == 'SS004') {
                    if (prod_status != 'NORMAL') {
                        alert("This Sub Status Use On Product Status Must be 'Normal' Only!!");
                        return false;
                    }
                }
                // END Add By Akkarapol, 04/09/2013, เพิ่มการตรวจสอบว่าหากเลือก Sub Status เป็น Return หรือ Re-Pack จะต้องเลือก Product Status = Normal เท่านั้น


                /*qty = rowData[i][ci_confirm_qty];
                 if(qty==""){
                 alert('Please fill all Receive Qty');
                 return false;
                 }
                 qty=parseInt(qty);
                 if(qty < 0){
                 alert('Negative Receive Qty is not allow');
                 return false;
                 }
                 */
                prod_status = rowData[i][ci_prod_status];
                if ("PENDING" == prod_status) {
                    alert("Product Status 'Pending' not Allow !!");
                    return false;
                }
            }

            if (confirm("Are you sure to action " + action_value + "?")) {
                $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", false);
                var f = document.getElementById(form_name);
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

                var oTable = $('#showProductTable').dataTable().fnGetData();
                for (i in oTable) {
                    var prod_data = oTable[i].join(separator);
                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
                    prodItem.setAttribute('value', prod_data);
                    f.appendChild(prodItem);
                }

                $.each(ci_list, function(i, obj) {
                    var ci_item = document.createElement("input");
                    ci_item.setAttribute('type', "hidden");
                    ci_item.setAttribute('name', obj.name);
                    ci_item.setAttribute('value', obj.value);
                    f.appendChild(ci_item);
                });

                var data_form = $("#" + form_name).serialize();
                var message = "";

                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
                    switch (data.status) {
                        case 'C001':
                            message = "Open Job Repackage Complete";
                            break;
                        case 'C002':
                            message = "Confirm Repackage Complete";
                            break;
                        case 'C003':
                            message = "Approve Repackage Complete";
                            break;
                        case 'E001':
                            message = "Save Repackage Incomplete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/flow/flowRepackageList";
                    redirect(url);
                }, "json");
            }
        } else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowRepackageList";
            redirect(url);
        }
    }

    function validateForm() {
        $("form").validate({
            rules: {
                renter_id: {required: true}
                , shipper_id: {required: true}
                , consignee_id: {required: true}
                , doc_refer_ext: {required: true}
                , receive_date: {required: true}
            }
        });
        return $("form").valid();
    }

    function changeOption(obj) {
        if ("RCV002" == obj.value) {
            $("[name='is_pending']").prop('checked', false);
            $("[name='is_pending']").attr("disabled", true);
            $("[name='is_repackage']").attr("disabled", false);
        } else {
            $("[name='is_repackage']").prop('checked', false);
            $("[name='is_repackage']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", false);
        }
    }

    function viewSplitInfo(item_id) {
        var url = "<?php echo site_url(); ?>/product_info/splitProd/" + item_id;
        window.open(url, 'TheWindow');
    }

</script>
<style>

/*    .tooltip {
        left: 180px !important;
        width: 200px !important;
    }*/
</style>

<div class="well">
    <form id="form_receive" method=post action="">
        <?php
        if (!isset($owner_id)) {
            $owner_id = "";
        }
        if (!isset($renter_id)) {
            $renter_id = "";
        }
        if (!isset($shipper_id)) {
            $shipper_id = "";
        }
        if (!isset($consignee_id)) {
            $consignee_id = "";
        }
        if (!isset($receive_type)) {
            $receive_type = "";
        }
        if (!isset($est_receive_date)) {
            $est_receive_date = date("d/m/Y");
        }
        if (!isset($remark)) {
            $remark = "";
        }
        if (!isset($process_type)) {
            $process_type = $data_form['process_type'];
        }
        if (!isset($document_no)) {
            $document_no = "";
        }
        if (!isset($doc_refer_int)) {
            $doc_refer_int = "";
        }
        if (!isset($doc_refer_ext)) {
            $doc_refer_ext = "";
        }
        if (!isset($doc_refer_inv)) {
            $doc_refer_inv = "";
        }
        if (!isset($doc_refer_ce)) {
            $doc_refer_ce = "";
        }
        if (!isset($doc_refer_bl)) {
            $doc_refer_bl = "";
        }
        if (!isset($receive_date)) {
            $receive_date = date('d/m/Y');
        }

        if ((!isset($is_pending)) || ($is_pending != 'Y')) {
            $is_pending = false;
        } else {
            $is_pending = true;
        }

        if ((!isset($is_repackage)) || ($is_repackage != 'Y')) {
            $is_repackage = false;
        } else {
            $is_repackage = true;
        }
        ?>
        <?php
        if (isset($flow_id)) {
            echo form_hidden('flow_id', $flow_id);
        }
        ?>
        <?php
        if (isset($order_id)) {
            echo form_hidden('order_id', $order_id);
        }
        ?>
        <?php echo form_hidden('process_id', $process_id); ?>
        <?php echo form_hidden('present_state', $present_state); ?>
        <?php echo form_hidden('user_id', $user_id); ?>
        <?php echo form_hidden('process_type', $process_type); ?>
        <?php echo form_hidden('owner_id', $owner_id); ?>
        <fieldset class="well">
            <legend>&nbsp;&nbsp;<b>Change Product Status</b>&nbsp;&nbsp;</legend>
            <table width="98%">
                <tr>
                    <td align="right">Renter</td>
                    <td align="left"><?php echo form_dropdown('renter_id', $renter_list, $renter_id, 'class="required"'); ?></td>
                    <td align="right">Shipper</td>
                    <td align="left"><?php echo form_dropdown('shipper_id', $shipper_list, $shipper_id, 'class="required" '); ?></td>
                    <td align="right">Consignee</td>
                    <td align="left"><?php echo form_dropdown('consignee_id', $consignee_list, $consignee_id, 'class="required"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Document No.</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" disabled class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Document External</td>
                    <td align="left"><?php echo form_input('doc_refer_ext', trim($doc_refer_ext), 'placeholder="' . DOCUMENT_EXT . '" class="required document " style="text-transform: uppercase"'); ?></td>
                    <td align="right">Document Internal</td>
                    <td align="left"><?php echo form_input('doc_refer_int', trim($doc_refer_int), 'placeholder="' . DOCUMENT_INT . '"  class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', trim($doc_refer_inv), 'placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', trim($doc_refer_ce), 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', trim($doc_refer_bl), 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Receive Type</td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange=changeOption(this)"); ?></td>
                    <td align="right">ASN.</td>
                    <td align="left"><?php echo form_input('est_receive_date', $est_receive_date, 'id="est_receive_date" placeholder="Advance Shipment Notice" class="required" readonly="readyonly" '); ?></td>
                    <td align="right">Receive Date.</td>
                    <td align="left"><?php echo form_input('receive_date', $receive_date, 'id="receive_date" placeholder="Receive Date" readonly="readyonly"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Vendor</td>
                    <td align="left"><?php echo form_dropdown('vendor_id', $vendor_list, $vendor_id, ''); ?></td>
                    <td align="right">Driver Name</td>
                    <td align="left"><?php echo form_input('driver_name', trim($driver_name), 'placeholder="' . DRIVER_NAME . '" '); ?></td>
                    <td align="right">Car No.</td>
                    <td align="left"><?php echo form_input('car_no', trim($car_no), 'placeholder="' . CAR_NO . '" '); ?></td>
                </tr>
                <tr valign="center">
                    <td align="right">Remark</td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
			</td>
			<td align="right"></td>
			<td align="left" style='display:none'>
                        <?php echo form_checkbox('is_pending', ACTIVE, false); ?>&nbsp;Pending
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox('is_repackage', ACTIVE, false); ?>&nbsp;Re-Package</td>
		  </tr>
	  </table>
	</fieldset>
	</form>
	<fieldset class="well">
	<legend>&nbsp;&nbsp;<b>Product Detail</b>&nbsp;&nbsp;</legend>
	  <table width="100%">
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
					<thead>
                            <?php
                            $show_column = array(
                                _lang('no'),
                                _lang('product_code'),
                                _lang('product_name'),
                                _lang('product_status'),
                                _lang('product_sub_status'),
                                _lang('lot'),
                                _lang('serial'),
                                _lang('product_mfd'),
                                _lang('product_exp'),
                                _lang('receive_qty'),
                                _lang('confirm_qty'),
                                _lang('unit'),
                                _lang('suggest_location'),
                                _lang('actual_location'),
                                _lang('remark'),
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id"
                            );
                            $str_header = "";
                            foreach ($show_column as $index => $column) {
                                $str_header .= "<th>" . $column . "</th>";
                            }
                            ?>
						<tr><?php echo $str_header; ?></tr>
					</thead>
                        <?php
                        if (isset($pending_detail)) {
                            $str_body = "";
                            $j = 1;
                            $sum_Receive_Qty = 0;   //add by kik : 28-10-2013   
                            $sum_Cf_Qty = 0;        //add by kik : 28-10-2013
                            foreach ($pending_detail as $order_column) {
//                                $str_body .= '<tr title="' . $order_column->Full_Product_Name . '" >';
//                                $str_body .= "<tr title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">"; // Edit by Ton! 20131127

                                $str_body .= "<tr>";

                                $str_body .= "<td>" . $j . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Code . "</td>";
                                // comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์
                                // $str_body .= "<td title='" . $order_column->Full_Product_Name . "' >" . $order_column->Product_Name . "</td>";
                                // END comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์                              
                                // Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
//                                $str_body .= '<td >' . $order_column->Product_Name . '</td>';
//                                $str_body .= "<td title='" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "' >" . $order_column->Product_Name . "</td>";
                                $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                // END Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
                                $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . $order_column->Reserv_Qty . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . $order_column->Confirm_Qty . "</td>";
                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Suggest_Location . "</td>";
                                $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                $str_body .= "<td>" . $order_column->Remark . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Product_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Sub_Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Unit_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Item_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Suggest_Location_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Actual_Location_Id . "</td>";
                                $str_body .= "</tr>";
                                $j++;
                                $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   28-10-2013
                                $sum_Cf_Qty+=$order_column->Confirm_Qty;            //add by kik    :   28-10-2013
                            }
                            echo $str_body;
                        }
                        ?>
					<tbody></tbody>
                                        <!-- show total qty : by kik : 28-10-2013-->
                                        <tfoot>
                                           <tr>
                                                 <th colspan='9' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo number_format(@$sum_Receive_Qty); ?></span></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_cf_qty"><?php echo number_format(@$sum_Cf_Qty); ?></span></th>
                                                 <th colspan='11' class ='ui-state-default indent' ></th>
                                            </tr> 
                                        </tfoot>
                                        <!-- end show total qty : by kik : 28-10-2013-->       
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>