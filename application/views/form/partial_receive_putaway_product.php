<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var allVals = new Array();
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var allVals = new Array();
    var separator = "<?php echo SEPARATOR; ?>";
    var form_name = "form_receive";
    var ci_prod_code = 1;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_reserv_qty = 9;
    var ci_confirm_qty = 10;
    var ci_remark = 15;
    //Define Hidden Field Datatable
    var ci_prod_id = 17;
    var ci_prod_status = 18;
    var ci_prod_sub_status = 19;
    var ci_unit_id = 20;
    var ci_item_id = 21;
    var ci_suggest_loc = 22;
    var ci_actual_loc = 23;
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
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_actual_loc', value: ci_actual_loc}

    ]

    $(document).ready(function() {
        initProductTable();

        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+$/i.test(value);
        }, "Document Format is invalid.");

        if ("RCV002" == $("[name='receive_type']").val()) {
            $("[name='is_pending']").attr("disabled", true);
            $("[name='is_repackage']").attr("disabled", false);
        } else {
            $("[name='is_repackage']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", false);
        }

        $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", true);

    });

    function initProductTable() {
        var oTable = $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "bScrollCollapse": false,
            "bPaginate": false,
            "sScrollX": "100%",
            "sScrollY": "300px",
            "sScrollXInner": "120%",
            "sDom": '<"H"lfr>t<"F"ip>'
//                                "bJQueryUI": true,
//                                "sScrollY": "400px",
//                                "sScrollX": "100%",
//                                "sScrollXInner": "150%",
//                                "bScrollCollapse": false,
//                                "bPaginate": false
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
                , {onblur: 'submit'} // Lot
                , {onblur: 'submit'} // Serial
                , {onblur: 'submit', type: 'datepicker', cssclass: 'date'} // MFD
                , {onblur: 'submit', type: 'datepicker', cssclass: 'date'} // EXP
                , {
                    sSortDataType: "dom-text", // Receive Qty
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number"
                }
                , {
                    sSortDataType: "dom-text", // Confirm Receive Qty
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number"
                }
                , {
                    //tooltip	 : 'Click to select Unit',
                    loadurl: '<?php echo site_url() . "/pre_receive/getProductUnit"; ?>',
                    loadtype: 'POST',
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                        console.log(settings);
                        var oTable = $('#showProductTable').dataTable( );
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_unit_id);
                        return value;
                    }
                }
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

        $('#showProductTable tbody td[title]').tooltip();
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
                qty = rowData[i][ci_confirm_qty];
                if (qty == "") {
                    alert('Please fill all Receive Qty');
                    return false;
                }
                qty = parseInt(qty);
                if (qty < 0) {
                    alert('Negative Receive Qty is not allow');
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
                            message = "Save Putaway Complete";
                            break;
                        case 'C002':
                            message = "Confirm Putaway Complete";
                            break;
                        case 'C003':
                            message = "Approve Putaway Complete";
                            break;
                        case 'E001':
                            message = "Save Putaway Incomplete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/flow/flowPartialReceiveList";
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
            url = "<?php echo site_url(); ?>/flow/flowPartialReceiveList";
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
            $receive_date = date("d/m/Y");
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
<?php if (isset($flow_id)) {
    echo form_hidden('flow_id', $flow_id);
} ?>
<?php if (isset($order_id)) {
    echo form_hidden('order_id', $order_id);
} ?>
<?php echo form_hidden('process_id', $process_id); ?>
<?php echo form_hidden('present_state', $present_state); ?>
<?php echo form_hidden('user_id', $user_id); ?>
<?php echo form_hidden('process_type', $process_type); ?>
<?php echo form_hidden('owner_id', $owner_id); ?>
        <fieldset class="well">
            <legend>&nbsp;&nbsp;<b>Order Receive</b>&nbsp;&nbsp;</legend>
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
			<td align="left">
                            <?php echo form_checkbox('is_pending', ACTIVE, $is_pending); ?>&nbsp;Pending
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <?php echo form_checkbox('is_repackage', ACTIVE, $is_repackage); ?>&nbsp;Re-Package</td>
		  </tr>
	  </table>
	</fieldset>
	</form>
	<fieldset class="well">
	<legend>&nbsp;&nbsp;<b>Product Detail</b>&nbsp;&nbsp;</legend>
	  <table width="100%">
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table class="well display" style="max-width: none" align="center" cellpadding="0" cellspacing="0" border="0" id="showProductTable" >
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
                            _lang('putaway_by'),
                            _lang('remark'),
                            "Split Info",
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
                            $str_header .= "<td>" . $column . "</td>";
                        }
                        ?>
						<tr><?php echo $str_header; ?></tr>
					</thead>
                        <?php
                        if (isset($order_deatil)) {
                            $str_body = "";
                            $j = 1;
                            foreach ($order_deatil as $order_column) {
                                $str_body .= "<tr>";
                                $str_body .= "<td>" . $j . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Code . "</td>";
//                                $str_body .= '<td title="' . $order_column->Full_Product_Name . '" >' . $order_column->Product_Name . '</td>';
//                                $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20131127
                                $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                $str_body .= "<td>" . $order_column->Reserv_Qty . "</td>";
                                $str_body .= "<td>" . $order_column->Confirm_Qty . "</td>";
                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Suggest_Location . "</td>";
                                $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                                if ("ATV002" != $order_column->Activity_Code) {
                                    
                                }
                                $str_body .= "<td>" . $order_column->Activity_By_Name . "</td>";
                                $str_body .= "<td>" . @$order_column->Reason_Remark . "</td>"; // Edit by Ak, 27/08/2013 , change 'Remark' to 'Reason_Remark' for show remark from HH
                                $str_body .= "<td align='center'><a ONCLICK=\"viewSplitInfo('" . $order_column->Item_Id . "')\" >" . img("css/images/icons/edit.png") . "</a></td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Product_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Sub_Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Unit_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Item_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Suggest_Location_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Actual_Location_Id . "</td>";
                                $str_body .= "</tr>";
                                $j++;
                            }
                            echo $str_body;
                        }
                        ?>
					<tbody></tbody>
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>