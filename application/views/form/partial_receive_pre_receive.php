<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var allVals = new Array();
    var separator = "<?php echo SEPARATOR; ?>";
    var form_name = "form_receive";
    var ci_prod_code = 1;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_reserv_qty = 9;
    var ci_remark = 11;
    //Define Hidden Field Datatable
    var ci_prod_id = 13;
    var ci_prod_status = 14;
    var ci_prod_sub_status = 15;
    var ci_unit_id = 16;
    var ci_item_id = 17;
    var ci_supplier_id = 18; // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เข้าไปจะได้เช็คได้ว่าข้อมูล product ที่เลือกลงมาเป็นของ supplier ที่เลือกหรือไม่
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
        {name: 'ci_supplier_id', value: ci_supplier_id} // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เข้าไปจะได้เช็คได้ว่าข้อมูล product ที่เลือกลงมาเป็นของ supplier ที่เลือกหรือไม่
    ]

    $(document).ready(function() {



        initProductTable();
        if ("RCV002" == $("[name='receive_type']").val()) {
            $("[name='is_pending']").attr("disabled", true);
            $("[name='is_repackage']").attr("disabled", false);
        } else {
            $("[name='is_repackage']").attr("disabled", true);
            $("[name='is_pending']").attr("disabled", false);
        }

        $("#est_receive_date").datepicker({}).keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#est_receive_date').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });
        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+$/i.test(value);
        }, "Document Format is invalid.");
//Define button Get onClick
        $('#getBtn').click(function() {
            allVals = new Array();
            var product_code = $('#productCode').val();
            var dataSet = {
                post_val: product_code,
                supplier_id: $("[name='shipper_id']").val()
            }
            $('#prdModalval').val(product_code);
            $.post('<?php echo site_url() . "/flow/flowPartialReceiveList" ?>', dataSet, function(data) {
                $(".modal-body").html(data);
                var oTable = $('#defDataTable').dataTable({
                    "bJQueryUI": true,
                    "bSort": false,
                    "oSearch": {"sSearch": product_code},
                    "bRetrieve": true,
                    "bDestroy": true,
                    "sPaginationType": "full_numbers"
                });
            }, "html");
            $('#mymodal').show(); // put your modal id 
        });
//Define button Search onClick
        $('#search_submit').click(function() {
            var dataSet = {
                post_val: allVals
            }
            $.post('<?php echo site_url() . "/flow/flowPartialReceiveList" ?>', dataSet, function(data) {
                var qty = "";
                var lot = "";
                var serial = "";
                var remark = "";
                var mfd = "";
                var exp = "";
                var id = "new";
                var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                $.each(data.product, function(i, item) {
                    $('#showProductTable').dataTable().fnAddData([
                        id
                                , item.Product_Code
                                , item.Product_NameEN
                                , item.Prod_Status_Value
                                , item.Prod_Sub_Status_Value
                                , lot
                                , serial
                                , mfd
                                , exp
                                , qty
                                , item.Dom_EN_Desc /*Product Unit*/
                                , remark
                                , del
                                , item.Product_Id
                                , item.Prod_Status
                                , item.Prod_Sub_Status
                                , item.Standard_Unit_Id
                                , id
                                , item.Supplier_Id // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เข้าไปจะได้เช็คได้ว่าข้อมูล product ที่เลือกลงมาเป็นของ supplier ที่เลือกหรือไม่
                    ]
                            );
                });
                initProductTable();
            }, "json");
            $('.modal.in').modal('hide');
            allVals = new Array();
        });
//        set data when enter key
        $('#formProductCode').submit(function() {
            var dataSet = {
                post_val: $('#productCode').val(),
                supplier_id: $("[name='shipper_id']").val()
            }

            $.post('<?php echo site_url() . "/pre_receive/showProductWhenEnterKey" ?>', dataSet, function(data) {

                if (data['error_msg'] != '') {
                    alert(data['error_msg']);
                }

                var qty = "";
                var lot = "";
                var serial = "";
                var remark = "";
                var mfd = "";
                var exp = "";
                var id = "new";
                var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
                $.each(data.product, function(i, item) {
                    $('#showProductTable').dataTable().fnAddData([
                        id
                                , item.Product_Code
                                , item.Product_NameEN
                                , item.Prod_Status_Value
                                , item.Prod_Sub_Status_Value
                                , lot
                                , serial
                                , mfd
                                , exp
                                , qty
                                , item.Dom_EN_Desc /*Product Unit*/
                                , remark
                                , del
                                , item.Product_Id
                                , item.Prod_Status
                                , item.Prod_Sub_Status
                                , item.Standard_Unit_Id
                                , id
                                , item.Supplier_Id // Add by Akkarapol, 27/08/2013, เพิ่ม supplier_id เข้าไปจะได้เช็คได้ว่าข้อมูล product ที่เลือกลงมาเป็นของ supplier ที่เลือกหรือไม่
                    ]
                            );
                });
                initProductTable();
            }, "json");
            $('.modal.in').modal('hide');
            allVals = new Array();
            return false;
        });
        $('#select_all').click(function() {
            var cdata = $('#defDataTable').dataTable();
            allVals = new Array();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', 'checked');
                allVals.push($this.val());
            });
        });
        $('#deselect_all').click(function() {
            var selected = new Array();
            var cdata = $('#defDataTable').dataTable();
            $(cdata.fnGetNodes()).find(':checkbox').each(function() {
                $this = $(this);
                $this.attr('checked', false);
                selected.push($this.val());
                allVals.pop($this.val());
            });
            allVals = new Array();
        });

    });
    //Define Dialog Model 
    $('#myModal').modal('toggle').css({
        'width': function() {
            return ($(document).width() * .9) + 'px'; // make width 90% of screen
        },
        'margin-left': function() {
            return -($(this).width() / 2); // center model
        }
    });
    function getCheckValue(obj) {
        var isChecked = $(obj).attr("checked");
        if (isChecked) {
            allVals.push($(obj).val());
        } else {
            allVals.pop($(obj).val());
        }
    }

    function initProductTable() {
        $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "sScrollY": "200px",
            //"bScrollCollapse": true
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
                    loaddata: function(value, settings) {
                        var is_pending = $("[name='is_pending']").prop('checked');
                        if (true == is_pending) {
                            is_pending = '<?php echo ACTIVE; ?>'
                        }
                        var dataSet = {
                            is_pending: is_pending
                        };
                        return dataSet;
                    },
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
                        console.log(settings);
                        var oTable = $('#showProductTable').dataTable();
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
                        var oTable = $('#showProductTable').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                        return value;
                    }
                }
                , {onblur: 'submit'}										// Lot
                , {onblur: 'submit'} 									// Serial
                , {onblur: 'submit', type: 'datepicker', cssclass: 'date'}	// MFD
                , {onblur: 'submit', type: 'datepicker', cssclass: 'date'}	// EXP
                , {
                    sSortDataType: "dom-text",
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
                        var oTable = $('#showProductTable').dataTable();
                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                        oTable.fnUpdate(value, rowIndex, ci_unit_id);
                        return value;
                    }
                }
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
        $('#showProductTable').dataTable().fnSetColumnVis(ci_supplier_id, false); // Add by Akkarapol, 29/08/2013, เพิ่ม ci_supplier_id เข้าไปจะได้ให้ column ของ Supplier_Id มัน hide ไว้
        $('#showProductTable tbody td[title]').tooltip();
    }

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#showProductTable').dataTable().fnDeleteRow(index);
    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#showProductTable').dataTable().fnGetData(index);
        $('#showProductTable').dataTable().fnDeleteRow(index);
        var f = document.getElementById(form_name);
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
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

            var is_pending = $("[name='is_pending']").prop('checked');
            var is_repackage = $("[name='is_repackage']").prop('checked');
            for (i in rowData) {

//            #2064, เพิ่มการตรวจสอบตอนที่จะ submit ว่า product ที่อยู่ใน datatable จะต้องเป็นสินค้าของ supplier คนที่เลือกอยู่เท่านั้น
//            DATE:2013 - 08 - 27
//            #BY:Akkarapol
//            START
                supplier_id = $("[name='shipper_id']").val();
                item_supplier_id = rowData[i][18];
                if (supplier_id != item_supplier_id) {
                    alert('Please Check Your Supplier');
                    return false;
                }
//            END #2064, เพิ่มการตรวจสอบตอนที่จะ submit ว่า product ที่อยู่ใน datatable จะต้องเป็นสินค้าของ supplier คนที่เลือกอยู่เท่านั้น

                qty = rowData[i][ci_reserv_qty];
                if (qty == "" || qty == 0) {
                    alert('Please fill all Receive Qty');
                    return false;
                }

                qty = parseInt(qty);
                if (qty < 0) {
                    alert('Negative Receive Qty is not allow');
                    return false;
                }

                if (!is_pending) {
                    prod_status = rowData[i][ci_prod_status];
                    if ("PENDING" == prod_status) {
                        alert("Product Status 'Pending' not Allow !!");
                        return false;
                    }
                } else {
                    prod_status = rowData[i][ci_prod_status];
                    if ("PENDING" != prod_status) {
                        alert("Product Status Must be 'Pending' Only!!");
                        return false;
                    }
                }

                if (is_repackage) {
                    prod_status = rowData[i][ci_prod_status];
                    if ("NORMAL" != prod_status) {
                        alert("Product Status Must be 'NORMAL' Only!!");
                        return false;
                    }
                }

            }

            if (confirm("Are you sure to action " + action_value + "?")) {
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
//				for(i in oTable){
//					var prodItem	= document.createElement("input"); 
//					prodItem.setAttribute('type',"hidden"); 
//					prodItem.setAttribute('name',"prod_list[]"); 
//					prodItem.setAttribute('value',oTable[i]);
//					f.appendChild(prodItem);
//				}
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
                            message = "Save Pre-Receive Complete";
                            break;
                        case 'C002':
                            message = "Confirm Pre-Receive Complete";
                            break;
                        case 'C003':
                            message = "Approve Pre-Receive Complete";
                            break;
                        case 'E001':
                            message = "Save Pre-Receive Incomplete";
                            break;
                    }
                    alert(message);
                    url = "<?php echo site_url(); ?>/flow/flowPartialReceiveList";
                    redirect(url)
                }, "json");
            } // close confirm action
        }
        else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPartialReceiveList";
            redirect(url)
        }
    }

    function validateForm() {
        $("form").validate({
            rules: {
                renter_id: {required: true}
                , shipper_id: {required: true}
                , consignee_id: {required: true}
                , doc_refer_ext: {required: true}
                , est_receive_date: {required: true}
                , receive_type: {required: true}
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


// Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable
    function lookup() {        
        var minChar = 2; // เอาไว้เซ็ตค่าว่าต้องการให้กี่ตัวอักษรถึงจะทำการ autocomplete
        if ($('#productCode').val().length >= minChar) {
            if ($('#productCode').val() != '') {
                $.post("<?php echo site_url(); ?>/pre_receive/showProductList", {text_search: $('#productCode').val(), supplier_id: $("[name='shipper_id']").val()}, function(val, data) {
                    if (data.length > 0) {
                        $('#suggestions').show();
                        $('#autoSuggestionsList').html(val, data);
                    }
                });
            }
        }
    }

    function fill(id, code) {
        $('#productCode').val(code);
        $('#formProductCode').submit();
        $('#productCode').val('');
        setTimeout("$('#suggestions').hide();", 1);
    }

    function hideSuggestions() {
        setTimeout("$('#suggestions').hide();", 100);
    }

// END Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable




</script>
<style>
    #myModal {
        width: 900px;	/* SET THE WIDTH OF THE MODAL */
        margin: -250px 0 0 -450px; /* CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;) */
    }

    /*Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable*/

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
    /* END Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable*/


</style>
<div class="well">
    <form id="form_receive" method="post" action="">
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
            $receive_date = "";
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
            <legend>&nbsp;Add Order Pre-Receive&nbsp;</legend>
            <table width="98%">
                <tr>
                    <td align="right">Renter</td>
                    <td align="left"><?php echo form_dropdown('renter_id', $renter_list, $renter_id, ' class="required" '); ?></td>
                    <td align="right">Shipper</td>
                    <td align="left"><?php echo form_dropdown('shipper_id', $shipper_list, $shipper_id, 'class="required" '); ?></td>
                    <td align="right">Consignee</td>
                    <td align="left"><?php echo form_dropdown('consignee_id', $consignee_list, $consignee_id, 'class="required"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Document No.</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'placeholder="Auto Generate GRN" disabled class="required" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Document External</td>
                    <td align="left"><?php echo form_input('doc_refer_ext', $doc_refer_ext, 'placeholder="' . DOCUMENT_EXT . '" class="required document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Document Internal</td>
                    <td align="left"><?php echo form_input('doc_refer_int', $doc_refer_int, 'placeholder="' . DOCUMENT_INT . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'placeholder="' . DOCUMENT_INV . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">Receive Type</td>
                    <td align="left"><?php echo form_dropdown('receive_type', $receive_list, $receive_type, "onChange=changeOption(this)"); ?></td>
                    <td align="right">ASN.</td>
                    <td align="left"><?php echo form_input('est_receive_date', $est_receive_date, 'id="est_receive_date" placeholder="Advanced Shipment Notices" '); ?></td>
                    <td align="right">Receive Date.</td>
                    <td align="left"><?php echo form_input('receive_date', $receive_date, 'id="receive_date" placeholder="Receive Date" disabled '); ?></td>
                </tr>
                <tr valign="center">
                    <td align="right">Remark</td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
			</td>
			<td align="right"></td>
			<td align="left">
                        <?php echo form_checkbox('is_pending', ACTIVE, $is_pending); ?>&nbsp;Pending
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <?php echo form_checkbox('is_repackage', ACTIVE, $is_repackage); ?>&nbsp;Re-Package</td>
		  </tr>
		  <tr>
			<td align="right"></td>
			<td align="left"></td>
			<td align="right"></td>
			<td align="left"></td>
			<td align="right"></td>
			<td align="left"></td>
		  </tr>
	  </table>
	</fieldset>
    </form>
	<fieldset class="well">
	<legend>&nbsp;Product Detail&nbsp;</legend>
	  <table width="100%">
		  <tr>
			<td align="left" width="100"><?php echo _lang('product_code'); ?></td>
			<td align="left">
                            <form id="formProductCode" action="" method="post">
                        <?php echo form_input("productCode", "", "id='productCode' placeholder='"._lang('product_code')."' autocomplete='off' onkeyup='lookup();' onblur='hideSuggestions();' "); ?> <!-- Edit by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable -->
                                
                        <!--Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable-->
                        <div class="suggestionsBox" id="suggestions" style="display:none;">
				<div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div>
			</div>
			<input type="hidden" id="product_id" name="product_code" />&nbsp;<a href="#myModal" role="button" class="btn success" data-toggle="modal" id="getBtn">Get Detail</a>
                        <!--END Add by Akkarapol, 27/08/2013, ทำ autocomplete ในส่วนของ Product Code และเมื่อคลิกที่ list product ดังกล่าว ก็จะให้ auto input to datatable-->
  
                        <input type="submit" style="display:none;">
                            </form>
                            </td>
			<td align="right"></td>
			<td align="left">&nbsp;</td>
			<td align="right">&nbsp;</td>
			<td align="left">&nbsp;</td>
		  </tr>
		  <tr align="center" >
			<td align="center" colspan="6" id="showDataTable" >
				<table align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
					<thead>
                            <?php
                            $show_column = array(
                                _lang('no')
                                , _lang('product_code')
                                , _lang('product_name')
                                , _lang('product_status')
                                , _lang('product_sub_status')
                                , _lang('lot')
                                , _lang('serial')
                                , _lang('product_mfd')
                                , _lang('product_exp')
                                , _lang('receive_qty')
                                , _lang('unit')
                                , _lang('remark')
                                , DEL
                                , "Product_Id"
                                , _lang('product_status')
                                , _lang('product_sub_status')
                                , "Unit_Id"
                                , "Item_Id"
                                , "Supplier_Id" //Add by Akkarapol, 27/08/2013, เพิ่มการตรวจสอบตอนที่จะ submit ว่า product ที่อยู่ใน datatable จะต้องเป็นสินค้าของ supplier คนที่เลือกอยู่เท่านั้น
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
                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Remark . "</td>";
                                $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Product_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Sub_Status_Code . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Unit_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Item_Id . "</td>";
                                $str_body .= "<td style-\"display:none\">" . $order_column->Supplier_Id . "</td>"; //Add by Akkarapol, 27/08/2013, เพิ่มการตรวจสอบตอนที่จะ submit ว่า product ที่อยู่ใน datatable จะต้องเป็นสินค้าของ supplier คนที่เลือกอยู่เท่านั้น
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
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Product Detail</h3>
        <input  value="" type="hidden" name="prdModalval" id="prdModalval" >
    </div>
    <div class="modal-body"><!-- working area--></div>
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
</div>