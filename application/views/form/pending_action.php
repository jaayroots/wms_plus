<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var master_product_status = {};
    var master_product_sub_status = "";
    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Pending'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowPendingList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';
    
    var allVals = new Array();
    var nowTemp = new Date();
    var separator = "<?php echo SEPARATOR; ?>";
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var allVals = new Array();
    var form_name = "form_pending";
    var ci_prod_code = 1;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_reserv_qty = 9;
    var ci_confirm_qty = 10;
    
    //Add By por 2014-01-09 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
    var ci_price_per_unit = 12;
    var ci_unit_price = 13;
    var ci_all_price = 14;
    var statusprice = '<?php echo $statusprice;?>';
    //END Add
    
    var ci_remark = 17; //Edit by por 2014-01-15 แก้ไขจากเลข 14 เป็น 17 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    //Define Hidden Field Datatable
    var ci_prod_id = 18; //Edit by por 2014-01-15 แก้ไขจากเลข 15 เป็น 18 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_prod_status = 19; //Edit by por 2014-01-15 แก้ไขจากเลข 16 เป็น 19 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_prod_sub_status = 20; //Edit by por 2014-01-15 แก้ไขจากเลข 17 เป็น 20 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_unit_id = 21; //Edit by por 2014-01-15 แก้ไขจากเลข 18 เป็น 21 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
         
    var ci_item_id = 22; //Edit by por 2014-01-15 แก้ไขจากเลข 19 เป็น 22 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_suggest_loc = 23; //Edit by por 2014-01-15 แก้ไขจากเลข 20 เป็น 23 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_actual_loc = 24; //Edit by por 2014-01-15 แก้ไขจากเลข 21 เป็น 24 เนื่องจากมีการเพิ่ม column เลยทำให้คีย์เปลี่ยน
    var ci_unit_price_id = 25;      //Edit by por 2014-01-17 เพิ่มคีย์ รหัส unit price
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
        //Add By por 2014-01-15 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
        {name: 'ci_price_per_unit', value: ci_price_per_unit},
        {name: 'ci_unit_price', value: ci_unit_price},
        {name: 'ci_all_price', value: ci_all_price},
        {name: 'ci_unit_price_id', value: ci_unit_price_id}
        //END Add
        
    ]

    $(document).ready(function() {



        $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', {"is_pending" : "Y"}, function(data) {
            $.extend(master_product_status, JSON.parse(data));
        });
        
        $.post('<?php echo site_url() . "/pre_receive/getProductStatus"; ?>', function(data) {
             $.extend(master_product_status, JSON.parse(data));
        });
        
        $.post('<?php echo site_url() . "/pre_receive/getSubStatus"; ?>', function(data) {
            master_product_sub_status = data;
        });

        $.post('<?php echo site_url() . "/pre_receive/getProductUnit"; ?>', function(data) {
            master_product_unit = data;
        });
    
    	
        // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if($(this).val()!=''){
                $( this ).removeClass('required');
            }
        });
        // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        
        
        
        initProductTable();

        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._-]+$/i.test(value);
        }, "Document Format is invalid.");

        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark] ").attr("disabled", true);

        $("[name='is_urgent']").attr("disabled", false);
    });

    function initProductTable() {
        $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            // "bSort": false,
            // "bRetrieve": true,
            // "bDestroy": true,
            // "sPaginationType": "full_numbers",
            // "sDom": '<"H"lfr>t<"F"ip>',
            // "sScrollY": "200px",
            //"sScrollX": "100%",
            // "sScrollXInner": "110%",
            "bScrollCollapse": true,
            "aoColumnDefs": [
                         {"sWidth": "3%", "sClass": "center", "aTargets": [0]},
                         {"sWidth": "7%", "sClass": "center", "aTargets": [1]},
                         {"sWidth": "15%", "sClass": "left_text", "aTargets": [2]},
                         {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [3]}, // Edit by Ton! 20131001
                         {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
                         {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                         {"sWidth": "7%", "sClass": "left_text", "aTargets": [6]},
                         {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [7]},
                         {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [8]},
                         {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [9]},
                         {"sWidth": "5%", "sClass": "center", "aTargets": [10]},
                         {"sWidth": "5%", "sClass": "center", "aTargets": [11]},
                         {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [12]},
                         {"sWidth": "7%", "sClass": "center", "aTargets": [13]},
                         {"sWidth": "7%", "sClass": "right_text set_number_format", "aTargets": [14]},
                         {"sWidth": "7%", "sClass": "center", "aTargets": [15]},
                         {"sWidth": "7%", "sClass": "center", "aTargets": [16]},
                         {"sWidth": "7%", "sClass": "center", "aTargets": [17]}
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
                    data: JSON.stringify(master_product_status),
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    select_filter_active : true,
                    select_filter_by : "PENDING",
                    sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_prod_status);
                    return value;
                    }
                }
                , {
                    data : master_product_sub_status,
                    event: 'click',
                    type: 'select',
                    onblur: 'submit',
                    sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
                    var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
                    oTable.fnUpdate(value, rowIndex, ci_prod_sub_status);
                    return value;
                    }
                }
                , null //lot
                        , null// Serial
                        , null// MFD
                        , null// EXP
                        , null //receive qty
                        , null //unit
                        , null
                        {priceperunit} //price/unit
                        {unitofprice} //unit price
                        , null //all price
                        , null //suggest location
                , null //actual location
                        , {onblur: 'submit'} //remark
                        
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
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
        
        //ADD BY POR 2014-01-13 เพิ่มเติมกรณีไม่ได้กำหนดให้แสดงราคา ก็ให้ซ่อน 3 รายการนี้ไว้
        if(statusprice!=true){
           $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
           $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
           $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }
        //END ADD
        $('#showProductTable tbody tr td[title]').hover(function(){
            
            // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 
            var chk_title = $(this).attr('title');
            var chk_innerHTML = this.innerHTML;            
            if(chk_title != chk_innerHTML){
                $(this).show_tooltip();
            }           
            // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา 
            
        }, function(){
			$(this).hide_tooltip();
        });
    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {
	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');
	        
        $("input[name='prod_list[]']").remove();
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
                
                if(statusprice==true){
                    var price = rowData[i][ci_price_per_unit];
                    if (price == "" || price == 0) {
                        alert('Please fill all Price/Unit');
                        return false;
                    }

                    price = parseInt(price);
                    if (price < 0) {
                        alert('Negative Price/Unit is not allow');
                        return false;
                    }
                }
            }

//            if (confirm("Are you sure to action " + action_value + "?")) {
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

                global_data_form = $("#" + form_name).serialize();
                var message = "";


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
//                    switch (data.status) {
//                        case 'C001':
//                            message = "Open Job Change Status Complete";
//                            break;
//                        case 'C002':
//                            message = "Confirm Change Status  Complete";
//                            break;
//                        case 'C003':
//                            message = "Approve Change Status  Complete";
//                            break;
//                        case 'E001':
//                            message = "Save Change Status  Incomplete";
//                            break;
//                    }
//                    alert(message);
//                    url = "<?php echo site_url(); ?>/flow/flowPendingList";
//                    redirect(url);
//                }, "json");
//            }
        } else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPendingList";
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
    
    
    function calculate_qty(){
    var rowData = $('#showProductTable').dataTable().fnGetData();
    var rowData2 = $('#showProductTable').dataTable();
            var num_row = rowData.length;
            var sum_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมดต่อหน่วย
            var sum_all_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมด
        
            for (i in rowData){
                var tmp_reserv_qty = 0;
                
                var str = rowData[i][ci_reserv_qty];
                
                rowData[i][ci_reserv_qty] = str.replace(/\,/g, '');
                tmp_reserv_qty = parseFloat(rowData[i][ci_reserv_qty]); 
                
                if (!($.isNumeric(tmp_reserv_qty))){
                    tmp_reserv_qty = 0;
                }
                
                //+++++ADD BY POR 2014-01-16 เพิ่มการคำนวณราคา
                var tmp_price = 0;//ราคาต่อหน่วย
                var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ
                
                var str2 = rowData[i][ci_price_per_unit]; //ราคาต่อหน่วย
                rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
                tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
                if (!($.isNumeric(tmp_price))){
                    tmp_price = 0;
                }

                //นำ qty มาคูณกับราคาต่อหน่วย เพื่อหาราคาทั้งหมดต่อหนึ่งรายการ
                all_price = tmp_price * tmp_reserv_qty;

                sum_price = sum_price + tmp_price; //รวมราคาทุกรายการต่อหน่วย
                sum_all_price = sum_all_price + all_price; //รวมราคาทั้งหมด

                rowData2.fnUpdate(set_number_format(all_price), parseInt(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
                //END ADD
            
            }

            $('#sum_price_unit').html(set_number_format(sum_price)); 
            $('#sum_all_price').html(set_number_format(sum_all_price)); 
    }

</script>
<style>    
    .tooltip{
        width: 200px !important;
        left: 180px !important;
    }
</style>
<div class="well">
    <form id="form_pending" method=post action="">
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
            $receive_date = $receive_date = date('d/m/Y');
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
        if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
            $is_urgent = false;
        } else {
            $is_urgent = true;
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
                    <td align="right">External Document</td>
                    <td align="left"><?php echo form_input('doc_refer_ext', trim($doc_refer_ext), 'placeholder="' . DOCUMENT_EXT . '" class="required document " style="text-transform: uppercase"'); ?></td>
                    <td align="right">Internal Document</td>
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
			<td align="right">
                            <!--//add for ISSUE 3312 : by kik : 20140120-->
                            <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                        </td>
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
				<table width="100%" align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
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
                                _lang('price_per_unit'),
                                _lang('unit_price'),
                                _lang('all_price'),
                                _lang('suggest_location'),
                                _lang('actual_location'),
                                _lang('remark'),
                                "Product_Id",
                                "Product_Status",
                                "Product_Sub_Status",
                                "Unit_Id",
                                "Item_Id",
                                "Suggest_Location_Id",
                                "Actual_Location_Id",
                                "Unit Price ID"
                            );
                            $str_header = "";
                            foreach ($show_column as $index => $column) {
                                $str_header .= "<th>" . $column . "</th>";
                            }
                            ?>
						<tr><?php echo $str_header; ?></tr>
					</thead>
                        <tbody>
                        <?php
                        $sum_Receive_Qty = 0;   //add by kik : 25-10-2013
                        $sumPriceUnit = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมดต่อหน่วย
                        $sumPrice = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมด
                       
                        if (isset($pending_detail)) {
                            $str_body = "";
                            $j = 1;
                               
                            foreach ($pending_detail as $order_column) {
                                $str_body .= "<tr>";
                                $str_body .= "<td>" . $j . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Code . "</td>";
                                // comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์
                                // $str_body .= "<td title='" . $order_column->Full_Product_Name . "' >" . $order_column->Product_Name . "</td>";
                                // END comment By Akkarapol , 04/09/2013, การใช้ quote แบบนี้จะทำให้การแสดงผลที่มี ' มีปัญหา ทำให้ตัว Tooltip แสดงชื่อ Product ไม่สมบูรณ์                              
                                // Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
//                                $str_body .= "<td title=\"" . $order_column->Full_Product_Name . "\">" . $order_column->Product_Name . "</td>";
//                                $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20131127
                                $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                // END Add By Akkarapol , 04/09/2013, เปลี่ยนรูปแบบการใช้ quote ให้สามารถแสดงผลในตัว Tooltip ได้อย่างสมบูรณ์
                                $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                                $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                $str_body .= "<td style='text-align: right;'>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                                $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                //ADD BY POR 2014-01-15 เพิ่ม column เกี่ยวกับ price
                                $str_body .= "<td>" . set_number_format($order_column->Price_Per_Unit) . "</td>";
                                $str_body .= "<td>" . $order_column->unitprice_name . "</td>";
                                $str_body .= "<td>" . set_number_format($order_column->All_Price) . "</td>";
                                //END ADD
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
                                $str_body .= "<td style=\"display:none\">" . $order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-17 เพิ่ม ID ของ unit price
                                $str_body .= "</tr>";
                                $j++;
                                $sum_Receive_Qty+=$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                            
                                //ADD BY POR 2014-01-13 รวมราคาต่อหน่วยและราคารวม
                                $sumPriceUnit+=$order_column->Price_Per_Unit;
                                $sumPrice+=$order_column->All_Price;
                                //END ADD
                            }
                            echo $str_body;
                        }
                        ?>
                             
			</tbody>
                                  
                        <!-- show total qty : by kik : 25-10-2013-->
                        <tfoot>
                           <tr>
                                 <th colspan='9' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_all_qty"><?php echo set_number_format($sum_Receive_Qty);?></span></th>
                                 <th></th>
                                 <th></th>
                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                               <th></th>
                                <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                 <th colspan='12' class ='ui-state-default indent' ></th>
                            </tr> 
                        </tfoot>
                        <!-- end show total qty : by kik : 25-10-2013-->
                            
                            
				</table>
			</td>
		  </tr>
	  </table>
	</fieldset>
</div>
<?php $this->load->view('element_modal_message_alert'); ?>