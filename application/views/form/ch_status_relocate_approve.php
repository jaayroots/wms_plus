<!--#Defect ID : 339 #1, เช็ค Suggest Location กับ Actual Location ว่าถ้าไม่ตรงกัน ก็บังคับให้กรอก Remark ด้วย  -->

<!--<script language="JavaScript" type="text/javascript" src="<?php echo base_url() . "datatables/media/js/jquery.jeditable.datepicker.js" ?>"></script>-->
<script>
    var flag = false;
    var form_name = "action_form";
    var separator = "<?php echo SEPARATOR; ?>";
    var config_pallet = "<?php echo ($config_pallet == 1 ? true : false)?>"; // add by kik for ISSUE 3333 : 20140220

    var site_url = '<?php echo site_url(); ?>';
    var curent_flow_action = 'Change Product Status'; // เปลี่ยนให้เป็นตาม Flow ที่ทำอยู่เช่น Pre-Dispatch, Adjust Stock, Stock Transfer เป็นต้น
    var data_table_id_class = '#showProductTable'; // เปลี่ยนให้เป็นตาม id หรือ class ของ data table โดยถ้าเป็น id ก็ใส่ # เพิ่มไป หรือถ้าเป็น class ก็ใส่ . เพิ่มไปเหมือนการเรียกใช้ด้วย javascript ปกติ
    var redirect_after_save = site_url + "/flow/flowChangeStatusList"; // เปลี่ยนให้เป็นตาม url ของหน้าที่ต้องการจะให้ redirect ไป โดยปกติจะเป็นหน้า list ของ flow นั้นๆ
    var set_suggest = '<?php echo $set_suggest; ?>'; //ADD BY POR  2014-06-10 รับค่า setting suggest_locate
    var global_module = '';
    var global_sub_module = '';
    var global_action_value = '';
    var global_next_state = '';
    var global_data_form = '';

    var ci_prod_code = 1;
    var ci_status_val = 3;
    var ci_sub_status_val = 4;
    var ci_lot = 5;
    var ci_serial = 6;
    var ci_mfd = 7;
    var ci_exp = 8;
    var ci_balance_qty = 9;
    var ci_confirm_qty = 10;

    //Add By por 2014-01-09 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
    var ci_price_per_unit = 12;
    var ci_unit_price = 13;
    var ci_all_price = 14;
    var statusprice = '<?php echo $statusprice;?>';
    //END Add

    var ci_location_from = 15;
    var ci_suggest_name = 16;
    var ci_actual_name = 17;
    var ci_show_actual_location = 17;
    var ci_pallet_code = 18;            //add for ISSUE3334 by kik : 20140220
    var ci_remark = 19;
    var ci_prod_id = 20;
    var ci_prod_status = 21;
    var ci_prod_sub_status = 22;
    var ci_unit_id = 23;
    var ci_item_id = 24;
    var ci_suggest_loc = 25;
    var ci_actual_loc = 26;
    var ci_old_loc = 27;
    var ci_unit_price_id = 28;      //Edit by por 2014-01-16 เพิ่มคีย์ รหัส unit price
    var ci_dp_type_pallet = 29;     //add by kik for ISSUE 3334 : 2014-02-21

    var ci_list = [
        {name: 'ci_prod_id', value: ci_prod_id},
        {name: 'ci_prod_code', value: ci_prod_code},
        {name: 'ci_status_val', value: ci_status_val},
        {name: 'ci_sub_status_val', value: ci_sub_status_val},
        {name: 'ci_lot', value: ci_lot},
        {name: 'ci_serial', value: ci_serial},
        {name: 'ci_mfd', value: ci_mfd},
        {name: 'ci_exp', value: ci_exp},
        {name: 'ci_prod_status', value: ci_prod_status},
        {name: 'ci_item_id', value: ci_item_id},
        {name: 'ci_location_from', value: ci_location_from},
        {name: 'ci_suggest_loc', value: ci_suggest_loc},
        {name: 'ci_suggest_name', value: ci_suggest_name},
        {name: 'ci_actual_loc', value: ci_actual_loc},
        {name: 'ci_actual_name', value: ci_actual_name},
        {name: 'ci_unit_id', value: ci_unit_id},
        {name: 'ci_balance_qty', value: ci_balance_qty},
        {name: 'ci_confirm_qty', value: ci_confirm_qty},
        {name: 'ci_remark', value: ci_remark},
        {name: 'ci_old_loc', value: ci_old_loc},
        //Add By por 2014-01-15 เพิ่มตัวแปร ราคาต่อหน่วย, หน่วย, ราคารวม
        {name: 'ci_price_per_unit', value: ci_price_per_unit},
        {name: 'ci_unit_price', value: ci_unit_price},
        {name: 'ci_all_price', value: ci_all_price},
        {name: 'ci_unit_price_id', value: ci_unit_price_id},
        //END Add
        //add for ISSUE3334 by kik : 20140221
        {name: 'ci_pallet_code', value: ci_pallet_code} ,
        {name: 'ci_dp_type_pallet', value: ci_dp_type_pallet}
        //end add for ISSUE3334 by kik : 20140221
    ]


    var ci_prod_no = 0;
    var ci_prod_document = 1;
    var ci_prod_product_code = 2;
    var ci_prod_name = 3;
    //var ci_prod_status = 4;
    //var ci_prod_sub_status = 5;
    var ci_prod_rec_date = 6;
    var ci_prod_lot = 7;
    var ci_prod_sel = 8;
    var ci_prod_mfd = 9;
    var ci_prod_exp = 10;
    var ci_prod_pallet = 11;


    $(document).ready(function() {

        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="assigned_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        initProductTable();
        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark],[name=assigned_id]").attr("disabled", true);
        $("[name='is_urgent']").attr("disabled", false);
        //Define Dialog Model

    });

    //Define Dialog Model
    $('#myModal').modal('toggle').css({
        'width': function() {
            return ($(document).width() * .9) + 'px';   // make width 90% of screen
        },
        'margin-left': function() {
            return -($(this).width() / 2);				// center model
        }
    });


    function initProductTable() {
        $('#showProductTable').dataTable({
            "bJQueryUI": true,
            "bAutoWidth": false,
            "bSort": false,
            "bRetrieve": true,
            "bDestroy": true,
            "sPaginationType": "full_numbers",
            "sDom": '<"H"lfr>t<"F"ip>',
//            "aoColumnDefs": [
//                {"sClass": "td_actual_location", "aTargets": [14]}
//            ]
            "aoColumnDefs": [
                {"sWidth": "35", "sClass": "center", "aTargets": [0]},
                {"sWidth": "95", "sClass": "center", "aTargets": [1]},
                {"sWidth": "20%", "sClass": "left_text", "aTargets": [2]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [3]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [4]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [5]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [6]},
                {"sWidth": "5%", "sClass": "indent obj_mfg", "aTargets": [7]},
                {"sWidth": "5%", "sClass": "center obj_exp", "aTargets": [8]},
                {"sWidth": "3%", "sClass": "right_text set_number_format", "aTargets": [9]},
                {"sWidth": "3%", "sClass": "right_text set_number_format", "aTargets": [10]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [11]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [12]},
                {"sWidth": "5%", "sClass": "center", "aTargets": [13]},
                {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [14]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [15]},
                {"sWidth": "7%", "sClass": "center", "aTargets": [16]},
                {"sWidth": "7%", "sClass": "td_actual_location center", "aTargets": [17]},
                {"sWidth": "5%", "sClass": "left_text", "aTargets": [18]},
            ]
        }).makeEditable({
            sUpdateURL: function(value, settings) {
                return value;
            },
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
                        /*   COMMENT BY POR 2014-06-19 เนื่องจากระบบกำหนดให้ระบุจำนวนเท่ากันอยู่แล้ว ดังนั้นจึงปิดไว้เลย ไม่ต้องคีย์
                        , {
                    sSortDataType: "dom-text",
                    sType: "numeric",
                    type: 'text',
                    onblur: "submit",
                    event: 'click',
                    cssclass: "required number",
                    fnOnCellUpdated: function(sStatus, sValue, settings) {   // add fnOnCellUpdated for update total qty : by kik : 04-11-2013
                        calculate_qty();
                    }
                }
                */
                , null
                , null
                , null
                , null
                        , null
                        , null
//                        , {
//                    loadurl: '<?php echo site_url() . "/pending/getSuggestLocRule"; ?>',
//                    loaddata: function(value, settings) {
//                        // AJAX Post Data
//                        var oTable = $('#showProductTable').dataTable( );
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        var aData = oTable.fnGetData(rowIndex);
//                        var dataSet = {
//                            receive_type: $("#receive_type").val(),
//                            product_code: aData[ci_prod_code],
//                            product_status: aData[ci_prod_status],
//                            lot: aData[ci_lot],
//                            serial: aData[ci_serial],
//                            prod_mfd: "",
//                            prod_exp: ""
//                        };
//                        return dataSet;
//                    },
//                    loadtype: "POST",
//                    type: 'select',
//                    onblur: 'submit',
//                    sUpdateURL: function(value, settings) {
//                        console.log(settings);
//                        var oTable = $('#showProductTable').dataTable( );
//                        var rowIndex = oTable.fnGetPosition($(this).closest('tr')[0]);
//                        oTable.fnUpdate(value, rowIndex, ci_actual_loc);
//                        sNewCellDisplayValue = sNewCellDisplayValue + '<a ONCLICK="showProdInLoc(\'' + value + '\',\'' + sNewCellDisplayValue + '\')"><?php echo img("css/images/icons/view.png"); ?></a>';
//                        sNewCellValue = sNewCellDisplayValue;
//                        return value;
//                    }
//                }
//


                        // Add By Akkarapol, 01/10/2013, เปลี่ยน ActualLocation จาก DropdownList ไปเป็น Textbox
                        /*, {
                         //                    sSortDataType: "dom-text",
                         type: 'text',
                         onblur: "submit",
                         event: 'click'
                         }*/
                        , null
                        // END Add By Akkarapol, 01/10/2013, เปลี่ยน ActualLocation จาก DropdownList ไปเป็น Textbox

                        , null
                        , {
                    onblur: 'submit',
                    type: 'text',
                    event: 'click' // Add By Akkarapol, 30/10/2013, เพิ่มเพื่อให้เกิด event ในการคลิกเพียงคลิกเดียว
                }
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
        $('#showProductTable').dataTable().fnSetColumnVis(ci_old_loc, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price_id, false);
        $('#showProductTable').dataTable().fnSetColumnVis(ci_dp_type_pallet, false); //add by kik : 20140221

        if(statusprice!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_price_per_unit, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_unit_price, false);
            $('#showProductTable').dataTable().fnSetColumnVis(ci_all_price, false);
        }

        //add by kik : 20140221
        if(config_pallet!=true){
            $('#showProductTable').dataTable().fnSetColumnVis(ci_pallet_code, false);
        }
        //end add by kik : 20140221

        $('#showProductTable tbody tr td[title]').hover(function() {

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

    }

    function postRequestAction(module, sub_module, action_value, next_state, elm) {

    	global_module = module;
	global_sub_module = sub_module;
	global_action_value = action_value;
	global_next_state = next_state;
	curent_flow_action = $(elm).data('dialog');

        var statusisValidateForm = validateForm();

        if (statusisValidateForm === true) {
//================================= Check Validation Form ==================================

// Add BY Akkarapol, 12/12/2013, เพิ่มการวน loop หาตัว input ที่เป็น class 'acturl_location' แล้วทำการส่งค่า ajax ไปหา location_id และ location_code เพื่อนำไปทำการ DataTable.fnUpdate ให้เซ็ตค่าเข้าไปแล้วทำงานในขั้นตอนต่อไป
            $('.actual_location').each(function() {
                var input = $(this).val();
                var parent_selector = $(this).parent('td');
                $.ajax({
                    type: 'POST',
                    url: "<?php echo site_url('/location/autoCompleteActualLocation'); ?>",
                    data:{criteria: input},
                    success: function(data) {
                        data = data['0'];
                        var oTable = $('#showProductTable').dataTable(); // get datatables
                        var indexOfRow = oTable.fnGetPosition($(parent_selector).closest('tr').get(0)); // find rows index
                        oTable.fnUpdate(data.location_code, indexOfRow, ci_show_actual_location); // update new data to datatables
                        oTable.fnUpdate(data.location_id, indexOfRow, ci_actual_loc); // update new data to datatables
                    },
                    dataType: 'json',
                    async: false
                });
            });
// END Add BY Akkarapol, 12/12/2013, เพิ่มการวน loop หาตัว input ที่เป็น class 'acturl_location' แล้วทำการส่งค่า ajax ไปหา location_id และ location_code เพื่อนำไปทำการ DataTable.fnUpdate ให้เซ็ตค่าเข้าไปแล้วทำงานในขั้นตอนต่อไป

            var rowData = $('#showProductTable').dataTable().fnGetData();
            var num_row = rowData.length;
            if (num_row <= 0) {
                alert("Please Select Product Order Detail");
                return false;
            }

            for (i in rowData) {
                var balance_qty = parseFloat(rowData[i][ci_balance_qty]);
                var confirm_qty = parseFloat(rowData[i][ci_confirm_qty]);
                if (balance_qty != confirm_qty) {
                    alert('Balance Qty Must be Equal Confirm Qty');
                    return false;
                }

                var chk_suggest_loc = parseFloat(rowData[i][ci_suggest_loc]);
                var chk_actual_loc = rowData[i][ci_actual_loc];
                var chk_show_actual_location = rowData[i][ci_show_actual_location];

                var chk_remark = rowData[i][ci_remark];
                if (chk_actual_loc != "" || chk_show_actual_location != "") {
                    if(set_suggest==true){
                        
                        if (chk_actual_loc == 0) {
                            alert("Please select destination location.");
                            return false;
                        }
                        
                        if (chk_suggest_loc != chk_actual_loc) {
                            if (chk_remark == "") {
                                alert('Please Check Your Information Remark');
                                var oTable = $('#showProductTable').dataTable(); // get datatables
                                var cTable = oTable.fnGetNodes();
                                $(cTable[i]).find("td:eq("+ci_remark+")").html('');
                                var input = $('<input>').attr('class', 'txt_remark required').appendTo($(cTable[i]).find("td:eq("+ci_remark+")"));
                                return false;
                            }
                            
                        }
                    }
                } else {
                    alert('Please Select Actual Location');
                    return false;
                }
            }


//------------------------ End check validate Form ---------------------------------

//============================ Start data_form ====================================
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

                $('.class_prod_list').remove();  // Add By Akkarapol, 04/11/2013, เพิ่มโค๊ดในการลบ prodItem ทั้งหมดที่เคยได้ append เข้าไปใน form เพราะไม่งั้นมันจะเกิดอาการ duplicate ซึ่งส่งผลเสียให้กับระบบแน่ๆ เลยทำการ ลบก่อน เผื่อเกิดเหตุการณ์ที่ไม่คาดฝันเกิดขึ้น เช่น ajax รันไปไม่ทำงาน หรือจะ return กลับมาแล้วไม่ได้ redirect แล้วดันไปกดปุ่ม submit อีกครั้ง อะไรแบบนี้ จึงทำขึ้นมาป้องกันก่อนเป็นดีที่สุด

                var oTable = $('#showProductTable').dataTable().fnGetData();
                for (i in oTable) {
                    var prod_data = oTable[i].join(separator);
                    var prodItem = document.createElement("input");
                    prodItem.setAttribute('type', "hidden");
                    prodItem.setAttribute('name', "prod_list[]");
                    prodItem.setAttribute('class', "class_prod_list"); // Add By Akkarapol, 04/11/2013, เพิ่ม class ที่ชื่อ 'class_prod_list' ให้กับ prodItem ที่จะถูก append เพื่อที่ว่า จะได้เรียกถึงตัวของ prodItem นี้ได้ในภายหลัง
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

                global_data_form  = $("#" + form_name).serialize();
//---------------------------------- End data_form ---------------------------------

//======================== Start check validate Data ===============================

            var mess = '<div id="confirm_text"> Are you sure to do following action : ' + curent_flow_action + '?</div>';
                $('#div_for_alert_message').html(mess);
                $('#div_for_modal_message').modal('show').css({
                    'margin-left': function() {
                            return ($(window).width() - $(this).width()) / 2;
                        }
            });

//            if (confirm("Are you sure to action " + action_value + "?")) {
//
//                var message = "";
//                var step_name = "Putaway";
//                $.post("<?php echo site_url(); ?>" + "/" + module + "/" + sub_module, data_form, function(data) {
//                    switch (data.status) {
//                        case 'C001':
//                            message = "Save " + step_name + " Complete";
//                            break;
//                        case 'C002':
//                            message = "Confirm " + step_name + " Complete";
//                            break;
//                        case 'C003':
//                            message = "Approve " + step_name + " Complete";
//                            break;
//                        case 'E001':
//                            message = "Save " + step_name + " Incomplete";
//                            break;
//                    }
//                    alert(message);
//                    url = "<?php echo site_url(); ?>/flow/flowChangeStatusList";
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
            url = "<?php echo site_url(); ?>/flow/flowChangeStatusList";
            redirect(url);
        }
    }

    function validateForm() {
        $("form").validate({
            rules: {
                assigned_id: {required: true}
            }
        });
        return $("form").valid();
    }

    function showProdInLoc(location_id, location_code) {
        var dataSet = {location_id: location_id, location_code: location_code}
        $.post('<?php echo site_url() . "/reLocation/showProductInLocation" ?>', dataSet, function(data) {
            $('#boxDetail #myModalLabel').html(location_code + ' Product Detail ');
            $("#boxDetail .modal-body").html(data);
            $('#defDataTable').dataTable({
                "bJQueryUI": true,
                "bAutoWidth": false,
                "bSort": false,
                "bRetrieve": true,
                "bDestroy": true,
                "sPaginationType": "full_numbers",
                "aoColumnDefs": [
                    {"sWidth": "35", "sClass": "center", "aTargets": [0]},
                    {"sWidth": "95", "sClass": "center", "aTargets": [1]},
                    {"sWidth": "", "sClass": "center", "aTargets": [2]},
                    {"sWidth": "20%", "sClass": "left_text", "aTargets": [3]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [4]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [5]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [6]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [7]},
                    {"sWidth": "7%", "sClass": "left_text", "aTargets": [8]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [9]},
                    {"sWidth": "7%", "sClass": "center", "aTargets": [10]},
                    {"sWidth": "5%", "sClass": "right_text set_number_format", "aTargets": [11]}
                ]
            });

             if(!config_pallet){ // check config built_pallet if It's false then hide a column Pallet Code
                $('#defDataTable').dataTable().fnSetColumnVis(ci_prod_pallet, false);
             }

        }, "html");
        $('#boxDetail').modal('show');
    }

//// Add By Akkarapol, 08/10/2013, เพิ่ม script สำหรับใช้ autoComplete ในส่วนของ Actual Location
//    $(document).ready(function() {
//    function bindDataTD() {
//            $('.actual_loc_class').click(function() {
////        console.log('x');
//                var input = $('<input>').attr('class', 'suggest_location').appendTo($(this));
//            });
//    }
//
//        actualLocation();
//
//        function selectBind(elm) {
//
//            $('.sel_actual_location').click(function() {
//                var location_id = $(this).data('location_id');
//                $(elm).text($(this).data('location_code'));
//                var datatables = $('#showProductTable').dataTable();
//                var aPos = datatables.fnGetPosition(elm);
//                var rowData = datatables.fnGetData();
//                rowData[aPos[0]][ci_actual_loc] = location_id;
//                actualLocation();
////                        console.log(rowData[aPos[0]][ci_actual_loc]);
//            });
//        }
//        function actualLocation()
//        {
//            $('.actual_loc_class').on('keyup focus', function(e)
//            {
//                var _this = this;
//                var tr_data_no = $(this).parent('tr').children('td')[0].innerHTML;
//
//                $(this).append('<div class="suggestionsBox" id="suggestions-' + tr_data_no + '" uu="' + tr_data_no + '" style="display:none;"> <div class="suggestionList" id="autoSuggestionsList"> &nbsp; </div> </div>');
//                var value = $(this).find('form input').val();
//                var minChar = 1;
//                if (value.length >= minChar) {
//                    if (value != '') {
//                        $.post("<?php echo site_url(); ?>/repackage/autoCompleteActualLocation", {tr_data_no: tr_data_no, text_search: value}, function(val, data) {
//                            if (data.length > 0) {
//                                var response = $.parseJSON(val);
//                                $('#suggestions-' + tr_data_no).show();
//                                $('.suggestionList').html(' '); // Add By Akkarapol, 08/10/2013, เคลียร์ค่าของ DIV $('.suggestionList') ให้เป็นค่าว่าง เพื่อรอรับการ append ต่อไป
//                                //$('#autoSuggestionsList').html(val, data);
//                                $.each(response, function(idx, value) {
//                                    var li = $('<li>').attr('class', 'sel_actual_location').data('location_id', value.location_id).data('location_code', value.location_code).html(value.location_code);
//                                    $('#autoSuggestionsList').append(li);
//                                });
//                                selectBind(_this);
//                            }
//                        });
//                    }
//                }
//
//
//            });
//        }
//
//    });
//
//
//    function hideSuggestions() {
//        setTimeout("$('#suggestions').hide();", 100);
//    }
//    // END Add By Akkarapol, 08/10/2013, เพิ่ม script สำหรับใช้ autoComplete ในส่วนของ Actual Location






    $(document).ready(function() {
//alert('lol');
        function bindDataTD() {
            $('.td_actual_location').click(function() {
//        console.log('x');
//                var input = $('<input>').attr('class', 'actual_location').appendTo($(this));
//                var input = $('<input>').attr('width', '100').appendTo($(this));
//                $('.actual_location').css('width','30px');
            });
        }

// Suggestion
        suggestLocation();

// Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อตอน click ที่ list ของ location จาก div ที่ได้เตรียมไว้ ว่าให้นำค่าเข้าไปที่ td และอัพเดทเข้าที่ dataTable ด้วย พร้อมทั้ง hide div ตัวนั้นและซ่อน input ออกไปพร้อมกับเซ็ท label ของ td ให้เป็น location Code
        function suggesLocationClick(selector, parent_selector)
        {
            $('.actual_location_id').on('click', function(e)
            {
//                console.log($(this).data('id'));
                flag = false;
                var elm = $(selector);
                $(selector).attr('value', $(this).text()).removeClass('required');
                // START Create Temp [draft version]
//				var input = '<input class="'+elm.attr('class')+'" placeholder="'+elm.attr('placeholder')+'" value="'+$(this).text()+'" data-status="'+elm.data('status')+'" data-sub_status="'+elm.data('sub_status')+'" data-category="'+elm.data('category')+'">';
                var input = $(this).text();
                var oTable = $('#showProductTable').dataTable(); // get datatables
                var indexOfRow = oTable.fnGetPosition($(parent_selector).closest('tr').get(0)); // find rows index
                oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); // update new data to datatables
                oTable.fnUpdate($(this).data('id'), indexOfRow, ci_actual_loc); // update new data to datatables
                suggestLocation(); // bind event again
                // END
                setTimeout(function() {
                    $('#suggestion_div').hide();
                }, 200);
            });
        }
        // END Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อตอน click ที่ list ของ location จาก div ที่ได้เตรียมไว้ ว่าให้นำค่าเข้าไปที่ td และอัพเดทเข้าที่ dataTable ด้วย พร้อมทั้ง hide div ตัวนั้นและซ่อน input ออกไปพร้อมกับเซ็ท label ของ td ให้เป็น location Code

        // Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อ keyup หรือ focus ที่ textbox ให้ทำการ get ค่าที่เซ็ทไว้ใน td ตัวแม่ มาเข้า ajax เพื่อคืนค่า location ที่ถูกต้อง พร้อมทั้งนำค่า return นั้นมา append list ของ location เพื่อให้เป็น autocomplete ต่อไป
        function suggestLocation()
        {
            $('.actual_location').on('keyup focus', function(e)
            {
//                console.log($(this));
                var _this = $(this);
                var _parent = $(this).parent('td');
                $('#suggestion_div').css({top: ($(this).offset().top + 30), left: $(this).offset().left}).show();
                var data = $(this).parent('td').data();
                $.post("<?php echo site_url('/reLocation/autoCompleteActualLocation'); ?>", {criteria: $(this).val()}, function(response)
                {
                    $('#suggestionLocationList').html(''); // clear old list
//                    console.log(response);
                    $.each(response, function(index, value)
                    {
//                        console.log(value);
                        var lists = $('<li>').attr('class', 'actual_location_id').attr('data-id', value.location_id).html(value.location_code);

                        $('#suggestionLocationList').append(lists);
                    });
                    suggesLocationClick(_this, _parent);
                });
//                $('.actual_location').focus();
            }).blur(function() {
                var _this = this;
                var _parent = $(this).parent('td');

                setTimeout(function() {
                    //ADD BY POR 2013-12-16 เพิ่มให้ตรวจสอบ location ว่ามีจริงหรือไม่ (ล้อมาจากเมนู Relocation(locoation))
                    $.get("<?php echo site_url(); ?>" + "/location/check_exist_location", {location_code: $(_this).val()}, function(data)
                    {
                        if (data == "null")
                        {
//                                alert('Sorry!!, This actual location not found, Please select another location.');

                            flag = false;
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input = $(_this).val();
                            var oTable = $('#showProductTable').dataTable(); // get datatables
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); // find rows index
                            oTable.fnUpdate('', indexOfRow, ci_show_actual_location); // update new data to datatables
                            oTable.fnUpdate('', indexOfRow, ci_actual_loc); // update new data to datatables

                            var datas = $('#showProductTable').dataTable().fnGetData(indexOfRow);
                            if(datas[ci_dp_type_pallet] == "FULL"){
                                update_pallet_actualLoc(indexOfRow,input,data,datas[ci_pallet_code]);
                            }

                            $(_this).val('');
                        }else{
                            //ADD BY POR 2013-12-16 เพิ่มให้ update ค่าใน datatable ด้วยกรณีมีการคีย์ข้อมูล
                            flag = false;
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input = $(_this).val();
                            var oTable = $('#showProductTable').dataTable(); // get datatables
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); // find rows index
                            oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); // update new data to datatables
                            oTable.fnUpdate(data, indexOfRow, ci_actual_loc); // update new data to datatables
                            //END ADD

                            //start add for ISSUE 3334 : by kik : 20140221
                            var datas = $('#showProductTable').dataTable().fnGetData(indexOfRow);
                            if(datas[ci_dp_type_pallet] == "FULL"){
                                update_pallet_actualLoc(indexOfRow,input,data,datas[ci_pallet_code]);
                            }
                            //end add for ISSUE 3334 : by kik : 20140221
                        }
                    });
                    if ($(_this).val() != "") {
                        
                        $.get("<?php echo site_url(); ?>" + "/product_status/get_location", {location_code: $(_this).val()}, function(result)
                        {

                            console.log(result);   
                        //    return;
                            if (result != "null")  
                            {
                            alert('Sorry!!, Can not choose this location, Please select another location.'); 
                    
                            $(_this).attr('value', $(_this).val()).removeClass('required');
                            var input ="";
                            var oTable = $('#showProductTable').dataTable();
                            var indexOfRow = oTable.fnGetPosition($(_parent).closest('tr').get(0)); 
                            oTable.fnUpdate(input, indexOfRow, ci_show_actual_location); 
                            oTable.fnUpdate('', indexOfRow, ci_actual_loc); 
                            } 
                            else {
                                $(_this).data('', $(_this).val());
                            }
                          });
                    }
                    //END ADD
                    $('#suggestion_div').hide();
                }, 200);
            });
        }
        // END Add By Akkarapol, 08/10/2013, เพิ่ม script เมื่อ keyup หรือ focus ที่ textbox ให้ทำการ get ค่าที่เซ็ทไว้ใน td ตัวแม่ มาเข้า ajax เพื่อคืนค่า location ที่ถูกต้อง พร้อมทั้งนำค่า return นั้นมา append list ของ location เพื่อให้เป็น autocomplete ต่อไป
// END

        // Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง
        $('.required').each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            }
        });
        // END Add By Akkarapol, 23/09/2013, เช็คว่า ถ้าเป็น Class required แล้วมีข้อมูลในช่องแล้ว ก็ให้ถอด Class ออก เพื่อจะได้ไม่มีขอบแดง

        // Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง
        $('[name="assigned_id"]').change(function() {
            if ($(this).val() != '') {
                $(this).removeClass('required');
            } else {
                $(this).addClass('required');

            }
        });
        // END Add By Akkarapol, 21/09/2013, เพิ่ม onClick ของช่อง Worker Name ว่าถ้ามีข้อมูลให้เอากรอบแดงออก แต่ถ้าช่องมันว่าง ให้ใส่ขอบแดง

        initProductTable();
        // Add By Akkarapol, 08/10/2013, เพิ่ม script ที่จะทำงานตอน คลิกที่ td ที่อยู่ใน column ของ Suggest Location โดยจะ append textbox ขึ้นมาเพื่อใช้สำหรับรับค่าเข้าฟังก์ชั่น autoComplete ต่อไป

        $('td.td_actual_location').click(function() {

            if (!flag)
            {
                if ($(this).text().length == 0)
                {
                    var input = $('<input>').attr('class', 'actual_location').appendTo($(this));
                    //$(this).html(input);
                    suggestLocation();
                    input.focus();
                }
                else
                {
                    var temp = $(this).text();
                    $(this).html('');
                    var input = $('<input>').attr({'class': 'actual_location', 'data-val': temp}).prop('value', temp).appendTo($(this));
                    suggestLocation();
//                    input.attr('data-val',temp);
                    input.focus();
                }
                flag = true;

            }

        });
        // END Add By Akkarapol, 08/10/2013, เพิ่ม script ที่จะทำงานตอน คลิกที่ td ที่อยู่ใน column ของ Suggest Location โดยจะ append textbox ขึ้นมาเพื่อใช้สำหรับรับค่าเข้าฟังก์ชั่น autoComplete ต่อไป
        $("#" + form_name + " :input").not("[name=showProductTable_length],[name=remark],[name=assigned_id] ").attr("disabled", true);
        $("[name='is_urgent']").attr("disabled", false);
        //Define Dialog Model

    });

    //Define Dialog Model
    $('#myModal').modal('toggle').css({
        'width': function() {
            return ($(document).width() * .9) + 'px';   // make width 90% of screen
        },
        'margin-left': function() {
            return -($(this).width() / 2);				// center model
        }
    });

    //add for ISSUE 3334 : by kik : 20140221
    function update_pallet_actualLoc(row_index,txt_value,value,pallet_code){

        var oTable = $('#showProductTable').dataTable();
        var rowData = $('#showProductTable').dataTable().fnGetData();

        for (var i=0; i < rowData.length ; i++){
            if(pallet_code == rowData[i][ci_pallet_code] && row_index!=i){

                oTable.fnUpdate(value, i, ci_actual_loc);
                oTable.fnUpdate(txt_value, i, ci_show_actual_location);
            }
        }

    }
    //end add by kik : 20140221

    // Add function calculate_qty : by kik : 28-10-2013
    function calculate_qty() {

        var rowData = $('#showProductTable').dataTable().fnGetData();
        var rowData2 = $('#showProductTable').dataTable();
        var num_row = rowData.length;
        var sum_cf_qty = 0;
        var sum_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมดต่อหน่วย
        var sum_all_price = 0; //ADD BY POR 2014-01-16 ราคาทั้งหมด
        for (i in rowData) {
            var tmp_qty = 0;
            tmp_qty = parseFloat(rowData[i][ci_confirm_qty].replace(/\,/g, '')); //EDIT BY POR 2013-12-03 แก้ไขให้นำ comma ออก และแปลงเป็น parseFloat

            if (!($.isNumeric(tmp_qty))) {
                tmp_qty = 0;
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
            all_price = tmp_price * tmp_qty;

            sum_price = sum_price + tmp_price; //รวมราคาทุกรายการต่อหน่วย
            sum_all_price = sum_all_price + all_price; //รวมราคาทั้งหมด

            rowData2.fnUpdate(set_number_format(all_price), parseInt(i), ci_all_price); //update ราคารวมทั้งหมดใน datatable
            //END ADD

            sum_cf_qty = sum_cf_qty + tmp_qty;
        }
        $('#sum_recieve_qty').html(set_number_format(sum_cf_qty));
        $('#sum_price_unit').html(set_number_format(sum_price)); //ADD BY POR 2014-01-16 เพิ่มให้แสดงราคารวมทั้งหมดต่อหน่วย
        $('#sum_all_price').html(set_number_format(sum_all_price)); //ADD BY POR 2014-01-16 เพิ่มให้แสดงราคารวมทั้งหมด

    }
    // end function calculate_qty : by kik : 28-10-2013



    function exportFile(file_type) {
        if (file_type == 'PDF') {
            var backupForm = document.getElementById('form_flow_id').innerHTML;
            var f = document.getElementById('form_flow_id');

            var oTable = $('#showProductTable').dataTable().fnGetData();

            var actual_location = $(".actual_location");

            $("input[name='prod_list[]']").remove();

            for (i in oTable) {

                if($(actual_location[i]).val()!==undefined){
                    oTable[i][ci_act_loc_name] = $(actual_location[i]).val();
                }
                var prod_data = oTable[i].join(separator);
                var prodItem = document.createElement("input");
                prodItem.setAttribute('type', "hidden");
                prodItem.setAttribute('name', "prod_list[]");
                prodItem.setAttribute('value', prod_data);
                f.appendChild(prodItem);

            }

            //Est. Action Date
            var est_relocate_date = document.createElement("input");
            est_relocate_date.setAttribute('type', "hidden");
            est_relocate_date.setAttribute('name', "est_action_date");
            est_relocate_date.setAttribute('value', $('#est_action_date').val());
            f.appendChild(est_relocate_date);

            //Action Date
            var relocate_date = document.createElement("input");
            relocate_date.setAttribute('type', "hidden");
            relocate_date.setAttribute('name', "action_date");
            relocate_date.setAttribute('value', $('#action_date').val());
            f.appendChild(relocate_date);

            //Document No.
            var relocation_no = document.createElement("input");
            relocation_no.setAttribute('type', "hidden");
            relocation_no.setAttribute('name', "document_no");
            relocation_no.setAttribute('value', $('#document_no').val());
            f.appendChild(relocation_no);

            //Worker Name
            var worker_id = document.createElement("input");
            worker_id.setAttribute('type', "hidden");
            worker_id.setAttribute('name', "worker_name");
            worker_id.setAttribute('value', $('[name="assigned_id"] option:selected').text());
            f.appendChild(worker_id);

            //key
            $.each(ci_list, function(i, obj) {
                var ci_item = document.createElement("input");
                ci_item.setAttribute('type', "hidden");
                ci_item.setAttribute('name', obj.name);
                ci_item.setAttribute('value', obj.value);
                f.appendChild(ci_item);
            });

            $("#form_flow_id").attr('action', "<?php echo site_url(); ?>" + "/report/export_change_product_status_pdf")
        }

        console.log($("#form_flow_id"));
        $("#form_flow_id").submit();
    }

</script>
<style>
    /*    #myModal {
            width: 1024px;	 SET THE WIDTH OF THE MODAL
            margin: -250px 0 0 -450px;  CHANGE MARGINS TO ACCOMODATE THE NEW WIDTH (original = margin: -250px 0 0 -280px;)
        }*/


/*    #myModal {
        width: 1170px;	 SET THE WIDTH OF THE MODAL
        margin: -250px 0 0 -600px;
    } */

    /*Add by Akkarapol, 01/10/2013, เธ—เธณ autocomplete เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� Product Code เน�เธฅเธฐเน€เธกเธทเน�เธญเธ�เธฅเธดเธ�เธ—เธตเน� list product เธ”เธฑเธ�เธ�เธฅเน�เธฒเธง เธ�เน�เธ�เธฐเน�เธซเน� auto input to datatable*/

    #myModal,
    #boxDetail{
        width: 85%!important;	/* SET THE WIDTH OF THE MODAL */
        top:43%!important;
        margin-left: -42.5%!important;

    }

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
    /* END Add by Akkarapol, 01/10/2013, เธ—เธณ autocomplete เน�เธ�เธชเน�เธงเธ�เธ�เธญเธ� Product Code เน�เธฅเธฐเน€เธกเธทเน�เธญเธ�เธฅเธดเธ�เธ—เธตเน� list product เธ”เธฑเธ�เธ�เธฅเน�เธฒเธง เธ�เน�เธ�เธฐเน�เธซเน� auto input to datatable*/

    #suggestion_div {
        width: 220px;
        height: 170px;
        display: none;
        z-index: 2000;
        position: absolute;
        background-color: #FFF;
        overflow-y:hidden;
    }

/*    .tooltip {
        left: 140px !important;
        width: 200px !important;
    }*/

</style>
<div class="well">
    <form id="action_form" method=post action="" >
        <?php
        if (!isset($owner_id)) {
            $owner_id = "";
        }
        if (!isset($est_action_date)) {
            $est_action_date = date("d/m/Y");
        }
        if (!isset($process_type)) {
            $process_type = $data_form['process_type'];
        }
        if (!isset($document_no)) {
            $document_no = "";
        }
        if (!isset($action_date)) {
            $action_date = "";
        }
        if (!isset($remark)) {
            $remark = "";
        }
        if (!isset($assigned_id)) {
            $assigned_id = "";
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
        <?php
        if (isset($renter_id)) {
            echo form_hidden('renter_id', $renter_id);
        }

         if ((!isset($is_urgent)) || ($is_urgent != 'Y')) {
                $is_urgent = false;
            } else {
                $is_urgent = true;
            }
        ?>
        <?php echo form_hidden('process_id', $process_id); ?>
        <?php echo form_hidden('present_state', $present_state); ?>
        <?php echo form_hidden('user_id', $user_id); ?>
        <?php echo form_hidden('process_type', $process_type); ?>
        <?php echo form_hidden('owner_id', $owner_id); ?>
        <input type="hidden" name="token" value="<?php echo $token; ?>" />
        <fieldset class="well">
            <legend>&nbsp;&nbsp;<b>Change Product Status</b>&nbsp;&nbsp;</legend>
            <table width="98%">
                <tr>
                    <td align="right">Est. Action Date</td>
                    <td align="left"><?php echo form_input('est_action_date', $est_action_date, 'id="est_action_date" placeholder="Estimate Re-Location Date" class="required" readonly="readyonly" '); ?></td>
                    <td align="right">Action Date</td>
                    <td align="left"><?php echo form_input('action_date', $action_date, 'id="action_date" placeholder="Putaway Date"  readonly="readyonly" '); ?></td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>
                <tr>
                    <td align="right">Document No.</td>
                    <td align="left"><?php echo form_input('document_no', $document_no, 'id="document_no" placeholder="Auto Generate Document" disabled style="text-transform: uppercase"'); ?></td>
                    <td align="right">Worker Name</td>
                    <td align="left"><?php echo form_dropdown('assigned_id', $assign_list, $assigned_id, " class='required' "); ?></td>
                    <td align="right"></td>
                    <td align="left"></td>
                </tr>
                <tr valign="center">
                    <td align="right">Remark</td>
                    <td align="left" colspan="2">
                        <TEXTAREA ID="remark" NAME="remark" ROWS="2" COLS="4" style="width:80%" placeholder="Remark..."><?php echo trim($remark); ?></TEXTAREA>
			</td>
			<td align="left">
                        <!--//add for ISSUE 3312 : by kik : 20140120-->
                        <?php echo form_checkbox(array('name' => 'is_urgent', 'id' => 'is_urgent'), ACTIVE, $is_urgent); ?>&nbsp;<font color="red" ><b>Urgent</b> </font>
                        </td>
                        <td align="left"></td>
			<td align="left"></td>
		  </tr>
	  </table>
	</fieldset>
    </form>

    <form class="" method="POST" action="" id="form_flow_id" name="form_flow_id" target='_blank'>
        <?php if(@$this->settings['show_column_change_product_status_job'] and !empty($flow_id)):?>
            <fieldset>
            <legend>Show Column Report</legend>
                <div id='div_change_product_status_job' style='text-align: center;font-size: 11px;margin-bottom: 5px;'>
                    <?php echo form_checkbox('change_product_status_job[no]', TRUE, array_key_exists('no', $show_column_change_product_status_job), 'id="change_product_status_job_no"'); echo form_label('No.', 'change_product_status_job_no'); ?>
                    <?php echo form_checkbox('change_product_status_job[product_code]', TRUE, array_key_exists('product_code', $show_column_change_product_status_job), 'id="change_product_status_job_product_code"'); echo form_label('Code', 'change_product_status_job_product_code'); ?>
                    <?php echo form_checkbox('change_product_status_job[product_name]', TRUE, array_key_exists('product_name', $show_column_change_product_status_job), 'id="change_product_status_job_product_name"'); echo form_label('Name', 'change_product_status_job_product_name'); ?>
                    <?php echo form_checkbox('change_product_status_job[product_status]', TRUE, array_key_exists('product_status', $show_column_change_product_status_job), 'id="change_product_status_job_product_status"'); echo form_label('Status', 'change_product_status_job_product_status'); ?>
                    <?php echo form_checkbox('change_product_status_job[product_sub_status]', TRUE, array_key_exists('product_sub_status', $show_column_change_product_status_job), 'id="change_product_status_job_product_sub_status"'); echo form_label('Sub Status', 'change_product_status_job_product_sub_status'); ?>
                    <?php echo form_checkbox('change_product_status_job[lot]', TRUE, array_key_exists('lot', $show_column_change_product_status_job), 'id="change_product_status_job_lot"'); echo form_label('Lot', 'change_product_status_job_lot'); ?>
                    <?php echo form_checkbox('change_product_status_job[serial]', TRUE, array_key_exists('serial', $show_column_change_product_status_job), 'id="change_product_status_job_serial"'); echo form_label('Serial', 'change_product_status_job_serial'); ?>
                    <?php echo form_checkbox('change_product_status_job[move_qty]', TRUE, array_key_exists('move_qty', $show_column_change_product_status_job), 'id="change_product_status_job_move_qty"'); echo form_label('Move QTY', 'change_product_status_job_move_qty'); ?>
                    <?php echo form_checkbox('change_product_status_job[confirm_qty]', TRUE, array_key_exists('confirm_qty', $show_column_change_product_status_job), 'id="change_product_status_job_confirm_qty"'); echo form_label('Confirm QTY', 'change_product_status_job_confirm_qty'); ?>

                    <?php
                        if($this->settings['price_per_unit'] == TRUE):
                            echo form_checkbox('change_product_status_job[price_per_unit]', TRUE, array_key_exists('price_per_unit', $show_column_change_product_status_job), 'id="change_product_status_job_price_per_unit"'); echo form_label('Price Per Unit', 'change_product_status_job_price_per_unit');
                            echo form_checkbox('change_product_status_job[unit_price]', TRUE, array_key_exists('unit_price', $show_column_change_product_status_job), 'id="change_product_status_job_unit_price"'); echo form_label('Unit Price', 'change_product_status_job_unit_price');
                            echo form_checkbox('change_product_status_job[all_price]', TRUE, array_key_exists('all_price', $show_column_change_product_status_job), 'id="change_product_status_job_all_price"'); echo form_label('All Price', 'change_product_status_job_all_price');
                        endif;
                    ?>

                    <?php echo form_checkbox('change_product_status_job[location_from]', TRUE, array_key_exists('location_from', $show_column_change_product_status_job), 'id="change_product_status_job_location_from"'); echo form_label('Location From', 'change_product_status_job_location_from'); ?>
                    <?php echo form_checkbox('change_product_status_job[suggest_location]', TRUE, array_key_exists('suggest_location', $show_column_change_product_status_job), 'id="change_product_status_job_suggest_location"'); echo form_label('Suggest Location', 'change_product_status_job_suggest_location'); ?>
                    <?php echo form_checkbox('change_product_status_job[actual_location]', TRUE, array_key_exists('actual_location', $show_column_change_product_status_job), 'id="change_product_status_job_actual_location"'); echo form_label('Actual Location', 'change_product_status_job_actual_location'); ?>

                    <?php
                        if($this->settings['build_pallet'] == TRUE):
                            echo form_checkbox('change_product_status_job[pallet_code]', TRUE, array_key_exists('pallet_code', $show_column_change_product_status_job), 'id="change_product_status_job_pallet_code"'); echo form_label('Pallet Code', 'change_product_status_job_pallet_code');
                        endif;
                    ?>

                    <?php echo form_checkbox('change_product_status_job[remark]', TRUE, array_key_exists('remark', $show_column_change_product_status_job), 'id="change_product_status_job_remark"'); echo form_label('Remark', 'change_product_status_job_remark'); ?>

                </div>
            </fieldset>
        <?php endif; ?>
        <?php echo form_hidden('flow_id', $flow_id); ?>
        <?php echo form_hidden('showfooter', 'show'); ?>
    </form>

    <fieldset class="well">
    <legend>&nbsp;Order Detail&nbsp;</legend>
    <table width="100%">
        <tr align="center" >
            <td align="center" colspan="6" id="showDataTable" >
                <table width ="100%"  align="center" cellpadding="0" cellspacing="0" border="0" class="display" id="showProductTable" >
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
                    _lang('change_qty'),
                    _lang('confirm_qty'),
                    _lang('unit'),
                    _lang('price_per_unit'),
                    _lang('unit_price'),
                    _lang('all_price'),
                    _lang('from_location'),
                    _lang('suggest_location'),
                    _lang('actual_location'),
                    _lang('pallet_code'),
                    _lang('remark'),
                    "Product_Id",
                    "Product_Status",
                    "Product_Sub_Status",
                    "Unit_Id",
                    "Item_Id",
                    "Suggest_Location_Id",
                    "Actual_Location_Id",
                    "Old_Location_Id",
                    "Unit Price ID",
                    "DP_Type_Pallet"
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
                $sum_Reserv_Qty = 0;   //add by kik : 04-11-2013
                $sum_Confirm_Qty = 0;   //add by kik : 04-11-2013
                $sumPriceUnit = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมดต่อหน่วย
                $sumPrice = 0; //ADD BY POR 2014-01-16 ราคารวมทั้งหมด
                if (isset($order_detail)) {
                    $str_body = "";
                    $count_rows = 1;

                    foreach ($order_detail as $order_column) {
//                                    $str_body .= "<tr title='" . $order_column->Full_Product_Name . "' >";
                        $str_body .= "<tr>"; // Edit by Ton! 20131127
                        $str_body .= "<td>" . $count_rows . "</td>";
                        $str_body .= "<td>" . $order_column->Product_Code . "</td>";
//                                    $str_body .= "<td title=\"" . htmlspecialchars($order_column->Full_Product_Name, ENT_QUOTES) . "\">" . $order_column->Product_Name . "</td>";
                        $str_body .= "<td title=\"" . str_replace('"','&quot;',$order_column->Full_Product_Name) . "\">" . $order_column->Product_Name . "</td>"; // Edit by Ton! 20140114
                        $str_body .= "<td>" . $order_column->Status_Value . "</td>";
                        $str_body .= "<td>" . $order_column->Sub_Status_Value . "</td>";
                        $str_body .= "<td>" . $order_column->Product_Lot . "</td>";
                        $str_body .= "<td>" . $order_column->Product_Serial . "</td>";
                        $str_body .= "<td>" . $order_column->Product_Mfd . "</td>";
                        $str_body .= "<td>" . $order_column->Product_Exp . "</td>";
                        $str_body .= "<td>" . set_number_format($order_column->Reserv_Qty) . "</td>";
                        $str_body .= "<td>" . set_number_format($order_column->Confirm_Qty) . "</td>";
                        $str_body .= "<td>" . $order_column->Unit_Value . "</td>";
                        //ADD BY POR 2014-01-15 เพิ่ม column เกี่ยวกับ price
                            $str_body .= "<td>" . set_number_format($order_column->Price_Per_Unit) . "</td>";
                            $str_body .= "<td>" . $order_column->unit_price . "</td>";
                            $str_body .= "<td>" . set_number_format($order_column->All_Price) . "</td>";
                        //END ADD
                        if ($order_column->Old_Location == "") {
                            $order_column->Old_Location = $order_column->Actual_Location;
                        }
                        $icon_old_loc = '<a ONCLICK="showProdInLoc(\'' . $order_column->Old_Location_Id . '\',\'' . $order_column->Old_Location . '\')">' . img("css/images/icons/view.png") . '</a>';
                        $icon_suggest_loc = '<a ONCLICK="showProdInLoc(\'' . $order_column->Suggest_Location_Id . '\',\'' . $order_column->Suggest_Location . '\')">' . img("css/images/icons/view.png") . '</a>';

                        $str_body .= "<td>" . $order_column->Old_Location . " " . $icon_old_loc . "</td>";
                        $str_body .= "<td>" . $order_column->Suggest_Location . " " . $icon_suggest_loc . "</td>";
//                                $str_body .= "<td>" . ($order_column->Actual_Location!=''?$order_column->Actual_Location:'Edit...') . "</td>";
                        $str_body .= "<td>" . $order_column->Actual_Location . "</td>";
                        $str_body .= "<td>" . $order_column->Pallet_Code . "</td>";
                        $str_body .= "<td>" . $order_column->Remark . "</td>";
                        $str_body .= "<td>" . $order_column->Product_Id . "</td>";
                        $str_body .= "<td>" . $order_column->Status_Code . "</td>";
                        $str_body .= "<td>" . $order_column->Sub_Status_Code . "</td>";
                        $str_body .= "<td>" . $order_column->Unit_Id . "</td>";
                        $str_body .= "<td>" . $order_column->Item_Id . "</td>";
                        $str_body .= "<td>" . $order_column->Suggest_Location_Id . "</td>";
                        $str_body .= "<td>" . $order_column->Actual_Location_Id . "</td>";
                        $str_body .= "<td>" . $order_column->Old_Location_Id . "</td>";
                        $str_body .= "<td>" . $order_column->Unit_Price_Id . "</td>"; //ADD BY POR 2014-01-16 เพิ่ม ID ของ unit price
                        $str_body .= "<td>" . $order_column->DP_Type_Pallet . "</td>"; //add by kik : 20140221
                        $str_body .= "</tr>";
                        $count_rows++;
                        $sum_Reserv_Qty+=$order_column->Reserv_Qty;        //add by kik    :   04-11-2013
                        $sum_Confirm_Qty+=$order_column->Confirm_Qty;        //add by kik    :   04-11-2013

                        //ADD BY POR 2014-01-13 รวมราคาต่อหน่วยและราคารวม
                        $sumPriceUnit+=$order_column->Price_Per_Unit;
                        $sumPrice+=$order_column->All_Price;
                        //END ADD
                    }
                    echo $str_body;
                }
                ?>
                </tbody>
                    <!-- show total qty : by kik : 29-10-2013-->
                    <tfoot>
                       <tr>
                             <th colspan='9' class ='ui-state-default indent' style='text-align: center;'><b>Total</b></th>
                             <th  class ='ui-state-default indent' style='text-align: right;'><?php echo set_number_format($sum_Reserv_Qty); ?></th>
                             <th  class ='ui-state-default indent' style='text-align: right;'><span  id="sum_recieve_qty"><?php echo set_number_format($sum_Confirm_Qty); ?></span></th>
                             <th></th>
                             <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_price_unit"><?php echo set_number_format($sumPriceUnit); ?></span></th>
                             <th></th>
                             <th class ='ui-state-default indent' style='text-align: right;'><span id="sum_all_price"><?php echo set_number_format($sumPrice); ?></span></th>
                             <th colspan='15' class ='ui-state-default indent' ></th>
                        </tr>
                    </tfoot>
                    <!-- end show total qty : by kik : 29-10-2013-->
                </table>
            </td>
        </tr>
    </table>
    </fieldset>
</div>
<!-- Modal -->
<div style="min-height:500px;padding:5px 10px;" id="boxDetail" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">X</button>
        <h3 id="myModalLabel">List Product In Location</h3>
        <input  value="" type="hidden" name="prdModalval" id="prdModalval" >
    </div>
    <div class="modal-body"><!-- working area--></div>
    <div class="modal-footer"></div>
</div>

<!--Add By Akkarapol, 08/10/2013, เพิ่ม div ขึ้นมาเพื่อรองรับ autoComplete ที่จะ append list เข้ามาแสดงที่นี่-->
<div id="suggestion_div">
	<div class="suggestionList" id="suggestionLocationList"></div>
</div>
<!--END Add By Akkarapol, 08/10/2013, เพิ่ม div ขึ้นมาเพื่อรองรับ autoComplete ที่จะ append list เข้ามาแสดงที่นี่-->

<!--call element Show modal message alert modal : add by kik : 06-03-2014-->
<?php $this->load->view('element_modal_message_alert'); ?>