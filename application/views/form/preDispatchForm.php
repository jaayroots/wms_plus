<head>
	<meta charset="utf-8">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css" rel="stylesheet" />
	<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js"></script> 
 
    </head>
<script>
    var del_item_id = new Array;
    
    var master_container_dropdown_list = '" "';
    var master_container_size = "";
    var arr_index = new Array(
            'no'
            , 'product_code'
            , 'product_name'
            , 'product_status_label'
            , 'product_sub_status_label'
            , 'location_code'
            , 'lot'
            , 'serial'
            , 'product_mfd'
            , 'Last_Mfd' //ADD Last MFD
            , 'product_exp'
            , 'invoice_no'
            , 'container'
            , 'est_balance_qty'
            , 'reserv_qty'
            , 'unit'
            , 'price_per_unit'
            , 'unit_price'
            , 'all_price'
            , 'remark'
            , 'pallet_code'
            , 'dp_type_pallet'
            , 'del'
            , 'product_id'
            , 'product_status'
            , 'product_sub_status'
            , 'unit_id'
            , 'item_id'
            , 'inbound_id'
            , 'suggest_location'
            , 'unit_price_id'
            , 'container_id'
            );
    var hide_arr_index = new Array();
    var dp_type_link_prod_status = $.parseJSON('<?php echo $dp_type_link_prod_status; ?>');
    var global_ci_prod_status_label = arr_index.indexOf("product_status_label"); // เปลี่ยนให้เป็นตาม index ของ dataTable ใน ช่องที่เป็น Product Status

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Pre-Dispatch';
    var data_table_id_class = '#showProductTable';
    var redirect_after_save = site_url + "/flow/flowPreDispatchList";
    var allVals = []; // add global var BALL
    var dispatch_type;
    var oTable = null;
    var separator = "<?php echo SEPARATOR; ?>";
    var statusprice = "<?php echo $price_per_unit; ?>"; //add by kik : 20140113
    var conf_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    var conf_inv = '<?php echo ($conf_inv) ? true : false ?>';
    var conf_invoice_require = '<?php echo $conf_invoice_require; ?>'; //ADD BY POR 2014-10-14
    var conf_cont = '<?php echo ($conf_cont) ? true : false ?>';
    var order_id = '<?php echo empty($order_id) ? '' : $order_id ?>';
    var ci_prod_code = arr_index.indexOf("product_code");
    var ci_location_code = arr_index.indexOf("location_code");
    var ci_lot = arr_index.indexOf("lot");
    var ci_serial = arr_index.indexOf("serial");
    var ci_mfd = arr_index.indexOf("product_mfd");
    var ci_lmfd = arr_index.indexOf("Last_Mfd"); //ADD last mfd
    var ci_exp = arr_index.indexOf("product_exp");
    var ci_balance_qty = arr_index.indexOf("est_balance_qty");
    var ci_invoice = arr_index.indexOf("invoice_no");
    var ci_container = arr_index.indexOf("container");
    var ci_reserv_qty = arr_index.indexOf("reserv_qty");
    var ci_price_per_unit = arr_index.indexOf("price_per_unit");
    var ci_unit_price = arr_index.indexOf("unit_price");
    var ci_all_price = arr_index.indexOf("all_price");
    var ci_remark = arr_index.indexOf("remark");
    var ci_pallet_code = arr_index.indexOf("pallet_code");
    var ci_dp_type_pallet = arr_index.indexOf("dp_type_pallet");
    //Define Hidden Field Datatable
    var ci_prod_id = arr_index.indexOf("product_id");
    var ci_prod_status = arr_index.indexOf("product_status");
    var ci_prod_sub_status = arr_index.indexOf("product_sub_status");
    var ci_unit_id = arr_index.indexOf("unit_id");
    var ci_item_id = arr_index.indexOf("item_id");
    var ci_inbound_id = arr_index.indexOf("inbound_id");
    var ci_suggest_loc = arr_index.indexOf("suggest_location");
    var ci_unit_price_id = arr_index.indexOf("unit_price_id");
    var ci_cont_id = arr_index.indexOf("container_id");
    //var config_pallet = "<?php echo $conf_pallet; ?>"; //add for ISSUE 2549 : by kik : 20140217

    var ci_list = [
    {name: 'ci_prod_code', value: ci_prod_code},
    {name: 'ci_location_code', value: ci_location_code},
    {name: 'ci_lot', value: ci_lot},
    {name: 'ci_serial', value: ci_serial},
    {name: 'ci_mfd', value: ci_mfd},
    {name: 'ci_lmfd', value: ci_lmfd},
    {name: 'ci_exp', value: ci_exp},
    {name: 'ci_balance_qty', value: ci_balance_qty},
    {name: 'ci_invoice', value: ci_invoice}, // add by por for show invoice : 20140813
    {name: 'ci_container', value: ci_container}, // add by por for show invoice : 20140813
    {name: 'ci_reserv_qty', value: ci_reserv_qty},
    {name: 'ci_price_per_unit', value: ci_price_per_unit}, // add by kik : 2014-01-13
    {name: 'ci_unit_price', value: ci_unit_price}, // add by kik : 2014-01-13
    {name: 'ci_all_price', value: ci_all_price}, // add by kik : 2014-01-13
    {name: 'ci_remark', value: ci_remark},
    {name: 'ci_prod_id', value: ci_prod_id},
    {name: 'ci_prod_status', value: ci_prod_status},
    {name: 'ci_unit_id', value: ci_unit_id},
    {name: 'ci_item_id', value: ci_item_id},
    {name: 'ci_inbound_id', value: ci_inbound_id},
    {name: 'ci_suggest_loc', value: ci_suggest_loc},
    {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
    {name: 'ci_unit_price_id', value: ci_unit_price_id},
    {name: 'ci_pallet_code', value: ci_pallet_code}, // ADD BY POR 2014-02-19
    {name: 'ci_dp_type_pallet', value: ci_dp_type_pallet}, // ADD BY POR 2014-02-19
    {name: 'ci_invoice', value: ci_invoice}, // add by kik for show invoice : 20140709
    {name: 'ci_container', value: ci_container},
    {name: 'ci_cont_id', value: ci_cont_id}
    ];
    $(document).ready(function() {
        var active_bot_gen = '<?php echo $active_bot_gen; ?>';
        // console.log(active_bot_gen);
        if(active_bot_gen != '1'){
           $( "#GEN" ).prop( "disabled", true );
           $("#GEN").css('background','#b9b9b9');
           $("#GEN").css('border','#b9b9b9');
        }
    
    
    if (order_id == ''){
    $('#btn_action_reject').hide();
    };


    if ($('#dispatch_type_select :selected').val() == 'DP0'){
//            Area for Dispatch type 'Manual'

    } else{
    $("#productStatus_select").find("option:contains('Normal')").each(function(){
    $(this).attr("selected", "selected");
    $("#productStatus_select").attr("disabled", "disabled");
    });
    }

    $('#dispatch_type_select').change(function(){
    var check_status = dp_type_link_prod_status[$.trim($('#dispatch_type_select :selected').text())];
    $("#productStatus_select").removeAttr('disabled');
    $('#productStatus_select option:eq(0)').prop('selected', true);
        $("#productStatus_select").find("option:contains('" + check_status + "')").each(function()
        {
        $(this).attr("selected", "selected");
        $("#productStatus_select").attr("disabled", "disabled");
        });
        });

        $.post('<?php echo site_url() . "/pre_receive/getContainerSize"; ?>', function(data) {
        master_container_size = data;
        });
        if (order_id != ''){
        $.post('<?php echo site_url("/receive/getContainerDropdownList"); ?>', {'order_id' : order_id}, function(data) {
        master_container_dropdown_list = data;
        });
        }

    $("#add_container").click(function(){
    //console.log($("#container_list").val());
    $('#dynamic_modal').on('show.bs.modal', function (e) {

    var dynamic_modal_body = $("#dynamic_modal_body");
    var container_list = $("#container_list");
    var FieldCount = 1;
    var container_data = $("#doc_refer_container").val();
    var container_size = $("#doc_refer_con_size").val();
    var container_data_confirm = '<?php echo $container_list; ?>'; //ค่านี้จะได้จากตอน confirm and approve

    if (container_list.val() == '{'){
    container_list.val(container_data_confirm);
    }
//                console.log(container_list.val());
    $('#save_container_data').show();
    $("#dynamic_modal_label").html("Container List");
    if (container_list.val() != "" && container_list.val() != '{}') {   //behide save
    var get_list_data = $.parseJSON(container_list.val());
    $.each(get_list_data, function(idx2, val2){
    var objDiv = $("<div>");
    var objConID2 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val(val2.id);
    var objInput2 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(val2.name).css({"textTransform":"uppercase", "width":"350px"});
    var objSelect2 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width":"120px"});
    var _master_container_size2 = $.parseJSON(master_container_size);
    $.each(_master_container_size2, function(idx, val){
    var objOption2 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect2);
    if (val2.size == val.Id){
    objOption2.prop("selected", true);
    }
    });
    if (idx2 == 0){
    var objImage2 = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
    objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
    dynamic_modal_body.empty().append(objDiv);
    } else{
    var objImage2 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "marginBotton":"3px", "marginLeft":"3px", "cursor":"pointer"});
    objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
    dynamic_modal_body.append(objDiv);
    }
    });
    } else{  //case open
    var objConID = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]");
    var objInput = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(container_data).css({"textTransform" : "uppercase", "width": "350px"});
    var objSelect = $("<select>").prop("class", "con_size").prop("class", "con_size").css({"width" : "120px"}).prop("name", "con_size[]").val(container_size);
    var _master_container_size = $.parseJSON(master_container_size);
    $.each(_master_container_size, function(idx, val){
    var objOption = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect);
    if (container_size == val.Id){
    objOption.prop("selected", true);
    }
    });
    var objImage = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
    dynamic_modal_body.empty().append(objConID).append(objInput).append(objSelect).append(objImage);
    }

    $(".add_more_btn").click(function (e)  //on add input button click
    {
    FieldCount++; //text box added increment
    var objDiv = $("<div>");
    var objConID3 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val('NEW');
    var objInput3 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").css({"textTransform":"uppercase", "width":"350px"});
    var objSelect3 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width":"120px"});
    var _master_container_size3 = $.parseJSON(master_container_size);
    $.each(_master_container_size3, function(idx, val){
    var objOption3 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect3);
    });
    var objImage3 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "height":"24px", "marginBottom":"3px", "marginLeft":"3px", "cursor":"pointer"});
    objDiv.append(objConID3).append(objInput3).append(objSelect3).append(objImage3);
    dynamic_modal_body.append(objDiv);
    return false;
    });
    dynamic_modal_body.on("click", ".removeclass", function(e) {
    $(this).parent('div').remove(); //remove text box
    return false;
    });
    });
    $('#dynamic_modal').modal({
    keyboard: false
            , backdrop: "static"
    });
    });
    $('#save_container_data').click(function (e) {
    var temp = {};
    var data = "";
    var container_list = $(".container_list");
    var container_list_size = $(".con_size");
    var container_list_id = $(".container_id");
    var container_size_name = '';
    $.each(container_list, function(idx, val){
    var container_size_id = $(container_list_size[idx]).val();
    var container_id = $(container_list_id[idx]).val();
    var container_name = $(val).val();
    var objTemp = {};
    objTemp['id'] = container_id;
    objTemp['name'] = container_name.toUpperCase();
    objTemp['size'] = container_size_id;
    temp[idx] = objTemp;
    });
    $("#container_list").val(JSON.stringify(temp));
    //SENT VALUE FOR EDIT
    if (order_id != ''){  //case confirm
    $.post('<?php echo site_url() . "/receive/updateContainer" ?>', $("#frmPreDispatch").serialize(), function(data) {
//                        console.log(data.val);
    if (data.val == 'NO') {
    alert('Can not save changes');
    } else {
    $('#showProductTable').dataTable().makeEditable({
    data: data,
            event: 'click',
            type: 'select',
            onblur: 'submit',
            sUpdateURL: function(value, settings) {
            var oTable = $('#showProductTable').dataTable();
            var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
            oTable.fnUpdate(value, rowIndex, ci_cont_id);
            return value;
            }
    });
    var newData = jQuery.parseJSON(data);
    var $el = $("#doc_refer_container");
    $el.empty(); // remove old options
    $.each(newData, function(value, label) {
    $el.append($("<option></option>")
            .attr("value", value).text(label));
    });
    initProductTable();
    master_container_dropdown_list = data;
    alert('Save changes success');
    $('#dynamic_modal').modal("hide");
    }
    });
    } else{  //case open
    var newData = temp;
    var $el = $("#doc_refer_container");
    $el.empty(); // remove old options
    var temp_list = {};
    var i = 0;
    $.each(newData, function(value, obj) {
    //var objList = {};
    var label = obj.name + "  " + $(".con_size:first option[value='" + obj.size + "']").text();
    $el.append($("<option></option>")
            .attr("value", value).text(label));
    temp_list[i] = obj.name + "  " + $(".con_size:first option[value='" + obj.size + "']").text(); //ADD BY POR 2014-08-14 : กำหนดสำหรับแสดงใน select  container datatable
    i++;
    });
    master_container_dropdown_list = JSON.stringify(temp_list);
    initProductTable();
    $("#doc_refer_con_size").val($("#doc_refer_container_size").val());
    $('#dynamic_modal').modal("hide");
    }


    });
    /**
     * Search Product Code By AutoComplete
     */
    $("#productCode").autocomplete({
    minLength: 0,
            search: function(event, ui) {
            $('#highlight_productCode').attr("placeholder", '');
            },
            source: function(request, response) {
            $.ajax({
            url: "<?php echo site_url(); ?>/product_info/ajax_show_product_list",
                    dataType: "json",
                    type:'post',
                    data: {
                    text_search: $('#productCode').val()
                    },
                    success: function(val, data) {
                    if (val != null){
                    response($.map(val, function(item) {
                    return {
                    label: item.product_code + ' ' + item.product_name,
                            value: item.product_code
                    }
                    }));
                    }
                    },
            });
            },
            open: function(event, ui) {
            var auto_h = $(window).innerHeight() - $('#table_of_productCode').position().top - 50;
            $('.ui-autocomplete').css('max-height', auto_h);
            },
            focus: function(event, ui) {
            $('#highlight_productCode').attr("placeholder", ui.item.label);
            return false;
            },
            select: function(event, ui) {
            $('#highlight_productCode').attr("placeholder", '');
            $('#productCode').val(ui.item.value);
            return false;
            },
            close: function(){
            $('#highlight_productCode').attr("placeholder", '');
            }
    });
    // Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
    $('.required').each(function() {
    if ($(this).val() != '') {
    $(this).removeClass('required');
    }
    });
    // END Add By Akkarapol, 21/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

    // Add By Akkarapol, 21/09/2013, เพิ่ม onKeyup ของช่อง Document External ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
    $('[name="doc_refer_ext"]').keyup(function() {
    if ($(this).val() != '') {
    $(this).removeClass('required');
    } else {
    $(this).addClass('required');
    }
    });
    // END Add By Akkarapol, 21/09/2013, เพิ่ม onKeyup ของช่อง Document External ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

    // Add By Ball
    // Prevent unwant redirect pages
    window.onbeforeunload = function() {
    return "You have not yet saved your work.Do you want to continue? Doing so, may cause loss of your work?";
    };
    // If found key F5 remove unload event!!
    $(document).bind('keydown keyup', function(e) {
    if (e.which === 116) {
    window.onbeforeunload = null;
    }
    if (e.which === 82 && e.ctrlKey) { // F5 with Ctrl
    window.onbeforeunload = null;
    }
    });
    // Add By Ball

    //initProductTable();
    reInitProductTable();
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    //Est. Dispatch Date
    $("#estDispatchDate").datepicker().keypress(function(event) {
    event.preventDefault();
    }).on('changeDate', function(ev) {
    //$('#estDispatchDate').datepicker('hide');
    }).bind("cut copy paste", function(e) {
    e.preventDefault();
    });
    //Product Mfd
    $("#productMfd").datepicker().keypress(function(event) {
//            event.preventDefault(); //comment by kik(26-09-2013)
    }).on('changeDate', function(ev) {
    //$('#productMfd').datepicker('hide');
    }).bind("cut copy paste", function(e) {
    e.preventDefault();
    });
    //Product Exp
    $("#productExp").datepicker().keypress(function(event) {
//            event.preventDefault(); //comment by kik(26-09-2013)
    }).on('changeDate', function(ev) {
    //$('#productExp').datepicker('hide');
    }).bind("cut copy paste", function(e) {
    e.preventDefault();
    });
    $.validator.addMethod("document", function(value, element) {
    return this.optional(element) || /^[a-zA-Z0-9._/\\,-]+$/i.test(value);
    }, "Document Format is invalid.");
    $('#getBtn').click(function() {
           $( "#GEN" ).prop( "disabled", true );
           $("#GEN").css('background','#b9b9b9');
           $("#GEN").css('border','#b9b9b9');
//            if ($('#productCode').val() == "") {
//                alert("Please fill <?php // echo _lang('product_code');            ?>");
//                $('#productCode').focus();
//                return false;
    //comment by por 2014-09-25 change add container on dispatch HH
//            } else if( conf_cont == true && $("#doc_refer_container option").length <= 0){
//                alert("Please input container");
//                $('#add_container').click();
//                return false;
//            } else
    if ($("[name='doc_refer_ext']").val() == "") {
    alert("Please fill <?php echo _lang('document_ext'); ?>");
    $('#doc_refer_ext').focus();
    return false;
    } else {
    showModalTable();
    // Edit By Akkarapol, 21/01/2014,set dataTable bind search when enter key
    $('#modal_data_table_filter label input')
            .unbind('keypress keyup')
            .bind('keypress keyup', function(e){
            if (e.keyCode == 13){
            oTable.fnFilter($(this).val());
            }
            });
    // Edit By Akkarapol, 21/01/2014,set dataTable bind search when enter key

    }
    });
//      Add By kik, 19/11/2013,  add event when enter key
    
    
    $('#formProductCode').submit(function() {
    $("#preload").show();
    if ($('#doc_refer_ext').val() == "") {
    alert("Please fill <?php echo _lang('document_ext'); ?>");
    $('#doc_refer_ext').focus();
    return false;
    }
<?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
            if ($('#productCode').val() == "") {
            alert("Please fill <?php echo _lang('product_code'); ?>");
            $('#productCode').focus();
            return false;
            }
<?php endif; ?>

    //บังคับให้กรอก container ก่อนถึงจะ enter ได้
    /* COMMENT BY POR 2014-09-27 : not use container this page use again in dispatch HH
     if(conf_cont){
     if($("#doc_refer_container option").length <= 0){
     alert("Please input container");
     $('#add_container').click();
     return false;
     }
     }
     */
    if ('<? echo $this->config->item('gen_dispatch_record') ?>' == '1') {

    var product_code = $('#productCode').val();
    var productStatus_select = $('#productStatus_select').val();
    var productSubStatus_select = $('#productSubStatus_select').val();
    var productLot = $('#productLot').val();
    var productSerial = $('#productSerial').val();
    var productMfd = $('#productMfd').val();
    var productExp = $('#productExp').val();
    //add for ISSUE 2549 : by kik : 20140217
    if (conf_pallet){
    var palletCode = $('#palletCode').val();
    var palletIsFull = $('[name="palletIsFull"]:checked').val();
    var palletDispatchType = $('[name="palletDispatchType"]:checked').val();
    var chkPallet = 0;
    if ($("#chkPallet").is(':checked')){
    var chkPallet = 1;
    }
    }


    var oTable = $('#showProductTable').dataTable().fnGetData();
    var arr_select_inbound_id = new Array;
    var arr_select_reserv_qty = new Array;
    for (i in oTable) {
    arr_select_inbound_id.push(oTable[i][ci_inbound_id]);
    arr_select_reserv_qty.push(oTable[i][ci_reserv_qty]);
    }

    if (conf_pallet && chkPallet == 1){
    var dataSet = {productCode_val: product_code
            , productStatus_val: productStatus_select
            , productSubStatus_val: productSubStatus_select
            , productLot_val: productLot
            , productSerial_val: productSerial
            , productMfd_val: productMfd
            , productExp_val: productExp
            , palletCode_val : palletCode
            , palletIsFull_val : palletIsFull
            , palletDispatchType_val : palletDispatchType
            , chkPallet_val : chkPallet
            , arr_select_inbound_id : arr_select_inbound_id
            , stage : 1
            , arr_select_reserv_qty : arr_select_reserv_qty
<?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
            , qty_of_sku : $('#qty_of_sku').val()
<?php endif; ?>
    }
    } else{
    var dataSet = {productCode_val: product_code
            , productStatus_val: productStatus_select
            , productSubStatus_val: productSubStatus_select
            , productLot_val: productLot
            , productSerial_val: productSerial
            , productMfd_val: productMfd
            , productExp_val: productExp
            , arr_select_inbound_id : arr_select_inbound_id
            , stage : 1
            , arr_select_reserv_qty : arr_select_reserv_qty
<?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
            , qty_of_sku : $('#qty_of_sku').val()
<?php endif; ?>
    }
    }
        // console.log(arr_select_inbound_id);
        // console.log('BB');
        // return false    
    var oTable = $('#showProductTable').dataTable();
    var recordsTotal = oTable.fnSettings().fnRecordsTotal() + 1;
    $.post('<?php echo site_url() . "/pre_dispatch/showAndGenSelectData" ?>', dataSet, function(data) {
    //ADD BY POR 2014-01-09 กำหนดให้ ราคาต่อหน่วย,หน่วยของราคา และราคารวม เป็นค่าว่างในเบื้องต้น

    if (data.alert != "OK"){
    alert(data.alert);
    $('#productCode').focus().select();
    $("#preload").hide();
    return false;
    }

//                    var unitprice="";
    var allprice = "";
    var price = set_number_format(0);
    //END ADD

    var dispatch_qty = "";
    var remark = "";
    var arr_row_gen = new Array();
    $.each(data.product, function(i, item) {
    var invoice = "";
    var container = "";
    var unit_Price_value = "";
    var pallet_code = "";
    var type_pallet = "";
    var unit_price = "";
    var container_id = "";
    //add by kik : 20140113
//                        if(item.Price_Per_Unit != ""){
//                            price=item.Price_Per_Unit;
//                        }

    if (statusprice){
    if (item.Price_Per_Unit != ""){
    price = set_number_format(item.Price_Per_Unit);
    }

    unit_Price_value = item.Unit_Price_value;
    unit_price = item.Unit_Price_Id;
    }
    //end add by kik : 20140113

    var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
    dispatch_qty = item.Reserve_Qty;
    //ADD BY POR 20114-02-19 ตรวจสอบกรณี dispatch_type=FULL จะไม่สามารถแก้ไข qty ได้เนื่องจากเราจะ change ทั้ง pallet
    if (conf_pallet){
    if (item.DP_Type_Pallet == "FULL"){
    $dispatch_qty = item.Est_Balance_Qty; //ให้มีค่าเท่ากับ Balance_Qty เนื่องจากบังคับให้ยกไปทั้งก้อน
    var dispatch_qty = $($dispatch_qty).text();
    //del = "DEL";
    del = "<a ONCLICK=\"removePalletItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"; // ADD BY POR 2014-02-20 credit by kik
    }

    pallet_code = item.Pallet_Code;
    type_pallet = item.DP_Type_Pallet;
    }

    oTable.fnAddData([
            recordsTotal + i
            , item.Product_Code
            , item.Product_NameEN
            , item.Status_Value
            , item.Sub_Status_Value
            , item.Actual_Location
            , item.Product_Lot
            , item.Product_Serial
            , item.Product_Mfd
            , item.Last_Mfd //ADD LastMFD 
            , item.Product_Exp
            , invoice
            , container
            , item.Est_Balance_Qty
            , set_number_format(dispatch_qty)
            , item.Unit_Value
            , price                     //add by kik : 20140113
            , unit_Price_value     //add by kik : 20140113
            , allprice                  //add by kik : 20140113
            , remark
            , pallet_code //ADD BY POR 2014-02-19 เพิ่มให้แสดง Pallet_Code ด้วย
            , type_pallet       //ADD BY POR : 2014-02-19
            , del
            , item.Product_Id
            , item.Product_Status
            , item.Product_Sub_Status
            , item.Unit_Id
            , 'new'
            , item.Inbound_Id
            , item.Actual_Location_Id
            , unit_price        //add by kik : 20140113
            , container_id
    ]);
//                        calculate_qty();

    var new_td_item = $('td:eq(1)', $('#showProductTable tr:last'));
    new_td_item.addClass("td_click");
    new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.Product_Code + '"' + ',' + item.Inbound_Id + ')');
    if (conf_pallet){
    if (item.DP_Type_Pallet == "FULL"){
    var dp_type_full = $('td:eq(' + ci_reserv_qty + ')', $('#showProductTable tr:last'));
    dp_type_full.addClass("readonly");
    }
    }

    arr_row_gen.push($("#showProductTable tr:last").index());
    });
    initProductTable(function(){

//                        $("#preload").show();

//                        console.log(arr_row_gen);
    $.each(arr_row_gen, function(key, rowPosition) {
//                          var rowPosition = $("#showProductTable tr:last").index();
//                            console.log(rowPosition);
    var value = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_reserv_qty];
    if (validateForm() === true) {
//                                    var columnPosition = ci_reserv_qty;
//                                    var dataTableColumnPosition = ci_reserv_qty;
//                                    var sColumnName = oTable.fnSettings().aoColumns[dataTableColumnPosition].sTitle;

    var doc_refer_ext = $("[name='doc_refer_ext']").val().toUpperCase();
    var product_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_id];
    var product_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_code];
    var product_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_status];
    var product_sub_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_sub_status];
    var suggest_loc = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_suggest_loc];
    var lot = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_lot];
    var serial = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_serial];
    var mfd = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_mfd];
    var exp = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_exp];
    var reserv_qty = value;
    var unit_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_id];
    var price_per_unit = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_price_per_unit];
    var unit_price_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_price_id];
    var all_price = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_all_price];
    var remark = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_remark];
    var pallet_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_pallet_code];
    var item_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_item_id];
    var inbound_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_inbound_id];
    var callback_count = 1;
    var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
    if (typeof order_id == 'undefined'){

    var dataSet = $("#frmPreDispatch").serialize();
    $.ajax({
    type: "POST",
            url: '<?php echo site_url() . "/pre_dispatch/ajaxCreateDocument" ?>',
            data: dataSet,
            dataType: 'json',
            async: false,
            success: function (datas) {

            if (datas.success != "OK"){
            $("#preload").hide();
            call_modal_alert(datas, null);
            } else{
            $('#document_no').val(datas.document_no);
            order_id = datas.order_id;
            var frmPreDispatch = document.getElementById("frmPreDispatch");
            var append_order_id = document.createElement("input");
            append_order_id.setAttribute('type', "hidden");
            append_order_id.setAttribute('name', "order_id");
            append_order_id.setAttribute('value', datas.order_id);
            frmPreDispatch.appendChild(append_order_id);
            var append_flow_id = document.createElement("input");
            append_flow_id.setAttribute('type', "hidden");
            append_flow_id.setAttribute('name', "flow_id");
            append_flow_id.setAttribute('value', datas.flow_id);
            frmPreDispatch.appendChild(append_flow_id);
            var append_token = document.createElement("input");
            append_token.setAttribute('type', "hidden");
            append_token.setAttribute('name', "token");
            append_token.setAttribute('value', datas.token);
            frmPreDispatch.appendChild(append_token);
            $('#btn_action_reject').show();
            }

            callback_count--;
            }
    });
    } else{
    callback_count--;
    }

    var initInterval = setInterval(function(){
    if (callback_count == 0) {
    clearInterval(initInterval);
    var dataSet = {
    order_id: order_id
            , doc_refer_ext: doc_refer_ext
            , product_id: product_id
            , product_code: product_code
            , product_status: product_status
            , product_sub_status: product_sub_status
            , suggest_loc: suggest_loc
            , lot: lot
            , serial: serial
            , mfd: mfd
            , exp: exp
            , reserv_qty: reserv_qty
            , unit_id: unit_id
            , price_per_unit: price_per_unit
            , unit_price_id: unit_price_id
            , all_price: all_price
            , remark: remark
            , pallet_code: pallet_code
            , item_id: item_id
            , inbound_id: inbound_id
    }
    // console.log('790');
    $.ajax({
    type: "POST",
            url: '<?php echo site_url() . "/pre_dispatch/ajaxSaveEditedRecordReservQty" ?>',
            data: dataSet,
            dataType: 'json',
            async: false,
            success: function (datas) {
//                                                oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
            if (datas.success != "OK"){
            call_modal_alert(datas, null);
            } else{
            oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
            oTable.fnUpdate(value, rowPosition, ci_reserv_qty);
//                                                    oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
            socket.emit('update_est_balance', db_config, uniqid, datas.item_id);
            }
            }
    });
    calculate_qty();
    $("#preload").hide();
    }
    }, 500);
    } else {
    $("#preload").hide();
    alert("Please Check Your Require Information (Red label).");
    return false;
    }
    });
    });
    $('#qty_of_sku').val('');
    $('#productCode').val('').focus();
    }, "json");
    } else{
    $('#getBtn').click();
    }

    return false;
    });
//       END Add By kik, 19/11/2013,  add event when enter key





    $('#select_all').click(function() {
    var cdata = $('#modal_data_table').dataTable();
//            allVals = new Array(); // Comment By Akkarapol, 29/11/2013, คอมเม้นต์ทิ้งเพราะถ้าเกิดเซ็ตให้ allVals มันเป็น new Array() แล้ว จะทำให้ ค่าที่เคยเลือกไว้หายไปด้วย แล้วจะได้ค่าที่ไม่ตรงตามความต้องการ จึงจำเป็นต้องปิดส่วนนี้ไป
    $(cdata.fnGetNodes()).find(':checkbox').each(function() {
    $this = $(this);
    $this.attr('checked', 'checked');
    allVals.push($this.val());
    });
    //alert('select all '+allVals);
    });
    $('#deselect_all').click(function() {
    var selected = new Array();
    var cdata = $('#modal_data_table').dataTable();
    $(cdata.fnGetNodes()).find(':checkbox').each(function() {
    $this = $(this);
    $this.attr('checked', false);
    selected.push($this.val());
    allVals.pop($this.val());
    });
    allVals = new Array();
    });
    $('#search_submit').click(function() {
//            alert(allVals);
    if (allVals.length == 0) {
    alert("No Product was selected ! Please select a product or click cancle button to exit.");
    return false;
    }
    var oTable = $('#showProductTable').dataTable();
//            var oSettings = oTable.fnSettings();
    var recordsTotal = oTable.fnSettings().fnRecordsTotal() + 1;
    $.ajax({
    type: 'post',
            dataType: 'json',
            url: '<?php echo site_url() . "/pre_dispatch/showSelectData" ?>', // in here you should put your query
            data: 'post_val=' + allVals + "&tableName=showProductTable&dp_type_pallet_val=" + dispatch_type, // here you pass your id via ajax .
            // in php you should use $_POST['post_id'] to get this value
            success: function(r){
            //ADD BY POR 2014-01-09 กำหนดให้ ราคาต่อหน่วย,หน่วยของราคา และราคารวม เป็นค่าว่างในเบื้องต้น
            var price = set_number_format(0);
            var allprice = "";
            //END ADD

            var dispatch_qty = "";
            var remark = "";
            var arr_row_pallet = new Array();
            $.each(r.product, function(i, item) {
            //add by kik : 20140113
            var invoice = "";
            var container = "";
            var unit_Price_value = "";
            var pallet_code = "";
            var type_pallet = "";
            var unit_price = "";
            var container_id = "";
            if (statusprice){
            if (item.Price_Per_Unit != ""){
            price = item.Price_Per_Unit;
            }

            unit_Price_value = item.Unit_Price_value;
            unit_price = item.Unit_Price_Id;
            }
            //end add by kik : 20140113
            var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
            var can_add_new_row = true;
            //ADD BY POR 20114-02-19 ตรวจสอบกรณี dispatch_type=FULL จะไม่สามารถแก้ไข qty ได้เนื่องจากเราจะ change ทั้ง pallet
            if (conf_pallet){
            if (item.DP_Type_Pallet == "FULL"){


            var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
            if (typeof order_id == 'undefined'){

            var dataSet = $("#frmPreDispatch").serialize();
            $.ajax({
            type: "POST",
                    url: '<?php echo site_url() . "/pre_dispatch/ajaxCreateDocument" ?>',
                    data: dataSet,
                    dataType: 'json',
                    async: false,
                    success: function (datas) {

                    if (datas.success != "OK"){
                    call_modal_alert(datas, null);
                    can_add_new_row = false;
                    } else{
                    $('#document_no').val(datas.document_no);
                    order_id = datas.order_id;
                    var frmPreDispatch = document.getElementById("frmPreDispatch");
                    var append_order_id = document.createElement("input");
                    append_order_id.setAttribute('type', "hidden");
                    append_order_id.setAttribute('name', "order_id");
                    append_order_id.setAttribute('value', datas.order_id);
                    frmPreDispatch.appendChild(append_order_id);
                    var append_flow_id = document.createElement("input");
                    append_flow_id.setAttribute('type', "hidden");
                    append_flow_id.setAttribute('name', "flow_id");
                    append_flow_id.setAttribute('value', datas.flow_id);
                    frmPreDispatch.appendChild(append_flow_id);
                    var append_token = document.createElement("input");
                    append_token.setAttribute('type', "hidden");
                    append_token.setAttribute('name', "token");
                    append_token.setAttribute('value', datas.token);
                    frmPreDispatch.appendChild(append_token);
                    $('#btn_action_reject').show();
                    }
                    }
            });
            }


            $dispatch_qty = item.Est_Balance_Qty; //ให้มีค่าเท่ากับ Balance_Qty เนื่องจากบังคับให้ยกไปทั้งก้อน
            var dispatch_qty = $($dispatch_qty).text();
            //del = "DEL";
            del = "<a ONCLICK=\"removePalletItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"; // ADD BY POR 2014-02-20 credit by kik
            }

            pallet_code = item.Pallet_Code;
            type_pallet = item.DP_Type_Pallet;
            }

            if (!can_add_new_row){
            return false;
            }

            //var get_list_container = $.parseJSON(master_container_dropdown_list);

//                        $.each(get_list_container, function(idx2, val2){
//                            var objDiv = $("<div>");
//                            var objConID2 = $("<input>").prop("type", "hidden").prop("class", "container_id").prop("name", "con_id[]").val(val2.id);
//                            var objInput2 = $("<input>").prop("type", "text").prop("placeholder", "<?php echo DOCUMENT_CONTAINER ?>").prop("class", "container_list").prop("name", "container[]").val(val2.name).css({"textTransform":"uppercase", "width":"350px"});
//                            var objSelect2 = $("<select>").prop("class", "con_size").prop("name", "con_size[]").css({"width":"120px"});
//                            var _master_container_size2 = $.parseJSON(master_container_size);
//                            $.each(get_list_container, function(idx, val){
//                                var objOption2 = $("<option>").val(val.Id).html(val.No + val.Unit_Code).appendTo(objSelect2);
//                                if (val2.size == val.Id){
//                                    objOption2.prop("selected", true);
//                                }
//                            });
//                            if (idx2 == 0){
//                                var objImage2 = $("<img>").prop("class", "add_more_btn").prop("src", "<?php echo base_url("images/add.png") ?>").css({"width": "22px", "height": "22px", "marginBottom": "3px", "cursor": "pointer"});
//                                objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
//                                dynamic_modal_body.empty().append(objDiv);
//                            }else{
//                                var objImage2 = $("<img>").prop("class", "removeclass").prop("src", "<?php echo base_url("images/delete.png") ?>").css({"width":"24px", "height":"24px", "marginBotton":"3px", "marginLeft":"3px", "cursor":"pointer"});
//                                objDiv.append(objConID2).append(objInput2).append(objSelect2).append(objImage2);
//                                dynamic_modal_body.append(objDiv);
//                            }
//                        });

            //console.log(master_container_dropdown_list);
            // console.log(item.Last_Mfd);
            oTable.fnAddData([
                    recordsTotal + i
                    , item.Product_Code
                    , item.Product_NameEN
                    , item.Status_Value
                    , item.Sub_Status_Value
                    , item.Actual_Location
                    , item.Product_Lot
                    , item.Product_Serial
                    , item.Product_Mfd
                    , item.Last_Mfd //ADD LastMFD 
                    , item.Product_Exp
//                  , item.Balance_Qty comment by kik (03-10-2013)
                    , invoice
                    , container
                    , item.Est_Balance_Qty   // add by kik (03-10-2013)
                    , set_number_format(dispatch_qty)
                    , item.Unit_Value
                    , price                         //add by kik : 20140113
                    , unit_Price_value        //add by kik : 20140113
                    , allprice                      //add by kik : 20140113
                    , remark
                    , pallet_code //ADD BY POR 2014-02-19 เพิ่มให้แสดง Pallet_Code ด้วย
                    , type_pallet       //ADD BY POR : 2014-02-19
                    , del
                    , item.Product_Id
                    , item.Product_Status
                    , item.Product_Sub_Status
                    , item.Unit_Id
                    , 'new'
                    , item.Inbound_Id
                    , item.Actual_Location_Id
                    , unit_price         //add by kik : 20140113
                    , container_id
            ]);
            calculate_qty();
            // add by kik : 2013-11-12
            var new_td_item = $('td:eq(1)', $('#showProductTable tr:last'));
            new_td_item.addClass("td_click");
            new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.Product_Code + '"' + ',' + item.Inbound_Id + ')');
            // end add by kik

            if (conf_pallet){
            if (item.DP_Type_Pallet == "FULL"){
            var dp_type_full = $('td:eq(' + ci_reserv_qty + ')', $('#showProductTable tr:last'));
            dp_type_full.addClass("readonly");
            arr_row_pallet.push($("#showProductTable tr:last").index());
            }
            }


            var class_balance_qty = $('td:eq(' + ci_balance_qty + ')', $('#showProductTable tr:last'));
            class_balance_qty.addClass("label_inbound_id_" + item.Inbound_Id);
            
            var Last_Mfd = item.Last_Mfd;
            var Product_Mfd = item.Product_Mfd; 

            Last_Mfd = Last_Mfd.split("/");
            Last_Mfd =  Last_Mfd[1]+'/'+Last_Mfd[0]+'/'+Last_Mfd[2];
            Last_Mfd = new Date(Last_Mfd);


            Product_Mfd = Product_Mfd.split("/");
            Product_Mfd =  Product_Mfd[1]+'/'+Product_Mfd[0]+'/'+Product_Mfd[2];
            Product_Mfd = new Date(Product_Mfd);

            var new_td_item = $('td', $('#showProductTable tr:last'));
            if(Last_Mfd > Product_Mfd){
             
                new_td_item.attr('style','background-color: rgba(255,0,0,0.35)');
            }else{
                var new_td_item = $('td', $('#showProductTable tr:last'));
                
            }
            });
            initProductTable(function(){

//                        $("#preload").show();

//                        console.log(arr_row_pallet);
            $.each(arr_row_pallet, function(key, rowPosition) {
//                            console.log(rowPosition);
            var value = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_reserv_qty];
            if (validateForm() === true) {
//                                    var columnPosition = ci_reserv_qty;
//                                    var dataTableColumnPosition = ci_reserv_qty;
//                                    var sColumnName = oTable.fnSettings().aoColumns[dataTableColumnPosition].sTitle;

            var doc_refer_ext = $("[name='doc_refer_ext']").val().toUpperCase();
            var product_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_id];
            var product_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_code];
            var product_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_status];
            var product_sub_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_sub_status];
            var suggest_loc = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_suggest_loc];
            var lot = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_lot];
            var serial = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_serial];
            var mfd = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_mfd];
            // var lmfd = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_lmfd]; //ADD last MFD
            var exp = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_exp];
            var reserv_qty = value;
            var unit_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_id];
            var price_per_unit = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_price_per_unit];
            var unit_price_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_price_id];
            var all_price = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_all_price];
            var remark = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_remark];
            var pallet_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_pallet_code];
            var item_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_item_id];
            var inbound_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_inbound_id];
            var callback_count = 1;
            var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
            if (typeof order_id == 'undefined'){

            var dataSet = $("#frmPreDispatch").serialize();
            $.ajax({
            type: "POST",
                    url: '<?php echo site_url() . "/pre_dispatch/ajaxCreateDocument" ?>',
                    data: dataSet,
                    dataType: 'json',
                    async: false,
                    success: function (datas) {

                    if (datas.success != "OK"){
                    call_modal_alert(datas, null);
                    } else{
                    $('#document_no').val(datas.document_no);
                    order_id = datas.order_id;
                    var frmPreDispatch = document.getElementById("frmPreDispatch");
                    var append_order_id = document.createElement("input");
                    append_order_id.setAttribute('type', "hidden");
                    append_order_id.setAttribute('name', "order_id");
                    append_order_id.setAttribute('value', datas.order_id);
                    frmPreDispatch.appendChild(append_order_id);
                    var append_flow_id = document.createElement("input");
                    append_flow_id.setAttribute('type', "hidden");
                    append_flow_id.setAttribute('name', "flow_id");
                    append_flow_id.setAttribute('value', datas.flow_id);
                    frmPreDispatch.appendChild(append_flow_id);
                    var append_token = document.createElement("input");
                    append_token.setAttribute('type', "hidden");
                    append_token.setAttribute('name', "token");
                    append_token.setAttribute('value', datas.token);
                    frmPreDispatch.appendChild(append_token);
                    $('#btn_action_reject').show();
                    }

                    callback_count--;
                    }
            });
            } else{
            callback_count--;
            }

            var initInterval = setInterval(function(){
            if (callback_count == 0) {
            clearInterval(initInterval);
            var dataSet = {
            order_id: order_id
                    , doc_refer_ext: doc_refer_ext
                    , product_id: product_id
                    , product_code: product_code
                    , product_status: product_status
                    , product_sub_status: product_sub_status
                    , suggest_loc: suggest_loc
                    , lot: lot
                    , serial: serial
                    , mfd: mfd
                    , exp: exp
                    , reserv_qty: reserv_qty
                    , unit_id: unit_id
                    , price_per_unit: price_per_unit
                    , unit_price_id: unit_price_id
                    , all_price: all_price
                    , remark: remark
                    , pallet_code: pallet_code
                    , item_id: item_id
                    , inbound_id: inbound_id
            }
            // console.log('1175');
            $.ajax({
            type: "POST",
                    url: '<?php echo site_url() . "/pre_dispatch/ajaxSaveEditedRecordReservQty" ?>',
                    data: dataSet,
                    dataType: 'json',
                    async: false,
                    success: function (datas) {
//                                                oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
                    if (datas.success != "OK"){
                    call_modal_alert(datas, null);
                    } else{
                    oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
                    oTable.fnUpdate(value, rowPosition, ci_reserv_qty);
//                                                    oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
                    socket.emit('update_est_balance', db_config, uniqid, datas.item_id);
                    }
                    }
            });
            calculate_qty();
            $("#preload").hide();
            }
            }, 500);
            } else {
            $("#preload").hide();
            alert("Please Check Your Require Information (Red label).");
            return false;
            }
            });
            });
            }
    });
    $('.modal.in').modal('hide');
    allVals = new Array();
    });
    });
    $('#myModal').modal('toggle').css({
    'width': function() {	// make width 90% of screen
    return ($(document).width() * 0.95) + 'px';
    },
            'margin-left': function() {  // center model
            return - ($(this).width() / 2);
            }
    });
    var c_s = 0;

    
    function postRequestAction(module, sub_module, action_value, next_state, elm) {

        global_module = module;
        global_sub_module = sub_module;
        global_action_value = action_value;
        global_next_state = next_state;
        curent_flow_action = $(elm).data('dialog');

        $("#to_warehouse_select").removeAttr('disabled');
 
        $("input[name='prod_list[]']").remove();
        var update_inb;
        var productExprie = new Array();
        if (sub_module == 'openPreDispatch' || sub_module == 'rejectAction') {
            $('#btn_confirm_alert_message').show();
            update_inb = 1;
        } else {
            $('#preload').show();
            $.when(updateInbound()).then(function (param) {
                update_inb = param;
                $('#preload').fadeOut('slow',function(){});
            });
        }
        //check data and update inbound for order detail import file form excel : by kik
        if (update_inb) {

            //validate Engine called here.

            if (sub_module == 'rejectAction') {
                 var statusisValidateForm = true
            } else {
                var statusisValidateForm = validateForm();
            }

            if (statusisValidateForm === true) {

                //check reject action
                if (sub_module != 'rejectAction') {

                    var rowData = $('#showProductTable').dataTable().fnGetData();
                    var num_row = rowData.length;
                    if (num_row <= 0) {
                        alert("Please Select Product Order Detail");
                        return false;
                    }
                    /* COMMENT BY POR 2014-09-27 : not use container this page use again in dispatch HH
                    if(conf_cont){
                        if($("#doc_refer_container option").length <= 0){
                            alert("Please input container");
                            return false;
                        }
                    }
                    */
                    //check require by config: comment by por 2014-10-14
                    if(conf_inv){
                        if(conf_invoice_require){
                            for (i in rowData) {
                                invoice = rowData[i][ci_invoice];
                                if (invoice == "") {
                                    alert('Please input invoice');
                                    return false;
                                }
                            }
                        }
                    }


                    /* COMMENT BY POR 2014-09-27 : not use container this page use again in dispatch HH
                    if(conf_cont){
                        for (i in rowData) {
                            container = rowData[i][ci_container];
                            if (container == "") {
                                alert('Please input container in line');
                                return false;
                            }
                        }
                    }
                    */
                    for (i in rowData) {
                        qty = rowData[i][ci_reserv_qty];
                        if (qty == "" || qty == 0) {
                            alert('Please fill all Reserve Qty');
                            return false;
                        }
                    }

                    if(statusprice==true){
                        price = rowData[i][ci_price_per_unit];
                        if (price == "" || price == 0) {
                            alert('Please fill all Price/Unit');
                            flagSubmit = false;
                            return false;
                        }

                        price = parseFloat(price);
                        if (price < 0) {
                            alert('Negative Price/Unit is not allow');
                            flagSubmit = false;
                            return false;
                        }
                    }

                    global_rowData = rowData;

                    for (i in rowData) {

                        var str1 = rowData[i][ci_reserv_qty];
                        var tmp1 = str1.replace(/\,/g, '');
                        var str2 = rowData[i][ci_balance_qty];
                        var tmp2 = str2.replace(/\,/g, '');

                        reserve_qty = parseFloat(tmp1);
                        balance_qty = parseFloat(tmp2);
                        
                        if (reserve_qty == "" || reserve_qty == 0) {
                            alert('Please fill all Dispatch Qty and not equal 0!');
                            return false;
                        }
                        reserve_qty = parseFloat(reserve_qty);
                        if (reserve_qty <= 0) {
                            alert('Negative Receive Qty is not allow and not equal 0!');
                            return false;
                        }

                        // BALL ADD TEMP
//                        if (reserve_qty != balance_qty) {
//                            alert('Full pallet only');
//                            return false;
//                        }
                        
                    }

                    if(dp_type_link_prod_status != null){
                        // Validate validate_form.js
                        // AJI Normal = Concession and Approve
                        check_dispatch_type_when_submit_form();
                    }


                    if($('#dispatch_type_select').val() == 'DP0'){
                        if(!confirm('Dispatch Type is "Manual", You want to Submit?')){
                            return false;
                        }
                    }

                }// end check reject action


                    //getValueFromTableData();

                    var backupForm = document.getElementById('frmPreDispatch').innerHTML;
                    var f = document.getElementById("frmPreDispatch");
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

                        var prod_data = "";
                        $.each(oTable[i], function(idx, elm_val)
                        {
                            prod_data += strip(elm_val) + separator;
                        });

                        prod_data = prod_data.substring(0, prod_data.length - 3);
                        //var prod_data = oTable[i].join(separator);
                        //console.log(prod_data);
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

                    global_data_form = $("#frmPreDispatch").serialize();
                    var message = "";

                    if(global_sub_module != 'rejectAction' && global_sub_module !='rejectAndReturnAction'){
                        validation_data();
                    }else{
                        var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                        $('#div_for_alert_message').html(mess);
                        $('#div_for_modal_message').modal('show').css({
                            'margin-left': function() {
                                return ($(window).width() - $(this).width()) / 2;
                            }
                        });
                    }

//                }
            }
            else {
                alert("Please Check Your Require Information (Red label).");
                return false;
            }
        }
        //END
    }


    //START BY KIK 2013-10-13 function สำหรับเรียกข้อมูลบันทึก Inbound_Item_Id
    function updateInbound() {

        var varerror = new Array();
        var is_not_error = true;
        var arr_delete_row = new Array();
        var arr_problem_row = new Array();
        var arr_problem_item_id = new Array();
        var arr_alert_text = new Array();

        //get order id
        var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
        //end get order id

        //get order detail data by kik
        var oTable = $('#showProductTable').dataTable().fnGetData();
        var product_item = new Array();

        var arr_select_inbound_id = new Array;
        var arr_select_reserv_qty = new Array;
        for (i in oTable) {
            arr_select_inbound_id.push(oTable[i][ci_inbound_id]);
            arr_select_reserv_qty.push(oTable[i][ci_reserv_qty]);
        }

        var row_update = -1;
        var dataSet = new Array();

        for (i in oTable) {
            var flag_ajax_complete = false;
            row_update += 1;
            if(oTable[i][ci_inbound_id] == ''){
                var itemDataSet = {productCode_val: oTable[i][ci_prod_code]
                    , productStatus_val: oTable[i][ci_prod_status]
                    , productSubStatus_val: oTable[i][ci_prod_sub_status]
                    , productLot_val: oTable[i][ci_lot]
                    , productSerial_val: oTable[i][ci_serial]
                    , productMfd_val: oTable[i][ci_mfd]
                    , productExp_val: oTable[i][ci_exp]
                    , qty_of_sku : oTable[i][ci_reserv_qty]
                    , arr_select_inbound_id : arr_select_inbound_id
                    , arr_select_reserv_qty : arr_select_reserv_qty
                    , item_id : oTable[i][ci_item_id]
                    , row_count : i
                }
                dataSet.push(itemDataSet);
            }
        }

        $.ajax({
            type: "POST",
            url: '<?php echo site_url() . "/pre_dispatch/batchShowAndGenSelectData" ?>',
            data: {"dataSet": dataSet},
            dataType: 'json',
            async: false,
            success: function (datas) {
                var invoice = "";
                var container = "";
                var unit_Price_value = "";
                var pallet_code = "";
                var type_pallet = "";
                var unit_price = "";
                var container_id = "";
                var price = set_number_format(0);
                var allprice = "";
                var remark = "";
                var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";

                if(typeof datas.arr_problem_row != 'undefined'){
                    $.each(datas.arr_problem_row, function(key, item) {
                        $("#" + item.item_id).addClass("highlight_table_err");
                        $('#showProductTable').dataTable().fnUpdate(set_number_format(item.less_qty), parseInt(item.row_count), ci_reserv_qty);
//                         $("#location_code_" + item.item_id).html('<input type="button" onclick="refill_this('+item.item_id+');">');
//                        console.log();
                        arr_alert_text.push("Line No." + (parseInt(key)+1) + " :: 'Est.Balance Qty' is less than 'Reserve Qty', Please check again.");
                        is_not_error = false;
                    });
                }

                if(typeof datas.arr_problem_rule != 'undefined'){
                    $.each(datas.arr_problem_rule, function(key, item) {
                        var data = JSON.parse(item);
                        if (data.product_code != null) {
                            //$("#" + data.item_id).addClass("highlight_table_err");
                            //$('#showProductTable').dataTable().fnUpdate(set_number_format(item.less_qty), parseInt(item.row_count), ci_reserv_qty);
                            arr_alert_text.push("Product Code '" + data.product_code + "' On '" + data.location + "' does not meet the picking rule condition, Please check again.");
                            is_not_error = false;
                        }
                    });
                }
                
                if(typeof datas.can_del_row != 'undefined'){
                    arr_delete_row = datas.can_del_row;
                    arr_delete_row = arr_delete_row.reverse();
                    var oTable = $('#showProductTable').dataTable();
                    // Loop for delete row
                    $.each(arr_delete_row, function(key, item) {
                        var data = oTable.fnGetData(item);
                        data = data.join(separator);
                        oTable.fnDeleteRow(item, function() {
                            var rows = oTable.fnGetNodes();
                            for (var i = 0; i < rows.length; i++)
                            {
                                $(rows[i]).find("td:eq(0)").html(i + 1);
                            }
                        });
                        // End Refresh
                        var f = document.getElementById("frmPreDispatch");
                        var prodDelItem = document.createElement("input");
                        prodDelItem.setAttribute('type', "hidden");
                        prodDelItem.setAttribute('name', "prod_del_list[]");
                        prodDelItem.setAttribute('value', data);
                        f.appendChild(prodDelItem);
                    });
                    calculate_qty();
                }

                var recordsTotal = $('#showProductTable').dataTable().fnSettings().fnRecordsTotal() + 1;
                $.each(datas.all_product_list, function(key, item) {
                    var dispatch_qty = set_number_format(item.Reserve_Qty);
                    $('#showProductTable').dataTable().fnAddData([
                        recordsTotal + i
                        , item.Product_Code
                        , item.Product_NameEN
                        , item.Status_Value
                        , item.Sub_Status_Value
                        , item.Actual_Location + "  <input id='ball_"+item.Product_Code+"' type='button' style='width:50px'  onclick=\"refill_this('"+item.Product_Code+"');\" value='Refill'>"
                        , item.Product_Lot 
                        , item.Product_Serial
                        , item.Product_Mfd
                        , item.Last_Mfd //ADD LastMFD 
                        , item.Product_Exp
                        , invoice
                        , container
                        , item.Est_Balance_Qty
                        , dispatch_qty
                        , item.Unit_Value
                        , price
                        , unit_Price_value
                        , allprice
                        , remark
                        , item.Pallet_Code
                        , type_pallet
                        , del
                        , item.Product_Id
                        , item.Product_Status
                        , item.Product_Sub_Status
                        , item.Unit_Id
                        , 'new'
                        , item.Inbound_Id
                        , item.Actual_Location_Id
                        , unit_price
                        , container_id
                    ]);
                    if (item.ByRule == "") {
                        $('#showProductTable tr:last').css({backgroundColor: '#feefb3'});
                    }
                    var new_td_item = $('td:eq(1)', $('#showProductTable tr:last'));
                    new_td_item.addClass("td_click");
                    new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.Product_Code + '"' + ',' + item.Inbound_Id + ')');
                });

                $('#btn_confirm_alert_message').show();
                if(arr_alert_text.length > 0){
                    var mess = '';
                    mess += '<div id="div_unsuccess">';
                    mess += '<h4> Critical </h4>- ' + arr_alert_text.join('<BR>- ') + '</div>';
                    $('#div_for_alert_message').html(mess);
                    $('#div_for_modal_message').modal('show').css({
                        'margin-left': function () {
                            return ($(window).width() - $(this).width()) / 2;
                        }
                    });
                    $('#btn_confirm_alert_message').hide();
                }

                var rowData = $('#showProductTable').dataTable().fnGetData();
                var count_new_row = 1;
                for (i in rowData) {
                    $('#showProductTable').dataTable().fnUpdate(count_new_row, count_new_row-1, arr_index.indexOf("no"));
                    count_new_row++;
                }

                calculate_qty();

            }
        });

        return is_not_error;
    }
    //END
    
            function generate(id){
//                console.log(id);
                var settings = {
                    "method": "POST",
                    "url": "<?php echo base_url(); ?>index.php/c_bypass/call_from_master_list",                 
                    "timeout": 0,
                    "headers": {
                       "Content-Type": "text/plain"
                    },
                    "data": {"id":id},
                };
                
                $.ajax(settings).done(function (response) {
//                    console.log(response);
                     $("#id").val(response);
                     $( "#after_gen" ).submit();
//                    alert('Successfully Updated'); window.location = '<?php echo base_url(); ?>index.php/replenishment_/replenishment_form'
                });
            }
//    
    
    function refill_this(product_code){
      var txt;
         $( "#ball_"+product_code ).prop( "disabled", true );
        $("#ball_"+product_code).css('background','#b9b9b9');
        $("#ball_"+product_code).css('border','#b9b9b9');
      
        if (confirm("Refill "+product_code)) {
             var settings = {
                    "method": "POST",
                    "url": "<?php echo base_url(); ?>index.php/c_bypass/call_for_refill_1pl",                 
                    "timeout": 0,
                    "headers": {
                       "Content-Type": "text/plain"
                    },
                    "data": {"product_code":product_code},
             };
     
                 $.ajax(settings).done(function (response) {
//                     console.log(response);
                     $("#id").val(response);
                     $( "#after_gen" ).submit();
                });
        } else {
       
        }

    }

    function getValueFromTableData() {
    // var items = [[1,2],[3,4],[5,6]];
    /*
     Test Comment for not Use Function
     var xx = [];
     var rowCount = $('#showProductTable tbody tr');
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
     */

    }
    function showModalTable() {
    allVals = new Array();
    var product_code = $('#productCode').val();
    var productStatus_select = $('#productStatus_select').val();
    var productSubStatus_select = $('#productSubStatus_select').val();
    var productLot = $('#productLot').val();
    var productSerial = $('#productSerial').val();
    var productMfd = $('#productMfd').val();
    var productExp = $('#productExp').val();
    var dataSet = {productCode_val: product_code
            , productStatus_val: productStatus_select
            , productSubStatus_val: productSubStatus_select
            , productLot_val: productLot
            , productSerial_val: productSerial
            , productMfd_val: productMfd
            , productExp_val: productExp
    }

    $('#prdModalval').val(product_code);
    initProductsDatatables();
    }
    function getCheckValue(obj, dispatchtype, inbound) {
    var isChecked = $(obj).attr("checked");
    if (isChecked) {
    if (dispatchtype == 'FULL'){ //ADD BY POR 2014-02-19 กรณี dispatch full จะนำ inbound_id ทั้งหมด ที่หาได้จาก pallet_id มาแทนค่าใน array
    for (var i in inbound){
    allVals.push(inbound[i]);
    }
    } else{
    allVals.push($(obj).val()); //EDIT BY POR กำหนดให้ส่ง dispatchtype ไปด้วย ถ้าเลือกเงื่อนไขแบบไม่มี pallet ค่าจะเป็น 0
    }

    dispatch_type = dispatchtype;
    } else {
//            allVals.pop($(obj).val()); // Comment By Akkarapol, 28/11/2013, คอมเม้นต์ allVals.pop($(obj).val()) ทิ้งเพราะการใช้ pop มันไม่ได้เอาค่าของ $(obj).val() ออกไปจริงๆ แต่มันเอาอันสุดท้ายที่ถูก puch เข้าไป ออก ซึ่ง ผิดมหันต์เลยล่ะ เพราะค่าที่เหลือ จะไม่ใช่ค่าที่ต้องการจริงๆ

// Add BY Akkarapol, 28/11/2013, เพิ่มการใช้ฟังก์ชั่น grep เพื่อ return ค่าที่เหลือหลังจากการตัด ค่าที่ไม่ได้เลือกออกไป จะเหลือเฉพาะค่าที่ เลือกแล้วเท่านั้น จะได้ค่าที่ตรงตามต้องการจริงๆ
    allVals = jQuery.grep(allVals, function(value) {
    return value != $(obj).val();
    });
// END Add BY Akkarapol, 28/11/2013, เพิ่มการใช้ฟังก์ชั่น grep เพื่อ return ค่าที่เหลือหลังจากการตัด ค่าที่ไม่ได้เลือกออกไป จะเหลือเฉพาะค่าที่ เลือกแล้วเท่านั้น จะได้ค่าที่ตรงตามต้องการจริงๆ

    }
//        console.log(allVals);
    }

    function initProductTable(callback) {

    var sOldValue = 0;
    var oTable = $('#showProductTable').dataTable({
    "bJQueryUI": true,
            "bSort": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "bAutoWidth": false,
            "iDisplayLength"    : 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            //"sScrollY": "100%", // change to use 100%
            "aoColumnDefs": [
            {"sWidth": "3%", "sClass": "center", "aTargets": [arr_index.indexOf("no")]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("product_code")]},
            {"sWidth": "20%", "sClass": "left_text", "aTargets": [arr_index.indexOf("product_name")]},
            {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [arr_index.indexOf("product_status")]}, // Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [arr_index.indexOf("product_sub_status")]}, //Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [arr_index.indexOf("location_code")]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [arr_index.indexOf("lot")]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [arr_index.indexOf("serial")]},
            {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [arr_index.indexOf("product_mfd")]},
            {"sWidth": "7%", "sClass": "center obj_last_mfd", "aTargets": [arr_index.indexOf("Last_Mfd")]}, //ADD last MFD
            {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [arr_index.indexOf("product_exp")]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("invoice")]},
            {"sWidth": "5%", "sClass": "center obj_container", "aTargets": [arr_index.indexOf("container")]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("est_balance_qty")]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("reserv_qty")]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("unit")]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("price_per_unit")]}, //add by kik : 20140113
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("unit_price")]}, //add by kik : 20140113
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("all_price")]}, //add by kik : 20140113
            {"sWidth": "7%", "sClass": "center", "aTargets": [arr_index.indexOf("remark")]}, //add by kik : 20140113
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("pallet_code")]}, //add by kik : 20140113
            ]
    }).makeEditable({
    "aoColumns": [
            null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
    {reinit_invoice}
    {init_invoice}
    , null
            , {
            indicator: 'Saving...',
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    loadfirst: true,
                    cssclass: "required number",
                    sUpdateURL: function(value, settings)
                    {
                    $("#preload").show();
                    var value_column = value;
                    if (validateForm() === true) {

                    var rowPosition = oTable.fnGetPosition(this)[0];
                    var columnPosition = oTable.fnGetPosition(this)[1];
                    var dataTableColumnPosition = oTable.fnGetPosition(this)[2];
                    var sColumnName = oTable.fnSettings().aoColumns[dataTableColumnPosition].sTitle;
                    var doc_refer_ext = $("[name='doc_refer_ext']").val().toUpperCase();
                    var product_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_id];
                    var product_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_code];
                    var product_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_status];
                    var product_sub_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_sub_status];
                    var suggest_loc = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_suggest_loc];
                    var lot = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_lot];
                    var serial = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_serial];
                    var mfd = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_mfd];
                    var exp = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_exp];
                    var reserv_qty = value;
                    var unit_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_id];
                    var price_per_unit = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_price_per_unit];
                    var unit_price_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_price_id];
                    var all_price = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_all_price];
                    var remark = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_remark];
                    var pallet_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_pallet_code];
                    var item_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_item_id];
                    var inbound_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_inbound_id];
                    var callback_count = 1;
                    var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
                    if (typeof order_id == 'undefined'){

                    var dataSet = $("#frmPreDispatch").serialize();
                    $.ajax({
                    type: "POST",
                            url: '<?php echo site_url() . "/pre_dispatch/ajaxCreateDocument" ?>',
                            data: dataSet,
                            dataType: 'json',
                            async: false,
                            success: function (datas) {

                            if (datas.success != "OK"){
                            call_modal_alert(datas, null);
                            } else{
                            $('#document_no').val(datas.document_no);
                            order_id = datas.order_id;
                            var frmPreDispatch = document.getElementById("frmPreDispatch");
                            var append_order_id = document.createElement("input");
                            append_order_id.setAttribute('type', "hidden");
                            append_order_id.setAttribute('name', "order_id");
                            append_order_id.setAttribute('value', datas.order_id);
                            frmPreDispatch.appendChild(append_order_id);
                            var append_flow_id = document.createElement("input");
                            append_flow_id.setAttribute('type', "hidden");
                            append_flow_id.setAttribute('name', "flow_id");
                            append_flow_id.setAttribute('value', datas.flow_id);
                            frmPreDispatch.appendChild(append_flow_id);
                            var append_token = document.createElement("input");
                            append_token.setAttribute('type', "hidden");
                            append_token.setAttribute('name', "token");
                            append_token.setAttribute('value', datas.token);
                            frmPreDispatch.appendChild(append_token);
                            $('#btn_action_reject').show();
                            }

                            // console.log('1760');
                            if (typeof order_id != 'undefined'){
                            var dataSet = {
                            order_id: order_id
                                    , doc_refer_ext: doc_refer_ext
                                    , product_id: product_id
                                    , product_code: product_code
                                    , product_status: product_status
                                    , product_sub_status: product_sub_status
                                    , suggest_loc: suggest_loc
                                    , lot: lot
                                    , serial: serial
                                    , mfd: mfd
                                    , exp: exp
                                    , reserv_qty: reserv_qty
                                    , unit_id: unit_id
                                    , price_per_unit: price_per_unit
                                    , unit_price_id: unit_price_id
                                    , all_price: all_price
                                    , remark: remark
                                    , pallet_code: pallet_code
                                    , item_id: item_id
                                    , inbound_id: inbound_id
                            }

                            $.ajax({
                            type: "POST",
                                    url: '<?php echo site_url() . "/pre_dispatch/ajaxSaveEditedRecordReservQty" ?>',
                                    data: dataSet,
                                    dataType: 'json',
                                    async: false,
                                    success: function (datas) {
                                    if (datas.success != "OK"){
                                    call_modal_alert(datas, null);
                                    } else{
                                    oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
                                    oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
                                    socket.emit('update_est_balance', db_config, uniqid, datas.item_id);
                                    }
                                    }
                            });
                            calculate_qty();
                            $("#preload").hide();
                            } else{
                            $("#preload").hide();
                            }

                            } // END create document

                    }); // END AJAX create document.

                    } else{

                    if (typeof order_id != 'undefined'){
                    var dataSet = {
                    order_id: order_id
                            , doc_refer_ext: doc_refer_ext
                            , product_id: product_id
                            , product_code: product_code
                            , product_status: product_status
                            , product_sub_status: product_sub_status
                            , suggest_loc: suggest_loc
                            , lot: lot
                            , serial: serial
                            , mfd: mfd
                            , exp: exp
                            , reserv_qty: reserv_qty
                            , unit_id: unit_id
                            , price_per_unit: price_per_unit
                            , unit_price_id: unit_price_id
                            , all_price: all_price
                            , remark: remark
                            , pallet_code: pallet_code
                            , item_id: item_id
                            , inbound_id: inbound_id
                    }

                    $.ajax({
                    type: "POST",
                            url: '<?php echo site_url() . "/pre_dispatch/ajaxSaveEditedRecordReservQty" ?>',
                            data: dataSet,
                            dataType: 'json',
                            async: false,
                            success: function (datas) {
                            if (datas.success != "OK"){
                            call_modal_alert(datas, null);
                            } else{
                            oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
                            oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
                            socket.emit('update_est_balance', db_config, uniqid, datas.item_id);
                            }
                            }
                    }); // END condition reserv pd
                    calculate_qty();
                    $("#preload").hide();
                    } else{
                    $("#preload").hide();
                    } // END condition order_id

                    }

                    } else {
                    $("#preload").hide();
                    alert("Please Check Your Require Information (Red label).");
                    return false;
                    }

                    },
                    fnOnCellUpdated: function(sStatus, sValue, settings) {}
            }
    , null
    {price}           //price
    {unitofprice}     //unitprice
    , null           //allprice
            //END ADD
            , {
            onblur: 'submit',
                    sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
            }
    , null
            , null
            , null
            , null
            , null
            , null
            , null
            , null      //add by kik : 20140113
    ]
    });
    $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_status, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_item_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_inbound_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false); //add by kik : 20140113
    $('#showProductTable').dataTable().fnSetColumnVis(ci_cont_id, false); //ADD BY POR : 2014-08-19
    $('#showProductTable').dataTable().fnSetColumnVis(ci_container, false); //ADD BY POR : 2014-08-19 close container in datatable because change add container on dispatch HH
    hide_arr_index.push(ci_container);
    if (!conf_pallet){ //ให้แสดงก็ต่อเมื่อมีการ config=true
    $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false); // add index ci_dp_type_pallet BY POR 2014-02-19
    hide_arr_index.push(ci_pallet_code);
    }
    $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false); // add index ci_dp_type_pallet BY POR 2014-02-19
    //add by kik : 20140113
    if (!statusprice){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
    hide_arr_index.push(ci_price_per_unit);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
    hide_arr_index.push(ci_unit_price);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
    hide_arr_index.push(ci_all_price);
    }

    if (!conf_inv){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
    hide_arr_index.push(ci_invoice);
    }
    //end add by kik : 20140113
    $('#showProductTable tbody tr td[title]').hover(function() {

    // Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา
    var chk_title = $(this).attr('title');
    var chk_innerHTML = this.innerHTML;
    if (chk_title != chk_innerHTML){
    $(this).show_tooltip();
    }
    // END Add By Akkarapol, 23/12/2013, เพิ่มการตรวจสอบว่า ถ้า Product Name กับ Product Full Name นั้น ไม่เหมือนกัน ให้แสดง tooltip ของ Product Full Name ขึ้นมา

    }, function() {
    $(this).hide_tooltip();
    });
    if (typeof callback != 'undefined'){
    callback();
    }

    }


    // reInitProductTable
    function reInitProductTable() {
//        console.log('reInitProductTable');
    var oTable = $('#showProductTable').dataTable({
    "bJQueryUI": true,
            "bSort": false,
            "bStateSave": true,
            "bRetrieve": true,
            "bDestroy": true,
            "bAutoWidth": false,
            "iDisplayLength"    : 250,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
            "aoColumnDefs": [
            {"sWidth": "3%", "sClass": "center", "aTargets": [arr_index.indexOf("no")]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("product_code")]},
            {"sWidth": "20%", "sClass": "left_text", "aTargets": [arr_index.indexOf("product_name")]},
            {"sWidth": "7%", "sClass": "left_text obj_status", "aTargets": [arr_index.indexOf("product_status")]}, // Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text obj_sub_status", "aTargets": [arr_index.indexOf("product_sub_status")]}, //Edit by Ton! 20131001
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [arr_index.indexOf("location_code")]},
            {"sWidth": "7%", "sStyle":"background-color: powderblue","sClass": "left_text", "aTargets": [arr_index.indexOf("lot")]},
            {"sWidth": "7%", "sClass": "left_text", "aTargets": [arr_index.indexOf("serial")]},
            {"sWidth": "7%", "sClass": "center obj_mfg", "aTargets": [arr_index.indexOf("product_mfd")]},
            {"sWidth": "7%", "sClass": "center obj_last_mfd", "aTargets": [arr_index.indexOf("Last_Mfd")]}, //ADD last MFD
            {"sWidth": "7%", "sClass": "center obj_exp", "aTargets": [arr_index.indexOf("product_exp")]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("invoice")]},
            {"sWidth": "5%", "sClass": "center obj_container", "aTargets": [arr_index.indexOf("container")]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("est_balance_qty")]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("reserv_qty")]},
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("unit")]},
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("price_per_unit")]}, //add by kik : 20140113
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("unit_price")]}, //add by kik : 20140113
            {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [arr_index.indexOf("all_price")]}, //add by kik : 20140113
            {"sWidth": "7%", "sClass": "center", "aTargets": [arr_index.indexOf("remark")]}, //add by kik : 20140113
            {"sWidth": "5%", "sClass": "center", "aTargets": [arr_index.indexOf("pallet_code")]}, //add by kik : 20140113
            ]
    }).makeEditable({
    "aoColumns": [
            null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
            , null
    {init_invoice}
    , null
            , null
            , {
//                    sSortDataType: "dom-text",
//                    sType: "numeric",
//                    type: 'text',
//                    onblur: "submit",
//                    event: 'click',
//                    loadfirst: true,
//                    cssclass: "required number",
//                    sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
//                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
//                        calculate_qty();
//                    }

            indicator: 'Saving...',
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    loadfirst: true,
                    cssclass: "required number",
//                    sUpdateURL: "<?php echo site_url('/pre_dispatch/saveEditedRecord') ?>",

                    sUpdateURL: function(value, settings)
                    {
                    $("#preload").show();
//                        console.log(value);
                    var value_column = value;
                    if (validateForm() === true) {

                    var rowPosition = oTable.fnGetPosition(this)[0];
                    var columnPosition = oTable.fnGetPosition(this)[1];
                    var dataTableColumnPosition = oTable.fnGetPosition(this)[2];
                    var sColumnName = oTable.fnSettings().aoColumns[dataTableColumnPosition].sTitle;
                    var doc_refer_ext = $("[name='doc_refer_ext']").val().toUpperCase();
                    var product_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_id];
                    var product_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_code];
                    var product_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_status];
                    var product_sub_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_sub_status];
                    var suggest_loc = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_suggest_loc];
                    var lot = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_lot];
                    var serial = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_serial];
                    var mfd = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_mfd];
                    var exp = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_exp];
                    var reserv_qty = value;
                    var unit_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_id];
                    var price_per_unit = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_price_per_unit];
                    var unit_price_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_price_id];
                    var all_price = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_all_price];
                    var remark = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_remark];
                    var pallet_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_pallet_code];
                    var item_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_item_id];
                    var inbound_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_inbound_id];
                    var callback_count = 1;
                    var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
                    if (typeof order_id == 'undefined'){

                    var dataSet = $("#frmPreDispatch").serialize();
                    $.ajax({
                    type: "POST",
                            url: '<?php echo site_url() . "/pre_dispatch/ajaxCreateDocument" ?>',
                            data: dataSet,
                            dataType: 'json',
                            async: false,
                            success: function (datas) {

                            if (datas.success != "OK"){
                            call_modal_alert(datas, null);
                            } else{
                            $('#document_no').val(datas.document_no);
                            order_id = datas.order_id;
                            var frmPreDispatch = document.getElementById("frmPreDispatch");
                            var append_order_id = document.createElement("input");
                            append_order_id.setAttribute('type', "hidden");
                            append_order_id.setAttribute('name', "order_id");
                            append_order_id.setAttribute('value', datas.order_id);
                            frmPreDispatch.appendChild(append_order_id);
                            var append_flow_id = document.createElement("input");
                            append_flow_id.setAttribute('type', "hidden");
                            append_flow_id.setAttribute('name', "flow_id");
                            append_flow_id.setAttribute('value', datas.flow_id);
                            frmPreDispatch.appendChild(append_flow_id);
                            var append_token = document.createElement("input");
                            append_token.setAttribute('type', "hidden");
                            append_token.setAttribute('name', "token");
                            append_token.setAttribute('value', datas.token);
                            frmPreDispatch.appendChild(append_token);
                            $('#btn_action_reject').show();
                            }

                            callback_count--;
                            }
                    });
                    } else{
                    callback_count--;
                    }

                    var initInterval = setInterval(function(){
                    if (callback_count == 0) {
                    clearInterval(initInterval);
                    if (typeof order_id != 'undefined'){
                    var dataSet = {
                    order_id: order_id
                            , doc_refer_ext: doc_refer_ext
                            , product_id: product_id
                            , product_code: product_code
                            , product_status: product_status
                            , product_sub_status: product_sub_status
                            , suggest_loc: suggest_loc
                            , lot: lot
                            , serial: serial
                            , mfd: mfd
                            , exp: exp
                            , reserv_qty: reserv_qty
                            , unit_id: unit_id
                            , price_per_unit: price_per_unit
                            , unit_price_id: unit_price_id
                            , all_price: all_price
                            , remark: remark
                            , pallet_code: pallet_code
                            , item_id: item_id
                            , inbound_id: inbound_id
                    }
                    // console.log('2113');
                    $.ajax({
                    type: "POST",
                            url: '<?php echo site_url() . "/pre_dispatch/ajaxSaveEditedRecordReservQty" ?>',
                            data: dataSet,
                            dataType: 'json',
                            async: false,
                            success: function (datas) {
//                                                console.log(datas);
//                                                console.log(datas.item_id);
//                                                console.log(rowPosition);
//                                                console.log(ci_item_id);
                            if (datas.success != "OK"){
                            call_modal_alert(datas, null);
                            } else{
                            oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
                            oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
                            socket.emit('update_est_balance', db_config, uniqid, datas.item_id);
                            }
//                                                if(datas.success == 'OK'){
//                                                    oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
//                                                }
                            }
                    });
//                                        oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
                    calculate_qty();
                    $("#preload").hide();
                    } else{
                    $("#preload").hide();
                    }
                    }
                    }, 500);
                    } else {
                    $("#preload").hide();
                    alert("Please Check Your Require Information (Red label).");
                    return false;
                    }


                    },
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
//                        calculate_qty();
                    }
            }
    , null
    {reinit_invoice_col}
    {price}
    {unitofprice}
    , null
            , {
            onblur: 'submit',
//                    sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
                    event: 'click',
            }
    , null
            , null
            , null
            , null
            , null
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
    $('#showProductTable').dataTable().fnSetColumnVis(ci_inbound_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_suggest_loc, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_cont_id, false);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_container, false);
    hide_arr_index.push(ci_container);
    if (!conf_pallet){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false);
    hide_arr_index.push(ci_pallet_code);
    }
    $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false);
    if (!statusprice){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
    hide_arr_index.push(ci_price_per_unit);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
    hide_arr_index.push(ci_unit_price);
    $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
    hide_arr_index.push(ci_all_price);
    }
    if (!conf_inv){
    $('#showProductTable').dataTable().fnSetColumnVis(ci_invoice, false);
    hide_arr_index.push(ci_invoice);
    }

    $('#showProductTable tbody tr td[title]').hover(function() {
    var chk_title = $(this).attr('title');
    var chk_innerHTML = this.innerHTML;
    if (chk_title != chk_innerHTML){
    $(this).show_tooltip();
    }
    }, function() {
    $(this).hide_tooltip();
    });
    }
    // End reInitProductTable

    function removeItem(obj) {

    $('#preload').show();
    var index = $(obj).closest("table tr").index();
    var oTable = $('#showProductTable').dataTable();
    var item_id = $('#showProductTable').dataTable().fnGetData()[index][ci_item_id];
    var callback_count = 1;
    var can_remove = true;
    if (item_id != 'new'){
    var dataSet = {
    item_id: item_id
    }

    $.ajax({
    type: "POST",
            url: '<?php echo site_url() . "/pre_dispatch/ajaxRemoveRecordReservQty" ?>',
            data: dataSet,
            dataType: 'json',
            async: false,
            success: function (datas) {
            if (datas.success != "OK"){
            can_remove = false;
            call_modal_alert(datas, null);
            }
            callback_count--;
            }
    });
    } else{
    callback_count--;
    }


    var initInterval = setInterval(function(){
    if (callback_count == 0) {
    clearInterval(initInterval);
    if (can_remove){
    var data = oTable.fnGetData(index);
    oTable.fnDeleteRow(index, function() {
    var rows = oTable.fnGetNodes();
    for (var i = 0; i < rows.length; i++){
    $(rows[i]).find("td:eq(0)").html(i + 1);
    }
    });
    calculate_qty();
    }
    $('#preload').hide();
    }
    }, 500);
    }

    function removePalletItem(obj) {

    $('#preload').show();
    var DT = $('#showProductTable').dataTable();
    var index = $(obj).closest("table tr").index();
    var datas = DT.fnGetData(index);
    var data = datas[ci_pallet_code];
    if (confirm("Do you want to delete all recode in pallet " + data + " ?")) {

    var rowData = DT.fnGetData();
    var length_data = (rowData.length) - 1;
    for (var i = length_data; i >= 0; i--){

    if (data == rowData[i][ci_pallet_code] && rowData[i][ci_dp_type_pallet] == 'FULL'){

    $('#preload').show();
    var item_id = DT.fnGetData()[i][ci_item_id];
    var can_remove = true;
    if (item_id != 'new'){
    var dataSet = {
    item_id: item_id
    }

    $.ajax({
    type: "POST",
            url: '<?php echo site_url() . "/pre_dispatch/ajaxRemoveRecordReservQty" ?>',
            data: dataSet,
            dataType: 'json',
            async: false,
            success: function (datas) {
            if (datas.success != "OK"){
            can_remove = false;
            call_modal_alert(datas, null);
            } else {
            DT.fnDeleteRow(i, function() {
            var rows = DT.fnGetNodes();
            for (var j = 0; j < rows.length; j++){
            $(rows[j]).find("td:eq(0)").html(j + 1);
            }
            });
            }
            }
    });
    } else{
    DT.fnDeleteRow(i, function() {
    var rows = DT.fnGetNodes();
    for (var j = 0; j < rows.length; j++){
    $(rows[j]).find("td:eq(0)").html(j + 1);
    }
    });
    }
    }
    }

    $('#preload').hide();
    calculate_qty();
    }

    }

    function aa22() {


    var index = 0;//ind;//$(obj).closest("table tr").index();
    var oTable = $('#showProductTable').dataTable();
  //  var item_id = iid;//$('#showProductTable').dataTable().fnGetData()[index][ci_item_id];

    var callback_count = 0;
    var can_remove = true;
    // var dataSet = {
    //                     item_id: item_id
    //                     }
    // console.log(dataSet);

   // callback_count = callback_count - 1;
    
    var initInterval = setInterval(function(){  
                                            if (callback_count == 0) {
                                                
                                                    clearInterval(initInterval);
                                                    if (can_remove){
                                                        var data = oTable.fnGetData(index);
                                                       // oTable.fnDeleteRow(index, function() {
                                                          //  var rows = oTable.fnGetNodes();
                                                            // for (var i = 0; i < rows.length; i++){
                                                            //      $(rows[i]).find("td:eq(0)").html(i + 1);
                                                            // }
                                                       // });
                                                        calculate_qty();
                                                    }
                                                  //  $('#preload').hide();
                                                }
                                            }, 500);
    }

    function deleteItem(obj) {


    $('#preload').show();
    var index = $(obj).closest("table tr").index();

    var oTable = $('#showProductTable').dataTable();
    var item_id = $('#showProductTable').dataTable().fnGetData()[index][ci_item_id];
    var callback_count = 1;
    var can_remove = true;
    if (item_id != 'new'){
    var dataSet = {
    item_id: item_id
    }
//    console.log(dataSet);
//    return;
    $.ajax({
    type: "POST",
            url: '<?php echo site_url() . "/pre_dispatch/ajaxRemoveRecordReservQty" ?>',
            data: dataSet,
            dataType: 'json',
            async: false,
            success: function (datas) {
            if (datas.success != "OK"){
            can_remove = false;
            call_modal_alert(datas, null);
            }
            callback_count--;
            }
    });
    } else{
    callback_count--;
    }


    var initInterval = setInterval(function(){
    if (callback_count == 0) {
    clearInterval(initInterval);
    if (can_remove){
    var data = oTable.fnGetData(index);
    oTable.fnDeleteRow(index, function() {
    var rows = oTable.fnGetNodes();
    for (var i = 0; i < rows.length; i++){
    $(rows[i]).find("td:eq(0)").html(i + 1);
    }
    });
    calculate_qty();
    }
    $('#preload').hide();
    }
    }, 500);
    }
    
    function clear_row(row_index,Item_Id) {

    $('#preload').show();
    var index = 0;//ind;//$(obj).closest("table tr").index();
    // var index = 0;
    // console.log(index);
    // return
    // var index = $(obj).closest("table tr").index();
    var oTable = $('#showProductTable').dataTable();
    var item_id = Item_Id;//$('#showProductTable').dataTable().fnGetData()[index][ci_item_id];
        // console.log('rm_row');
        // console.log(ind);
        // console.log(item_id);

    var callback_count = 1;
    var can_remove = true;
//    if (item_id != 'new'){
    var dataSet = {
                        item_id: item_id
                        }
    // console.log(index);
    // return
//    $.ajax({
//    type: "POST",
//            url: '<?php // echo site_url() . "/pre_dispatch/ajaxRemoveRecordReservQty" ?>',
//            data: dataSet,
//            dataType: 'json',
//            async: false,
//            success: function (datas) {
//                if (datas.success != "OK"){
//                    can_remove = false;
//                    call_modal_alert(datas, null);
//                }
//                callback_count--;
//            }
//    });
//    } else{
    callback_count = callback_count - 1;

//    }
    
    var initInterval = setInterval(function(){
                                            if (callback_count == 0) {
                                                
                                                    clearInterval(initInterval);
                                                    if (can_remove){
                                                        var data = oTable.fnGetData(index);
                                                        oTable.fnDeleteRow(index, function() {
                                                            var rows = oTable.fnGetNodes();
                                                            for (var i = 0; i < rows.length; i++){
                                                                 $(rows[i]).find("td:eq(0)").html(i + 1);
                                                            }
                                                        });
                                                        calculate_qty();
                                                    }
                                                    $('#preload').hide();
                                                }
                                            }, 500);
    }
    
    function delet_after_save(){
        $.each(del_item_id, function( index, value ) {

              var dataSet = {
                item_id: value
              }
            $.ajax({
            type: "POST",
                    url: '<?php  echo site_url() . "/pre_dispatch/ajaxRemoveRecordReservQty" ?>',
                    data: dataSet,
                    dataType: 'json',
                    async: false,
                    success: function (datas) {
                        if (datas.success != "OK"){
//                            can_remove = false;
//                            call_modal_alert(datas, null);
                        }  
                    }
            });
             
        });
//        var del_item_id = null;
        //  console.log('---');
    }

    function gen(){
        
        $( "#GEN" ).prop( "disabled", true );
        $("#GEN").css('background','#b9b9b9');
        $("#GEN").css('border','#b9b9b9');
        
        
        var test = '<?php echo json_encode($order_detail_data); ?>';
        test = jQuery.parseJSON(test);
 
        $.each(test, function( index, value ) {   
            del_item_id.push(value.Item_Id);
             clear_row(index,value.Item_Id);
         });
        var test2 = '<?php echo json_encode($order_detail_data_group); ?>';
        test2 = jQuery.parseJSON(test2);
        //  console.log(test2);
//         exit();
         var index_id = 0
         //count
//         console.log(test2.length)

         for (let value of test2) {
                gen2(value.Product_Code,value.Reserv_Qty,value,index_id,test2.length)
            index_id++
         }
         
        /////////////////////////////////////////////////////////////////////////////////////////////////
//        var test2 = '<?php // echo json_encode($order_detail_data_group); ?>';
//        test2 = jQuery.parseJSON(test2);
//
//         $.each(test2, function( index, value ) {
//            gen2(value.Product_Code,value.Reserv_Qty,index+1,test2.length );
//         });
        /////////////////////////////////////////////////////////////////////////////////////////////////

    }
    
  async  function gen2(ar_prod_id,ar_res_qty,del_list,row_index,count_item){

    //      console.log(ar_prod_id,ar_res_qty);
    $("#preload").show();
    if ($('#doc_refer_ext').val() == "") {
    //    alert("Please fill <?php echo _lang('document_ext'); ?>");
    //    $('#doc_refer_ext').focus();
    //    return false;
    }
<?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
            if ($('#productCode').val() == "") {
//            alert("Please fill <?php // echo _lang('product_code'); ?>");
//            $('#productCode').focus();
//            return false;
            }
<?php endif; ?>

    //บังคับให้กรอก container ก่อนถึงจะ enter ได้
    /* COMMENT BY POR 2014-09-27 : not use container this page use again in dispatch HH
     if(conf_cont){
     if($("#doc_refer_container option").length <= 0){
     alert("Please input container");
     $('#add_container').click();
     return false;
     }
     }
     */
    if ('<? echo $this->config->item('gen_dispatch_record') ?>' == '1') {

//    var product_code = $('#productCode').val();
    var product_code = ar_prod_id;
    var res_qty =  ar_res_qty;
    
    var productStatus_select = $('#productStatus_select').val();
    var productSubStatus_select = $('#productSubStatus_select').val();
    var productLot = $('#productLot').val();
    var productSerial = $('#productSerial').val();
    var productMfd = $('#productMfd').val();
    var productExp = $('#productExp').val();

    // var state = c_s;

    // console.log(state);
    //add for ISSUE 2549 : by kik : 20140217
    if (conf_pallet){
    var palletCode = $('#palletCode').val();
    var palletIsFull = $('[name="palletIsFull"]:checked').val();
    var palletDispatchType = $('[name="palletDispatchType"]:checked').val();
    var chkPallet = 0;
    if ($("#chkPallet").is(':checked')){
    var chkPallet = 1;
    }
    }


    var oTable = $('#showProductTable').dataTable().fnGetData();
    var arr_select_inbound_id = new Array;
    var arr_select_reserv_qty = new Array;
    for (i in oTable) {
    arr_select_inbound_id.push(oTable[i][ci_inbound_id]);
    arr_select_reserv_qty.push(oTable[i][ci_reserv_qty]);
    }
    
    if (conf_pallet && chkPallet == 1){
    var dataSet = {productCode_val: product_code
            , productStatus_val: productStatus_select
            , productSubStatus_val: productSubStatus_select
            , productLot_val: productLot
            , productSerial_val: productSerial
            , productMfd_val: productMfd
            , productExp_val: productExp
            , palletCode_val : palletCode
            , palletIsFull_val : palletIsFull
            , palletDispatchType_val : palletDispatchType
            , chkPallet_val : chkPallet
            , arr_select_inbound_id : arr_select_inbound_id
            , arr_select_reserv_qty : arr_select_reserv_qty
<?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
            , qty_of_sku : res_qty//$('#qty_of_sku').val()
<?php endif; ?>
    }
    } else{
    var dataSet = {productCode_val: product_code
            , productStatus_val: productStatus_select
            , productSubStatus_val: productSubStatus_select
            , productLot_val: productLot
            , productSerial_val: productSerial
            , productMfd_val: productMfd
            , productExp_val: productExp    
            , arr_select_inbound_id : arr_select_inbound_id
            , arr_select_reserv_qty : arr_select_reserv_qty
<?php if (@$this->settings['pre_dispatch_auto_qty']): ?>
            , qty_of_sku : res_qty//$('#qty_of_sku').val()
<?php endif; ?>
    }
    }
    // console.log('AA');
    // return false
    var oTable = $('#showProductTable').dataTable();
    var recordsTotal = oTable.fnSettings().fnRecordsTotal() + 1;
    $.post('<?php echo site_url() . "/pre_dispatch/showAndGenSelectData" ?>', dataSet, function(data) {
    //ADD BY POR 2014-01-09 กำหนดให้ ราคาต่อหน่วย,หน่วยของราคา และราคารวม เป็นค่าว่างในเบื้องต้น
    var all_sum = 0;
    // var abc = 0;
    //     return 
    data.product.map(element => {
        all_sum = parseInt(all_sum)+parseInt(element.Balance_Qty)
    });
        var list = '#'+del_list.Item_Id;
        var rm_list = '#res_qty'+del_list.Item_Id;

        if(data.alert != 'OK'){
            var rm_total = del_list.Reserv_Qty-all_sum;
            var get_total = $('#sum_recieve_qty').html()
            var sum_total = get_total-all_sum;
            $(rm_list).html(rm_total.toFixed(3));
            $(list).css({"color": "red"})
            all_sum = 0;

        }else{
//            clear_row(row_index,del_list.Item_Id);
        }

        // console.log(count_item)
        // return

    // if (data.alert != "OK"){
    // alert(data.alert);
    // $('#productCode').focus().select();
    // $("#preload").hide();
    // return false;
    // }

//                    var unitprice="";
    var allprice = "";
    var price = set_number_format(0);
    //END ADD

    var dispatch_qty = "";
    var remark = "";
    var arr_row_gen = new Array();
    $.each(data.product, function(i, item) {
    var invoice = "";
    var container = "";
    var unit_Price_value = "";
    var pallet_code = "";
    var type_pallet = "";
    var unit_price = "";
    var container_id = "";
    //add by kik : 20140113
//                        if(item.Price_Per_Unit != ""){
//                            price=item.Price_Per_Unit;
//                        }

    if (statusprice){
    if (item.Price_Per_Unit != ""){
    price = set_number_format(item.Price_Per_Unit);
    }

    unit_Price_value = item.Unit_Price_value;
    unit_price = item.Unit_Price_Id;
    }
    //end add by kik : 20140113

    var del = "<a ONCLICK=\"removeItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>";
    dispatch_qty = item.Reserve_Qty;
    //ADD BY POR 20114-02-19 ตรวจสอบกรณี dispatch_type=FULL จะไม่สามารถแก้ไข qty ได้เนื่องจากเราจะ change ทั้ง pallet
    if (conf_pallet){
    if (item.DP_Type_Pallet == "FULL"){
    $dispatch_qty = item.Est_Balance_Qty; //ให้มีค่าเท่ากับ Balance_Qty เนื่องจากบังคับให้ยกไปทั้งก้อน
    var dispatch_qty = $($dispatch_qty).text();
    //del = "DEL";
    del = "<a ONCLICK=\"removePalletItem(this)\" >" + '<?php echo img("css/images/icons/del.png"); ?>' + "</a>"; // ADD BY POR 2014-02-20 credit by kik
    }

    pallet_code = item.Pallet_Code;
    type_pallet = item.DP_Type_Pallet;
    }

    oTable.fnAddData([
            recordsTotal + i
            , item.Product_Code
            , item.Product_NameEN
            , item.Status_Value
            , item.Sub_Status_Value
            , item.Actual_Location
            , item.Product_Lot
            , item.Product_Serial
            , item.Product_Mfd
            , item.Last_Mfd //ADD LastMFD 
            , item.Product_Exp
            , invoice
            , container
            , item.Est_Balance_Qty
            , set_number_format(dispatch_qty)
            , item.Unit_Value
            , price                     //add by kik : 20140113
            , unit_Price_value     //add by kik : 20140113
            , allprice                  //add by kik : 20140113
            , remark
            , pallet_code //ADD BY POR 2014-02-19 เพิ่มให้แสดง Pallet_Code ด้วย
            , type_pallet       //ADD BY POR : 2014-02-19
            , del
            , item.Product_Id
            , item.Product_Status
            , item.Product_Sub_Status
            , item.Unit_Id
            , 'new'
            , item.Inbound_Id
            , item.Actual_Location_Id
            , unit_price        //add by kik : 20140113
            , container_id
    ]);
//                        calculate_qty();

    var new_td_item = $('td:eq(1)', $('#showProductTable tr:last'));
    new_td_item.addClass("td_click");
    new_td_item.attr('onclick', 'showProductEstInbound(' + '"' + item.Product_Code + '"' + ',' + item.Inbound_Id + ')');
    if (conf_pallet){
    if (item.DP_Type_Pallet == "FULL"){
    var dp_type_full = $('td:eq(' + ci_reserv_qty + ')', $('#showProductTable tr:last'));
    dp_type_full.addClass("readonly");
    }
    }

    arr_row_gen.push($("#showProductTable tr:last").index());
    });

    initProductTable(function(){

//                        $("#preload").show();

//                        console.log(arr_row_gen);
    $.each(arr_row_gen, function(key, rowPosition) {
//                          var rowPosition = $("#showProductTable tr:last").index();
//                            console.log(rowPosition);
    var value = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_reserv_qty];
    if (validateForm() === true) {
//                                    var columnPosition = ci_reserv_qty;
//                                    var dataTableColumnPosition = ci_reserv_qty;
//                                    var sColumnName = oTable.fnSettings().aoColumns[dataTableColumnPosition].sTitle;

    var doc_refer_ext = $("[name='doc_refer_ext']").val().toUpperCase();
    var product_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_id];
    var product_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_code];
    var product_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_status];
    var product_sub_status = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_prod_sub_status];
    var suggest_loc = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_suggest_loc];
    var lot = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_lot];
    var serial = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_serial];
    var mfd = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_mfd];
    var exp = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_exp];
    var reserv_qty = value;
    var unit_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_id];
    var price_per_unit = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_price_per_unit];
    var unit_price_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_unit_price_id];
    var all_price = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_all_price];
    var remark = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_remark];
    var pallet_code = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_pallet_code];
    var item_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_item_id];
    var inbound_id = $('#showProductTable').dataTable().fnGetData()[rowPosition][ci_inbound_id];
    var callback_count = 1;
    var order_id = $('#frmPreDispatch').find('input[name="order_id"]').val();
    if (typeof order_id == 'undefined'){

    var dataSet = $("#frmPreDispatch").serialize();


//    $.ajax({
//    type: "POST",
//            url: '<?php // echo site_url() . "/pre_dispatch/ajaxCreateDocument" ?>',
//            data: dataSet,
//            dataType: 'json',
//            async: false,
//            success: function (datas) {
//
//            if (datas.success != "OK"){
//            $("#preload").hide();
//            call_modal_alert(datas, null);
//            } else{
//            $('#document_no').val(datas.document_no);
//            order_id = datas.order_id;
//            var frmPreDispatch = document.getElementById("frmPreDispatch");
//            var append_order_id = document.createElement("input");
//            append_order_id.setAttribute('type', "hidden");
//            append_order_id.setAttribute('name', "order_id");
//            append_order_id.setAttribute('value', datas.order_id);
//            frmPreDispatch.appendChild(append_order_id);
//            var append_flow_id = document.createElement("input");
//            append_flow_id.setAttribute('type', "hidden");
//            append_flow_id.setAttribute('name', "flow_id");
//            append_flow_id.setAttribute('value', datas.flow_id);
//            frmPreDispatch.appendChild(append_flow_id);
//            var append_token = document.createElement("input");
//            append_token.setAttribute('type', "hidden");
//            append_token.setAttribute('name', "token");
//            append_token.setAttribute('value', datas.token);
//            frmPreDispatch.appendChild(append_token);
//            $('#btn_action_reject').show();
//            }
//
//            callback_count--;
//            }
//    });
    } else{
    callback_count--;
    }

    var initInterval = setInterval(function(){
    if (callback_count == 0) {
    clearInterval(initInterval);
    var dataSet = {
    order_id: order_id
            , doc_refer_ext: doc_refer_ext
            , product_id: product_id
            , product_code: product_code
            , product_status: product_status
            , product_sub_status: product_sub_status
            , suggest_loc: suggest_loc
            , lot: lot
            , serial: serial
            , mfd: mfd
            , exp: exp
            , reserv_qty: reserv_qty
            , unit_id: unit_id
            , price_per_unit: price_per_unit
            , unit_price_id: unit_price_id
            , all_price: all_price
            , remark: remark
            , pallet_code: pallet_code
            , item_id: item_id
            , inbound_id: inbound_id
    }
    // console.log('790');
//    $.ajax({
//    type: "POST",
        //    url: '<?php //echo site_url() . "/pre_dispatch/ajaxSaveEditedRecordReservQty" ?>',
//            data: dataSet,
//            dataType: 'json',
//            async: false,
//            success: function (datas) {
////                                                oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
//            if (datas.success != "OK"){
//            call_modal_alert(datas, null);
//            } else{
//            oTable.fnUpdate(datas.item_id, rowPosition, ci_item_id);
//            oTable.fnUpdate(value, rowPosition, ci_reserv_qty);
////                                                    oTable.fnUpdate(value_column, rowPosition, dataTableColumnPosition);
//            socket.emit('update_est_balance', db_config, uniqid, datas.item_id);
//            }
//            }
//    });
    calculate_qty();
    $("#preload").hide();
    }
    }, 500);
    } else {
    $("#preload").hide();
    alert("Please Check Your Require Information (Red label).");
    return false;
    }
    });
    });
    $('#qty_of_sku').val('');
    $('#productCode').val('').focus();
    }, "json");
    // $('#sum_recieve_qty').html(sum_total);
    } else{
    $('#getBtn').click();
    // $('#sum_recieve_qty').html(sum_total);
    }
    calculate_qty();
    return false;
    
    
    
    }
     

    function cancel() {
    if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
    url = "<?php echo site_url(); ?>/flow/flowPreDispatchList";
    redirect(url)
    }
    }

    function validateForm() {
<?php //echo $validate           ?>
    //validate engine
    var status;
    var index = 0;
    var flag = 0;
    $("form").each(function() {//focusCleanup: true,

    $(this).validate({
    rules: {
    estDispatchDate: {
    required: true,
            custom_date: true
    }
    }
    });
    $(this).valid();
    flag += ($(this).valid() == true ? 1 : 0);
    index++;
    });
    status = (index == flag ? true : false);
    return status;
    }

    function validateDateRange(dateFrom, dateTo, interval) {
    var nowTemp = new Date();
    var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
    var checkin = $(dateFrom).datepicker({
    onRender: function(date) {
    return date.valueOf() < now.valueOf() ? 'disabled' : '';
    }
    }).on('changeDate', function(ev) {
    if (ev.date.valueOf() > checkout.date.valueOf()) {
    var newDate = new Date(ev.date);
    newDate.setDate(newDate.getDate() + 1);
    checkout.setValue(newDate);
    }
    checkin.hide();
    $(dateTo)[0].focus();
    }).data('datepicker');
    var checkout = $(dateTo).datepicker({
    onRender: function(date) {
    return date.valueOf() <= checkin.date.valueOf() ? 'disabled' : '';
    }
    }).on('changeDate', function(ev) {
    checkout.hide();
    }).data('datepicker');
    }

    /*
     //
     Backup Code : Test Comment not Use Function
     function checkOnlyEnglish(id){
     var selector = '#'+id;
     $(selector).keypress(function(event){
     var ew = event.which;
     //            if(ew == 32)
     //                return true; SpaceBar Is Not Allow here.
     if(48 <= ew && ew <= 57){
     return true;
     }
     if(65 <= ew && ew <= 90){
     return true;
     }
     if(97 <= ew && ew <= 122){
     return true;
     }
     return false;
     });
     }
     */
    function setNumberFormat(id) {
    var selector = "#".id;
    $(selector).blur(function() {
    $(this).parseNumber({format: "#,###.00", locale: "us"});
    $(this).formatNumber({format: "#,###.00", locale: "us"});
    });
    }

    // Add function calculate_qty : by kik : 28-10-2013
    function calculate_qty() {

    var rowData = $('#showProductTable').dataTable().fnGetData();
    var rowData2 = $('#showProductTable').dataTable(); //add by kik : 20140113

    var num_row = rowData.length;
    var sum_cf_qty = 0;
    var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
    var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด
    for (i in rowData) {
    var tmp_qty = 0;
    var tmp_price = 0; //ราคาต่อหน่วย
    var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ

    //+++++ADD BY POR 2013-11-29 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
    var str = rowData[i][ci_reserv_qty];
    rowData[i][ci_reserv_qty] = str.replace(/\,/g, '');
    tmp_qty = parseFloat(rowData[i][ci_reserv_qty]); //+++++ADD BY POR 2013-11-29 แก้ไขให้เป็น parseFloat เนื่องจาก qty เปลี่ยนเป็น float

    if (!($.isNumeric(tmp_qty))) {
    tmp_qty = 0;
    }

    //+++++ADD BY POR 2014-01-09 เพิ่มการคำนวณราคา
    var str2 = rowData[i][ci_price_per_unit]; //ราคาต่อหน่วย
    rowData[i][ci_price_per_unit] = str2.replace(/\,/g, '');
    tmp_price = parseFloat(rowData[i][ci_price_per_unit]);
    if (!($.isNumeric(tmp_price))) {
    tmp_price = 0;
    }

    //นำ qty มาคูณกับราคาต่อหน่วย เพื่อหาราคาทั้งหมดต่อหนึ่งรายการ
    all_price = tmp_price * tmp_qty;
    sum_price = sum_price + tmp_price; //รวมราคาทุกรายการต่อหน่วย
    sum_all_price = sum_all_price + all_price; //รวมราคาทั้งหมด

    rowData2.fnUpdate(set_number_format(all_price), parseFloat(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
    //END ADD

    sum_cf_qty = sum_cf_qty + tmp_qty;
    }

    $('#sum_recieve_qty').html(set_number_format(sum_cf_qty));
    $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
    $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-09 เพิ่มให้แสดงราคารวมทั้งหมด

    }
    // end function calculate_qty : by kik : 28-10-2013


    function initProductsDatatables(results) {
    if (oTable != null) {
//            console.log('not null');
//            console.log(oTable);
    oTable.fnDestroy();
//            console.log(oTable);
    }

    var product_code = $('#productCode').val();
    var productStatus_select = $('#productStatus_select').val();
    var productSubStatus_select = $('#productSubStatus_select').val();
    var productLot = $('#productLot').val();
    var productSerial = $('#productSerial').val();
    var productMfd = $('#productMfd').val();
    var productExp = $('#productExp').val();
    var dp_type = $('#dispatch_type_select').val();
    if (conf_pallet){
    var palletCode = $('#palletCode').val();
    var palletIsFull = $('[name="palletIsFull"]:checked').val();
    var palletDispatchType = $('[name="palletDispatchType"]:checked').val();
    var chkPallet = 0;
    if ($("#chkPallet").is(':checked')){
    var chkPallet = 1;
    }
    }

    getDetailurl = "<?php echo site_url(); ?>/pre_dispatch/getSelectPreDispatchData";
    var tmp_oTable = $('#showProductTable').dataTable().fnGetData();
    var arr_select_inbound_id = new Array;
    var arr_select_reserv_qty = new Array;
    for (i in tmp_oTable) {
    arr_select_inbound_id.push(tmp_oTable[i][ci_inbound_id]);
    arr_select_reserv_qty.push(tmp_oTable[i][ci_reserv_qty]);
    }
    arr_select_inbound_id = arr_select_inbound_id.join(separator);
    arr_select_reserv_qty = arr_select_reserv_qty.join(separator);
    //กรณีมีการเปิดใช้ pallet และ dispatch type='FULL' ให้แสดง datatable คนละตัวกับที่ใช้ปกติ

    if ((conf_pallet && palletDispatchType == 'FULL') && chkPallet == 1){
    $('#modal_data_table').hide();
    $('#modal_data_table_pallet').show();
    oTable = $('#modal_data_table_pallet').dataTable({
    "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "iDisplayLength": 250,
            "sPaginationType": "full_numbers",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": getDetailurl,
            "fnServerData": function(sSource, aoData, fnCallback) {
            var spl = $("[name='doc_refer_ext']").val().toUpperCase();
            aoData.push(
            {"name": "productCode_val", "value": product_code},
            {"name": "productStatus_val", "value": productStatus_select},
            {"name": "productSubStatus_val", "value": productSubStatus_select},
            {"name": "productLot_val", "value": productLot},
            {"name": "productSerial_val", "value": productSerial},
            {"name": "productMfd_val", "value": productMfd},
            {"name": "productExp_val", "value": productExp},
            {"name": "dp_type", "value": dp_type},
            {"name": "arr_select_inbound_id", "value": arr_select_inbound_id},
            {"name": "arr_select_reserv_qty", "value": arr_select_reserv_qty}
            );
            if (conf_pallet && chkPallet == 1){
            aoData.push(
            {"name": "palletCode_val", "value": palletCode},
            {"name": "palletIsFull_val", "value": palletIsFull},
            {"name": "palletDispatchType_val", "value": palletDispatchType},
            {"name": "chkPallet_val", "value": chkPallet}
            );
            }

            $.getJSON(sSource, aoData, function(json) {
            fnCallback(json);
            for (i in allVals) {
            $('#chkBoxVal' + allVals[i]).prop('checked', true);
            }
            });
            }
    });
    $('#modal_data_table_pallet_filter label input')
            .unbind('keypress keyup')
            .bind('keypress keyup', function(e){
            if (e.keyCode == 13){
            oTable.fnFilter($(this).val());
            }
            });
    // DATATABLE
    } else { //อันนี้คือแบบธรรมดา
    $('#modal_data_table_pallet').hide();
    $('#modal_data_table').show();
    oTable = $('#modal_data_table').dataTable({
    "bJQueryUI": true,
            "bSort": false,
            "bAutoWidth": false,
            "iDisplayLength": 250,
            "sPaginationType": "full_numbers",
            "bProcessing": true,
            "bServerSide": true,
            "sAjaxSource": getDetailurl, //add for ISSUE 2549 : by kik : 20140217
            "fnRowCallback" : processRow, // Add for trigger row event
            "fnServerData": function(sSource, aoData, fnCallback) {
            var spl = $("[name='doc_refer_ext']").val().toUpperCase();
            aoData.push(
            {"name": "productCode_val", "value": product_code},
            {"name": "productStatus_val", "value": productStatus_select},
            {"name": "productSubStatus_val", "value": productSubStatus_select},
            {"name": "productLot_val", "value": productLot},
            {"name": "productSerial_val", "value": productSerial},
            {"name": "productMfd_val", "value": productMfd},
            {"name": "productExp_val", "value": productExp},
            {"name": "dp_type", "value": dp_type},
            {"name": "arr_select_inbound_id", "value": arr_select_inbound_id},
            {"name": "arr_select_reserv_qty", "value": arr_select_reserv_qty}
            );
            //add for ISSUE 2549 : by kik : 20140217
            if (conf_pallet && chkPallet == 1){
            aoData.push(
            {"name": "palletCode_val", "value": palletCode},
            {"name": "palletIsFull_val", "value": palletIsFull},
            {"name": "palletDispatchType_val", "value": palletDispatchType},
            {"name": "chkPallet_val", "value": chkPallet}
            );
            }
            //end add for ISSUE 2549 : by kik : 20140217

            $.getJSON(sSource, aoData, function(json) {
            fnCallback(json);
            //                    $('.fg-toolbar.ui-toolbar.ui-widget-header.ui-corner-tl.ui-corner-tr.ui-helper-clearfix').hide(); // Add BY Akkarapol, 27/11/2013, เซ็ตให้ แถบด้านบนของ Modal ที่มี "Show 100 entries" กับ แถบ Search ซ่อนเอาไว้ไม่ต้องแสดง

            // Add By Akkarapol, 27/11/2013, เพิ่มสำหรับวน loop เซ็ตให้ checkbox มัน checked ตามที่ได้ถูก check ไว้ในกรณีที่เปลี่ยนหน้าไปหน้าอื่น แล้วกลับมายังหน้านั้น จะทำให้ checkbox จะยังถูก checked อยู่
            for (i in allVals) {
            $('#chkBoxVal' + allVals[i]).prop('checked', true);
            }
            // END Add By Akkarapol, 27/11/2013, เพิ่มสำหรับวน loop เซ็ตให้ checkbox มัน checked ตามที่ได้ถูก check ไว้ในกรณีที่เปลี่ยนหน้าไปหน้าอื่น แล้วกลับมายังหน้านั้น จะทำให้ checkbox จะยังถูก checked อยู่

            });
            },
            //ADD BY POR 2013-11-29 เพิ่มให้ตัวเลข qty ชิดขวา
            "aoColumnDefs": [
            {"sClass": "right_text", "aTargets": [12]},
            {"sClass": "right_text", "aTargets": [13]},
            {"bVisible": conf_pallet, "aTargets": [14]}
            ]
    });
    }
    }


</script>
<style>
    #myModal {
        width: 90%!important;	/* SET THE WIDTH OF THE MODAL */
        top:42%!important;
        margin-left: -46%!important;
    }
    /*    #myModal {
            width: 1024px;  SET THE WIDTH OF THE MODAL
            margin: -250px 0 0 -512px;  CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;)
        }*/
    .Stitle{
        width: 100px;
        padding-left: 5px;
    }
    .Stxt{
        width: 180px;
    }
    /* START Custom position */
    /*    .tooltip {
            left: 180px !important;
            width: 200px !important;
        }*/
    /* END Custom position*/

    /*COMMENT BY KIK 2013-10-18 เพิ่ม class สำหรับบอกว่าเป็น record ที่ไม่มี inbound_id หรือ ไม่มี location ที่สามารถหยิบได้*/
    .item_err{
        /*background-color:red;*/
        /*border-style:solid;*/
        /*border-color: red;*/
        /*border-width:1px;*/
        color: red;
        /*font-style: C*/
    }

    .background {
      background-color: rgba(255,0,0,0.35);
    }

    /*END COMMENT*/
    .select2-search--dropdown .select2-search__field {
    padding: 4px;
    width: 100%;
    box-sizing: border-box;
    width: 100%;
    height: auto !important;
    font-size: 13px;
}

</style>
<?php
if (!isset($DocNo)) {
    $DocNo = "";
}
if (!isset($est_action_date)) {
    $est_action_date = date("d/m/Y");
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
if (!isset($remark)) {
    $remark = "";
}
if (!isset($process_type)) {
    $process_type = $data_form['process_type'];
}

if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
    $is_urgent = false;
} else {
    $is_urgent = true;
}
?>
<div class="content well" style='height:100%' >
    <form class="" method="POST" action="" id="frmPreDispatch" name="frmPreDispatch" >
        <fieldset style="margin:0px auto;">
            <?php
            if (isset($flow_id)) {
                echo form_hidden('flow_id', $flow_id, 'id="flow_id"');
            }
            ?>
            <?php
            if (isset($order_id)) {
                echo form_hidden('order_id', $order_id);
            }
            ?>
            <?php echo form_hidden('process_id', $process_id); ?>
            <?php echo form_hidden('present_state', $present_state); ?>
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <input type="hidden" name="queryText" id="queryText" value=""/>
            <input type="hidden" name="search_param" id="search_param" value=""/>
            <legend>Pre-Dispatch Order</legend>
            <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                <tr>
                    <td align="right">
                        Renter : 
                    </td>
                    <td align="left">
                        {renter_id_select}
                    </td>
                    <td align="right">
                        <?php echo _lang("cus_shipper_outbound"); ?> :
                    </td>
                    <td align="left">
                        {frm_warehouse_select}
                    </td>
                    <td align="right">
                        Consignee :
                    </td>
                    <td>
                        {to_warehouse_select}
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        Document No. :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="AUTO GENERATE DDR" id="document_no" name="document_no" disabled value="<?php echo $DocNo; ?>" />
                    </td>
                    <td align="right">
                        Document External :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="<?php echo DOCUMENT_EXT; ?>" id="doc_refer_ext" name="doc_refer_ext"  value="<?php echo $doc_refer_ext; ?>"class="required" style="text-transform: uppercase"/>
                        <!--<input type="text" placeholder="<?php echo DOCUMENT_EXT; ?>" id="doc_refer_ext" name="doc_refer_ext"  value="<?php echo (!empty($doc_refer_ext)) ? $doc_refer_ext : time(); ?>"class="required document notAllowSpace" style="text-transform: uppercase"/>-->
                    </td>
                    <td align="right">
                        Document Internal :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="<?php echo DOCUMENT_INT; ?>" class="document" id="doc_refer_int" name="doc_refer_int" value="<?php echo $doc_refer_int; ?>" style="text-transform: uppercase"/>
                    </td>
                </tr>
                <tr>
                    <td align="right">Invoice No. :</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'placeholder="' . DOCUMENT_INV . '"  class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry : </td>
                    <td align="left"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No. :</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">
                        Dispatch Type :
                    </td>
                    <td>
                        {dispatch_type_select}
                    </td>
                    <td align="right">
                        Est. Dispatch Date :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" value="<?php echo $est_action_date; ?>"/>
                    </td>
                    <td align="right">
                        Dispatch Date :
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Date Format" id="dispatchDate" name="dispatchDate" value="" disabled>
                    </td>
                </tr>
                <tr>                    
                    <td align="right"><label style="margin-top: -33px;">Delivery Time :</label></td>
                    <td align="left">
                        <input type="text" id="DeliveryTime" name="DeliveryTime" value="<?php echo $DeliveryTime ?>" style="margin-bottom: 12px;margin-top: -12px;">
                        <br>
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b></font>
                    </td>
                    <td align="right">
                        <label >Destination :</label>
                    </td>
                    <td>
                        <textarea id="DestinationDetail" name="DestinationDetail" rows="3" ><?php echo $DestinationDetail ?></textarea>
                    </td>   
                    <td align="right">Remark :</td>
                    <td align="left" colspan="2">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="3" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
	     </td>
                </tr>
                <tr> 
<!--                     <td></td>
                    <td align="left">
                        //add for ISSUE 3312 : by kik : 20140120
                    <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                    </td>-->
                   
                    <?php if ($conf_cont): ?>
                                            <!--ยกเลิกการทำงานในส่วนนี้ ให้ไปทำงานในหน้า Dispatch HH แทน-->
                                            <!--                        <td align="right"><?php echo _lang('container_out'); ?></td>
                                                                    <td align="left">
                        <?php //echo form_multiselect('doc_refer_container', (empty($doc_refer_container)?array():$doc_refer_container), NULL, 'disabled="disabled" id="doc_refer_container" placeholder="' . DOCUMENT_CONTAINER . '"  style="text-transform: uppercase"');  ?>
                                                                        <img id="add_container" src="<?php echo base_url("images/add.png") ?>" style="width: 22px; height: 22px; margin-bottom: 3px; cursor: pointer;" />
                                                                        <input type="hidden" id="doc_refer_con_size" name="doc_refer_con_size">
                                                                    </td>-->
                    <?php endif; ?>
                </tr>
            </table>
            </fieldset>
            <input type="hidden" name="token" value="<?php echo $token ?>" />
            <input type="hidden" id="container_list" name="container_list" value="<?php echo $container_list; ?>" />
        <input type="hidden" id="container_size_list" name="container_size_list" value="<?php echo $container_size_list; ?>" />
        </form>
            <fieldset>

                <legend>Product Detail </legend>

                <table cellpadding="1" cellspacing="1" style="width:100%; margin:0px auto;" >
                    <tr>
                        <td>
                            <table style="width:100%; margin:0px auto;">
                        <?php $this->load->view('element_filtergetDetail'); ?>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td >
                            <div id="modal_data_table_wrapper" class="dataTables_wrapper" role="grid" style="width:100%;overflow-x: auto;margin:0px auto;">
                                <div style="width:100%;overflow-x: auto;" id="showDataTable">
                                    <table cellpadding="2" cellspacing="0" border="0" class="display" id="showProductTable">
                                        <thead>
                                    <?php
                                    $show_column = array(
                                        _lang('no'),
                                        _lang('product_code'),
                                        _lang('product_name'),
                                        _lang('product_status'),
                                        _lang('product_sub_status'),
                                        _lang('location_code'),
                                        _lang('lot'),
                                        _lang('serial'),
                                        _lang('product_mfd'),
                                        _lang('last_mfd'),
                                        _lang('product_exp'),
                                        _lang('invoice_out'),
                                        _lang('container_out'),
                                        _lang('est_balance_qty'),
                                        _lang('reserve_qty'),
                                        _lang('unit'),
                                        _lang('price_per_unit'),
                                        _lang('unit_price'),
                                        _lang('all_price'),
                                        _lang('remark'),
                                        _lang('pallet_code_in'),
                                        _lang('DP_Type_Pallet'),
                                        _lang('del'),
                                        "Product_Id",
                                        "Product_Status",
                                        "Product_Sub_Status",
                                        "Unit_Id",
                                        "Item_Id",
                                        "Inbound Id",
                                        "Suggest_Location_Id",
                                        "Price/Unit ID", //add by kik : 20140113
                                        "Container_id"
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
                                    $sum_Reserv_Qty = 0;   //add by kik : 25-10-2013
                                    $sumPriceUnit = 0; //ADD BY POR 2014-01-09 ราคารวมทั้งหมดต่อหน่วย
                                    $sumPrice = 0; //ADD BY POR 2014-01-09 ราคารวมทั้งหมด

                                    if (isset($order_detail_data)) {
                                        $str_body = "";
                                        $j = 1;

                                        foreach ($order_detail_data as $order_column) {
                                            $class_DP_Type_Palet = (!empty($order_column->DP_Type_Pallet) && $order_column->DP_Type_Pallet == "FULL") ? "readonly" : "";
                                            // $bg  = ($order_column->Last_Mfd >= $order_column->Product_Mfd)? "background-color:rgba(255,0,0,0.35)" : "";
                                            // $last_mfd = $order_column->Last_Mfd;
                                            // $Product_Mfd = $order_column->Product_Mfd;
   
                                            $res = explode("/", $order_column->Last_Mfd);
                                            $changedDate = $res[2]."-".$res[1]."-".$res[0];
                                            $last_mfd =  date('Y-m-d',strtotime($changedDate));

                                            $res1 = explode("/", $order_column->Product_Mfd);
                                            $changedDate1 = $res1[2]."-".$res1[1]."-".$res1[0];
                                            $Product_Mfd =  date('Y-m-d',strtotime($changedDate1));

                                            if($last_mfd > $Product_Mfd){
                                            $order_id = $order_column->Order_Id;
                                            $str_body .= "<tr id=\"" . $order_column->Item_Id . "\">";
                                            $str_body .= "<font-color='red'><td class= 'background'; >" . $j . "</td>";
                                            //add class td_click and ONCLICK for show Product Est. balance Detail modal : by kik : 06-11-2013
                                            $str_body .= "<td  style='background-color:rgba(255,0,0,0.35)'  id='prod_code_" . $order_column->Item_Id . "'  " . (!empty($order_column->Inbound_Item_Id) ? " class='td_click' ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'" : "") . ">" . $order_column->Product_Code . "</td>"; // add id : by kik : 2013-11-12
//                                            $str_body .= "<td title=\"" . $order_column->Full_Product_Name . "\">" . $order_column->Product_Name . "</td>";
//                                            $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20131127
                                            $str_body .= "<td  class= 'background';  title=\"" . str_replace('"', '&quot;', $order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                            $str_body .= "<td  class= 'background';  id='status_" . $order_column->Item_Id . "'>" . $order_column->Status_Value . "</td>";
                                            $str_body .= "<td class= 'background';  id='sub_status_" . $order_column->Item_Id . "'>" . $order_column->Sub_Status_Value . "</td>";
//                                            <input type='button' style='width:20px'  onclick='refill_this(".$order_column->Product_Code.");'></input>
//                                            if(empty($order_column->Suggest_Location )){
                                                $str_body .= "<td class= 'background';  id='location_code_" . $order_column->Item_Id . "'>" . $order_column->Suggest_Location .  "</td>";
//                                            }
//                                            else{
//                                                $str_body .= "<td id='location_code_" . $order_column->Item_Id . "'>" . $order_column->Suggest_Location .  "<input type='button' style='width:40px'  onclick=\"refill_this('".$order_column->Product_Code."');\" value='refill'></td>";
//                                            }
                                            $str_body .= "<td class= 'background';  id='lot_" . $order_column->Item_Id . "'>" . $order_column->Product_Lot . "</td>";
                                            $str_body .= "<td class= 'background';  id='sel_" . $order_column->Item_Id . "'>" . $order_column->Product_Serial . "</td>";
                                            $str_body .= "<td class= 'background';  id='mfd_" . $order_column->Item_Id . "'>" . $order_column->Product_Mfd . "</td>";
                                            $str_body .= "<td class= 'background';  id='last_mfd_" . $order_column->Item_Id . "'>" . $order_column->Last_Mfd . "</td>"; //ADD Last MFD
                                            $str_body .= "<td class= 'background'; id='exp_" . $order_column->Item_Id . "'>" . $order_column->Product_Exp . "</td>";
//                                            $str_body .= "<td>" . $order_column->Balance_Qty . "</td>";   //comment by kik (03-10-2013)
                                            $str_body .= "<td class= 'background'; >" . @$order_column->Invoice_No . "</td>";
                                            $str_body .= "<td class= 'background'; >" . @$order_column->Cont_No . ' ' . @$order_column->Cont_Size_No . @$order_column->Cont_Size_Unit_Code . "</td>";
//                                            $str_body .= "<td style='text-align: right;' class='number label_inbound_id_{$order_column->Inbound_Item_Id}' id='est_" . $order_column->Item_Id . "'>" . set_number_format($order_column->Est_Balance_Qty) . "</td>"; //add by kik (03-10-2013)
                                            $str_body .= "<td class= 'background'; style='text-align: right;   class='number' id='est_" . $order_column->Item_Id . "'><span class='est_of_inbound_" . $order_column->Inbound_Item_Id . "' >" . set_number_format($order_column->Est_Balance_Qty) . "</span></td>"; //add by kik (03-10-2013)
                                            $str_body .= "<td class= 'background'; style='text-align: right;   id='res_qty" . $order_column->Item_Id . "'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Unit_Value . "</td>";
                                            $str_body .= "<td class= 'background'; >" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";       //add by kik : 20140113
                                            $str_body .= "<td class= 'background';  >" . @$order_column->Unit_Price_value . "</td>";                        //add by kik : 20140113
                                            $str_body .= "<td class= 'background'; >" . set_number_format(@$order_column->All_Price) . "</td>";            //add by kik : 20140113
                                            $str_body .= "<td class= 'background'; >" . $order_column->Remark . "</td>";
                                            $str_body .= "<td class= 'background'; style='text-align: center;  '>" . @$order_column->Pallet_Code . "</td>";
                                            $str_body .= "<td style=\"display:none;\">" . @$order_column->DP_Type_Pallet . "</td>";    // ADD BY POR 2014-02-19
                                            $str_body .= "<td class= 'background'; ><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Product_Id . "</td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Product_Status . "</td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Product_Sub_Status . "</td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Unit_Id . "</td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Item_Id . "</td>";
                                            $str_body .= "<td class= 'background';  id='inbound_" . $order_column->Item_Id . "'>" . $order_column->Inbound_Item_Id . "</td>";
                                            $str_body .= "<td class= 'background'; >" . $order_column->Suggest_Location_Id . "</td>";
                                            $str_body .= "<td class= 'background'; >" . @$order_column->Unit_Price_Id . "</td>";                           //add by kik : 20140113
                                            $str_body .= "<td style=\"display:none\">" . @$order_column->Cont_Id . "</td>";
                                            $str_body .= "</font></tr>";
                                            $j++;
                                            $sum_Reserv_Qty+=@$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                            $sumPriceUnit+=@$order_column->Price_Per_Unit;      //add by kik : 20140113
                                            $sumPrice+=@$order_column->All_Price; 
                                        }else{
                                            $order_id = $order_column->Order_Id;
                                            $str_body .= "<tr id=\"" . $order_column->Item_Id . "\">";
                                            $str_body .= "<font-color='red'><td>" . $j . "</td>";
                                            $str_body .= "<td id='prod_code_" . $order_column->Item_Id . "'  " . (!empty($order_column->Inbound_Item_Id) ? " class='td_click' ONCLICK='showProductEstInbound(\"{$order_column->Product_Code}\",{$order_column->Inbound_Item_Id})'" : "") . ">" . $order_column->Product_Code . "</td>"; // add id : by kik : 2013-11-12
                                            $str_body .= "<td title=\"" . str_replace('"', '&quot;', $order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                                            $str_body .= "<td id='status_" . $order_column->Item_Id . "'>" . $order_column->Status_Value . "</td>";
                                            $str_body .= "<td id='sub_status_" . $order_column->Item_Id . "'>" . $order_column->Sub_Status_Value . "</td>";                                                $str_body .= "<td id='location_code_" . $order_column->Item_Id . "'>" . $order_column->Suggest_Location .  "</td>";
                                            $str_body .= "<td id='lot_" . $order_column->Item_Id . "'>" . $order_column->Product_Lot . "</td>";
                                            $str_body .= "<td id='sel_" . $order_column->Item_Id . "'>" . $order_column->Product_Serial . "</td>";
                                            $str_body .= "<td id='mfd_" . $order_column->Item_Id . "'>" . $order_column->Product_Mfd . "</td>";
                                            $str_body .= "<td class='Last_Mfd' id='last_mfd_" . $order_column->Item_Id . "'>" . $order_column->Last_Mfd . "</td>"; //ADD Last MFD
                                            $str_body .= "<td id='exp_" . $order_column->Item_Id . "'>" . $order_column->Product_Exp . "</td>";
                                            $str_body .= "<td>" . @$order_column->Invoice_No . "</td>";
                                            $str_body .= "<td>" . @$order_column->Cont_No . ' ' . @$order_column->Cont_Size_No . @$order_column->Cont_Size_Unit_Code . "</td>";
                                            $str_body .= "<td style='text-align: right;' class='number' id='est_" . $order_column->Item_Id . "'><span class='est_of_inbound_" . $order_column->Inbound_Item_Id . "' >" . set_number_format($order_column->Est_Balance_Qty) . "</span></td>"; //add by kik (03-10-2013)
                                            $str_body .= "<td  style='text-align: right;' id='res_qty" . $order_column->Item_Id . "'>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                                            $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                                            $str_body .= "<td>" . set_number_format(@$order_column->Price_Per_Unit) . "</td>";       //add by kik : 20140113
                                            $str_body .= "<td>" . @$order_column->Unit_Price_value . "</td>";                        //add by kik : 20140113
                                            $str_body .= "<td>" . set_number_format(@$order_column->All_Price) . "</td>";            //add by kik : 20140113
                                            $str_body .= "<td>" . $order_column->Remark . "</td>";
                                            $str_body .= "<td style='text-align: center;'>" . @$order_column->Pallet_Code . "</td>";
                                            $str_body .= "<td  style=\"display:none;\">" . @$order_column->DP_Type_Pallet . "</td>";    // ADD BY POR 2014-02-19
                                            $str_body .= "<td><a ONCLICK=\"deleteItem(this)\" >" . img("css/images/icons/del.png") . "</a></td>";
                                            $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Status . "</td>";
                                            $str_body .= "<td>" . $order_column->Product_Sub_Status . "</td>";
                                            $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                                            $str_body .= "<td id='inbound_" . $order_column->Item_Id . "'>" . $order_column->Inbound_Item_Id . "</td>";
                                            $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                                            $str_body .= "<td>" . @$order_column->Unit_Price_Id . "</td>";                           //add by kik : 20140113
                                            $str_body .= "<td style=\"display:none\">" . @$order_column->Cont_Id . "</td>";
                                            $str_body .= "</font></tr>";
                                            $j++;
                                            $sum_Reserv_Qty+=@$order_column->Reserv_Qty;        //add by kik    :   25-10-2013
                                            $sumPriceUnit+=@$order_column->Price_Per_Unit;      //add by kik : 20140113
                                            $sumPrice+=@$order_column->All_Price; 

                                            }              //add by kik : 20140113
                                        }
                                        echo $str_body;
                                    }
                                    ?>
                                        </tbody>
                                         <!-- show total qty : by kik : 28-10-2013-->
                                        <tfoot>
                                           <tr>
                                                 <th colspan='14' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                                                 <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Reserv_Qty); ?></span></th>
                                                 <th></th>
                                                 <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                                                 <th></th>
                                                 <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                                                 <th colspan='13' class ='ui-state-default indent' ></th>
                                            </tr>
                                        </tfoot>
                                        <!-- end show total qty : by kik : 28-10-2013-->
                                    </table>
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </fieldset>
</div>
<div id="c_s"></div>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">Pre-Dispatch Order</h3>
    </div>
    <div class="modal-body">

        <!--Add By Akkarapol, 28/11/2013, เพิ่ม table เข้ามาเพื่อใช้รับ JSON ที่เราทำการ Customize ตัว dataTable ขึ้นมาเอง เพื่อรองรับการทำ performance ในเรื่องของการ load page ทีละ page -->
        <table id="modal_data_table" cellpadding="0" cellspacing="0" border="0" aria-describedby="modal_data_table_info" class="well" style="max-width: none;">
            <thead>
                    <tr>
                            <th></th>
                            <th><?php echo _lang('product_code'); ?></th>
                            <th><?php echo _lang('product_name'); ?></th>
                            <th><?php echo _lang('product_status'); ?></th>
                            <th><?php echo _lang('product_sub_status'); ?></th>
                            <th><?php echo _lang('location_code'); ?></th>
                            <th><?php echo _lang('lot'); ?></th>
                            <th><?php echo _lang('serial'); ?></th>
                            <th><?php echo _lang('receive_date'); ?></th>
                            <th><?php echo _lang('product_mfd'); ?></th>
                            <th><?php echo _lang('product_exp'); ?></th>
                            <th><?php echo _lang('aging'); ?></th>
                            <th><?php echo _lang('est_balance_qty'); ?></th>
                            <th><?php echo _lang('balance'); ?></th>
                            <th><?php echo _lang('pallet_code_in'); ?></th>

                    </tr>
            </thead>
            <tbody></tbody>
            <tfoot></tfoot>
    </table>
    <!--ADD BY POR 2014-02-18 สร้าง datatable เพิ่มเติมกรณีแสดงแบบ dispatch full-->
    <table id="modal_data_table_pallet" cellpadding="0" cellspacing="0" border="0" aria-describedby="modal_data_table_info" class="well" style="max-width: none;">

            <thead>
        <thead>
            <tr>
                <th>Select</th>
                <!-- <th>Pallet Code</th>
                <th>Pallet Type</th>
                <th>Pallet Name</th> -->
                <th><?php echo _lang('pallet_code'); ?></th>
                <th><?php echo _lang('pallet_type'); ?></th>
                <th><?php echo _lang('pallet_name'); ?></th>
            </tr>
        </thead>
        <!-- <tbody></tbody>
        <tfoot></tfoot> -->
    </table>
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
</div>
   <form id="after_gen" action="<?php echo base_url(); ?>index.php/replenishment/openActionForm" method="POST"  target="_blank">
       <input  id="id" name="id" type="hidden" value="" id="id">
   </form>
<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>
<?php $this->load->view('element_modal_message_alert'); ?>
<?php $this->load->view('element_modal'); ?>
<script>
    $("#show_search_pallet").hide();
<?php if ($this->config->item('build_pallet')): ?>
            $('#chkPallet').click(function() {
            $("#show_search_pallet").toggle(this.checked);
            });
<?php endif; ?>


</script>

<script type="text/javascript">
$('#to_warehouse_select').select2({
    sorter: data => data.sort((a, b) => a.text.localeCompare(b.text)),
    width: '100%' 
});



</script>


