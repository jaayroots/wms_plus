<?php echo link_tag(base_url("css/themes/smoothness/custom_checkbox.css"));?>
<script language="JavaScript" type="text/javascript" src="<?php echo base_url("js/custom_checkbox.js") ?>"></script>
<script>

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Picking'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#ShowDataTableForInsert'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowPickingList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';

    var form_name = "frmPicking";

    // Read config from controller
    var built_pallet = '<?php echo ($conf_pallet) ? true : false; ?>';
    var conf_inv = '<?php echo ($conf_inv) ? true : false ?>';
    var conf_cont = '<?php echo ($conf_cont) ? true : false ?>';
    var statusprice = '<?php echo ($price_per_unit) ? true : false; ?>';


    var separator = "<?php echo SEPARATOR; ?>";

    var arr_index = new Array(
    'no'
    ,'product_code'
    ,'product_name'
    ,'product_status'
    ,'product_sub_status'
    ,'lot'
    ,'serial'
    ,'product_mfd'
    ,'product_exp'
    ,'invoice_no'
    ,'container'
    ,'reserve_qty'
    ,'confirm_qty'
    ,'unit'
    ,'price_per_unit'
    ,'unit_price'
    ,'all_price'
    ,'suggest_location'
    ,'actual_location'
    ,'picking_by'
    ,'pallet_code'
    ,'remark'
    ,'h_product_id'
    ,'h_product_status'
    ,'h_product_sub_status'
    ,'unit_Id'
    ,'item_Id'
    ,'inbound_id'
    ,'suggest_location_id'
    ,'actual_Location_id'
    ,'price_per_unit_id'
    );

    var form_name = "frmPicking";
    var ci_prod_code = arr_index.indexOf("product_code");
    var ci_lot = arr_index.indexOf("lot"); //5
    var ci_serial = arr_index.indexOf("serial"); //6
    var ci_mfd = arr_index.indexOf("product_mfd"); //7
    var ci_exp = arr_index.indexOf("product_exp"); //8
    var ci_invoice = arr_index.indexOf("invoice_no");
    var ci_container = arr_index.indexOf("container");
    var ci_reserv_qty = arr_index.indexOf("reserve_qty"); //9
    var ci_confirm_qty = arr_index.indexOf("confirm_qty"); //10

    var ci_pallet_id = arr_index.indexOf("pallet_code"); //18
    var ci_remark = arr_index.indexOf("remark"); //19
    //Define Hidden Field Datatable
    var ci_prod_id = arr_index.indexOf("h_product_id"); //20
    var ci_prod_status = arr_index.indexOf("h_product_status"); //21
    var ci_prod_sub_status = arr_index.indexOf("h_product_sub_status"); //22
    var ci_unit_id = arr_index.indexOf("unit_Id"); //23
    var ci_item_id = arr_index.indexOf("item_Id"); //24
    var ci_inbound_id = arr_index.indexOf("inbound_id"); //25
    var ci_suggest_loc = arr_index.indexOf("suggest_location_id"); //26
    var ci_actual_loc = arr_index.indexOf("actual_Location_id"); //27

    // add by kik : 2014-01-14
    var ci_price_per_unit = arr_index.indexOf("price_per_unit"); //12
    var ci_unit_price = arr_index.indexOf("unit_price"); //13
    var ci_all_price = arr_index.indexOf("all_price"); //14
    var ci_unit_price_id = arr_index.indexOf("price_per_unit_id"); //28
    //end add by kik : 2014-01-14

    var ci_list = [
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_invoice', value: ci_invoice}, // add by por for show invoice : 20140813
        {name: 'ci_container', value: ci_container}, // add by por for show invoice : 20140813
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_reserv_qty', value: ci_reserv_qty},
        {name: 'ci_price_per_unit', value: ci_price_per_unit},      // add by kik : 2014-01-14
        {name: 'ci_unit_price', value: ci_unit_price},              // add by kik : 2014-01-14
        {name: 'ci_all_price', value: ci_all_price},                // add by kik : 2014-01-14
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_prod_id', value: ci_prod_id},
        {name: 'ci_prod_status', value: ci_prod_status},
        {name: 'ci_unit_id', value: ci_unit_id},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_inbound_id', value: ci_inbound_id},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_actual_loc', value: ci_actual_loc},
        {name: 'ci_prod_sub_status', value: ci_prod_sub_status},
        {name: 'ci_unit_price_id', value: ci_unit_price_id}         // add by kik : 2014-01-14
    ]


        function show_hide_column(this_event){
            var count_colspan_before_reserve_qty = 0;
            var count_colspan_before_suggest_location = 0;
            var start_count_colspan_after_confirm_qty = false;
            this_event.parent().children('input').each(function(key, value){
                if($(this).attr('id')==='picking_job_reserve_qty'){
                    if(count_colspan_before_reserve_qty == 0){
                        $('#colspan_before_reserve_qty').hide();
                    }else{
                        $('#colspan_before_reserve_qty').show();
                        $('#colspan_before_reserve_qty').attr('colspan',count_colspan_before_reserve_qty);
                    }
                }else if($(this).attr('id')==='picking_job_suggest_location'){
                    start_count_colspan_after_confirm_qty = true;
                }

                if(start_count_colspan_after_confirm_qty){
                    if($(this).is(':checked')){
                        count_colspan_before_suggest_location = count_colspan_before_suggest_location+1;
                    }
                }

                if($(this).is(':checked')){
                    count_colspan_before_reserve_qty = count_colspan_before_reserve_qty+1;
                }
            });

            if(count_colspan_before_suggest_location == 0){
                $('#colspan_before_suggest_location').hide();
            }else{
                $('#colspan_before_suggest_location').show();
                $('#colspan_before_suggest_location').attr('colspan',count_colspan_before_suggest_location);
            }

            var tmp_column = this_event.attr('id').replace('picking_job','pkj');
            if(this_event.is(':checked')){
                $('.'+tmp_column).show();
            }else{
                $('.'+tmp_column).hide();
            }
        }


        function load_detail(set_order_by){

            $('#report').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');

            $.ajax({
                type: 'post',
                url: '<?php echo site_url(); ?>/picking/get_picking_detail', // in here you should put your query
                data: 'flow_id=<?php echo $flow_id; ?>&set_order_by='+set_order_by,
                success: function(data)
                {
                    //alert(data);
                    $("#report").hide();
                    $('#show_loader').html('<img src="<?php echo base_url() ?>/images/ajax-loader.gif" />');
                    $("#report").html(data);

                    //ADD BY POR 2013-11-05 กำหนดให้แสดงปุ่ม print report หลังจากได้ข้อมูลแล้ว
                    $("#pdfshow").show();
                    $("#excelshow").show();
                    //END ADD
                }
            });

        }

    $(document).ready(function() {

        $.validator.addMethod("australianDate",function(value, element) {
                return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
            },
            "Please enter a date in the format dd/mm/yyyy."
        );

        /* Export Function */
        $("#btn_export").click(function(){
			var export_type = $("#export_type").val();
			var export_document = $("#export_document").val();
			var export_order_by = $("input[name='order_by']:checked").val();
            var url = '<?php echo site_url("/report/export_picking_pdf/")?>?t=' + export_type + '&d=' + export_document + '&o=' + export_order_by + '&flow_id=<?php echo $flow_id; ?>';
			var new_Tab = window.open (url , '_blank');
			$("#exportFileModal").modal('hide');
		});
		/* End */

        $( "#div_picking_job" ).buttonset();
        // function for show/hide column
        $('#div_picking_job input').on("change", function(event){
            show_hide_column($(this));
        });



        // Add By Akkarapol, 19/12/2013, เพิ่ม $('#ShowDataTableForInsert tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย
        $('#ShowDataTableForInsert tbody tr td[title]').hover(function() {

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
        // END Add By Akkarapol, 19/12/2013, เพิ่ม $('#showProductTable tbody tr td[title]').hover เพราะ script ที่เรียกใช้ฟังก์ชั่น initProductTable ไม่ทำงาน อาจเนื่องมาจากว่า ไม่ได้ถูก bind จึงนำมาเรียกใช้ใน $(document).ready เลย


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


//        initProductTable();

        load_detail('<?php echo $picking_default_order_by; ?>');

        $('#select_sort').change(function(){
            var elm = $(this).find(":selected");
            load_detail(elm.val());
        });

        var nowTemp = new Date();
        var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);

        $("#estDispatchDate").datepicker().keypress(function(event) {
            event.preventDefault();
        }).on('changeDate', function(ev) {
            //$('#estDispatchDate').datepicker('hide');
        }).bind("cut copy paste", function(e) {
            e.preventDefault();
        });

        $.validator.addMethod("document", function(value, element) {
            return this.optional(element) || /^[a-zA-Z0-9._/\\,-]+$/i.test(value);
        }, "Document Format is invalid.");

        $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", true);
        $("[name='is_urgent']").attr("disabled", false);

        $('#div_picking_job').children('input').each(function(key, value){
            show_hide_column($(this));
        });
        
         $('#div_for_modal_message').on('hidden.bs.modal', function (e) {
            $('#renter_id_select').attr("disabled", true);
            $('#frm_warehouse_select').attr("disabled", true);
            $('#to_warehouse_select').attr("disabled", true);
            $('#document_no').attr("disabled", true);
            $('#doc_refer_ext').attr("disabled", true);
            $('#doc_refer_int').attr("disabled", true);
            $("[name='doc_refer_inv']").attr("disabled", true);
            $("[name='doc_refer_ce']").attr("disabled", true);
            $("[name='doc_refer_bl']").attr("disabled", true);
            $('#dispatch_type_select').attr("disabled", true);
            $('#estDispatchDate').attr("disabled", true);
            $('#remark').attr("disabled", true);
        });
        
        
    });


    function postRequestAction(module, sub_module, action_value, next_state, elm) {

	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        //#ISSUE 3034 Reject Document
        //#เพิ่มในส่วนของ reject and (reject and return)
        //#start if check Sub_Module : by kik : 2013-12-11

        //validate Engine called here.//
        if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction') {
            var statusisValidateForm = validateForm();
        } else {
            var statusisValidateForm = true;
        }

        //alert(statusisValidateForm);
        if (statusisValidateForm === true) {

            var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
            if (sub_module != 'rejectAndReturnAction' && sub_module != 'rejectAction') {
                var num_row = rowData.length;
                if (num_row <= 0) {
                    alert("Please Select Product Order Detail");
                    return false;
                }

                for (i in rowData) {
                    qty = rowData[i][ci_reserv_qty];
                    if (qty == "") {
                        alert('Please fill all Reserve Qty');
                        return false;
                    }
                }
                for (i in rowData) {
                    reserve_qty = parseInt(rowData[i][ci_reserv_qty]);
                    confirm_qty = parseInt(rowData[i][ci_confirm_qty]);
                    if (reserve_qty != confirm_qty && sub_module != "quickApproveAction") { // Add By pass if quick approve
                        alert('Please Confirm Qty Must be Equal Reserve Qty');
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
            }// end if check Sub_Module : by kik : 2013-12-11

                $("#" + form_name + " :input").not("[name=showProductTable_length]").attr("disabled", false);
                var f = document.getElementById("frmPicking");
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

                var oTable = $('#ShowDataTableForInsert').dataTable().fnGetData();
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

                global_data_form = $("#frmPicking").serialize();
                var message = "";

                        var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                        $('#div_for_alert_message').html(mess);
                        $('#div_for_modal_message').modal('show').css({
                            'margin-left': function() {
                                return ($(window).width() - $(this).width()) / 2;
                            }
                        });

        }else {
            alert("Please Check Your Require Information (Red label).");
            return false;
        }

    }
//
//    function initProductTable() {
//        console.log('x');
//        $('#ShowDataTableForInsert').dataTable({
//            "bJQueryUI": true,
//            "bAutoWidth": false,
//            "bSort": false,
//            "bStateSave": true,
//            "bRetrieve": true,
//            "bDestroy": true,
//            "bAutoWidth": false,
//            "iDisplayLength"    : 250,
//			//"sScrollX": "100%",
//			"sPaginationType": "full_numbers",
//            "sDom": '<"H"lfr>t<"F"ip>',
//            "aoColumnDefs": [
//                {"sWidth": "3%", "sClass": "center pkj_no", "aTargets": [0]},
//                {"sWidth": "5%", "sClass": "center pkj_product_code", "aTargets": [1]},
//                {"sWidth": "12%", "sClass": "left_text pkj_product_name", "aTargets": [2]},
//                {"sWidth": "6%", "sClass": "left_text obj_status pkj_product_status", "aTargets": [3]}, // Edit by Ton! 20131001
//                {"sWidth": "6%", "sClass": "left_text obj_sub_status pkj_product_sub_status", "aTargets": [4]}, //Edit by Ton! 20131001
//                {"sWidth": "6%", "sClass": "left_text pkj_lot", "aTargets": [5]},
//                {"sWidth": "6%", "sClass": "left_text pkj_serial", "aTargets": [6]},
//                {"sWidth": "6%", "sClass": "center obj_mfg pkj_mfd", "aTargets": [7]},
//                {"sWidth": "6%", "sClass": "center obj_exp pkj_exp", "aTargets": [8]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_reserve_qty", "aTargets": [9]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_confirm_qty", "aTargets": [10]},
//                {"sWidth": "5%", "sClass": "center pkj_unit", "aTargets": [11]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_price_per_unit", "aTargets": [12]},
//                {"sWidth": "5%", "sClass": "center pkj_unit_price", "aTargets": [13]},
//                {"sWidth": "5%", "sClass": "right_text set_number_format pkj_all_price", "aTargets": [14]},
//                {"sWidth": "7%", "sClass": "center pkj_suggest_location", "aTargets": [15]},
//                {"sWidth": "7%", "sClass": "center pkj_actual_location", "aTargets": [16]},
//                {"sWidth": "7%", "sClass": "center pkj_pick_by", "aTargets": [17]},
//                {"sWidth": "5%", "sClass": "center pkj_pallet_code", "aTargets": [18]},
//                {"sWidth": "5%", "sClass": "center pkj_remark", "aTargets": [19]},
//            ]
//        }).makeEditable({
//            "aoColumns": [
//                null,
//                null,
//                null,
//                null,
//                null,
//                null,
//                null,
//                null,
//                null,
//                null,
//                null   //add by kik : 2014-01-14
////                comment by kik : 2014-01-14
////                {
////                    sSortDataType: "dom-text",
////                    sType: "numeric",
////                    type: 'text',
////                    onblur: "submit",
////                    event: 'click',
////                    cssclass: "required number",
////                    sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
////                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
////                        calculate_qty();
////                    }
////                }
////               end comment by kik : 2014-01-14
//
//                , null
//                //                comment by kik : 2014-01-14
//                , {
//                    sSortDataType: "dom-text",
//                    sType: "numeric",
//                    type: 'text',
//                    onblur: "submit",
//                    event: 'click',
//                    cssclass: "required number",
//                    sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
//                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 25-10-2013
//                        calculate_qty();
//                    }
//                }
//                , null
////               end comment by kik : 2014-01-14
//                //ADD BY POR 2014-06-11 แสดงราคาต่อหน่วย, หน่วย, ราคารวม
//                {price}           //price
//                {unitofprice}     //unitprice
//                ,null           //allprice
//                //END ADD
//                , null
//                , null
//                , null
//                , null
////                , {
////                    onblur: 'submit',
////                    sUpdateURL: "<?php echo site_url() . '/pre_dispatch/saveEditedRecord'; ?>",
////                    event: 'click', // Add By Akkarapol, 14/10/2013, เซ็ตให้ event ในการแก้ไขข้อมูลช่อง Remark นี้ โดยการ คลิกเพียง คลิกเดียว
////                }
//                , null
//                , null
//                , null
//                , null
//                , null
//                , null
//                , null
//            ]
//        });
//
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_prod_id, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_prod_status, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_prod_sub_status, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_id, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_item_id, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_inbound_id, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_suggest_loc, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_actual_loc, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_price_id, false);
//        $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_container, false); //comment by por 2014-09-27 not use container this page use again in dispatch HH
//
//
//        if(!built_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
//            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_pallet_id, false);
//        }
//
//        //add by kik : 20140114
//        if(statusprice!=true){
//            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_price_per_unit, false);
//            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_unit_price, false);
//            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_all_price, false);
//        }
//        //end add by kik : 20140114
//
//        if(!conf_inv){
//            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_invoice, false);
//        }
//
//        if(!conf_cont){
//            $('#ShowDataTableForInsert').dataTable().fnSetColumnVis(ci_container, false);
//        }
//
//    }
//

    function removeItem(obj) {
        var index = $(obj).closest("table tr").index();
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        calculate_qty();
    }

    function deleteItem(obj) {
        var index = $(obj).closest("table tr").index();
        var data = $('#ShowDataTableForInsert').dataTable().fnGetData(index);
        $('#ShowDataTableForInsert').dataTable().fnDeleteRow(index);
        var f = document.getElementById("frmPicking");
        var prodDelItem = document.createElement("input");
        prodDelItem.setAttribute('type', "hidden");
        prodDelItem.setAttribute('name', "prod_del_list[]");
        prodDelItem.setAttribute('value', data);
        f.appendChild(prodDelItem);
        calculate_qty();
    }

    function cancel() {
        if (confirm("All Data in this page will be lost and redirect to other page. Do you want to cancle?")) {
            url = "<?php echo site_url(); ?>/flow/flowPickingList";
            redirect(url)
        }
    }

    function validateForm() {
        //validate engine
        var status;
        $("form").each(function() {//focusCleanup: true,
            $(this).validate();
            $(this).valid();
            status = $(this).valid();
        });
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

    // Add function calculate_qty : by kik : 28-10-2013
    function calculate_qty() {
        var rowData = $('#ShowDataTableForInsert').dataTable().fnGetData();
        var rowData2 = $('#ShowDataTableForInsert').dataTable();        //ADD BY POR 2014-06-11

        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-09 ราคาทั้งหมด
        for (i in rowData) {
            var tmp_qty = 0;
            var tmp_price = 0;//ราคาต่อหน่วย
            var all_price = 0; //ราค่าทั้งหมดต่อหนึ่งรายการ

            //+++++ADD BY POR 2013-11-29 แปลงตัวเลขให้อยู่ในรูปแบบคำนวณได้
            var str = rowData[i][ci_confirm_qty];
            rowData[i][ci_confirm_qty] = str.replace(/\,/g, '');

            tmp_qty = parseFloat(rowData[i][ci_confirm_qty]); //+++++ADD BY POR 2013-11-29 แก้ไขให้เป็น parseFloat เนื่องจาก qty เปลี่ยนเป็น float

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
            }

            //+++++ADD BY POR 2014-06-11 เพิ่มการคำนวณราคา
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

        $('#sum_cf_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-06-11 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-06-11 เพิ่มให้แสดงราคารวมทั้งหมด

    }
    // end function calculate_qty : by kik : 28-10-2013

    // Function Export
    function exportFile(type, document) {
		$('#exportFileModal').modal('show');
		$('#exportFileModal').on('shown.bs.modal', function (e) {
			$("#export_type").val(type);
			$("#export_document").val(document);
		})
	}
	/*
    function exportFile(file_type) {
            if (file_type == 'PDF') {
                $("#frmFlowId").attr('action', "<?php echo site_url(); ?>" + "/report/export_picking_pdf")
            }
            console.log($("#frmFlowId"));
            $("#frmFlowId").submit();
        }
*/
</script>
<style>
    #report{
        margin:5px;
        text-align:center;
    }
    /* START Custom position */
/*    .tooltip {
        left: 150px !important;
        width: 200px !important;
    }*/
    /* END Custom position*/
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
    <form class="" method="POST" action="" id="frmPicking" name="frmPicking" >
        <fieldset style="margin:0px auto;">
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
            <?php echo form_hidden('process_type', $process_type); ?>
            <?php echo form_hidden('owner_id', $owner_id); ?>
            <legend>Picking Order</legend>
            <table cellpadding="1" cellspacing="1" style="width:90%; margin:0px auto;" >
                <tr>
                    <td align="right">
                        Renter
                    </td>
                    <td align="left">
                        {renter_id_select}
                    </td>
                    <td align="right">
                        Shipper
                    </td>
                    <td align="left">
                        {frm_warehouse_select}
                    </td>
                    <td align="right">
                        Consignee
                    </td>
                    <td>
                        {to_warehouse_select}
                    </td>
                </tr>
                <tr>
                    <td align="right">
                        Document No.
                    </td>
                    <td align="left">
                        <input type="text" placeholder="AUTO GENERATE DDR" id="document_no" name="document_no" disabled value="<?php echo $DocNo; ?>" />
                    </td>
                    <td align="right">
                        Document External
                    </td>
                    <td align="left">
                        <input type="text" placeholder="<?php echo DOCUMENT_EXT; ?>" id="doc_refer_ext" name="doc_refer_ext"  value="<?php echo $doc_refer_ext; ?>"class="required document" style="text-transform: uppercase"/>
                    </td>
                    <td align="right">
                        Document Internal
                    </td>
                    <td align="left">
                        <input type="text" placeholder="<?php echo DOCUMENT_INT; ?>" class="document" id="doc_refer_int" name="doc_refer_int" value="<?php echo $doc_refer_int; ?>" style="text-transform: uppercase"/>
                    </td>
                </tr>
                <tr>
                    <td align="right">Invoice No.</td>
                    <td align="left"><?php echo form_input('doc_refer_inv', $doc_refer_inv, 'placeholder="' . DOCUMENT_INV . '"  class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">Customs Entry	</td>
                    <td align="left"><?php echo form_input('doc_refer_ce', $doc_refer_ce, 'placeholder="' . DOCUMENT_CE . '" class="document" style="text-transform: uppercase"'); ?></td>
                    <td align="right">BL No.</td>
                    <td align="left"><?php echo form_input('doc_refer_bl', $doc_refer_bl, 'placeholder="' . DOCUMENT_BL . '" class="document" style="text-transform: uppercase"'); ?></td>
                </tr>
                <tr>
                    <td align="right">
                        Dispatch Type
                    </td>
                    <td>
                        {dispatch_type_select}
                    </td>
                    <td align="right">
                        Est. Dispatch Date
                    </td>
                    <td align="left">
                        <input type="text" placeholder="Date Format" id="estDispatchDate" name="estDispatchDate" class="australianDate" value="<?php echo $est_action_date; ?>"/>
                    </td>
                    <td>
                        <!-- Dispatch Date -->
                    </td>
                    <td>
                       <!--  <input type="text" placeholder="Date Format" id="dispatchDate" name="dispatchDate" value="" disabled> -->
                    </td>
                </tr>
                <tr>
                    <td align="right">Remark</td>
                    <td align="left" colspan="3">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:90%" placeholder="Remark..."><?php echo $remark; ?></TEXTAREA>
                    </td>
                    <td align="right">
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                    </td>
                    <td align="left"></td>
                </tr>
            </table>
        </fieldset>
         <input type="hidden" name="token" value="<?php echo $token ?>" />
    </form>

    <form class="" method="POST" action="" id="frmFlowId" name="frmFlowId" target='_blank'>
        <?php if(@$this->settings['show_column_picking_job']):?>
            <fieldset>
            <legend>Show Column Report</legend>
                <div id='div_picking_job' style='text-align: center;font-size: 11px;margin-bottom: 5px;'>
                    <?php echo form_checkbox('picking_job[no]', TRUE, array_key_exists('no', $show_column_picking_job), 'id="picking_job_no"'); echo form_label('No.', 'picking_job_no'); ?>
                    <?php echo form_checkbox('picking_job[product_code]', TRUE, array_key_exists('product_code', $show_column_picking_job), 'id="picking_job_product_code"'); echo form_label('Code', 'picking_job_product_code'); ?>
                    <?php echo form_checkbox('picking_job[product_name]', TRUE, array_key_exists('product_name', $show_column_picking_job), 'id="picking_job_product_name"'); echo form_label('Name', 'picking_job_product_name'); ?>
                    <?php echo form_checkbox('picking_job[product_status]', TRUE, array_key_exists('product_status', $show_column_picking_job), 'id="picking_job_product_status"'); echo form_label('Status', 'picking_job_product_status'); ?>
                    <?php echo form_checkbox('picking_job[product_sub_status]', TRUE, array_key_exists('product_sub_status', $show_column_picking_job), 'id="picking_job_product_sub_status"'); echo form_label('Sub Status', 'picking_job_product_sub_status'); ?>
                    <?php echo form_checkbox('picking_job[lot]', TRUE, array_key_exists('lot', $show_column_picking_job), 'id="picking_job_lot"'); echo form_label('Lot', 'picking_job_lot'); ?>
                    <?php echo form_checkbox('picking_job[serial]', TRUE, array_key_exists('serial', $show_column_picking_job), 'id="picking_job_serial"'); echo form_label('Serial', 'picking_job_serial'); ?>
                    <?php echo form_checkbox('picking_job[mfd]', TRUE, array_key_exists('mfd', $show_column_picking_job), 'id="picking_job_mfd"'); echo form_label('Mfd', 'picking_job_mfd'); ?>
                    <?php echo form_checkbox('picking_job[exp]', TRUE, array_key_exists('exp', $show_column_picking_job), 'id="picking_job_exp"'); echo form_label('Exp', 'picking_job_exp'); ?>
                    <?php echo form_checkbox('picking_job[reserve_qty]', TRUE, array_key_exists('reserve_qty', $show_column_picking_job), 'id="picking_job_reserve_qty"'); echo form_label('Receive QTY', 'picking_job_reserve_qty'); ?>
                    <?php echo form_checkbox('picking_job[confirm_qty]', TRUE, array_key_exists('confirm_qty', $show_column_picking_job), 'id="picking_job_confirm_qty"'); echo form_label('Confirm QTY', 'picking_job_confirm_qty'); ?>
                    <?php echo form_checkbox('picking_job[unit]', TRUE, array_key_exists('unit', $show_column_picking_job), 'id="picking_job_unit"'); echo form_label('Unit', 'picking_job_unit'); ?>

                    <?php
                        if($price_per_unit):
                            echo form_checkbox('picking_job[price_per_unit]', TRUE, array_key_exists('price_per_unit', $show_column_picking_job), 'id="picking_job_price_per_unit"'); echo form_label('Price Per Unit', 'picking_job_price_per_unit');
                            echo form_checkbox('picking_job[unit_price]', TRUE, array_key_exists('unit_price', $show_column_picking_job), 'id="picking_job_unit_price"'); echo form_label('Unit Price', 'picking_job_unit_price');
                            echo form_checkbox('picking_job[all_price]', TRUE, array_key_exists('all_price', $show_column_picking_job), 'id="picking_job_all_price"'); echo form_label('All Price', 'picking_job_all_price');
                        endif;
                    ?>

                    <?php echo form_checkbox('picking_job[suggest_location]', TRUE, array_key_exists('suggest_location', $show_column_picking_job), 'id="picking_job_suggest_location"'); echo form_label('Suggest Location', 'picking_job_suggest_location'); ?>
                    <?php echo form_checkbox('picking_job[actual_location]', TRUE, array_key_exists('actual_location', $show_column_picking_job), 'id="picking_job_actual_location"'); echo form_label('Actual Location', 'picking_job_actual_location'); ?>

                    <?php
                        if($this->settings['build_pallet'] == TRUE):
                            echo form_checkbox('picking_job[pallet_code]', TRUE, array_key_exists('pallet_code', $show_column_picking_job), 'id="picking_job_pallet_code"'); echo form_label('Pallet Code', 'picking_job_pallet_code');
                        endif;
                    ?>

                    <?php echo form_checkbox('picking_job[pick_by]', TRUE, array_key_exists('pick_by', $show_column_picking_job), 'id="picking_job_pick_by"'); echo form_label('Pick By', 'picking_job_pick_by'); ?>
                    <?php echo form_checkbox('picking_job[remark]', TRUE, array_key_exists('remark', $show_column_picking_job), 'id="picking_job_remark"'); echo form_label('Remark', 'picking_job_remark'); ?>

                </div>
            </fieldset>
        <?php endif; ?>
        <?php echo form_hidden('flow_id', $flow_id); ?>
        <?php echo form_hidden('showfooter', 'show'); ?>
    </form>



    <fieldset>
        <legend>Product Detail</legend>
        <div style="margin: 5px 0 0 10px;;">
            <span>
                Sort by :
            </span>
            <select id="select_sort" style="width: 150px;" >
                <option value="location" <?php echo ($picking_default_order_by=='location'?'selected="selected"':'') ?>>Location</option>
                <?php if($this->config->item('build_pallet')): ?>
                    <option value="pallet" <?php echo ($picking_default_order_by=='pallet'?'selected="selected"':'') ?>>Pallet</option>
                <?php endif; ?>
                <option value="item" <?php echo ($picking_default_order_by=='item'?'selected="selected"':'') ?>>Item</option>
            </select>
        </div>
        <div id="report" style="margin:10px">
            <img src="<?php echo base_url() ?>/images/ajax-loader.gif" />
        </div>
        <div id="show_loader" style="text-align: center;margin:10px;">

        </div>
    </fieldset>
</div>

<!--call element Product Est. balance Detail modal : add by kik : 06-11-2013-->
<?php $this->load->view('element_showEstBalance'); ?>

<?php $this->load->view('element_modal_message_alert'); ?>

<!-- Modal Export File -->
<div style="min-height:200px;padding:5px 10px;" id="exportFileModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
		<h3 id="myModalLabel">Please select order type</h3>
	</div>
	<div class="modal-body">
		<!-- // Content will put here -->
		<div class="form-group">
			<div class="col-sm-offset-2 col-sm-10">
				<div class="radio">
					<label><input type="radio" name="order_by" value="location" checked/> Order By Location</label>
				</div>
                                <?php if($this->config->item('build_pallet')): ?>
                                    <div class="radio">
                                        <label><input type="radio" name="order_by" value="pallet" /> Order By Pallet</label>
                                    </div>
                                <?php endif; ?>
				<div class="radio">
					<label><input type="radio" name="order_by" value="item" /> Order By Item</label>
				</div>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<div>
			<input class="btn btn-primary" value="Export" type="submit" id="btn_export">
			<input type="hidden" id="export_type" value="" >
			<input type="hidden" id="export_document" value="" >
		</div>
	</div>
</div>
